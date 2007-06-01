<?php

/**
 * This is the configurationclass for the testing object. 
 * 
 * It reads it's options from config/config.inc.
 * 
 * @package midcom.tests 
 */
class midcom_tests_config {

    /**
     * hostname of the server you want to test 
     */
    var $hostname = '';
    /**
     * prefix to the area you want to test
     */
    var $prefix   = '/';

    /**
     * Username to connect
     * @var string
     */
    var $username = '';
     
    /**
     * Password to connect
     * @var string 
     */
    var $password = '';
    
    /**
     * Portnumber if it's a nonstandard port'
     */
    var $port = 80;
    
    /**
     * Sitegroup to test with
     * @var string
     */
    var $sitegroup = "";

    function midcom_tests_config ($config = null) {
        if ($config !== null) {
            $this->username = $config->get('username');
            $this->password = $config->get('password');
            $this->sitegroup= $config->get('sitegroup');
            $this->hostname = $config->get('hostname');
            $this->prefix   = $config->get('prefix');
            $this->port     = $config->get('port');
            $this->_generate_defaults();
        }
    }

    /**
     * Simple function to use the parameters from the environment to generate default
     * settings. 
     * This function also tries to dig out the user password. This should probably be changed. 
     * @access private
     * @return void
     * 
     */
    function _generate_defaults() {
        if (is_object($_MIDCOM) ) {
            
            if ($_MIDCOM->auth->user !== null) {
                
                $user = $_MIDCOM->auth->user->get_storage();
                
                if ($this->username == '') { 
                    $this->username = $user->username;
                }
                if ($this->password == '') {
                    $this->password = $user->password;
                }
                if ($this->sitegroup == 0) {
                    $this->sitegroup = $user->sitegroup;
                }
            }
            if ($this->prefix == '') {
                $this->prefix       = $_MIDCOM->get_host_prefix();
            }
            if ($this->hostname == '') {
                $this->hostname     = $_MIDCOM->get_host_name();
            }
            
        }
        
    }
    /**
     * get the basic url for the server to test
     * @return string hostname + prefix
     */
    function get_base_url() {
        return $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        //return $this->hostname . ":" . $this->port .  $this->prefix;
    }
    /**
     * get the hostname of the host to check
     * @return string hostname 
     */
    function get_hostname() {
        
        return $this->hostname;
    }
    /**
     * Get the username set above
     * @return string username
     */
    function get_username() {
        return $this->username;
    }
    /**
     * Get the password 
     * @return string password
     */
    function get_password() {
        // handle plaintext passwords.
        if (substr($this->password, 0,2) == "**") {
            return substr($this->password, 2);
        }
        return $this->password;
    }
    
    /**
     * get the midcom_config array
     * @return array
     */
     function get_midcom_config () {
        return array (
            'log_filename' => '/tmp/midcom_tests.log',
            'log_level'     => MIDCOM_LOG_DEBUG
        );
     }
}