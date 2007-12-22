<?php
/**
 * @package cc.kaktus.pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 4368 2006-10-20 07:47:46Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server handler class for processing an uploaded release
 * 
 * @package cc.kaktus.pearserver
 */
class cc_kaktus_pearserver_handler_process extends midcom_baseclasses_components_handler
{
    function cc_kaktus_pearserver_handler_release()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _process_attachment($attachment)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $tmp_dir = "/tmp/{$attachment->guid}/";
        print_r($attachment);
        $handle = $attachment->open();
        print_r(fstat($handle));
        echo time();
        die();

        // New filename for copying the package
        $new_name = "{$tmp_dir}{$attachment->name}";

        while (is_dir($tmp_dir))
        {
            echo "-- directory '{$tmp_dir}' found, try another\n";
            $tmp_dir = "/tmp/" . md5($tmp_dir) . "/";
        }

        echo "-- directory will be {$tmp_dir}\n";

        if (!mkdir($tmp_dir))
        {
            debug_add("Failed to create directory '{$tmp_dir}'", MIDCOM_LOG_ERROR);
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the attachments, see error level log for details');
            // This will exit
        }

        echo "-- created {$tmp_dir}\n";

        debug_add("Created directory '{$tmp_dir}' for temporary use");

        // Create a temporary file for unpacking
        $tmp_file = fopen($new_name, 'w');

        echo "-- writing {$tmp_file}\n";
        $i = 1;

        fwrite ($tmp_file, file_get_contents($handle));

        preg_match('/\.(zip|tar(\.gz|\.bz2)?|tgz)$/', strtolower($attachment->name), $regs);

        switch ($regs[1])
        {
            case 'zip':
                $extract_cmd = "unzip -q -b -L -o {$new_name} -d {$tmp_dir}";
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
                $extract_cmd = "tar -x{$zj} -C {$tmp_dir} -f {$new_name}";
                break;
            default:
                // Unknown extension (we should never hit this)
                debug_add("unusable extension '{$regs[1]}'", MIDCOM_LOG_ERROR);
                $cmd = "rm -rf {$tmp_dir}";
                debug_add("executing '{$cmd}'");
                exec($cmd, $output, $ret);

                $this->_errors[] = array
                (
                    'filename' => $attachment->name,
                    'class' => 'error',
                    'message' => 'unknown file type',
                );

                // Remove the false attachment
                $attachment->delete();

                $this->_flush_temporary_directory($tmp_dir);

                return false;
        }

        debug_add("executing '{$extract_cmd}'");
        exec($extract_cmd, $output, $ret);

        echo "-- {$extract_cmd}\n";

        if ($ret != 0)
        {
            // extract failed
            debug_add("Failed to execute '{$extract_cmd}'", MIDCOM_LOG_ERROR);
            debug_pop();

            echo "-- failed to execute!\n";

            $this->_errors[] = array
            (
                'filename' => $attachment->name,
                'class' => 'error',
                'message' => 'failed to unpack',
            );

            $this->_flush_temporary_directory($tmp_dir);
            return false;
        }

        if (!file_exists("{$tmp_dir}package.xml"))
        {
            $this->_errors[] = array
            (
                'filename' => $attachment->name,
                'class' => 'error',
                'message' => 'manifest not found',
            );
        }

        $manifest = file_get_contents("{$tmp_dir}package.xml");

        debug_add("Got the manifest:\n{$manifest}");

        $attachment->set_parameter('PEAR package', 'manifest.inc', $manifest);

        $this->_flush_temporary_directory($tmp_dir);

        debug_pop();
        return true;
    }

    function _flush_temporary_directory($directory)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $exec = "rm -rf {$directory}";
        debug_add("executing '{$exec}'");
        exec($exec, $output, $ret);

        debug_add("Execute returned {$output}");
    }

    function _handler_process($handler_id, $args, &$data)
    {
        $this->_release = new org_openpsa_products_product($args[0]);

        echo "<pre>\n";

        // Get the attachments for the currently processed release
        $qb = midcom_baseclasses_database_attachment::new_query_builder();
        $qb->add_constraint('pid', '=', $this->_release->id);
        $qb->add_constraint('ptable', '=', 'org_openpsa_products_product');
        $this->_attachments = $qb->execute_unchecked();

        print_r($this->_attachments);

        foreach ($this->_attachments as $attachment)
        {
            $this->_process_attachment($attachment);
        }

        echo "</pre>\n";
        die();
    }
}
?>