<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class midcom_helper_datamanager_datatype_account extends midcom_helper_datamanager_datatype
{

    var $_enable_crypt;
    var $_username;
    var $_password;
    var $_verify_password;
    var $_password_was_empty;

    function midcom_helper_datamanager_datatype_account (&$datamanager, &$storage, $field)
    {
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "parameter";
        }
        if (!array_key_exists("account_enable_crypt", $field))
        {
            $field["account_enable_crypt"] = false;
        }
        $field["widget"] = "account";

        $this->_username = "";
        $this->_password = "";
        $this->_verify_password = "";
        $this->_password_was_empty = false;
        $this->_enable_crypt = $field["account_enable_crypt"];

        parent::_constructor ($datamanager, $storage, $field);

    }

    function load_from_storage ()
    {
        if ($this->_field["location"] == "MidgardPerson")
        {
            if ($this->_storage->__table__ != "person")
            {
                $this->_datamanager->append_error("CRITICAL: Storage object is not a MidgardPerson, cannot work with MidgardPerson credentials here.");
                debug_add("Trying to use MidgardPerson storage location with a non-person, this is critical.", MIDCOM_LOG_CRIT);
                debug_print_r("Storage object is: ", $this->_storage);
                return false;
            }

            $this->_username = $this->_storage->username;
            if ($this->_enable_crypt)
            {
                $this->_password = "";
                $this->_verify_password = "";
            }
            else
            {
                $this->_password = $this->_storage->password;
                $this->_verify_password = $this->_storage->password;
            }
            $this->_value = serialize(Array ("username" => $this->_username, "password" => $this->_password));
        }
        else
        {
            if (! parent::load_from_storage())
            {
                return false;
            }
            $tmp = unserialize($this->_value);
            $this->_username = $tmp["username"];
            $this->_password = $tmp["password"];
            $this->_verify_password = $tmp["password"];
        }
        return true;
    }

    function save_to_storage()
    {
        if (   ! $this->_password_was_empty
            && $this->_password != $this->_verify_password)
        {
            debug_add("Passwords do not match, save aborted.");
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                        $this->_field["description"],
                        $this->_datamanager->_l10n_midcom->get("password mismatch")));
            return MIDCOM_DATAMGR_FAILED;
        }

        if ($this->_field["location"] == "MidgardPerson")
        {
            if ($this->_storage->__table__ != "person")
            {
                $this->_datamanager->append_error("CRITICAL: Storage object is not a MidgardPerson, cannot work with MidgardPerson credentials here.");
                debug_add("Trying to use MidgardPerson storage location with a non-person, this is critical.", MIDCOM_LOG_CRIT);
                debug_print_r("Storage object is: ", $this->_storage);
            }

            if ($this->_password_was_empty && $this->_enable_crypt)
            {
                debug_add("Don't do anything, the password is empty and crypt was enabled, we cannot update the username this way. Failing gracefully.");
                return MIDCOM_DATAMGR_SAVED;
            }

            $result = false;
            if ($this->_enable_crypt)
            {
                $result = mgd_update_password($this->_storage->id, $this->_username, $this->_password);
            }
            else
            {
                $result = mgd_update_password_plain($this->_storage->id, $this->_username, $this->_password);
            }

            if (! $result)
            {
                $error = mgd_errstr();
                debug_add ("ERROR updating MidgardPerson login data for \"" . $this->_field["name"] . "\": $error", MIDCOM_LOG_ERROR);
                $this->_datamanager->append_error(
                    sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                            $this->_field["description"],
                            $error));
                debug_pop();
                return MIDCOM_DATAMGR_FAILED;
            }
            return MIDCOM_DATAMGR_SAVED;

        }
        else
        {
            if (! $this->_password_was_empty)
            {
                $this->_password = crypt($this->_password);
                $this->sync_widget_with_data();
            }
            $this->_value = serialize(Array ("username" => $this->_username, "password" => $this->_password));
            return parent::save_to_storage();
        }
    }

    function get_value ()
    {
        return Array (
            "username" => $this->_username,
            "password" => $this->_password,
            "enable_crypt" => $this->_enable_crypt
        );
    }

    function sync_widget_with_data()
    {
        $tmp = $this->get_value();
        $tmp["verify_password"] = "";
        $widget =& $this->get_widget();
        $widget->set_value($tmp);
    }

    function sync_data_with_widget() {
    	$widget =& $this->get_widget();
        $tmp = $widget->get_value();
        $this->_username = $tmp["username"];
        if (strlen($tmp["password"]) > 0)
        {
            $this->_password = $tmp["password"];
            $this->_verify_password = $tmp["verify_password"];
            $this->_password_was_empty = false;
        }
        else
        {
            $this->_password_was_empty = true;
            $this->sync_widget_with_data();
        }
    }

    function _get_empty_value()
    {
        return serialize(Array("username" => "", "password" => ""));
    }

    function _get_csv_data()
    {
        return "username={$this->_username}, password={$this->_password}";
    }

    function is_empty()
    {
        return ($this->_username == "");
    }
}

?>