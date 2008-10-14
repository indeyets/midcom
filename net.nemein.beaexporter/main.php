<?php
/**
 * @package net.nemein.beaexporter
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package net.nemein.beaexporter
 */
class net_nemein_beaexporter extends midcom_baseclasses_components_purecode
{
    var $mgd_api = '1.7';
    var $_object = false;
    var $_object_url = false;
    var $_object_old_url = false;
    var $_object_dumpname = false;
    var $_object_html = false;
    var $_dump_dir = false;
    var $_metadata_suffix = false;
    var $_metadata_domain = false;
    var $_clean_files = array(); // List of files we have created, used by the cleanup routines
    var $overwrite_properties = array(); // If we wish to overwrite some properties after they have been normally derived specify key => value
    var $mode = 'single'; // Use multiple to prevent automatic lock removal
    var $_lock_path = false;
    var $_time_format = false;
    var $_metadata_helper = false;
    var $_check_approves = false;

    function __construct()
    {
        $this->_component = 'net.nemein.beaexporter';
        if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
        {
            $this->mgd_api = '1.8';
        }
        parent::__construct();

        $this->_dump_dir = $this->_config->get('dump_directory');
        $this->_metadata_suffix = $this->_config->get('metadata_suffix');
        $this->_metadata_domain = $this->_config->get('metadata_domain');
        $this->_time_format = $this->_config->get('time_format');
        $this->_check_approves = $this->_config->get('check_approves');

        $this->_lock_path = $this->_dump_dir . '/net_nemein_beaexporter.lock';

        setlocale(LC_TIME, 'en_US');

        return true;
    }

    /**
     * Touches a lockfile to keep external scripts at bay while we're writing
     */
    function set_lock()
    {
        touch($this->_lock_path);
    }

    /**
     * Removes the lockfile
     */
    function unset_lock($force = true)
    {
        if (   (   $this->mode == 'multiple'
                || $this->mode == 2)
            && !$force)
        {
            return;
        }
        unlink($this->_lock_path);
    }

    /**
     * Set object's status to not-live and dump a dummy version it
     */
    function deleted($object)
    {
        //PONDER: how to know what the URL used to be ? do we need to have our own repligard-like table ??
        if (($state = net_nemein_beaexporter_state_dba::get_for($object->guid)) === false)
        {
            // Object has never been dumped, we abort with a success code
            return true;
        }
        $this->_object = $object;
        $this->_object_dumpname = $this->_object->guid . '_deleted.html';
        $this->_object_url = $state->targeturl;
        $this->_object_html = "<h1>Object deleted</h1>\n\n<p>Object {$object->guid}, has been deleted from Midgard</p>\n";
        $this->overwrite_properties['status'] = 'not-live';
        $this->overwrite_properties['expires'] = strftime($this->_time_format, time()-3600);
        $this->overwrite_properties['removeFromArchive'] = strftime($this->_time_format, time()-3600);
        $this->set_lock();
        $ret = $this->_dump(false);
        $this->unset_lock(false);
        if ($ret)
        {
            // Update state
            $state->objectaction = 'deleted';
            $stat = $state->update();
            if (!$stat)
            {
                debug_add("Could not update state object #{$state->id}", MIDCOM_LOG_WARN);
            }
        }
        return $ret;
    }

    /**
     * Object has been created, dump it.
     */
    function created($object)
    {
        $this->_object = $object;
        if ($this->_check_approves)
        {
            $this->_metadata_helper =& midcom_helper_metadata::retrieve($this->_object->guid);
            if (!$this->_metadata_helper->is_approved())
            {
                // Object is not approved, abort with success value
                return true;
            }
        }
        $this->set_lock();
        $ret = $this->_dump();
        $this->unset_lock(false);
        if ($ret)
        {
            $stat = net_nemein_beaexporter_state_dba::create_for($this->_object, $this->_object_url, 'created');
            if (!$stat)
            {
                debug_add("Could not create state object for {$this->_object->guid}", MIDCOM_LOG_WARN);
            }
        }
        return $ret;
    }

