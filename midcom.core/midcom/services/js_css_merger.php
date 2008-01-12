<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:tmp.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package midcom.services
 */
class midcom_services_js_css_merger extends midcom_baseclasses_core_object
{
    /**
     * local document root of the website
     *
     * we try to read this from environment if possible
     */
    var $documentroot = false;

    /**
     * maximum time unaccessed time for cache_id before it's garbage collected
     */
    var $max_unaccessed = 7200;

    /**
     * array of callbacks to send merged CSS to before storing
     *
     * must take 3 arguments (the css content, uri path and local path) and return new value for content (or false in critical failure)
     */
    var $css_plugins = array
    (
        array ('midcom_services_js_css_merger', 'rewrite_url_references'),
        array ('midcom_services_js_css_merger', 'remove_cstyle_comments'),
        array ('midcom_services_js_css_merger', 'minimize_whitespace'),
    );

    /**
     * array of callbacks to send merged JS to before storing
     *
     * must take 3 arguments (the JS content, uri path and local path) and return new value for content (or false in critical failure)
     */
    var $js_plugins = array
    (
        array ('midcom_services_js_css_merger', 'remove_cstyle_comments'),
    );

    /**
     * Javascript files to merge, values are url paths
     */
    var $_jsfiles = array();

    /**
     * CSS files to merge, multi-dimensional first dimension is the 'media' property
     */
    var $_cssfiles = array();

    /**
     * Cache can_merge results here
     */
    var $_can_merge_cache = array();

    /**
     * Cache of local paths, keyed by given path
     */
    var $_resolved_paths = array();

    var $_jsheaders_printed = false;
    var $_cssheaders_printed = false;

    /**
     * Constructor, sets default value and test memcached
     */
    function midcom_services_js_css_merger()
    {
        parent::midcom_baseclasses_core_object();
        $this->documentroot = @getenv('DOCUMENT_ROOT');
        // We check this key later
        $_MIDCOM->cache->memcache->put('jscss_merged', 'is_up', true);
    }

