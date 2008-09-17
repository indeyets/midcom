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
class midcom_helper_replicator_transporter_archive extends midcom_helper_replicator_transporter
{
    var $archive_filepath = false;
    var $archive_type = 'tar.bz2';
    var $_tmp_dir = false;
    var $_archive_method = false;
    var $_file_overwrite = false;
    var $program_paths = array();
    var $_file_counter = 1;
    
    function __construct($subscription)
    {
        $this->program_paths['tar'] = $GLOBALS['midcom_config']['utility_tar'];
        $this->program_paths['bzip2'] = '/usr/bin/bzip2';
    
         $ret = parent::__construct($subscription);
         if (!$this->_read_configuration_data())
         {
            $x = false;
            return $x;
         }
         return $ret;
    }

    /**
     * Reads transport configuration from subscription's parameters
     *
     * Also does some sanity checking
     */
    function _read_configuration_data()
    {
        if (!method_exists($this->subscription, 'list_parameters'))
        {
            // Can't read parameters (dummy subscription ??)
            return false;
        }
        $params = $this->subscription->list_parameters('midcom_helper_replicator_transporter_archive');
        if (!is_array($params))
        {
            // Error reading parameters
            return false;
        }
        if (array_key_exists('filepath', $params))
        {
            $this->archive_filepath = $params['filepath'];
        }
        if (array_key_exists('file_overwrite', $params))
        {
            $this->_file_overwrite = (bool)$params['file_overwrite'];
        }
        if (array_key_exists('archive_type', $params))
        {
            $this->archive_type = $params['archive_type'];
        }
        
        // TODO: read helper program paths from global config

        // Preliminary sanity checks
        if (!$this->_check_filepath())
        {
            return false;
        }
        switch ($this->archive_type)
        {
            // Handlers for special cases of archive_type ??
            default:
                $this->_archive_method = '_create_archive_' . str_replace('.', '_', $this->archive_type);
        }
        if (   !method_exists($this, $this->_archive_method)
            || !method_exists($this, '_can' . $this->_archive_method))
        {
            $this->_archive_method = false;
            // Archive type not supported at all
            return false;
        }

        // Make sure this is reset each time we instantiate
        $this->_file_counter = 1;
        
        return true;
    }

    function _check_filepath()
    {
        $this->archive_filepath = str_replace('DATETIME', date('YmdHis'), $this->archive_filepath);
        if (   file_exists($this->archive_filepath)
            && $this->_file_overwrite)
        {
            return unlink($this->archive_filepath);
        }
        $i = 2;
        $name_base = $this->archive_filepath;
        while (file_exists($this->archive_filepath))
        {
            $this->archive_filepath = $name_base . ".{$i}";
            $i++;
            if ($i > 100)
            {
                // prevent infinite loops
                return false;
            }
        }
        return true;
    }

    /**
     * Main entry point for processing the items received from queue manager
     */
    function process(&$items)
    {
        if (!$this->_create_tmp_dir())
        {
            $this->error = "Failed to create temporary directory";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!$this->_dump_items($items))
        {
            $this->error = "Failed to dump items";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_pop();
            $this->_clean_up(true);
            return false;
        }
        if (!$this->_create_archive())
        {
            $this->_clean_up(true);
            return false;
        }
        $this->_clean_up(false);
        return true;
    }

    /**
     * Cleans all our tmp files laying around
     */
    function _clean_up($also_archive_file = false)
    {
        if ($also_archive_file)
        {
            if (file_exists($this->archive_filepath))
            {
                unlink($this->archive_filepath);
            }
        }
        $cmd = "rm -rf '{$this->_tmp_dir}'";
        exec($cmd, $output, $ret);
    }

    /**
     * Creates tmp dir for us to use
     */
    function _create_tmp_dir()
    {
        if (!$this->archive_filepath)
        {
            return false;
        }
        $tmpfile = tempnam(dirname($this->archive_filepath), basename($this->archive_filepath) . '_');
        if (!$tmpfile)
        {
            return false;
        }
        unlink($tmpfile);
        if (!mkdir($tmpfile))
        {
            return false;
        }
        $this->_tmp_dir = $tmpfile;
        return true;
    }

