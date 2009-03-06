<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

// We use the PEAR WebDAV server class
require 'HTTP/WebDAV/Server.php';

// The PATH_INFO needs to be provided so that creates will work
$_SERVER['PATH_INFO'] = $_MIDCOM->context->uri;
$_SERVER['SCRIPT_NAME'] = $_MIDCOM->context->prefix;

/**
 * WebDAV server for MidCOM 3
 *
 * @package midcom_core
 */
class midcom_core_helpers_webdav extends HTTP_WebDAV_Server
{
    private $controller = null;
    private $route_id = '';
    private $action_method = '';
    private $action_arguments = array();
    private $locks = array();
    
    public function __construct($controller)
    {
        $this->controller = $controller;
        parent::HTTP_WebDAV_Server();
    }

    /**
     * Serve a WebDAV request
     *
     * @access public
     */
    public function serve($route_id, $action_method, $action_arguments) 
    {
        $this->route_id = $route_id;
        $this->action_method = $action_method;
        $this->action_arguments = $action_arguments;
    
        // special treatment for litmus compliance test
        // reply on its identifier header
        // not needed for the test itself but eases debugging
        foreach(apache_request_headers() as $key => $value) 
        {
            if (stristr($key, 'litmus'))
            {
                error_log("Litmus test {$value}");
                header("X-Litmus-reply: {$value}");
            }
        }

        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "\n\n=================================================");
        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Serving {$_SERVER['REQUEST_METHOD']} request for {$_SERVER['REQUEST_URI']}");
        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Controller: " . get_class($this->controller) . ", action: {$this->action_method}");
        
        header("X-Dav-Method: {$_SERVER['REQUEST_METHOD']}");
        