    /**
     * Object has been updated, dump it.
     */
    function updated($object)
    {
        $this->_object = $object;
        if ($this->_check_approves)
        {
            $this->_metadata_helper =& midcom_helper_metadata::retrieve($this->_object->guid);
            if (!$this->_metadata_helper->is_approved())
            {
                // Object is not approved, abort with success value
                return true;
            }
        }
        $state = net_nemein_beaexporter_state_dba::get_for($this->_object->guid);
        if ($state)
        {
            $this->_object_old_url = $state->targeturl;
        }
        $this->resolve_object_url();
        if (   !empty($this->_object_old_url)
            && $this->_object_old_url != $this->_object_url)
        {
            // Object url has been changed, what do we do ? (mark the old url as deleted)
            /* This should be tested first
            debug_add("Object {$this->_object->guid} URL has changed, 'delete' old url before continuing with the new", MIDCOM_LOG_INFO);
            $handler = new net_nemein_beaexporter();
            $handler->delete($this->_object);
            */
        }
        $this->set_lock();
        $ret = $this->_dump();
        if (!$state)
        {
            $stat = net_nemein_beaexporter_state_dba::create_for($this->_object, $this->_object_url, 'updated');
            if (!$stat)
            {
                debug_add("Could not create state object for {$this->_object->guid}", MIDCOM_LOG_WARN);
            }
        }
        else
        {
            $state->targeturl = $this->_object_url;
            $state->objectaction = 'updated';
            $stat = $state->update();
            if (!$stat)
            {
                debug_add("Could not update state object #{$state->id}", MIDCOM_LOG_WARN);
            }
        }
        $this->unset_lock(false);
        return $ret;
    }

    /**
     * Removes the site prefix (either full or relative) from given url
     */
    function clean_url($url)
    {
        $nav = new midcom_helper_nav();
        $root_node = $nav->get_node($nav->get_root_node());
        if (   !is_array($root_node)
            || !array_key_exists(MIDCOM_NAV_FULLURL, $root_node)
            || empty($root_node[MIDCOM_NAV_FULLURL]))
        {
            // Could not get the full root url for some reason
            return $url;
        }
        if (preg_match("|^{$root_node[MIDCOM_NAV_FULLURL]}|", $url))
        {
            // The fully qualified url has been matched, return removed
            return str_replace($root_node[MIDCOM_NAV_FULLURL], '/', $url);
        }
        if (!preg_match('|.*?://.*?(/.*)$|', $root_node[MIDCOM_NAV_FULLURL], $matches))
        {
            // Cannot determine relative prefix for site, return url as it is
            return $url;
        }
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("matches \n===\n" . org_openpsa_helpers::sprint_r($matches) . "===\n");
        debug_pop();
        */
        return str_replace($matches[1], '/', $url);
    }