    /**
     * Dumps items as files to temp dir
     */
    function _dump_items(&$items)
    {
        if (!$this->_tmp_dir)
        {
            return false;
        }
        foreach ($items as $key => $path)
        {
            $was_data = false;
            if (!is_file($path))
            {
                $was_data = true;
                // Item is data in stead of path
                $new_path = tempnam($this->_tmp_dir, 'mhreplicator_');
                $fp = fopen($new_path, 'w');
                if (!$fp)
                {
                    // PANIC: Can't open file for writing
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("can't open file {$new_path} for writing", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                fwrite($fp, $path, strlen($path));
                fclose($fp);
                unset($path, $fp);
                $path = $new_path;
                unset($new_path);
            }
            // Reset time limit while copying keys
            set_time_limit(30);
            $file = "{$this->_tmp_dir}/" . sprintf('%010d', $this->_file_counter) . ".xml";
            if (!copy($path, $file))
            {
                // In this transport any single item failure is fatal
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("can't copy file {$path} to {$file}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            if ($was_data)
            {
                unlink($path);
            }
            unset($items[$key], $path);
            $this->_file_counter++;
        }
        return true;
    }

    /**
     * Main entry point for creating the archive file, calls relevant method
     */
    function _create_archive()
    {
        if (!$this->_archive_method)
        {
            // Proper handler for type could not be determined
            $this->error = "No archive method set";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $can_method = '_can' .  $this->_archive_method;
        if (!$this->$can_method())
        {
            // We can't create the archive for some reason (likely missing dependencies)
            $this->error = "Can't create archive {$this->_archive_method}";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $exec_method = $this->_archive_method;
        return $this->$exec_method();
    }

    /**
     * Creates a .tar.bz2 archive of dumped files
     */
    function _create_archive_tar_bz2()
    {
        // Doublecheck
        if (!$this->_can_create_archive_tar_bz2())
        {
            $this->error = "Can't create .tar.bz2 archive";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // This must not be in the _can (which is called before we even can have tmp dir)
        if (   !$this->_tmp_dir
            || !$this->archive_filepath)
        {
            $this->error = "Either temporary directory or archive filepath unset.";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $cmd = "cd '{$this->_tmp_dir}' && {$this->program_paths['tar']} -cjf '{$this->archive_filepath}' ./";
        exec($cmd, $output, $ret);
        if ($ret != 0)
        {
            // command exited with error code
            $this->error = "Error running '{$cmd}'";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->error, MIDCOM_LOG_ERROR);
            debug_print_r("Command output: ", $output);
            debug_pop();
            return false;
        }
        return true;
    }

    /**
     * Sanity checks for _create_archive_tar_bz2()
     *
     */
    function _can_create_archive_tar_bz2()
    {
        return true;
    }

    /**
     * Sanity checks for _create_archive_tar()
     */
    function _can_create_archive_tar()
    {
        return true;
    }
    
    function get_information()
    {
        $recipient = $this->subscription->get_parameter('midcom_helper_replicator_transporter_archive', 'filepath');
        $info = sprintf($this->_l10n->get('archive to %s'), $recipient);
        return $info;
    }

    function add_ui_options(&$schema)
    {
        $schema->append_field
        (
            'filepath', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('archive file path', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_archive'
                ),
                'required' => true,
                'type' => 'text',
                'widget' => 'text',
            )
        );
        $schema->append_field
        (
            'file_overwrite', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('overwrite existing file', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_archive'
                ),
                'required' => true,
                'type' => 'select',
                'type_config' => array
                (
                    'options' => array
                    (
                        false => 'no',
                        true => 'yes',
                    ),
                ),
                'widget' => 'select',
            )
        );
        $schema->append_field
        (
            'archive_type', 
            array
            (
                'title' => $_MIDCOM->i18n->get_string('archive type', 'midcom.helper.replicator'),
                'storage' => array
                (
                    'location' => 'parameter',
                    'domain'   => 'midcom_helper_replicator_transporter_archive'
                ),
                'required' => true,
                'type' => 'select',
                'type_config' => array
                (
                    'options' => array
                    (
                        'tar.bz2' => 'tar.bz2 archive',
                    ),
                ),
                'widget' => 'select',
            )
        );

    }

}
?>