        // let the base class do all the work
        parent::ServeRequest();
        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Path was: {$this->path}");
        die();
    }

    /**
     * OPTIONS method handler
     *
     * The OPTIONS method handler creates a valid OPTIONS reply
     * including Dav: and Allowed: heaers
     * based on the route configuration
     *
     * @param  void
     * @return void
     */
    function http_OPTIONS() 
    {
        // Microsoft clients default to the Frontpage protocol 
        // unless we tell them to use WebDAV
        header("MS-Author-Via: DAV");

        // tell clients what we found
        $this->http_status("200 OK");
        
        // We support DAV levels 1 & 2
        // header("DAV: 1, 2"); TODO: Re-enable when we support locks
        header("DAV: 1, 2");
        
        header("Content-length: 0");
    }

    /**
     * PROPFIND method handler
     *
     * @param  array  general parameter passing array
     * @param  array  return array for file properties
     * @return bool   true on success
     */
    function PROPFIND(&$options, &$files) 
    {
        $this->filename_check();

        $_MIDCOM->authorization->require_user();

        // Run the controller
        $controller = $this->controller;
        $action_method = $this->action_method;
        $data = array();
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        
        if (!isset($data['children']))
        {
            // Controller did not return children
            $data['children'] = $this->get_node_children($_MIDCOM->context->page);
        }
        
        if (empty($data['children']))
        {
            return false;
        }
        
        // Convert children to PROPFIND elements
        $this->children_to_files($data['children'], &$files);
        
        return true;
    }

    private function children_to_files($children, &$files)
    {
        $files['files'] = array();

        foreach ($children as $child)
        {
            $child_props = array
            (
                'props' => array(),
                'path'  => $child['uri'],
            );
            $child_props['props'][] = $this->mkprop('displayname', $child['title']);
            $child_props['props'][] = $this->mkprop('getcontenttype', $child['mimetype']);
            
            if (isset($child['resource']))
            {
                $child_props['props'][] = $this->mkprop('resourcetype', $child['resource']);
            }

            if (isset($child['size']))
            {
                $child_props['props'][] = $this->mkprop('getcontentlength', $child['size']);
            }

            if (isset($child['revised']))
            {
                $child_props['props'][] = $this->mkprop('getlastmodified', strtotime($child['revised']));
            }

            $files['files'][] = $child_props;
        }
    }

    private function get_node_children(midgard_page $node)
    {
        // Load children for PROPFIND purposes
        $children = array();
        $mc = midgard_page::new_collector('up', $node->id);
        $mc->set_key_property('name');
        $mc->add_value_property('title');
        $mc->execute(); 
        $pages = $mc->list_keys();
        foreach ($pages as $name => $array)
        {
            if (empty($name))
            {
                continue;
            }
            $children[] = array
            (
                'uri'      => "{$_MIDCOM->context->prefix}{$name}/", // FIXME: dispatcher::generate_url
                'title'    => $mc->get_subkey($name, 'title'),
                'mimetype' => 'httpd/unix-directory',
                'resource' => 'collection',
            );
        }
        
        if ($_MIDCOM->context->page->id == $_MIDCOM->context->host->root)
        {
            // Additional "special" URLs
            $children[] = array
            (
                'uri'      => "{$_MIDCOM->context->prefix}__snippets/", // FIXME: dispatcher::generate_url
                'title'    => 'Code Snippets',
                'mimetype' => 'httpd/unix-directory',
                'resource' => 'collection',
            );
            $children[] = array
            (
                'uri'      => "{$_MIDCOM->context->prefix}__styles/", // FIXME: dispatcher::generate_url
                'title'    => 'Style Templates',
                'mimetype' => 'httpd/unix-directory',
                'resource' => 'collection',
            );
        }

        return $children;
    }

    /**
     * Check filename against some stupidity
     */
    private function filename_check($filename = null)
    {
        if (is_null($filename))
        {
            if (!isset($this->action_arguments['variable_arguments']))
            {
                return;
            }
            
            $filename = $this->action_arguments['variable_arguments'][count($this->action_arguments['variable_arguments']) - 1];
        }
        if (   $filename == '.DS_Store'
            || substr($filename, 0, 2) == '._')
        {
            $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Raising 404 for {$filename} because of filename sanity rules");
            throw new midcom_exception_notfound("OS X DotFiles not allowed");
        }
    }

    /**
     * GET method handler
     * 
     * @param  array  parameter passing array
     * @return bool   true on success
     */
    function GET(&$options) 
    {
        $this->filename_check();

        $_MIDCOM->authorization->require_user();

        // Run the controller
        $controller = $this->controller;
        $action_method = $this->action_method;
        $data =& $options;
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        
        return true;
    }

    /**
     * PUT method handler
     * 
     * @param  array  parameter passing array
     * @return bool   true on success
     */
    function PUT(&$options) 
    {
        $this->filename_check();

        $_MIDCOM->authorization->require_user();

        // Run the controller
        $controller = $this->controller;
        $action_method = $this->action_method;
        $data =& $options;
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        
        return true;
    }

    /**
     * MKCOL method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    public function MKCOL($options)
    {
        // Run the controller
        $controller = $this->controller;
        $action_method = $this->action_method;
        $data = array();
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        
        return '201 Created';
    }

    /**
     * MOVE method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function MOVE($options) 
    {
        // Run the controller
        $controller = $this->controller;
        $action_method = $this->action_method;
        $data =& $options;
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        
        return true;
    }

    /**
     * COPY method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function COPY($options) 
    {
        // Run the controller
        $controller = $this->controller;
        $action_method = $this->action_method;
        $data =& $options;
        $controller->$action_method($this->route_id, $data, $this->action_arguments);
        
        return true;
    }

    /**
     * DELETE method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function DELETE($options) 
    {
        // Run the controller
        $controller = $this->controller;
        $action_method = $this->action_method;
        $data =& $options;
        $controller->$action_method($this->route_id, $data, $this->action_arguments);

        return "204 No Content";
    }


    /**
     * LOCK method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function LOCK(&$options) 
    {
        $options['timeout'] = time() + $_MIDCOM->configuration->get('metadata_lock_timeout');

        // Run the controller
        $controller = $this->controller;
        $action_method = str_replace('action_', 'get_object_', $this->action_method);
        
        if (!method_exists($controller, $action_method))
        {
            throw new midcom_exception_httperror("Locking not allowed", 405);
        }
        
        $data =& $options;
        $object = $controller->$action_method($this->route_id, $data, $this->action_arguments);
        if (!$object)
        {
            throw new midcom_exception_notfound("No lockable objects");
        }

        $shared = false;
        if ($options['scope'] == 'shared')
        {
            $shared = true;
        }
        
        if (is_null($object))
        {
            throw new midcom_exception_notfound("Not found");
        }
        
        if (midcom_core_helpers_metadata::is_locked($object))
        {
            $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Object is locked by another user");
            return "423 Locked";
        }

        midcom_core_helpers_metadata::lock($object, $shared, $options['locktoken']);
        
        return "200 OK";
    }
    
    /**
     * UNLOCK method handler
     *
     * @param  array  general parameter passing array
     * @return bool   true on success
     */
    function UNLOCK(&$options) 
    {
        // Run the controller
        $controller = $this->controller;
        $action_method = str_replace('action_', 'get_object_', $this->action_method);
        
        if (!method_exists($controller, $action_method))
        {
            throw new midcom_exception_httperror("Locking not allowed", 405);
        }
        
        $data =& $options;
        $object = $controller->$action_method($this->route_id, $data, $this->action_arguments);
        if (!$object)
        {
            throw new midcom_exception_notfound("No lockable objects");
        }
        
        if (midcom_core_helpers_metadata::is_locked($object ))
        {
            $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Object is locked by another user {$object->metadata->locker}");
            return "423 Locked";
        }

        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Unlocking");
        midcom_core_helpers_metadata::unlock($object);

        return "200 OK";
    }

    /**
     * checkLock() helper
     *
     * @param  string resource path to check for locks
     * @return bool   true on success
     */
    function checkLock($path) 
    {
        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "CHECKLOCK: {$path}");
        if (isset($this->locks[$path]))
        {
            return $this->locks[$path];
        }
        
        try
        {
            $this->filename_check(basename($path));
        }
        catch (Exception $e)
        {
            // Don't bother checking these types of files for locks
            $this->locks[$path] = false;
            return $this->locks[$path];
        }

        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Resolving {$path} for locks");
        $resolv = new midcom_core_helpers_resolver($path);
        try
        {
            $resolution = $resolv->resolve();
        }
        catch (midgard_error_exception $e)
        {
            $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, 'Resolver got "' . $e->getMessage() . '"');
        }

        if (!in_array('PROPFIND', $resolution['route']['allowed_methods']))
        {
            // No PROPFIND supported, so don't supply lock information either
            $this->locks[$path] = false;
            return $this->locks[$path];
        }

        $controller_class = $resolution['route']['controller'];
        $action_method = "get_object_{$resolution['route']['action']}";

        if (!method_exists($controller_class, $action_method))
        {
            $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "{$controller_class} doesn't support {$action_method}");
            $this->locks[$path] = false;
            return $this->locks[$path];
        }

        $controller = new $controller_class($_MIDCOM->context->component_instance);
        $controller->dispatcher = $_MIDCOM->dispatcher;

        $data = array();
        $object = $controller->$action_method($resolution['route_id'], $data, $resolution['action_arguments']);

        if (!$object)
        {
            $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "No object from {$controller_class} {$action_method}");
            $this->locks[$path] = false;
            return $this->locks[$path];
        }
        

        if (!midcom_core_helpers_metadata::is_locked($object, false))
        {
            $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, "Not locked, locked = {$object->metadata->locked}, locker = {$object->metadata->locker}");
            $this->locks[$path] = false;
            return $this->locks[$path];
        }

        // Populate lock info from metadata
        $lock = array
        (
            'type' => 'write',
            'scope' => 'shared',
            'depth' => 0,
            'owner' => $object->metadata->locker,
            'created' => strtotime($object->metadata->locked  . ' GMT'),
            'modified' => strtotime($object->metadata->locked . ' GMT'),
            'expires' => strtotime($object->metadata->locked . ' GMT') + $_MIDCOM->configuration->get('metadata_lock_timeout') * 60,
        );
        
        if ($object->metadata->locker)
        {
            $lock['scope'] = 'exclusive';
        }
        
        $lock_token = $object->parameter('midcom_core_helper_metadata', 'lock_token');
        if ($lock_token)
        {
            $lock['token'] = $lock_token;
        }
        
        $_MIDCOM->log(__CLASS__ . '::' . __FUNCTION__, serialize($lock));

        $this->locks[$path] = $lock;
        return $this->locks[$path];
    }

    /**
     * Handle HTTP Basic authentication using MidCOM's authentication service
     *
     * @access private
     * @param  string  HTTP Authentication type (Basic, Digest, ...)
     * @param  string  Username
     * @param  string  Password
     * @return bool    true on successful authentication
     */
    function checkAuth($type, $username, $password)
    {
        if (!$_MIDCOM->authentication->is_user())
        {
            if (!$_MIDCOM->authentication->login($username, $password))
            {
                return false;
            }
        }
        return true;
    }
}
?>