<?php
/**
  * Class provides simple helpers for passwords
  */
class com_rohea_account_passwordhelper
{     
    function __construct()
    {
     
    }
    
    var $validation_error;
    
    /**
    * Creates an encrypted password
    * DEPRECATED: USE CORE's OWN functionss
     * @param string password in plain text
     * @return string crypted password
     */
    function crypt_password($password_plaintext)
    {
        // Create an encrypted password
        $salt = chr(rand(64,126)) . chr(rand(64,126));
        $password_crypted = crypt($password_plaintext, $salt);      
        return $password_crypted;
    }
    
    /**
    * Checks if the password is good enough to be used
     * @param string password in plaintext
     * @param string retyped password in plaintext
     */    
    function check_password_quality($password, $password2)
    {
        if ($password !== $password2)
        {
            return false;
        }      
        if (strlen($password) < 5 || strlen($password) > 30)
        {
            return false; 
        }      
        return true;
    }
    
    function check_input_similarity($password, $password2)
    {
        if ($password !== $password2)
        {
            return false;
        }              
        return true;
    }
    
    public function validate()
    {
        if(!isset($_POST['pw']) && !isset($_POST['pw2']))
        {
            return true;
        }
        $password = $_POST['pw'];
        $password2 = $_POST['pw2'];
        
        if(strlen($password) == strlen($password2) && strlen($password) == 0)
        {
            
            return array('bool' => true, 'error' => $this->validation_error);
        }
        if(! com_rohea_account_passwordhelper::check_password_quality($password, $password2))
        {
            $this->validation_error = "bad_quality";
            return array('bool' => false, 'error' => $this->validation_error);
        }
        if(! com_rohea_account_passwordhelper::check_input_similarity($password, $password2))
        {
            $this->validation_error = "similar";
            return array('book' => false, 'error' => $this->validation_error);
        }
        return true;
    }
        
}
?>