<?php

class aegir_login_test  extends midcom_tests_lib_web {

    var $config = null;

    function aegir_login_test($label = false, &$config) {
        parent::midcom_tests_lib_web($label,&$config);
        $this->config = &$config;
    }
    
   function testIfLoginWorks() {
        
        $this->login();     
            
   }
   
    function login() 
    {
        echo "logging in....";
        
        $this->get($this->config->get_base_url() . "login");
        $this->setField('username', $this->config->get_username());
        $this->setField('password', $this->config->get_password());
        $page = $this->clickSubmitById("midcom_services_auth_frontend_form_submit");
         
        $this->browser = $this->getBrowser();
        $this->assertPageDoesNotContainErrors();
        $this->assertWantedPattern('/objectbrowser/');
        $this->assertWantedPattern('/Topics/');
        if (!$this->assertResponse(array(200), "You are still not logged inn")) {
            
        }
       
    }
   
    

}
