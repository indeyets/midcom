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
    }

    function _show_search_form()
    {
    ?>
        <script language="javascript">
            function checkEmpty(frm)
            {
	        if (frm.username.value == "" || frm.password.value == "")
	        {
		    alert("Please enter username & password.");
		    frm.username.focus();
		    return false;
	        }
	        return true;
            }
           </script>

           <form  method="POST" onSubmit="return checkEmpty(this);" name="loginForm">
             <table border="0" cellpadding="2" cellspacing="0">
               <tr><td colspan="3" align="center"><?php echo $this->_l10n->get('enter login details to fetch your contacts'); ?></td></tr>
               <tr><td><?php echo $this->_l10n->get('username'); ?></td><td><input type="text" name="username" value="<?php /*echo $username;*/ ?>" /></td>
                 <td>	
                 <select name="domain" size="1">
		   <option value="gmail.com" <?php if (isset($_POST['domain']) && $_POST['domain']=="gmail.com") echo 'selected'; ?>>gmail</option>
		   <option value="hotmail.com" <?php if (isset($_POST['domain']) && $_POST['domain']=="hotmail.com") echo 'selected'; ?>>hotmail</option>
		   <option value="rediff.com" <?php if (isset($_POST['domain']) && $_POST['domain']=="rediff.com") echo 'selected'; ?>>rediff</option>		
		   <option value="yahoo.com" <?php if (isset($_POST['domain']) && $_POST['domain']=="yahoo.com") echo 'selected'; ?>>yahoo</option>
		   <option value="orkut.com" <?php if (isset($_POST['domain']) && $_POST['domain']=="orkut.com") echo 'selected'; ?>>orkut</option>
		   <option value="myspace.com" <?php if (isset($_POST['domain']) && $_POST['domain']=="myspace.com") echo 'selected'; ?>>myspace</option>
	         </select>
                 </td>
               </tr>
               <tr><td><?php echo $this->_l10n->get('password'); ?></td>
                 <td colspan="2"><input type="password" name="password" /></td>
              </tr>
              <tr><td colspan="3" align="center"><input type="submit" value="<?php echo $this->_l10n->get('fetch my contacts'); ?>" /></td></tr>    
              <tr><td colspan="3" align="center"><small><?php echo $this->_l10n->get('no details are stored'); ?></small></td></tr>    
            </table>
          </form>
      <?php
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
        $this->_show_search_form();
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
}
?>
