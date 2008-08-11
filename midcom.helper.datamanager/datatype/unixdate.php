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
class midcom_helper_datamanager_datatype_unixdate extends midcom_helper_datamanager_datatype {

    var $_withtime;

    /**
     * This is a MgdSchmea transition compatibility flag. It decides whether a
     * given field is stored as a ISO Timestamp by MgdSchema, or not. This flag
     * is valid for all storage destinations.
     *
     * This is a hotfix, so its behavior might change over time.
     *
     * You can set it by adding the key 'unixdate_store_as_iso_timestamp'
     * to the fields' declaration.
     *
     * The default for this value is false unless the storage location is one of
     * 'created', 'locked', 'revised' and 'approved', for which it is true.
     *
     * @var boolean
     * @access private
     */
    var $_store_as_iso_timestamp;

    function midcom_helper_datamanager_datatype_unixdate (&$datamanager, &$storage, $field)
    {
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "parameter";
        }
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "date";
        }
        if (!array_key_exists("date_withtime", $field))
        {
            $field["date_withtime"] = false;
        }

        if (   (!array_key_exists("widget_date_minyear", $field))
            || $field["widget_date_minyear"] < 1970)
        {
            $field["widget_date_minyear"] = 1970;
        }
        if (   (!array_key_exists("widget_date_maxyear", $field))
            || $field["widget_date_maxyear"] > 2037)
        {
            $field["widget_date_maxyear"] = 2037;
        }

        $this->_withtime = $field["date_withtime"];

        if (array_key_exists('unixdate_store_as_iso_timestamp', $field))
        {
            $this->_store_as_iso_timestamp = $field['unixdate_store_as_iso_timestamp'];
        }
        else
        {
            $this->_store_as_iso_timestamp = false;
        }

        parent::_constructor ($datamanager, $storage, $field);

    }

    /**
     * save_to_storage override with support for ISO timestamps.
     *
     * Data is written if and only if a value is set, if the timestamp
     * is undefined, nothing happens.
     */
    function save_to_storage ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $result = MIDCOM_DATAMGR_SAVED;
        if ($this->_value)
        {
            if ($this->_store_as_iso_timestamp)
            {
                    $tmp = strftime("%Y-%m-%d %T", $this->_value);
                    debug_add("Converted {$this->_value} to {$tmp}");
                    $oldvalue = $this->_value;
                    $this->_value = $tmp;
                    $result = parent::save_to_storage();
                    $this->_value = $oldvalue;
            }
            else
            {
                $result = parent::save_to_storage();
            }
        }
        debug_pop();
        return $result;
    }

    /**
     * load_from_storage override, with support for ISO timestamps.
     */
    function load_from_storage()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! parent::load_from_storage())
        {
            debug_add('Parent returned false. Aborting.');#
            debug_pop();
            return false;
        }

        if ($this->_store_as_iso_timestamp)
        {
            if ($this->_value)
            {
                $tmp = strtotime($this->_value);
                debug_add("Converted {$this->_value} to {$tmp}");
                $this->_value = $tmp;
            }
        }

        debug_pop();
        return true;
    }

    function sync_widget_with_data ()
    {
        $widget =& $this->get_widget();
        if ($this->_value > 0)
        {
            $widget->set_value(strftime(($this->_withtime == true) ? "%Y-%m-%d %H:%M:%S" : "%Y-%m-%d", $this->_value));
        }
        else
        {
            $widget->set_value("");
        }
    }

    function sync_data_with_widget ()
    {
        $widget =& $this->get_widget();
        $string = $widget->get_value();
        if (trim($string) == '')
        {
            /* Check for empty string, if yes, set the value to 0 */
            $this->_value = 0;
            return;
        }
        $date = strtotime($string, 0);
        if ($date == -1)
        {
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("%s could not be converted into a timestamp, ignored") . "<br>\n", $string));
            $this->sync_widget_with_data();
        }
        else
        {
            $this->_value = $date;
        }
    }

    function _get_default_value()
    {
        if (array_key_exists("default", $this->_field))
        {
            return $this->_field["default"];
        }
        else
        {
            return 0;
        }
    }

    function _get_widget_default_value ()
    {
        if ($this->_value > 0)
        {
            return strftime(($this->_withtime == true) ? "%Y-%m-%d %H:%M:%S" : "%Y-%m-%d", $this->_value);
        }
        else
        {
            return "";
        }
    }

    function get_value()
    {
        $gmt = ((int) $this->_value) - ((int) date("Z", $this->_value));
        if (($this->_withtime))
        {
            return Array (
                "timestamp" => $this->_value,
                "local_strtime" => strftime("%X", $this->_value),
                "local_strdate" => strftime("%x", $this->_value),
                "local_strfulldate" => strftime("%x %X", $this->_value),
                "strtime" => strftime("%H:%M:%S", $this->_value),
                "strdate" => strftime("%Y-%m-%d", $this->_value),
                "strfulldate" => strftime("%Y-%m-%d %H:%M:%S", $this->_value),
                "gmt_timestamp" => $gmt,
                "gmt_local_strtime" => strftime("%X", $gmt),
                "gmt_local_strdate" => strftime("%x", $gmt),
                "gmt_local_strfulldate" => strftime("%x %X", $gmt),
                "gmt_strtime" => strftime("%H:%M:%S", $gmt),
                "gmt_strdate" => strftime("%Y-%m-%d", $gmt),
                "gmt_strfulldate" => strftime("%Y-%m-%d %H:%M:%S", $gmt),
                "gmt_offset_seconds" => (int) date("Z", $this->_value),
                "gmt_offset_hours" => date("O", $this->_value),
                "rfc_822" => date("r", $this->_value),
            );
        }
        else
        {
            return Array (
                "timestamp" => $this->_value,
                "local_strtime" => "",
                "local_strdate" => strftime("%x", $this->_value),
                "local_strfulldate" => strftime("%x", $this->_value),
                "strtime" => "",
                "strdate" => strftime("%Y-%m-%d", $this->_value),
                "strfulldate" => strftime("%Y-%m-%d", $this->_value),
                "gmt_timestamp" => $gmt,
                "gmt_local_strtime" => "",
                "gmt_local_strdate" => strftime("%x", $gmt),
                "gmt_local_strfulldate" => strftime("%x", $gmt),
                "gmt_strtime" => "",
                "gmt_strdate" => strftime("%Y-%m-%d", $gmt),
                "gmt_strfulldate" => strftime("%Y-%m-%d", $gmt),
                "gmt_offset_seconds" => (int) date("Z", $this->_value),
                "gmt_offset_hours" => date("O", $this->_value),
                "rfc_822" => date("r", $this->_value),
            );
        }
    }

    function get_csv_data()
    {
        if (($this->_withtime))
        {
            return strftime("%Y-%m-%d %H:%M:%S", $this->_value);
        }
        else
        {
            return strftime("%Y-%m-%d", $this->_value);
        }
    }
}

?>