<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager Username data type. The text value encapsulated by this type is
 * passed as-is to the storage layers, no specialties done, just a string.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>int maxlength:</i> The maximum length of the string allowed for this field.
 *   This includes any newlines, which account as at most two characters, depending
 *   on the OS. Set this to 0 (the default) to have unlimited input.
 * - <i>string output_mode:</i> This option controls how convert_to_html operates. The
 *   default 'specialchars' will just pass the data entered in the field through
 *   htmlspecialchars(). See below for a full option listing.
 *
 * Available output modes:
 *
 * - 'html': No conversion is done.
 * - 'specialchars': The value is run through htmlspecialchars() (the default).
 * - 'midgard_f': Uses the Midgard :f formatter.
 * - 'midgard_F': Uses the Midgard :F formatter.
 *
 * @package midcom_helper_datamanager
 */
class com_rohea_account_datamanager_type_username extends midcom_helper_datamanager_type_text
{

    /**
     * The validation callback ensures that the username is fulfils the requirements of a decent username
     *
     * @return boolean Indicating validity.
     */
    protected function on_validate()
    {
        $this->value = trim($this->value);
        $validation_result = parent::on_validate();
        if ($validation_result == false) return false;
        else
        {            
            if(strlen($this->value) < 3)
            {
                $this->validation_error = 'key: Username is too short';
                return false;
            }
            if(strlen($this->value) > 12)
            {
                $this->validation_error = 'key: Username is too long';
                return false;
            }                        
            //Checking if the username is valid
            // {3,12} is the length limitation of the string
            // [a-zA-Z0-9_] are the allowed characters
            $pattern = "/[a-zA-Z0-9-]+$/i";            
            if (preg_match($pattern, $this->value) == false)
            {
                $this->validation_error = 'key: Forbidden characters in username';
                return false;
            }            
            $qb = new midgard_query_builder('midgard_person');
            $qb->add_constraint('username', 'LIKE', $this->value);
            $res = $qb->execute();
            if (count($res) > 0)
            {
                if($_MIDCOM->authentication->is_user())
                {
                    $logged = $_MIDCOM->authentication->get_person();
                    if ($logged->guid == $res[0]->guid)
                    {
                        return true;
                    }
                }

                $this->validation_error = 'key: Username already in use';
                return false;
            }
            
        }
        return true;
    }
}

?>