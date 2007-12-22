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
class midcom_helper_datamanager_datatype_boolean extends midcom_helper_datamanager_datatype
{

    function midcom_helper_datamanager_datatype_boolean(&$datamanager, &$storage, $field)
    {
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "parameter";
        }
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "checkbox";
        }
        parent::_constructor($datamanager, $storage, $field);
    }

    function is_empty()
    {
        return false;
    }

    function get_csv_value()
    {
        return ($this->_value == false) ? "false" : "true";
    }

    function save_to_storage()
    {
        /* Transform value to string, so that it works safely even with parameters */
        $oldvalue = $this->_value;
        if ($this->_value)
        {
            $this->_value = "1";
        }
        else
        {
            $this->_value = "";
        }
        parent::save_to_storage();
        $this->_value = $oldvalue;
    }

    function load_from_storage()
    {
        if (! parent::load_from_storage())
        {
            return false;
        }
        if ($this->_value)
        {
            $this->_value = true;
        }
        else
        {
            $this->_value = false;
        }
        return true;
    }

}

?>