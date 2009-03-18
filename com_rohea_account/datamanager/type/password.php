<?php
/**
 * Datamanager midgard_person compatible password datatype
 *
 * This encapsulates a Midgard password. When loading, the type automatically detects
 * crypted and plain text passwords. A crypted password is represented by a null value
 * (not an empty string). The '**' prefix of plain text passwords is not part of the
 * value.
 * 
 * Internally, the type holds a copy of the password value in crypted / uncrypted form,
 * depending on configuration. The type value, if set, transformed into an appropriate
 * storage represenation on transfrom-to-storage operations.
 *
 * This type does not allow you to unset your password as attempts to set an empty
 * password are ignored.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>boolean crypted:</i> Set this to true if you want to store the password crypted.
 *   This is enabled by default. Crypt mode is currently enforcing standard crypt
 *   operation, which is used in Midgard Databases.
 *
 * @package midcom.helper.datamanager
 */
class com_rohea_account_datamanager_type_password extends midcom_helper_datamanager_type_baseclass
{
    /**
     * The current clear text value of the current password, if available, or
     * null in case of a crypted password. Set this to the new password value
     * if you want to store anything. The password must be non-null and a non-empty
     * string for any storage operation to take place.
     *
     * @var string
     * @access public
     */
    public $value = null;
    
    /**
     * The real value as stored in the object. This takes crypting etc. into account.
     *
     * @var string
     * @access public
     */
    public $_real_value = '';
    
    /**
     * Indicating crypted operation
     *
     * @param boolean
     * @access public
     */
    public $crypted = true;
    
    public function convert_from_storage($source)
    {
        $this->_real_value = $source;
        if (substr($source, 0, 2) == '**')
        {
            $this->value = null;
        }
        else
        {
            $this->value = substr($source, 2);
        }
    }
    
    public function convert_to_storage()
    {
        
        if ($this->value)
        {
            $this->_update_real_value();
        }

        if ($this->storage->object->id == 0)
        { 
//            $this->storage->object->create(); // FIXME: This is a temporary solution
        }
        // $this->storage->object->password = $this->_real_value;
//        $user = new midgard_user($this->storage->object);
        
//        var_dump($this->storage->object->username);
//        var_dump($this->_real_value);
//        echo("{$this->storage->object->username} : $this->_real_value");
        //die("zaah");
//        midgard_connection::set_loglevel("debug");        
//        $user->password($this->storage->object->username, $this->_real_value);
        //$user->password('username', 'password');
        //var_dump($this->storage->object);
        //print 'teemu......';
        //var_dump($this->_real_value);
        //exit();
        //$user = new midgard_user($this->storage->object);
        //$user->password($this->storage->object->username, $this->_real_value);
        //echo 'password ' . $this->_real_value;
        //$this->storage->object->password = $this->_real_value;
        //$this->storage->object->update();
        
        return $this->_real_value;
    }
    
    
    /**
     * Internal helper function, which converts the currently set value (the clear-text
     * password) to the desired storage format, either crypting or prepending a double
     * asterisk.
     *
     * @access protected
     */
    protected function _update_real_value()
    {
        if ($this->crypted)
        {
            // Enforce crypt mode
            $salt = chr(rand(64, 126)) . chr(rand(64, 126));
            $this->_real_value = crypt($this->value, $salt);
        }
        else
        {
            $this->_real_value = "**{$this->value}";
        }
    }
    
    public function convert_to_html()
    {
        return '**********';
    }

    protected function on_validate()
    {
        return true;
        $func = new $this->validate_password;
        $status = $func->validate();
        unset($this->validation_error);
        if (!$status)
        {
            $this->validation_error = $status['error'];
            return false;
        }
        return true;        
    }


}

?>
