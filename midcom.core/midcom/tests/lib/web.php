<?php
/**
 * @package midcom.tests
 * 
 * This file is a basic testfile for running testcases against an application.
 * Look in the Aegir tests dir for more advanced examples. 
 * 
 */
class midcom_tests_lib_web extends WebTestCase {

    var $config;
    var $browser;
    /**
     * @param object MidcomTestConfig 
     */
    function midcom_tests_lib_web(&$config) {
        $this->config = &$config;
        
    }

    function setUp() {
        $this->get($this->config->get_base_url());
        $this->browser = $this->getBrowser();
    }

    function tearDown() {
    }
    
    /**
     * helper to run the different tests below on a page
     * 
     */
    function assertPageDoesNotContainErrors() {
        $this->assertNoHttpError();
        $this->assertNoPhpError();
        $this->assertPageDeliversXhtml();
        
        
        
    }

    /* helper functions
     * */
    function assertNoPhpError() {
        $this->assertNoUnwantedPattern('/error.*in.*on line .*\d/' , "Error on page: " . $this->getUrl());
    }

    function assertNoHttpError() {
        if ( ! $this->assertResponse(array(200)) ) {
            print("in url: " . $this->config->get_base_url() . "\n");        
        }
        return;
    }
    /**
     * assert if the page starts by delivering an xml document.
     * @todo: maybe validate the whole page?
     */
    function assertPageDeliversXhtml() {
       $html = $this->browser->getContent();
       $this->assertWantedPattern('/^<\?xml version*/',  "No xhtml start on page: " . $this->getUrl());
    }
    
    

}