    /**
     * Dumps the object and its attachments
     */
    function _dump($dl_render = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   empty($this->_dump_dir)
            || !is_writable($this->_dump_dir))
        {
            debug_add("this->_dump_dir ('{$this->_dump_dir}'), empty or not writable", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   !is_object($this->_object)
            || !$this->_object->guid)
        {
            debug_add('$this->_object is not a valid object', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // TODO: approval handling (remember that deletions cannot be approved...)

        if ($dl_render)
        {
            if (!$this->render_object_html())
            {
                debug_add('Could not render though requested to do so, aborting', MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        if (!$this->_dump_find_attachments())
        {
            debug_add('Error when dumping attachments, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (!$this->_dump_write_html())
        {
            debug_add('Error when writing HTML dump file, aborting', MIDCOM_LOG_ERROR);
            $this->_clean_files();
            debug_pop();
            return false;
        }

        if (!$this->_dump_write_properties())
        {
            debug_add('Error when writing properties dump file, aborting', MIDCOM_LOG_ERROR);
            $this->_clean_files();
            debug_pop();
            return false;
        }

        debug_pop();
        return true;
    }

    /**
     * Cleans the files we have created from the dump directory
     *
     * Used in case of errors
     */
    function _clean_files()
    {
        if (empty($this->_clean_files))
        {
            return;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        foreach ($this->_clean_files as $path => $bool)
        {
            if (!$bool)
            {
                continue;
            }
            debug_add("Removing file {$path}");
            if (!unlink($path))
            {
                debug_add("Error removing file {$path}", MIDCOM_LOG_WARN);
            }
        }
        debug_pop();
        return;
    }

    /**
     * Writes the HTML dump file
     */
    function _dump_write_html()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_dump_write_html_rewrite_links();
        $url_copy = $this->_object_url;
        $this->_fix_index($url_copy);
        if (!$this->_object_dumpname)
        {
            $this->_object_dumpname = $this->_object->guid . '_' . basename($url_copy);
            if (!preg_match('/\.html$/', $this->_object_dumpname))
            {
                // Append .html if (still) missing
                $this->_object_dumpname .= '.html';
            }
        }

        // TODO: Escape special characters in HTML ??

        $path = $this->_dump_dir . '/' . $this->_object_dumpname;
        if (file_exists($path))
        {
            debug_add("file {$path} already exists, removing old one first", MIDCOM_LOG_WARN);
            if (!unlink($path))
            {
                debug_add("Failed to delete existing file {$path}, aborting", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        $fp = fopen($path, 'w');
        if (!$fp)
        {
            debug_add("Could not open file {$path} for writing, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_clean_files[$path] = true;

        if (fwrite($fp, $this->_object_html) === false)
        {
            debug_add("Error while writing to '{$path}', aborting");
            debug_pop();
            return false;
        }
        fclose($fp);

        debug_pop();
        return true;
    }

    /**
     * Search and rewrite internal links
     */
    function _dump_write_html_rewrite_links()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $link_prefix =  $this->_config->get('link_prefix');
        $regex = "/(href)=([\"'�])(.*?)\\2/i";
        debug_add("searching links links with regex: {$regex}");
        if (!preg_match_all($regex, $this->_object_html, $matches))
        {
            debug_add('no matches found, returning');
            debug_pop();
            return true;
        }
        debug_add("matches\n===\n" . org_openpsa_helpers::sprint_r($matches) . "===\n");

        $seen_urls = array();
        foreach ($matches[3] as $k => $url)
        {
            $url_cleaned = $this->clean_url($url); // In case we have internal links as fully qualified in stead of relative, clean those too.
            if (preg_match('|^(.*?)://|', $url_cleaned, $matches_proto))
            {
                debug_add("url '{$url}' is not relative (protocol '{$matches_proto[1]}'), skipping");
                continue;
            }
            if (array_key_exists($url, $seen_urls))
            {
                // Avoid unnecessary str_replaces and attachment dumps (expensive)
                continue;
            }
            $seen_urls[$url] = true;
            $rewritten = $link_prefix . $url_cleaned;
            $this->_fix_index($rewritten);
            debug_add("Replacing '{$url}' with '{$rewritten}'");
            $this->_object_html = str_replace($url, $rewritten, $this->_object_html);
        }
        return true;
    }

    /**
     * Append 'index.html' to url string that ends with slash
     */
    function _fix_index(&$url)
    {
        if (preg_match('|/$|', $url))
        {
            // Url ends in slash, append index.html to be safe
            $url .= 'index.html';
        }
    }

    /**
     * Writes the properties dump file
     */
    function _dump_write_properties()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $metadata_merged = array();
        $this->_fix_index($this->_object_url); // Or do we need to do a copy ??

        // Map midgard default metadata to properties keys
        /*
        $metadata_merged[''] = ;
        */
        $metadata_merged['url'] = $this->_object_url;
        $metadata_merged['status'] = 'live';
        switch (true)
        {
            case (   $this->mgd_api == '1.7'
                  || is_a($this->_object, 'net_nemein_beaexporter_dummyobject')):
                $available = $this->_object->created;
                break;
            case ($this->mgd_api == '1.8'):
                $available = $this->_object->metadata->published;
                break;
        }
        $metadata_merged['available'] = strftime($this->_time_format, $available);
        $metadata_merged['title'] = $this->_object->title;
        /* What is this property actually used for ?
        $metadata_merged['description'] = $this->_object->abstract;
        */
        $author_person = new midcom_db_person($this->_object->author);
        if (is_a($author_person, 'midcom_db_person'))
        {
            $author_string = "{$author_person->firstname} {$author_person->lastname}";
            $author_email = $author_person->email;
        }
        else
        {
            $author_string = 'unknown';
            $author_email = '';
        }
        $metadata_merged['contributor'] = $author_string;
        $metadata_merged['email'] = $author_email;

        // Map MidCOM extended metadata (if we can)
        if (!is_a($this->_object, 'net_nemein_beaexporter_dummyobject'))
        {
            // If not initialized yet do it now
            if (!is_a($this->_metadata_helper, 'midcom_helper_metadata'))
            {
                $this->_metadata_helper =& midcom_helper_metadata::retrieve($this->_object->guid);
            }
            // extra safety
            if (is_a($this->_metadata_helper, 'midcom_helper_metadata'))
            {
                if ($expires = $this->_metadata_helper->get('schedule_end'))
                {
                    $metadata_merged['expires'] = strftime($this->_time_format, $expires);
                }
                // Overwrite the available tag if we have schedule_start set in metadata
                if ($available = $this->_metadata_helper->get('schedule_start'))
                {
                    $metadata_merged['available'] = strftime($this->_time_format, $available);
                }
            }
        }

        // Dump parameter metadata to the same (overwriting existing keys)
        if (   !empty($this->_metadata_domain)
            && method_exists($this->_object, 'list_parameters'))
        {
            // NOTE: DBA parameter API
            $params = $this->_object->list_parameters($this->_metadata_domain);
            if (!empty($params))
            {
                foreach ($params as $key => $value)
                {
                    $metadata_merged[$key] = $value;
                }
            }
        }

        // Handle overwrites
        foreach ($this->overwrite_properties as $key => $value)
        {
            $metadata_merged[$key] = $value;
        }

        // Get us a filepointer
        $path = $this->_dump_dir . '/' . $this->_object_dumpname . $this->_metadata_suffix;
        if (file_exists($path))
        {
            debug_add("file {$path} already exists, removing old one first", MIDCOM_LOG_WARN);
            if (!unlink($path))
            {
                debug_add("Failed to delete existing file {$path}, aborting", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        $fp = fopen($path, 'w');
        if (!$fp)
        {
            debug_add("Could not open file {$path} for writing, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_clean_files[$path] = true;

        // And start writing
        debug_add("metadata_merged\n===" . org_openpsa_helpers::sprint_r($metadata_merged) . "===\n");
        foreach ($metadata_merged as $key => $value)
        {
            //TODO: Escape special characters in value
            $metadata_string = "{$key}={$value}\n";
            if (fwrite($fp, $metadata_string) === false)
            {
                debug_add("Error while writing to '{$path}', aborting");
                debug_pop();
                fclose($fp);
                return false;
            }
        }
        fclose($fp);

        debug_pop();
        return true;
    }

    /**
     * Finds attachment links/embeds and dumps them (while rewriting the links), returns false in case of error, true otherwise
     */
    function _dump_find_attachments()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $regex = "/(src|href)=([\"'�])(.*?\/midcom-serveattachmentguid-([a-f0-9-]+)\/(.*?))\\2/i";
        debug_add("searching attachment links with regex: {$regex}");
        if (!preg_match_all($regex, $this->_object_html, $matches))
        {
            debug_add('no matches found, returning');
            debug_pop();
            return true;
        }

        $attachment_rewrite = $this->_config->get('attachment_rewrite');

        debug_add("attachment_rewrite={$attachment_rewrite}");
        debug_add("matches\n===\n" . org_openpsa_helpers::sprint_r($matches) . "===\n");

        $seen_urls = array();
        foreach ($matches[3] as $k => $url)
        {
            $filename = $matches[5][$k];
            $guid = $matches[4][$k];
            if (array_key_exists($url, $seen_urls))
            {
                // Avoid unnecessary str_replaces and attachment dumps (expensive)
                continue;
            }
            $seen_urls[$url] = true;
            $rewritten = sprintf($attachment_rewrite, $guid, $filename);
            if (!$this->_dump_write_attachment($guid, $rewritten))
            {
                debug_add("attachment write failed, aborting URL rewrite", MIDCOM_LOG_WARN);
                continue;
            }
            debug_add("Replacing '{$url}' with '{$rewritten}'");
            $this->_object_html = str_replace($url, $rewritten, $this->_object_html);
        }

        debug_pop();
        return true;
    }

    /**
     * Writes the given attachment to the dump directory
     */
    function _dump_write_attachment($guid, $rewritten)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $att = new midcom_baseclasses_database_attachment($guid);
        if (   !is_a($att, 'midcom_baseclasses_database_attachment')
            || empty($att->guid))
        {
            debug_add("Could not get attachment {$guid}, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $name = basename($rewritten);
        $path = $this->_dump_dir . '/' . $name;
        if (file_exists($path))
        {
            debug_add("file {$path} already exists, removing old one first", MIDCOM_LOG_WARN);
            if (!unlink($path))
            {
                debug_add("Failed to delete existing file {$path}, aborting", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        $fp_att = $att->open('r');
        if (!$fp_att)
        {
            debug_add("Could not open attachment {$att->guid} for reading, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $fp_file = fopen($path, 'w');
        if (!$fp_file)
        {
            debug_add("Could not open file {$path} for writing, aborting", MIDCOM_LOG_ERROR);
            /* This triggers unnecessary watcher operation (we did not write to the attachment...)
            $att->close();
            */
            fclose($fp_att);
            debug_pop();
            return false;
        }
        $this->_clean_files[$path] = true;
        while(!feof($fp_att))
        {
            if (fwrite($fp_file, fread($fp_att, 4096)) === false)
            {
                debug_add("Error while writing to '{$path}', aborting");
                debug_pop();
                fclose($fp_file);
                /* This triggers unnecessary watcher operation (we did not write to the attachment...)
                $att->close();
                */
                fclose($fp_att);
                return false;
            }
        }
        fclose($fp_file);
        /* This triggers unnecessary watcher operation (we did not write to the attachment...)
        $att->close();
        */
        fclose($fp_att);

        debug_pop();
        return true;
    }

    /**
     * Renders the object HTML via dynamic_load
     */
    function render_object_html()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (empty($this->_object_url))
        {
            $this->resolve_object_url();
            if (empty($this->_object_url))
            {
                // Could not resolve, abort
                debug_add("Could not resolve GUID {$this->_object->guid} via permalinks, aborting", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        debug_add("DL url: {$this->_object_url}");
        ob_start();
        $_MIDCOM->dynamic_load($this->_object_url);
        $this->_object_html = ob_get_contents();
        ob_end_clean();
        debug_pop();
        return true;
    }

    function resolve_object_url()
    {
        debug_add("resolving url for object '{$this->_object->guid}'");
        $fullurl = $_MIDCOM->permalinks->resolve_permalink($this->_object->guid);
        if (!$this->check_url($fullurl))
        {
            // We cannot access the url
            return false;
        }
        $this->_object_url = $this->clean_url($fullurl);
        return true;
    }

    function check_url($url)
    {
        $fp = @fopen($url, 'r');
        if (!$fp)
        {
            return false;
        }
        fclose($fp);
        return true;
    }
}

?>