    function print_jsheaders()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called');
        if ($this->_jsheaders_printed)
        {
            $GLOBALS['midcom_debugger']->print_function_stack('subsequent call to print_jsheaders from');
            debug_add('JS headers already printed', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_jsheaders_printed = true;
        $paths =& $this->_jsfiles;
        if (empty($paths))
        {
            debug_add('$this->_jsfiles is empty, returning early');
            $GLOBALS['midcom_debugger']->print_function_stack('called from');
            debug_pop();
            return true;
        }
        $cache_id = $this->calculate_cache_id_and_merge($paths, 'js_merge');
        if (empty($cache_id))
        {
            debug_add("Could not get cache id from calculate_cache_id_and_merge(\$paths, 'css_merge')", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $url = "{$_MIDGARD['self']}midcom-servejscsscache-js/{$cache_id}.js";
        echo '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
        $this->_jsheaders_printed = true;
        debug_pop();
        return true;
    }

    function remove_cstyle_comments(&$merged, $path, $local_path)
    {
        return preg_replace('%/\*.*?\*/%', '', $merged);
    }

    function minimize_whitespace(&$merged, $path, $local_path)
    {
        return preg_replace('/\s+/', ' ', $merged);
    }

    function rewrite_url_references(&$merged, $path, $local_path)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $path_dir = dirname($path);
        $searches = array();
        $replaces = array();
        $regexp_url = "/url\s*\(([\"'´])?(((https?|ftp):\/\/)?(.*?))\\1?\)/i";
        preg_match_all($regexp_url, $merged, $matches_url);
        debug_print_r ('$matches_url: ', $matches_url);
        $tmparr = array();
        $tmparr['whole']    = $matches_url[0];
        $tmparr['uri']      = $matches_url[2];
        $tmparr['proto']    = $matches_url[3];
        $tmparr['location'] = $matches_url[5];
        foreach ($tmparr['whole'] as $k => $search)
        {
            if (!empty($tmparr['proto'][$k]))
            {
                // fully qualified uri, don't bother rewriting
                continue;
            }
            if (substr($tmparr['location'][$k], 0, 1) == '/')
            {
                // uri relative from root, no need to rewrite
                continue;
            }
            $replace = "url('{$path_dir}/{$tmparr['location'][$k]}')";
            $searches[] = $search;
            $replaces[] = $replace;
        }
        debug_print_r('$searches: ', $searches);
        debug_print_r('$replaces: ', $replaces);
        $merged = str_replace($searches, $replaces, $merged);
        debug_pop();
        return $merged;
    }

    function calculate_cache_id_and_merge(&$paths, $method)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called, method={$method}");
        if (!is_callable(array($this, $method)))
        {
            debug_add("\$this->$method() is not callable", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $cache_id_and_mtimes = $this->_calculate_cache_id($paths);
        if (!is_array($cache_id_and_mtimes))
        {
            debug_add('_calculate_cache_id did not return array', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        list ($cache_id, $mtimes) = $cache_id_and_mtimes;
        /* TODO: Check for client no-cache headers to refresh
        if ()
        {
            $this->remove($cache_id);
        }
        */
        $cache_metadata = $_MIDCOM->cache->memcache->get('jscss_merged', 'cache_metadata');
        if (!is_array($cache_metadata))
        {
            $cache_metadata = array();
        }
        if (!isset($cache_metadata[$cache_id]))
        {
            $merged = $this->$method($paths);
            if ($merged === false)
            {
                debug_add("\$this->{$method}(\$paths) returned false, so do we", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $this->store($cache_id, $merged);
        }
        debug_add("all done, returning '{$cache_id}'");
        debug_pop();
        return $cache_id;
    }

    /**
     * Merges given uri paths
     *
     * @param array $paths array of uri paths, must been given via add_jsfile/add_cssfile previously
     * @return string merged data or false on critical failure
     */
    function merge(&$paths, &$plugins)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $merged = '';
        foreach ($paths as $path)
        {
            if (   !isset($this->_resolved_paths[$path])
                || !is_readable($this->_resolved_paths[$path]))
            {
                // A path has not been resolved! FATAL !!
                debug_add("Could path '{$path}' is not resolved", MIDCOM_LOG_ERROR);
                debug_pop();
                unset($merged);
                return false;
            }
            $local_path =& $this->_resolved_paths[$path];
            debug_add("Merging file '{$local_path}'");
            $content = file_get_contents($local_path);
            if (!$this->_call_plugins($plugins, $content, $path, $local_path))
            {
                return false;
            }
            $merged .= $content . "\n";
            unset($content);
        }
        debug_add('calling generate_cache_id()');
        $this->generate_cache_id($paths);
        debug_pop();
        return $merged;
    }

    /**
     * Merges given uri paths and calls JS post-processing plugins
     *
     * @param array $paths array of uri paths, must been given via add_jsfile previously
     * @return string merged data or false on critical failure
     */
    function js_merge(&$paths)
    {
        return $this->merge($paths, $this->js_plugins);
    }

    /**
     * Merges given uri paths and calls JS post-processing plugins
     *
     * @param array $paths array of uri paths, must been given via add_cssfile previously
     * @return string merged data or false on critical failure
     */
    function css_merge(&$paths)
    {
        return $this->merge($paths, $this->css_plugins);
    }

    function _call_plugins(&$plugins, &$merged, $path, $local_path)
    {
        if (!is_array($plugins))
        {
            return true;
        }
        foreach ($plugins as $callback)
        {
            if (!is_callable($callback))
            {
                continue;
            }
            $merged = call_user_func($callback, $merged, $path, $local_path);
            if ($merged === false)
            {
                return false;
            }
        }
        return true;
    }

    function print_cssheaders()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called');
        if ($this->_cssheaders_printed)
        {
            $GLOBALS['midcom_debugger']->print_function_stack('subsequent call to print_cssheaders from');
            debug_add('CSS headers already printed', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_cssheaders_printed = true;
        foreach ($this->_cssfiles as $media => $paths)
        {
            if (empty($paths))
            {
                continue;
            }
            $cache_id = $this->calculate_cache_id_and_merge($paths, 'css_merge');
            if (empty($cache_id))
            {
                debug_add("Could not get cache id from calculate_cache_id_and_merge(\$paths, 'css_merge')", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $url = "{$_MIDGARD['self']}midcom-servejscsscache-css/{$cache_id}.css";
            echo "<link rel='stylesheet' type='text/css' media='{$media}' href='{$url}' />\n";
        }

        debug_pop();
        return true;
    }


    /**
     * Can we merge this path (practically: is it a local file referred sanely)
     *
     * @return boolean indicating state
     */
    function can_merge($path)
    {
        if (isset($this->_can_merge_cache[$path]))
        {
            return $this->_can_merge_cache[$path];
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called for path {$path}");

        if (!$_MIDCOM->cache->memcache->get('jscss_merged', 'is_up'))
        {
            // We need working memcache to use this feature
            debug_add('memcache seems not to be running');
            debug_pop();
            $this->_can_merge_cache[$path] = false;
            return $this->_can_merge_cache[$path];
        }
        if (strpos($path, '?') !== false)
        {
            debug_add('path contains query string, cannot merge');
            debug_pop();
            return false;
        }

        // strip protocol://<server name> from beginning of path
        $path = preg_replace("#^(.*)?://{$_SERVER['SERVER_NAME']}#", '', $path);
        if (!strpos('/', $path) === 0)
        {
            // Does not start with single slash, ie does not refer to "local" resource
            debug_add("Can't cache uri '{$path}'");
            debug_pop();
            $this->_can_merge_cache[$path] = false;
            return $this->_can_merge_cache[$path];
        }
        $local_path = "{$this->documentroot}{$path}";
        if (!is_readable($local_path))
        {
            debug_add("Mapped file '{$local_path}' is not readable");
            debug_pop();
            // We can't read the file (likely it does not exist but possibly a permissions issue)
            $this->_can_merge_cache[$path] = false;
            return $this->_can_merge_cache[$path];
        }
        // Local path resolved, cache the result and return true
        debug_pop();
        $this->_resolved_paths[$path] = $local_path;
        $this->_can_merge_cache[$path] = true;
        return $this->_can_merge_cache[$path];
    }

    /**
     * add a jsfile to be merged
     *
     * You should check the path with can_merge() first
     *
     * @see midcom_application::add_jsfile
     * @param string $url The URL to the file to-be referenced.
     * @param boolean $prepend Whether to add the JS include to beginning of includes
     * @return boolean indicating success/failure
     */
    function add_jsfile($path, $prepend = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called for path '{$path}'");
        debug_add('disabled until we figure out a way to rewrite include calls (which may contain variable parts...)');
        debug_pop();
        return false;
        if ($this->_jsheaders_printed)
        {
            $GLOBALS['midcom_debugger']->print_function_stack('call to add_jsfile after print_jsheaders from');
            debug_add('JS headers already printed', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   !is_string($path)
            || empty($path))
        {
            debug_add("invalid path '{$path}', aborting early", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (in_array($path, $this->_jsfiles))
        {
            debug_add('already added, returnin true');
            debug_pop();
            return true;
        }
        if (!$this->can_merge($path))
        {
            debug_add("can_merge('{$path}') returned false, so fo we", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        if ($prepend)
        {
            array_unshift($this->_jsfiles, $path);
        }
        else
        {
            $this->_jsfiles[] = $path;
        }
        debug_print_r('$this->_jsfiles now: ', $this->_jsfiles);
        debug_pop();
        return true;
    }

    /**
     * add a CSS file to be merged
     *
     * You should check the path with can_merge() first.
     * Constraints: 'type' attribute must be 'text/css' (or unset), 'rel' attribute
     * must be 'stylesheet' (or unset), 'media' attribute will be honored if set, other
     * attributes will be dropped.
     *
     * @see midcom_application::add_jsfile
     * @param array $attributes Array of attribute => value pairs to be placed in the tag.
     * @return boolean indicating success/failure
     */
    function add_cssfile($attributes = false, $prepend = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('called for attributes: ', $attributes);
        if ($this->_cssheaders_printed)
        {
            $GLOBALS['midcom_debugger']->print_function_stack('call to add_cssfile after print_cssheaders from');
            debug_add('CSS headers already printed', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Sanity checks
        if (empty($attributes))
        {
            debug_add('attributes is empty, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Make sure we have usable path
        if (   !isset($attributes['href'])
            || empty($attributes['href']))
        {
            debug_add('no href set, aborting',  MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $path =& $attributes['href'];

        // We only support text/css stylesheets
        if (   isset($attributes['type'])
            && $attributes['type'] !== 'text/css')
        {
            debug_add('invalid type set, aborting',  MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $attributes['type'] = 'text/css';

        // We only support link rel="stylesheets"
        if (   isset($attributes['rel'])
            && $attributes['rel'] !== 'stylesheet')
        {
            debug_add('invalid rel set, aborting',  MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $attributes['rel'] = 'stylesheet';


        if (isset($attributes['media']))
        {
            $media = $attributes['media'];
        }
        else
        {
            debug_add('media is not set, using "all"');
            $media = 'all';
        }

        debug_add("using media '{$media}' array for futher checks");
        if (!isset($this->_cssfiles[$media]))
        {
            $this->_cssfiles[$media] = array();
        }
        $files_per_media =& $this->_cssfiles[$media];

        if (in_array($path, $files_per_media))
        {
            debug_add('already added, returning success');
            debug_pop();
            return true;
        }
        if (!$this->can_merge($path))
        {
            debug_add("can_merge('{$path}') returned false, so fo we", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        if ($prepend)
        {
            array_unshift($files_per_media, $path);
        }
        else
        {
            $files_per_media[] = $path;
        }
        debug_print_r('$this->_cssfiles now: ', $this->_cssfiles);
        debug_pop();
        return true;
    }

    function _calculate_cache_id(&$paths)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (empty($paths))
        {
            debug_add('$paths is empty, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $cache_id_base = '';
        $mtimes = array();
        foreach ($paths as $path)
        {
            if (   !$this->can_merge($path)
                || !isset($this->_resolved_paths[$path]))
            {
                // A path has not been resolved! FATAL !!
                debug_add("path '{$path}' has not been resolved", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $local_path =& $this->_resolved_paths[$path];
            $stat = @stat($local_path);
            if (!is_array($stat))
            {
                // FATAL: Could not stat file
                debug_add("Could not stat '{$local_path}'", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $last_modified =& $stat[9];
            if (empty($last_modified))
            {
                // FATAL: Could not read last-modified
                debug_add("last_modified is empty ('{$last_modified}')", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $cache_id_base .= "{$local_path}:{$last_modified},";
            $mtimes[$local_path] = $last_modified;
        }
        $cache_id = md5($cache_id_base);
        $ret = array($cache_id, $mtimes);
        debug_print_r('done, returning: ', $ret);
        return $ret;
    }

    function generate_cache_id(&$paths)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$_MIDCOM->cache->memcache->get('jscss_merged', 'is_up'))
        {
            // memcache is not up
            debug_add('memcache seems not to be running', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $cache_metadata = $_MIDCOM->cache->memcache->get('jscss_merged', 'cache_metadata');
        if (!is_array($cache_metadata))
        {
            $cache_metadata = array();
        }

        $cache_id_and_mtimes = $this->_calculate_cache_id($paths);
        if (!is_array($cache_id_and_mtimes))
        {
            debug_add('Could not calculate cache_id', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        list ($cache_id, $mtimes) = $cache_id_and_mtimes;

        if (!isset($cache_metadata[$cache_id]))
        {
            $cache_metadata[$cache_id] = array
            (
                'generated' => time(),
                'mtimes' => $mtimes,
                'last_access' => time(),
            );
            debug_print_r("adding metadata: ",  $cache_metadata[$cache_id]);
            $_MIDCOM->cache->memcache->put('jscss_merged', 'cache_metadata', $cache_metadata);
        }
        debug_pop();
        return $cache_id;
    }

    /**
     * Store data to memcache with given cache id
     *
     * @param string $cache_id key to use in cache, must be generated with generate_cache_id
     * @param string $data data to store in cache
     * @see generate_cache_id()
     */
    function store($cache_id, $data)
    {
        return $_MIDCOM->cache->memcache->put('jscss_merged', $cache_id . '@jscss', $data);
    }

    /**
     * get a cached_id from the cache
     *
     * @param string $cache_id cache id to get
     * @return string value or false on critical failure
     */
    function get($cache_id)
    {
        // Update access time
        $cache_metadata = $_MIDCOM->cache->memcache->get('jscss_merged', 'cache_metadata');
        if (!isset($cache_metadata[$cache_id]))
        {
            // We do not have metadata for this key
            $this->remove($cache_id);
            return false;
        }
        // update last accessed time
        $cache_metadata[$cache_id]['last_access'] = time();
        $_MIDCOM->cache->memcache->put('jscss_merged', 'cache_metadata', $cache_metadata);

        // return value
        return $_MIDCOM->cache->memcache->get('jscss_merged', $cache_id . '@jscss');
    }

    /**
     * go through all keys we have metadata for and check that they are still fresh
     */
    function garbage_collect()
    {
        $cache_metadata = $_MIDCOM->cache->memcache->get('jscss_merged', 'cache_metadata');
        if (!is_array($cache_metadata))
        {
            // Could not read metadata array, is memcache running ??
            return false;
        }
        foreach ($cache_metadata as $cache_id => $metadata)
        {
            if (   !isset($metadata['mtimes'])
                || !is_array($metadata['mtimes']))
            {
                $this->remove($cache_id);
                continue;
            }
            foreach ($metadata['mtimes'] as $local_path => $timestamp)
            {
                if (!is_readable($local_path))
                {
                    $this->remove($cache_id);
                    continue 2;
                }
                $stat = stat($local_path);
                if (!is_array($stat))
                {
                    $this->remove($cache_id);
                    continue 2;
                }
                $last_modified =& $stat[9];
                if ($last_modified != $timestamp)
                {
                    $this->remove($cache_id);
                    continue 2;
                }
            }
            // safety
            if (!isset($metadata['last_access']))
            {
                $metadata['last_access'] = 0;
            }
            // remove stale cache_ids
            $unaccessed = time() - $metadata['last_access'];
            if ($unaccessed > $max_unaccessed)
            {
                $this->remove($cache_id);
                continue;
            }
        }
    }

    /**
     * remove a specific key from the cache
     *
     * @param string $cache_id cache key to remove
     */
    function remove($cache_id)
    {
        $cache_metadata = $_MIDCOM->cache->memcache->get('jscss_merged', 'cache_metadata');
        if (isset($cache_metadata[$cache_id]))
        {
            // remove from metadata if set
            unset($cache_metadata[$cache_id]);
            $_MIDCOM->cache->memcache->put('jscss_merged', 'cache_metadata', $cache_metadata);
        }
        return $_MIDCOM->cache->memcache->invalidate($cache_id . '@jscss');
    }

    /**
     * Serve a "file" from the cache
     *
     * @param string $name must be of format <cached_id>.<js|css>
     */
    function serve($name)
    {
        if (!$_MIDCOM->cache->memcache->get('jscss_merged', 'is_up'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Cache is not running');
            // this will exit()
        }
        list ($cache_id, $extension) = explode('.', $name, 2);
        switch (strtolower($extension))
        {
            case 'css':
                $mimetype = 'text/css';
                break;
            case 'js':
                $mimetype = 'application/javascript';
                break;
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Don't know what to do with extension '{$extension}'");
                // this will exit()
        }
        $cache_metadata = $_MIDCOM->cache->memcache->get('jscss_merged', 'cache_metadata');
        if (!isset($cache_metadata[$cache_id]))
        {
            // We do not have metadata for this key
            $this->remove($cache_id);
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Metadata for key '{$cache_id}' not found in cache");
            // this will exit()
        }
        $last_modified =& $cache_metadata[$cache_id]['generated'];
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($_MIDCOM->cache->content->_check_not_modified($last_modified, $cache_id))
        {
            debug_add('_check_not_modified returned true, finishing up here then');
            if (!headers_sent())
            {
                debug_add('For the weirdest reason headers have not been sent, send again');
                // Doublecheck
                $_MIDCOM->header('HTTP/1.0 304 Not Modified', 304);
                $_MIDCOM->header("ETag: {$cache_id}");
                $_MIDCOM->header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 315360000) . ' GMT');
                $_MIDCOM->header('Cache-Control: public max-age=315360000');
                $_MIDCOM->header('Pragma: public');
            }
            while(@ob_end_flush());
            debug_pop();
            exit();
        }

        $data = $this->get($cache_id);
        if (empty($data))
        {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Key '{$cache_id}' not found in cache");
            // this will exit()
        }

        $_MIDCOM->header("ETag: {$cache_id}");
        $_MIDCOM->cache->content->content_type($mimetype);
        $_MIDCOM->header("Content-Type: {$mimetype}");
        $_MIDCOM->header('Last-Modified: ' . gmdate("D, d M Y H:i:s", $last_modified) . ' GMT');
        $_MIDCOM->header('Content-Length: ' . strlen($data));
        // PONDER: Support ranges ("continue download") somehow ?
        $_MIDCOM->header('Accept-Ranges: none');

        /* We want to cache these so lets override some things
        $_MIDCOM->cache->content->cache_control_headers();
        */
        $_MIDCOM->header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 315360000) . ' GMT');
        $_MIDCOM->header('Cache-Control: public max-age=315360000');
        $_MIDCOM->header('Pragma: public');
        while(@ob_end_flush());

        echo $data;
        unset($data, $mimetype, $last_modified);

        debug_add('data sent, exit()ing so nothing has a chance the mess things up anymore');
        debug_pop();
        exit();
    }
}

// Instantiate the server
$_MIDCOM->jscss = new midcom_services_js_css_merger();

?>