<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_importer_archive extends midcom_helper_replicator_importer
{
    var $retry_map = array();
    var $max_retries = 3;

    /**
     * Initializes the class.
     */
    function midcom_helper_replicator_importer_archive()
    {
        parent::midcom_helper_replicator_importer();
    }
 
     /**
     * Main entry point for importer, imports XML files in given archive
     *
     * @param string $filepath path to archive to extract and import
     * @param boolean $use_the_force Whether to force importing
     * @return boolean Whether importing was successful
     */   
    function import($filepath, $use_the_force = false)
    {
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix(__CLASS__ . '::' . __FUNCTION__);
        if (!preg_match('/\.(zip|tar(\.gz|\.bz2)?|tgz)$/', strtolower($filepath), $extension_matches))
        {
            $this->error = "{$filepath} is not a supported archive";
            $GLOBALS['midcom_helper_replicator_logger']->log($this->error, MIDCOM_LOG_ERROR);
            return false;
        }
        $extension =& $extension_matches[1];

        $tmp_dir = "{$filepath}_extracted";
        if (!mkdir($tmp_dir))
        {
            // Could not create temp dir
            $this->error = "failed to create directory '{$tmp_dir}'";
            $GLOBALS['midcom_helper_replicator_logger']->log($this->error, MIDCOM_LOG_ERROR);
            $this->_batch_handler_cleanup(false, $filepath);
            debug_pop();
            return false;
        }
        $zj = false;
        switch (strtolower($extension))
        {
            case 'zip':
                $extract_cmd = "unzip -q -b -L -o {$filepath} -d {$tmp_dir}";
                break;
            case 'tgz':
            case 'tar.gz':
                $zj = 'z';
            case 'tar.bz2':
                if (!$zj)
                {
                    $zj = 'j';
                }
            case 'tar':
                $extract_cmd = "tar -x{$zj} -C {$tmp_dir} -f {$filepath}";
                break;
            default:
                // Unknown extension (we should never hit this)
                $this->error = "unusable extension '{$extension}'";
                $GLOBALS['midcom_helper_replicator_logger']->log($this->error, MIDCOM_LOG_ERROR);
                $this->_batch_handler_cleanup($tmp_dir, $filepath);
                debug_pop();
                return false;
        }
        $GLOBALS['midcom_helper_replicator_logger']->log("executing '{$extract_cmd}'");
        exec($extract_cmd, $output, $ret);
        if ($ret != 0)
        {
            // extract failed
            $this->error = "failed to execute '{$extract_cmd}'";
            $GLOBALS['midcom_helper_replicator_logger']->log($this->error, MIDCOM_LOG_ERROR);
            $this->_batch_handler_cleanup($tmp_dir, $filepath);
            debug_pop();
            return false;
        }
        $files = array();
        // Handle archives with subdirectories correctly
        $this->_batch_handler_get_files_recursive($tmp_dir, $files);

        // NOTE: This form used on purpose see inside the loop
        reset($files);
        while (list($key, $file) = each($files))
        {
            $GLOBALS['midcom_helper_replicator_logger']->log("Processing file {$file}");
            $xml_content = file_get_contents($file);
            $stat = $this->import_xml($xml_content, $use_the_force);
            unset($xml_content);
            if (!$stat)
            {
                // TODO: Check for failure reason and retry only on (possibly) recoverable errors
                $GLOBALS['midcom_helper_replicator_logger']->log("Import failed for extracted file {$file}", MIDCOM_LOG_ERROR);
                if (!isset($this->retry_map[$file]))
                {
                    $this->retry_map[$file] = 0;
                }
                $retries =& $this->retry_map[$file];
                $retries++;
                if ($retries > $this->max_retries)
                {
                    $this->error = "Retry count exceeded for file {$file}";
                    $GLOBALS['midcom_helper_replicator_logger']->log($this->error, MIDCOM_LOG_ERROR);
                    $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                    return false;
                }
                $GLOBALS['midcom_helper_replicator_logger']->log("Moved file {$file} to end of queue for retrying", MIDCOM_LOG_ERROR);
                // NOTE: this doesn't seem to work with foreach
                unset($files[$key]);
                $files[] = $file;
                continue;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log("Imported extracted file {$file}", MIDCOM_LOG_INFO);
        }

        $this->_batch_handler_cleanup($tmp_dir, $filepath);

        $GLOBALS['midcom_helper_replicator_logger']->log("Imported archive {$filepath}", MIDCOM_LOG_INFO);
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return true;
    }

    function _batch_handler_cleanup($tmp_dir, $new_name)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called with: '{$tmp_dir}', '{$new_name}'");
        if (   empty($tmp_dir)
            || $tmp_dir === '/'
            /* TODO: better tmp dir matching */
            || !preg_match('|^/tmp/|', $tmp_dir)
            )
        {
            // Do somethign ? we cannot return as there's more work to do...
        }
        else
        {
            $cmd = "rm -rf {$tmp_dir}";
            debug_add("executing '{$cmd}'");
            exec($cmd, $output, $ret);
        }
        if (   empty($new_name)
            /* TODO: better tmp dir matching */
            || !preg_match('|^/tmp/|', $new_name)
            )
        {
            debug_pop();
            return;
        }
        $cmd = "rm -f {$new_name}";
        debug_add("executing '{$cmd}'");
        exec($cmd, $output, $ret);
        debug_pop();
    }

    function _batch_handler_get_files_recursive($path, &$files)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $cnt = count($files);
        debug_add("called with: '{$path}', file count: {$cnt}");
        debug_pop();
        $dp = @opendir($path);
        if (!$dp)
        {
            return;
        }
        while (($file = readdir($dp)) !== false)
        {
            if (preg_match('/(^\.)|(~$)/', $file))
            {
                // ignore dotfiles and backup files
                continue;
            }
            $filepath = "{$path}/{$file}";
            if (is_dir($filepath))
            {
                // It's a directory, recurse
                $this->_batch_handler_get_files_recursive($filepath, $files);
                continue;
            }
            if (is_link($filepath))
            {
                // Is a symlink, we can't do anything sensible with it
                continue;
            }
            if (!is_readable($filepath))
            {
                // for some weird reason the file *we* extracted is not readable by us...
                continue;
            }
            $files[] = $filepath;
        }
    }

}
?>