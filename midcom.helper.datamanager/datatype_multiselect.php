<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_datatype_multiselect extends midcom_helper_datamanager_datatype
{

    var $_selection_list;
    var $_current_selection;

    function midcom_helper_datamanager_datatype_multiselect (&$datamanager, &$storage, $field)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (!array_key_exists("location", $field))
        {
            $field["location"] = "attachment";
        }
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "multiselect";
        }
        if (!array_key_exists("multiselect_selection_list", $field))
        {
            $field["multiselect_selection_list"] = Array (
                0 => "This list must not be empty.",
                1 => "Please define field[selection_list]" );
        }

        $this->_selection_list = Array();
        foreach ($field['multiselect_selection_list'] as $key => $value)
        {
            $this->_selection_list[$key] = $datamanager->translate_schema_string($value);
        }
        $this->_current_selection = Array();

        parent::_constructor ($datamanager, $storage, $field);

        debug_pop();
    }

    function load_from_storage ()
    {
    	debug_push_class(__CLASS__, __FUNCTION__);

        // First load the data from the storage
        parent::load_from_storage();
        debug_print_r('Loaded data:', $this->_value);

        // Evaluate the string and extract the keys
        $extracted = explode (",", trim($this->_value));
        debug_print_r("Extracted this Array:", $extracted);

        // Verify each key first against some constraints and then against
        // the selection-list. All invalid keys will logged and dropped.
        // Note, that this means the invalid keys will be lost on the next
        // save call.
        foreach ($extracted as $key)
        {
            // Note: Do we need RegExes here, or does it consume to much runtime?
            // Perhaps checking upon save would be more efficient...
            if (preg_match ("/^[0-9a-z_][0-9a-z_ -]*$/i", $key) == 0)
            {
                debug_add("The key [{$key}] does not match the key regex. Ignoring.", MIDCOM_LOG_WARN);
                continue;
            }

            // Is the key known?
            if (! array_key_exists ($key, $this->_selection_list))
            {
                debug_add("The key [" . $key . "] is not known. Ignoring.", MIDCOM_LOG_WARN);
                continue;
            }

            // Insert the key/value pair into the current selection list.
            $this->_current_selection[$key] = $this->_selection_list[$key];
        }

        debug_pop();
        return true;
    }


    function save_to_storage ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $tosave = Array();
        foreach ($this->_current_selection as $key => $value)
        {
            $tosave[] = $key;
        }

        $this->_value = implode (",", $tosave);

        debug_pop();
        return parent::save_to_storage();
    }

    function get_value ()
    {
        return $this->_current_selection;
    }

    function sync_widget_with_data()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $widget =& $this->get_widget();
        $widget->set_value($this->_current_selection);

        debug_pop();
    }

    function sync_data_with_widget ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $widget =& $this->get_widget();
        $this->_current_selection = $widget->get_value();

        debug_pop();
    }

    function is_empty()
    {
        return (count ($this->_selection_list) == 0);
    }
}

?>