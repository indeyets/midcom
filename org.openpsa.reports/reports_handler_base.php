<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Baseclass for reports handler, provides some common methods
 *
 * @package org.openpsa.projects
 */
class org_openpsa_reports_handler_reports_base extends midcom_baseclasses_components_handler
{
    var $_datamanagers = array();
    var $module = false;


    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_generator_get($handler_id, $args, &$data)
    {
        $this->_set_active_leaf();
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !array_key_exists('org_openpsa_reports_query_data', $_REQUEST)
            || !is_array($_REQUEST['org_openpsa_reports_query_data']))
        {
            debug_add('query data not present or invalid', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // NOTE: This array must be a same format as we get from DM get_array() method
        $this->_request_data['query_data'] = $_REQUEST['org_openpsa_reports_query_data'];
        $this->_request_data['filename'] = 'get';

        $this->_handler_generator_style();

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_generator_get($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_show_generator($handler_id, $data);
        debug_pop();
        return;
    }

    function _initialize_datamanager1($type, $schemadb_snippet)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");
        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type]) {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }

        debug_pop();
        return true;
    }

    function _load_query($identifier, $dm_key)
    {
        $query = new org_openpsa_reports_query($identifier);

        if (!is_object($query))
        {
            return false;
        }

        // Load the project to datamanager
        if (!$this->_datamanagers[$dm_key]->init($query))
        {
            return false;
        }
        return $query;
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $query = new org_openpsa_reports_query();
        $stat = $query->create();
        if ($stat)
        {
            $this->_request_data['query'] = new org_openpsa_reports_query($query->id);
            //Debugging
            $result["storage"] =& $this->_request_data['query'];
            $result["success"] = true;
            return $result;
        }
        return null;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_query_form($handler_id, $args, &$data)
    {
        $this->_set_active_leaf();
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_load_query(null, $this->module);

        if (!$this->_datamanagers[$this->module]->init_creation_mode("newquery",$this,"_creation_dm_callback"))
        {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newquery'.");
            // This will exit
        }

        $this->_request_data['datamanager'] = $this->_datamanagers[$this->module];

        // Process the form
        switch ($this->_datamanagers[$this->module]->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;
            case MIDCOM_DATAMGR_SAVED:
                //Fall-trough intentional
            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['query']->parameter("midcom.helper.datamanager","layout","default");


                // Relocate to report view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                debug_pop();
                $_MIDCOM->relocate($prefix . $this->module . '/' . $this->_request_data['query']->guid() . "/");
                //this will exit

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $_MIDCOM->relocate('');
                // This will exit

            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;

            case MIDCOM_DATAMGR_FAILED:
                //Fall-trough intentional
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanager failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;
        }

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_query_form($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        midcom_show_style("{$this->module}_query_form");

        debug_pop();
        return true;
    }

    function _set_active_leaf()
    {
        // This should be overridden, but we default for 'generator_<module>'
        $this->_component_data['active_leaf'] = "{$this->_topic->id}:generator_{$this->module}";
    }


    function _generator_load_redirect(&$args)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add('Loading query object ' . $args[0]);
        $this->_request_data['query'] = $this->_load_query($args[0], $this->module);
        if ($this->_request_data['query'] === false)
        {
            debug_add('Could not load query', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (   !isset($args[1])
            || empty($args[1]))
        {
            debug_add('Filename part not specified in URL, generating');
            //We do not have filename in URL, generate one and redirect
            /* It seems created is returned as timestamp again
            debug_add("Generating timestamp from {$this->_request_data['query']->created}");
            $timestamp = strtotime($this->_request_data['query']->created);
            */
            $timestamp = $this->_request_data['query']->created;
            if (!$timestamp)
            {
                $timestamp = time();
            }
            $filename = date('Y_m_d', $timestamp);
            if ($this->_request_data['query']->title)
            {
                $filename .= '_' . preg_replace('/[^a-z0-9-]/i', '_', strtolower($this->_request_data['query']->title));
            }
            $filename .= $this->_request_data['query']->extension;
            debug_add('Generated filename: ' . $filename);
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            debug_pop();
            $_MIDCOM->relocate($prefix . $this->module . '/' . $this->_request_data['query']->guid() . '/' . $filename);
            //this will exit
        }
        $this->_request_data['filename'] = $args[1];

        //Get DM schema data to array
        $this->_request_data['query_data'] = $this->_datamanagers[$this->module]->get_array();
        debug_pop();
        return true;
    }

    function _handler_generator_style()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Handle style
        if (empty($this->_request_data['query_data']['style']))
        {
            debug_add('Empty style definition encountered, forcing builtin:basic');
            $this->_request_data['query_data']['style'] = 'builtin:basic';
        }
        if (!preg_match('/^builtin:(.+)/', $this->_request_data['query_data']['style']))
        {
            debug_add("appending '{$this->_request_data['query_data']['style']}' to substyle path");
            $_MIDCOM->substyle_append($this->_request_data['query_data']['style']);
        }

        //TODO: Check if we're inside DL if so do not force mimetype
        if (   !isset($this->_request_data['query_data']['skip_html_headings'])
            || empty($this->_request_data['query_data']['skip_html_headings']))
        {
            //Skip normal style, and force content type based on query data.
            $_MIDCOM->skip_page_style = true;
            debug_add('Forcing content type: ' . $this->_request_data['query_data']['mimetype']);
            $_MIDCOM->cache->content->content_type($this->_request_data['query_data']['mimetype']);
        }

        debug_pop();
    }

    /**
     * Convert midcom acl identifier to array of person ids
     */
    function _expand_resource($resource_id, $ret=array())
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Got resource_id: ' . $resource_id);
        $dba_obj =& $_MIDCOM->auth->get_assignee($resource_id);
        //debug_add("got dba_obj:\n===\n" . sprint_r($dba_obj) . "===\n");
        org_openpsa_reports_handler_reports_base::_verify_cache('users', $this->_request_data);
        switch (get_class($dba_obj))
        {
            case 'midcom_core_group':
            case 'midcom_core_group_midgard':
            case 'midcom_core_group_virtual':
                $members = $dba_obj->list_members();
                if (is_array($members))
                {
                    foreach ($members as $core_user)
                    {
                        $user_obj = $core_user->get_storage();
                        debug_add(sprintf('Adding user %s (id: %s)', $core_user->name, $user_obj->id));
                        $ret[] = $user_obj->id;
                        $this->_request_data['object_cache'][$user_obj->guid] = $user_obj;
                        $this->_request_data['object_cache']['users'][$user_obj->id] =& $this->_request_data['object_cache'][$user_obj->guid];
                    }
                }
            break;
            case 'midcom_core_user':
                $user_obj = $dba_obj->get_storage();
                debug_add(sprintf('Adding user %s (id: %s)', $dba_obj->name, $user_obj->id));
                $ret[] = $user_obj->id;
                $this->_request_data['object_cache'][$user_obj->guid] = $user_obj;
                $this->_request_data['object_cache']['users'][$user_obj->id] =& $this->_request_data['object_cache'][$user_obj->guid];
            break;
            default:
                debug_add('Got unrecognized class for dba_obj: ' . get_class($dba_obj), MIDCOM_LOG_WARN);
            break;
        }
        debug_pop();
        return $ret;
    }

    function _verify_cache($key, &$request_data)
    {
        if (   !array_key_exists('object_cache', $request_data)
            || !is_array($request_data['object_cache']))
        {
            $request_data['object_cache'] = array();
        }
        if ($key !== NULL)
        {
            if (   !array_key_exists($key, $request_data['object_cache'])
                || !is_array($request_data['object_cache'][$key]))
            {
                $request_data['object_cache'][$key] = array();
            }
        }
    }

    function &_get_cache($type, $id, &$request_data)
    {
        org_openpsa_reports_handler_reports_base::_verify_cache($type, $request_data);
        if (!array_key_exists($id, $request_data['object_cache'][$type]))
        {
            switch ($type)
            {
                case 'users':
                    $core_user = new midcom_core_user($id);
                    $obj = $core_user->get_storage();
                break;
                case 'groups':
                    $obj = new org_openpsa_contacts_group($id);
                break;
                default:
                    $method = "_get_cache_obj_{$type}";
                    $classname = __CLASS__;
                    $dummy = new $classname();
                    if (!method_exists($dummy, $method))
                    {
                        // TODO: generate error
                        debug_add("Method '{$method}' not in class '{$classname}'", MIDCOM_LOG_WARN);
                        return false;
                    }
                    //$obj = $this->$method($id);
                    $obj = call_user_func(array($classname, $method), $id);
                break;
            }
            $request_data['object_cache'][$obj->guid] = $obj;
            $request_data['object_cache'][$type][$obj->id] =& $request_data['object_cache'][$obj->guid];
        }
        else
        {
            $obj =& $request_data['object_cache'][$type][$id];
        }
        //debug_add("returning reference to object\n===\n" . sprint_r($request_data['object_cache'][$type][$obj->id]) . "===\n");
        return $request_data['object_cache'][$type][$obj->id];
    }


    function _get_cache_obj_tasks($id)
    {
        return new org_openpsa_projects_task($id);
    }

}

?>