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
class midcom_helper_datamanager_datatype_mailtemplate extends midcom_helper_datamanager_datatype
{

    var $_data;

    /**
     * I18n service object reference, used for charset conversions.
     *
     * @access private
     * @var midcom_helper_service_i18n
     */
    var $_i18n = null;

    function midcom_helper_datamanager_datatype_mailtemplate (&$datamanager, &$storage, $field)
    {
        $field["location"] = "attachment";
        $field["widget"] = "mailtemplate";
        $this->_data = $this->get_empty_value();
        $this->_i18n =& $_MIDCOM->get_service('i18n');

        parent::_constructor ($datamanager, $storage, $field);
    }

    function _complete_key($key, $default)
    {
        if (! array_key_exists($key, $this->_data))
        {
            $this->_data[$key] = $default;
        }
    }

    function _complete_data_keys()
    {
        $this->_complete_key("from", "");
        $this->_complete_key("reply-to", "");
        $this->_complete_key("cc", "");
        $this->_complete_key("bcc", "");
        $this->_complete_key("x-mailer", "");
        $this->_complete_key("subject", "");
        $this->_complete_key("body", "");
        $this->_complete_key("body_mime_type", "text/plain");
        $this->_complete_key("charset", "utf-8");
    }

    function load_from_storage()
    {
        if (!parent::load_from_storage())
        {
            return false;
        }
        $this->_data = unserialize($this->_value);
        if (! is_array($this->_data))
        {
            debug_add ("MAILTEMPLATE: Warning, the stored data was no array, reinitializing with defaults.", MIDCOM_LOG_WARN);
            debug_print_r("Serialized data was: ", $this->_value);
            debug_print_r("Unserialized data was: ", $this->_data);
            $this->_data = Array();
        }
        $this->_complete_data_keys();
        return true;
    }

    function save_to_storage()
    {
        $this->_value = serialize($this->_data);
        return parent::save_to_storage();
    }

    function get_empty_value()
    {
        $i18n =& $_MIDCOM->get_service('i18n');
        return Array(
            "from" => "",
            "reply-to" => "",
            "cc" => "",
            "bcc" => "",
            "x-mailer" => "",
            "subject" => "",
            "body" => "",
            "body_mime_type" => "text/plain",
            "charset" => $i18n->get_current_charset()
        );
    }

    function get_value()
    {
        return $this->_data;
    }

    function get_csv_data()
    {
        return $this->_value;
    }

    function _get_widget_default_value()
    {
        return $this->_convert_to_site_charset($this->_data);
    }

    function sync_widget_with_data()
    {
        $widget =& $this->get_widget();
        $widget->set_value($this->_convert_to_site_charset($this->_data));
    }

    function sync_data_with_widget()
    {
    	$widget =& $this->get_widget();
        $this->_data = $this->_convert_to_data_charset($widget->get_value());
        $this->_complete_data_keys();
    }

    function is_empty()
    {
        return (   strlen($this->_data["from"]) == 0
                && strlen($this->_data["subject"]) == 0
                && strlen($this->_data["body"]) == 0);
    }

    function _convert_to_site_charset($data)
    {
        $i18n =& $_MIDCOM->get_service("i18n");
        $dest = $i18n->get_current_charset();
        $source = $this->_data["charset"];

        return $this->_convert_to_charset ($source, $dest, $data);
    }

    function _convert_to_data_charset($data)
    {
        $i18n =& $_MIDCOM->get_service("i18n");
        $source = $i18n->get_current_charset();
        $dest = $this->_data["charset"];

        return $this->_convert_to_charset ($source, $dest, $data);
    }

    function _convert_to_charset($source, $dest, $data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        foreach ($data as $key => $value)
        {
            if ($key == "charset")
            {
                continue;
            }
            $converted = $this->_i18n->iconv ($source, $dest, $value);
            if ($converted === false)
            {
                debug_add("Failed to convert {$key}, iconv returned false. Keeping the original string", MIDCOM_LOG_WARN);
            }
            else
            {
                $data[$key] = $converted;
            }
        }

        debug_print_r ("Data array after conversion, should be {$dest}:", $data);

        debug_pop();
        return $data;
    }
}

?>