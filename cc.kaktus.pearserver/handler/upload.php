<?php
/**
 * @package cc.kaktus.pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 4368 2006-10-20 07:47:46Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server handler class for viewing the welcome screen
 * 
 * @package cc.kaktus.pearserver
 */
class cc_kaktus_pearserver_handler_upload extends midcom_baseclasses_components_handler
{
    var $_tmpdir;
    var $_tmpfile;
    var $_filename;

    var $_attachment;

    /**
     * Constructor. Ties to the parent class constructor.
     *
     * @access public
     */
    function cc_kaktus_pearserver_handler_upload()
    {
        parent::midcom_baseclasses_components_handler();
        $this->_root_group =& $this->_request_data['root_group'];
    }

    /**
     * Unpack the file for processing
     *
     * @access private
     * @return boolean
     */
    function _unpack()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Check the extension
        if (!preg_match('/\.(zip|tar(\.gz|\.bz2)?|tgz)$/', strtolower($this->_filename), $regs))
        {
            debug_add("Illegal file extension: '{$this->_filename}'");
            debug_pop();

            $_MIDCOM->uimessages->add($this->_l10n->get('cc.kaktus.pearserver'), sprintf($this->_l10n->get("unknown file type %s"), $regs[1]));
            return false;
        }

        $extension = $regs[1];
        $this->_tmpdir = "{$this->_tmpfile}_extracted";

        // Get the correct unpacking routine
        switch (strtolower($extension))
        {
            case 'zip':
                $extract_cmd = "unzip -q -b -L -o {$this->_tmpfile} -d {$this->_tmpdir}";
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
                $extract_cmd = "tar -x{$zj} -C {$this->_tmpdir} -f {$this->_tmpfile}";
                break;
            default:
                // Unknown extension (we should never hit this)
                debug_add("Illegal file extension: '{$this->_tmpfile}'");
                debug_pop();

                $_MIDCOM->uimessages->add($this->_l10n->get('cc.kaktus.pearserver'), sprintf($this->_l10n->get("unknown file type %s"), $extension));
                return false;
        }

        // Create a temporary directory for unpacking
        if (!mkdir($this->_tmpdir))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to create a temporary directory. Check the write permissions of the HTTPD services', MIDCOM_LOG_ERROR);
            debug_pop();

            $this->_flush($this->_tmpfile);

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a temporary directory. See error level log for details");
            // This will exit
        }

        // Extract the package
        debug_add("Executing '{$extract_cmd}'");
        exec($extract_cmd, $output, $ret);
        if ($ret != 0)
        {
            // extract failed
            debug_add("Failed to execute '{$extract_cmd}'", MIDCOM_LOG_ERROR);
            debug_pop();

            // Flush the temporary files
            $this->_flush();
            return false;
        }

//        $cmd = 'cp ' . escapeshellarg($v) . ' ' . escapeshellarg($new_v);

        // Print the file listing
        $this->_check_shell_args();

        // Get the package XML
        $this->_parse_package();

        // Flush the temporary files
        $this->_flush();

        return true;
    }

    function _parse_package()
    {
        if (!file_exists("{$this->_tmpdir}/package.xml"))
        {
            $_MIDCOM->uimessages->add($this->_l10n->get('cc.kaktus.pearserver'), $this->_l10n->get('could not find the package.xml'));
            return false;
        }

        $contents = file_get_contents("{$this->_tmpdir}/package.xml");
        echo "<pre>{$contents}</pre>\n";

        $this->_package = $contents;

        return true;
    }

    /**
     * Before accepting the package files should be checked to verify that the extracted files do not contain
     * shell arguments
     *
     * @access private
     */
    function _check_shell_args()
    {
        $dp = @opendir($this->_tmpdir);
        if (!$dp)
        {
            return;
        }

        // TODO: write a checkup routine
        while (($file = readdir($dp)) !== false)
        {
            echo "{$file}\n";
        }

        return true;
    }

    /**
     * Empty the temporary files and directories
     *
     * @access private
     */
    function _flush()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("Removing the temporary file '{$this->_tmpfile}'");
        unlink($this->_tmpfile);

        if (is_dir("{$this->_tmpdir}"))
        {
            $cmd = "rm -rf {$this->_tmpdir}";
            debug_add("Removing the temporary directory: executing command '{$cmd}'");

            exec($cmd, $output, $ret);

            if ($ret != 0)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to flush the directory: {$output}");
                // This will exit
            }
        }

        // UI messages
        $_MIDCOM->uimessages->add($this->_l10n->get('cc.kaktus.pearserver'), $this->_l10n->get('temporary files deleted'));

        debug_pop();
    }

    /**
     * Process the POST form
     *
     * @access private
     */
    function _process_form()
    {
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->uimessages->add($this->_l10n->get('cc.kaktus.pearserver'), $this->_l10n->get('upload cancelled'));
            $_MIDCOM->relocate('');
            // This will exit
        }

        if (!isset($_POST['f_submit']))
        {
            return;
        }

        // Add UI messages to tell the user what happened
        $_MIDCOM->uimessages->add($this->_l10n->get('cc.kaktus.pearserver'), $this->_l10n->get('file uploaded'));

        // Set the temporary filename
        $this->_filename = $_FILES['release']['name'];
        $this->_tmpfile = $_FILES['release']['tmp_name'];

        echo "<pre>\n";

        // Unpack the temporary file
        if (!$this->_unpack())
        {
            return false;
        }

        echo "</pre>\n";
        die();
    }

    /**
     * Handle the uploading interface
     *
     * @access public
     * @return boolean Indicating success
     */
    function _handler_upload($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');

        $this->_process_form();
        return true;
    }

    /**
     * Show the upload form
     *
     * @access public
     */
    function _show_upload($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;
        midcom_show_style('package-upload');
    }
}
?>