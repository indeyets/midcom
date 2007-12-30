<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5856 2007-05-04 12:13:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Shows the code object view page
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_code_import extends midcom_baseclasses_components_handler
{
    /**
     * The code to display
     *
     * @var midcom_db_device
     * @access private
     */
    var $_device = null;

    /**
     * The Datamanager of the code to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['code'] =& $this->_device;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }


    /**
     * Simple default constructor.
     */
    function org_maemo_devcodes_handler_code_import()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_process($handler_id, $args, &$data)
    {
        if (   !isset($_FILES['org_maemo_devcodes_import_file'])
            || empty($_FILES['org_maemo_devcodes_import_file']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No file uploaded');
            // this will exit
        }

        if (   !isset($_POST['org_maemo_devcodes_import_separator'])
            || empty($_POST['org_maemo_devcodes_import_separator']))
        {
            $data['separator'] = ';';
        }
        else
        {
            $data['separator'] = $_POST['org_maemo_devcodes_import_separator'];
        }

        if (   !isset($_POST['org_maemo_devcodes_import_device'])
            || empty($_POST['org_maemo_devcodes_import_device']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Device not defined');
            // this will exit
        }
        $this->_device =& org_maemo_devcodes_device_dba::get_cached($_POST['org_maemo_devcodes_import_device']);
        if (  !$this->_device
            || empty($this->_device->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The device '{$_POST['org_maemo_devcodes_import_device']}' was not found.");
            // This will exit.
        }
        $this->_device->require_do('midgard:create');
        $data['device'] =& $this->_device;


        $data['import_stats'] = array
        (
            'ok' => 0,
            'failed' => 0,
            'duplicate' => 0,
        );
        if (!$this->_import_from_file($_FILES['org_maemo_devcodes_import_file']['tmp_name']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'File import failed critically see log for details');
            // this will exit
        }

        $data['title'] = sprintf($this->_l10n->get('import codes for %s'), $this->_device->title);
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $this->_device->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "code/import/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $data['title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    function _import_from_file($file)
    {
        // Reset time counter
        set_time_limit(ini_get('max_execution_time'));
        $data =& $this->_request_data;
        $fp = fopen($file, 'r');
        if (!$fp)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not open file '{$file}' for reading", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $data['column_map'] = array();
        $columns_line = fgetcsv($fp, 4096, $data['separator']);
        if (empty($columns_line))
        {
            return false;
        }
        foreach ($columns_line as $column => $name)
        {
            $name = trim($name);
            if (empty($name))
            {
                continue;
            }
            $data['column_map'][$name] = $column;
        }
        $data['line_no'] = 1;
        while($csv_line = fgetcsv($fp, 4096, $data['separator']))
        {
            $data['line_no']++;
            if (!$this->_import_line($csv_line))
            {
                // import-line failed;
                continue;
            }
            // import line ok.
        }
        fclose($fp);
        return true;
    }

    function _import_line($csv_line)
    {
        // Reset time counter
        set_time_limit(ini_get('max_execution_time'));
        $data =& $this->_request_data;
        foreach ($data['column_map'] as $name => $column)
        {
            $csv_line[$name] =& $csv_line[$column];
        }
        if (   !isset($csv_line['code'])
            || empty($csv_line['code']))
        {
            // code is not set
            return false;
        }
        if (!org_maemo_devcodes_code_dba::code_is_unique_static($csv_line['code']))
        {
            // code already in database
            ++$data['import_stats']['duplicate'];
            return false;
        }

        // Try to resolve recipient as person object
        $recipient = false;
        if (   isset($csv_line['recipient'])
            && !empty($csv_line['recipient']))
        {
            $recipient =& org_openpsa_contacts_person::get_cached($csv_line['recipient']);
            if (   !$recipient
                || empty($recipient->guid))
            {
                $recipient = false;
            }
        }

        $code = new org_maemo_devcodes_code_dba();
        $code->device = $this->_device->id;
        $code->code = $csv_line['code'];
        if (   isset($csv_line['area'])
            && !empty($csv_line['area']))
        {
            $code->area = $csv_line['area'];
        }
        if ($recipient)
        {
            $code->recipient = $recipient->id;
        }

        if (!$code->create())
        {
            ++$data['import_stats']['failed'];
            return false;
        }
        $code->set_parameter('midcom.helper.datamanager2', 'schema_name', 'code');


        ++$data['import_stats']['ok'];
        return true;
    }

    function _show_process($handler_id, &$data)
    {
        midcom_show_style('import-codes-statistics');
    }

    /**
     * Handle actual code display
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_import($handler_id, $args, &$data)
    {
        $this->_device =& org_maemo_devcodes_device_dba::get_cached($args[0]);
        if (  !$this->_device
            || empty($this->_device->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The device '{$args[0]}' was not found.");
            // This will exit.
        }
        $this->_device->require_do('midgard:create');
        $data['device'] =& $this->_device;

        $data['title'] = sprintf($this->_l10n->get('import codes for %s'), $this->_device->title);
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $this->_device->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "code/import/{$this->_device->guid}",
            MIDCOM_NAV_NAME => $data['title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        /*
        $_MIDCOM->bind_import_to_object($this->_device, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_device->metadata->revised, $this->_device->guid);
        */
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        return true;
    }

    /**
     * Shows the loaded list.
     */
    function _show_import($handler_id, &$data)
    {
        $data['prefix'] = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        midcom_show_style('import-codes-form');
    }
}

?>