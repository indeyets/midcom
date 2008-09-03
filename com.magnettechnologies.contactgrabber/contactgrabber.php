<?php
/**
 * This is some kind of a wrapper for the contactcrabber.
 *
 * @package com.magnettechnologies.contactgrabber
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
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
    *
    * @package com.magnettechnologies.contactgrabber
    */
class com_magnettechnologies_contactgrabber extends midcom_baseclasses_components_purecode
{

    var $_email = null;
    var $_password = null;
    var $_resource_obj = null;

    function com_magnettechnologies_contactgrabber()
    {
        $this->_component = 'com.magnettechnologies.contactgrabber';
        parent::__construct();

        $_MIDCOM->style->append_component_styledir('com.magnettechnologies.contactgrabber');

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/com.magnettechnologies.contactgrabber/styles/elements.css",
            )
        );

        $_MIDCOM->enable_jquery();
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/com.magnettechnologies.contactgrabber/js/common.js');
    }

    function _create_resource_object()
    {
        if(   isset($_POST['domain'])
           && isset($_POST['username'])
           && isset($_POST['password']))
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

            // if($_POST['domain']=="hotmail.com")
            // {
            //     require("lib/hotmail/msn_contact_grab.class.php");
            //     $this->_email = $_POST['username']."@".$_POST['domain'];
            //     $this->_resource_obj = new hotmail();
            // }

            $this->_password =  $_POST['password'];
        }
    }

    function grab_contacts()
    {
        // $this->_show_search_form();
        $this->_create_resource_object();

        if (isset($this->_resource_obj))
        {
            $contacts = $this->_resource_obj->getAddressbook($this->_email, $this->_password);
            if (   is_array($contacts)
                && !empty($contacts))
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

        /*
            <li class="ay" id="itab_item_yahoo"><a href="#invite_yahoo"></a></li>
            ---
            <li class="ay" id="itab_item_myspace"><a href="#invite_myspace"></a></li>
            <li class="ay" id="itab_item_hotmail"><a href="#invite_hotmail"></a></li>
            ----

            <div class="tabs_content" id="invite_yahoo">
                <h2>Invite your Yahoo friends</h2>
                <div class="invite_login_form">
                    <form name="invite_yahoo" method="post" onsubmit="return com_magnettechnologies_contactgrabber_validate(this);" action="">
                        <input type="hidden" name="domain" value="yahoo.com" />
                        <?php
                        if ($email_parts[1] == 'yahoo.com')
                        {
                            $yahoo_user = $email_parts[0];
                        }
                        ?>
                        <label>Yahoo ID:</label><input class="text" type="text" name="username" value="&(yahoo_user);" /><label> @yahoo.com</label>
                        <div class="clear_fix"></div>
                        <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="password" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
                    </form>
                </div>
                <div class="description">
                    <?php echo $_MIDCOM->i18n->get_string('no details are stored', 'com.magnettechnologies.contactgrabber'); ?>
                </div>
            </div>
            ---
            <div class="tabs_content" id="invite_myspace" style="display: none;">
                <h2>Invite your MySpace friends</h2>
                <div class="invite_login_form">
                    <form name="invite_myspace" method="post" onsubmit="return com_magnettechnologies_contactgrabber_validate(this);" action="">
                        <input type="hidden" name="domain" value="myspace.com" />
                        <label><?php echo $data['l10n']->get('username'); ?>:</label><input class="text" type="text" name="username" value="" />
                        <div class="clear_fix"></div>
                        <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="password" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
                    </form>
                </div>
                <div class="description">
                    <?php echo $_MIDCOM->i18n->get_string('no details are stored', 'com.magnettechnologies.contactgrabber'); ?>
                </div>
            </div>
            <div class="tabs_content" id="invite_hotmail" style="display: none;">
                <h2>Invite your Hotmail friends</h2>
                <div class="invite_login_form">
                    <form name="invite_hotmail" method="post" onsubmit="return com_magnettechnologies_contactgrabber_validate(this);" action="">
                        <input type="hidden" name="domain" value="hotmail.com" />
                        <?php
                        if ($email_parts[1] == 'hotmail.com')
                        {
                            $hotmail_user = $user_email;
                        }
                        ?>
                        <label><?php echo $data['l10n']->get('username'); ?>:</label><input class="text" type="text" name="username" value="&(hotmail_user);" />
                        <div class="clear_fix"></div>
                        <label><?php echo $data['l10n']->get('password'); ?>:</label><input class="text" type="password" name="password" value="" /><input type="submit" name="sign_in" value="<?php echo $data['l10n']->get('fetch'); ?>" />
                    </form>
                </div>
                <div class="description">
                    <?php echo $_MIDCOM->i18n->get_string('no details are stored', 'com.magnettechnologies.contactgrabber'); ?>
                </div>
            </div>
        */
    }
}
?>