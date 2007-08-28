<?php

/**
 * This is some kind of a wrapper for the contactcrabber.
 */

/**
  * Contact Grabber 
  * Version 0.3
  * Released 9th May, 2007
  * Author: Magnet Technologies, vishal.kothari@magnettechnologies.com
  * Credits: Janak Prajapati, Pravin Shukla, Tapan Moharana
  * Copyright (C) 2007

  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License
  * as published by the Free Software Foundation; either version 2
  * of the License, or (at your option) any later version.

  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.

  * You should have received a copy of the GNU General Public License
  * along with this program; if not, write to the Free Software
  * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
  **/


class com_magnettechnologies_contactgrabber extends midcom_baseclasses_components_purecode
{

    var $_email = null;
    var $_password = null;
    var $_resource_obj = null;

    function com_magnettechnologies_contactgrabber()
    {
        $this->_component = 'com.magnettechnologies.contactgrabber';
        parent::midcom_baseclasses_components_purecode();
        
        $_MIDCOM->style->append_component_styledir('com.magnettechnologies.contactgrabber');
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/com.magnettechnologies.contactgraper/styles/elements.css",
            )
        );

        $_MIDCOM->enable_jquery();
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/com.magnettechnologies.contactgraper/js/common.js');
    }

    function _create_resource_object()
    {
        if((isset($_POST['username'])) && (isset($_POST['password']))) 
        {
            if($_POST['domain']=="rediff.com")
            {
                require("lib/rediff/grabRediff.class.php");
	        $this->_email = $_POST['username'];
	        $this->_resource_obj = new rediff();		
            }

            if($_POST['domain']=="gmail.com")
            {
                require("lib/gmail/libgmailer.php");
	        $this->_email = $_POST['username']."@".$_POST['domain'];
	        $this->_resource_obj = new GMailer();
            }

            if($_POST['domain']=="orkut.com")
            {
                require("lib/orkut/grabOrkut.class.php");
	        $this->_email = $_POST['username'];
                $this->_resource_obj = new orkut();
            }

            if($_POST['domain']=="myspace.com")
            {
                require("lib/myspace/grabMyspace.class.php");
		$this->_email = $_POST['username'];
	        $this->_resource_obj = new myspace();
            }

	    if($_POST['domain']=="yahoo.com")
            {
                require("lib/yahoo/class.GrabYahoo.php");
	        $this->_email = $_POST['username'];
	        $this->_resource_obj = new yahoo();
            }

	    if($_POST['domain']=="hotmail.com")
            {
                //require("lib/hotmail/msn_contact_grab.class.php");
	        require('lib/hotmail/libhotmail.php');
		$this->_email = $_POST['username']."@".$_POST['domain'];
	        $this->_resource_obj = new hotmail();
            }

	    $this->_password =  $_POST['password'];
	}
    }

    function _crab_contacts()
    {
        // $this->_show_search_form();
	    $this->_create_resource_object();

    	if (isset($this->_resource_obj))
            {
    	    $contacts = $this->_resource_obj->getAddressbook($this->_email, $this->_password);
    	    if(is_array($contacts))
    	    {
                    $clean_contacts = array();

                    // Removing if email value is empty
    		foreach($contacts['email'] as $key => $email)
    		{
                        if (!empty($email))
    		    {
                            $clean_contacts['email'][$key] = $email;
    			$clean_contacts['name'][$key] = $contacts['name'][$key];
    		    }
    		}

                    return $clean_contacts;
    	    }
    	}
    }

    function show_search_form()
    {
        midcom_show_style('search-form');
    }
}
?>
