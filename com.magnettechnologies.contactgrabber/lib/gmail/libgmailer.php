<?php

/**
  * Contact Grabber
  * Version 1.0
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


ob_start();
/**
 * Include (require) this page if your application wish to use the class Gmailer.
 * This page (libgmailer.php) is the definition of 3 classes: GMailer, GMailSnapshot
 * and Debugger (deprecated).
 *
 * @package libgmailer.php
 */
 
/**
 * Constant defined by application author. Set it to true if the class is used as
 * a module of an online office app or other situation where PHP Session should NOT
 * by destroyed after signing out from Gmail.
 *
 * @var bool
 */
define("GM_USE_LIB_AS_MODULE",      false); // Normal operation

/**#@+ 
 * URL's of Gmail.
 * @var string 
 */
define("GM_LNK_GMAIL",              "https://mail.google.com/mail/");
define("GM_LNK_GMAIL_HTTP",         "http://mail.google.com/mail/");
// Changed by Gan; 10 Sept 2005
define("GM_LNK_LOGIN",              "https://www.google.com/accounts/ServiceLoginAuth");
// Added by Neerav; 4 Apr 2006
define("GM_LNK_LOGIN_REFER",        "https://www.google.com/accounts/ServiceLogin?service=mail&passive=true&rm=false&continue=http%3A%2F%2Fmail.google.com%2Fmail%3Fui%3Dhtml%26zy%3Dl&ltmpl=yj_blanco&ltmplcache=2&hl=en");
// Added by Neerav; 5 June 2005
define("GM_LNK_INVITE_REFER",       "https://www.google.com/accounts/ServiceLoginBox?service=mail&continue=https%3A%2F%2Fmail.google.com%2Fmail");
// Updated by Neerav; 22 Aug 2006
define("GM_USER_AGENT", "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5.0.6");

/**
 * @deprecated
 */
define("GM_LNK_LOGOUT",             "https://mail.google.com/mail/?logout");
define("GM_LNK_REFER",              "https://www.google.com/accounts/ServiceLoginBox?service=mail&continue=https%3A%2F%2Fmail.google.com%2Fmail");
define("GM_LNK_CONTACT",            "https://mail.google.com/mail/?view=cl&search=contacts&pnl=a");
define("GM_LNK_ATTACHMENT",         "https://mail.google.com/mail/?view=att&disp=att");
define("GM_LNK_ATTACHMENT_ZIPPED",  "https://mail.google.com/mail/?view=att&disp=zip");
/**#@-*/

/**#@+ 
 * Constants defining Gmail content's type.
 * @var int 
*/
define("GM_STANDARD",           0x001);
define("GM_LABEL",              0x002);
define("GM_CONVERSATION",       0x004);
define("GM_QUERY",              0x008);
define("GM_CONTACT",            0x010);
define("GM_PREFERENCE",         0x020);
/**#@-*/

/**#@+ 
 * Constants defining Gmail action.
 * @var int 
*/
/**
 * Apply label to conversation
*/
define("GM_ACT_APPLYLABEL",     1);
/**
 * Remove label from conversation
*/
define("GM_ACT_REMOVELABEL",    2);
/**
 * Star a conversation
*/
define("GM_ACT_STAR",           3);
/**
 * Remove a star from (unstar) a conversation
*/
define("GM_ACT_UNSTAR",         4);
/**
 * Mark a conversation as spam
*/
define("GM_ACT_SPAM",           5);
/**
 * Unmark a conversation from spam
*/
define("GM_ACT_UNSPAM",         6);
/**
 * Mark conversation as read
*/
define("GM_ACT_READ",           7);
/**
 * Mark conversation as unread
*/
define("GM_ACT_UNREAD",         8);
/**
 * Trash a conversation
*/
define("GM_ACT_TRASH",          9);
/**
 * Directly delete a conversation
*/
define("GM_ACT_DELFOREVER",     10);
/**
 * Archive a conversation
*/
define("GM_ACT_ARCHIVE",        11);
/**
 * Move conversation to Inbox
*/
define("GM_ACT_INBOX",          12);
/**
 * Move conversation out of Trash
*/
define("GM_ACT_UNTRASH",        13);
/**
 * Discard a draft
*/
define("GM_ACT_UNDRAFT",        14);
/**
 * Trash individual message.
*/ 
define("GM_ACT_TRASHMSG",       15);        
/**
 * Untrash (retrieve from trash) individual message.
 * @since 27 Feb 2006
*/ 
define("GM_ACT_UNTRASHMSG",     18);        
/**
 * Delete spam, forever.
*/ 
define("GM_ACT_DELSPAM",        16);
/**
 * Delete trash message, forever.
*/ 
define("GM_ACT_DELTRASHED",     17);
/**
 * Deleted trashed messages from the thread forever.
 * @since 27 Feb 2006
*/ 
define("GM_ACT_DELTRASHEDMSGS", 19);
/**#@-*/

/**#@+ 
 * Other constants.
*/
define("GM_VER", "0.9 Beta 4");
define("GM_COOKIE_KEY",         "LIBGMAILER");
define("GM_COOKIE_IK_KEY",      "LIBGMAILER_IdKey");    // Added by Neerav; 6 July 2005
define("GM_USE_COOKIE",         0x001);
define("GM_USE_PHPSESSION",     0x002);
if (!defined("GM_COOKIE_TTL"))  { 
    $ttl = 60*60*24*7;
    define("GM_COOKIE_TTL",     $ttl); 
}
/**#@-*/


/**
 * Class GMailer is the main class/library for interacting with Gmail (Google's
 * free webmail service) with ease.
 * 
 * <b>Acknowledgement</b><br/>It is not completely built from scratch. It is based on: "Gmail RSS feed in PHP"
 * by thimal, "Gmail as an online backup system" by Ilia Alshanetsky, and "Gmail
 * Agent API" by Johnvey Hwang and Eric Larson. 
 *
 * Special thanks to Eric Larson and all other users, testers, and forum posters
 * for their bug reports, comments and advices.
 *
 * @package GMailer
 * @author Gan Ying Hung <ganyinghung|no@spam|users.sourceforge.net>
 * @author Neerav Modi <neeravmodi|no@spam|users.sourceforge.net>
 * @link http://gmail-lite.sourceforge.net Project homepage
 * @link http://sourceforge.net/projects/gmail-lite Sourceforge project page
 * @version 0.8.0-rc
*/
class GMailer {
   /**#@+
    * @access private
    * @var string
   */
    var $cookie_str;
    var $login;
    var $pwd;
    /**
     * Email domain
     *
     * Support for "Gmail for your domain". 
     * Domain name of email address to check.  
     *
     * @access private
     * @var string
     * @author Neerav
     * @since 8 Jun 2006
    */
    var $domain;
    var $GM_LNK_GMAIL           = GM_LNK_GMAIL;
    var $GM_LNK_GMAIL_HTTP      = GM_LNK_GMAIL_HTTP;
    var $GM_LNK_LOGIN           = GM_LNK_LOGIN;
    var $GM_LNK_LOGIN_REFER     = GM_LNK_LOGIN_REFER;
    var $GM_LNK_INVITE_REFER    = GM_LNK_INVITE_REFER;
    
    /**
     * @author Neerav
     * @since 13 Aug 2005
    */
    var $gmail_data;
    /**
     * Raw packet
    */
    var $raw;
    /**
     * Raw packet for contact list
    */
    var $contact_raw;
    var $timezone;
    var $use_session;
    var $proxy_host;
    var $proxy_auth;    
    /**#@-*/     
    
    /**
     * Reserved mailbox names
    */
    var $gmail_reserved_names = array("inbox", "star", "starred", "chat", "chats", "draft", "drafts", 
            "sent", "sentmail", "sent-mail", "sent mail", "all", "allmail", "all-mail", "all mail",
            "anywhere", "archive", "spam", "trash", "read", "unread");
    
    /**
     * @access public
     * @var bool
    */
   var $created;
   /**
     * Status of GMailer
     *
     * If something is wrong, check this class property to see what is
     * going wrong.
     *
     * @author Neerav
     * @since 8 July 2005
     * @var mixed[]
     * @access public
    */
    var $return_status = array();

    
    /**
     * Constructor of GMailer
     *
     * During the creation of GMailer object, it will perform several tests to see
     * if the cURL extension is available or not. However, 
     * note that the constructor will NOT return false or null even if these tests
     * are failed. You will have to check the class property {@link GMailer::$created} to see if
     * the object "created" is really, uh, created (i.e. working), and property
     * {@link GMailer::$return_status} or method {@link GMailer::lastActionStatus()} to see what was going wrong.
     *
     * Example:
     * <code>
     * <?php
     *    $gmailer = new GMailer();
     *    if (!$gmailer->created) {
     *       echo "Error message: ".$gmailer->lastActionStatus("message");
     *    } else {
     *       // Do something with $gmailer
     *    }
     * ? >
     * </code>
     *
    * A typical usage of GMailer object would be like this:
    * <code>
    * <?php
    *    require_once("libgmailer.php");
    *
    *    $gmailer = new GMailer();
    *    if ($gmailer->created) {
    *       $gmailer->setLoginInfo($gmail_acc, $gmail_pwd, $my_timezone);
    *       $gmailer->setProxy("proxy.company.com");
    *       if ($gmailer->connect()) {
    *          // GMailer connected to Gmail successfully.
    *          // Do something with it.
    *       } else {
    *          die("Fail to connect because: ".$gmailer->lastActionStatus());
    *       }
    *    } else {
    *       die("Failed to create GMailer because: ".$gmailer->lastActionStatus());
    *    }
    * ? >
    * </code>    
     *
     * @see GMailer::$created, GMailer::$return_status, GMailer::lastActionStatus()
     * @return GMailer
    */
    function GMailer() {
        // GMailer needs "curl" extension to work
        $this->created = true;
        if (!extension_loaded('curl')) {
            // Added to gracefully handle multithreaded servers; by Neerav; 8 July 2005
            if (isset($_ENV["NUMBER_OF_PROCESSORS"]) and ($_ENV["NUMBER_OF_PROCESSORS"] > 1)) {
                $this->created = false;
                $a = array(
                    "action"        => "constructing GMailer object",
                    "status"        => "failed",
                    "message"       => "libgmailer: Using a multithread server. Ensure php_curl.dll has been enabled (uncommented) in your php.ini."
                );
                array_unshift($this->return_status, $a);

            } else {
                if (/*!dl('php_curl.dll') &&*/ !dl('curl.so')) {
                    $this->created = false;
                    $a = array(
                        "action"        => "constructing GMailer object",
                        "status"        => "failed",
                        "message"       => "libgmailer: unable to load curl extension."
                    );
                    array_unshift($this->return_status, $a);
                }
            }
        }
        if (!function_exists("curl_setopt")) {            
            $this->created = false;
            $a = array(
                "action"        => "constructing GMailer object",
                "status"        => "failed",
                "message"       => "libgmailer: No curl."
            );
            array_unshift($this->return_status, $a);
        }

        $this->login        = 0;
        $this->pwd          = 0;
        $this->domain       = "";   // blank for @gmail.com and @googlemail.com
        $this->proxy_host   = "";
        $this->proxy_auth   = "";
        $this->use_session  = 2;

        if ($this->created == true) {
            $a = array(
                "action"        => "constructing GMailer object",
                "status"        => "success",
                "message"       => "libgmailer: Constructing completed."
            );
            array_unshift($this->return_status, $a);
        }
    }
    
    /**
    * Set Gmail's login information.
    *
    * @return void
    * @param string $my_login Gmail's login name (without @gmail.com)
    * @param string $my_pwd Password
    * @param float $my_tz Timezone with respect to GMT, but in decimal. For example, -2.5 for -02:30GMT
    */
    function setLoginInfo($my_login, $my_pwd, $my_tz) {
        $this->login    = $my_login;
        $this->pwd      = $my_pwd;
        $this->timezone = strval($my_tz*-60);

        // Added return_status; by Neerav; 16 July 2005
        $a = array(
            "action"        => "set login info",
            "status"        => "success",
            "message"       => "libgmailer: LoginInfo set."
        );
        array_unshift($this->return_status, $a);
    }
    
    /**
    * Set domain information if the account is on a hosted domain.
    *
    * @return void
    * @param string $my_domain Hosted domain name (e.g., mydomain.org or sayni.net)
    */
    function setDomain($my_domain) {
        //return;
        $my_domain = strtolower(trim($my_domain));
        if ($my_domain === "") {
            $a = array(
                "action"        => "set hosted domain info",
                "status"        => "sucess",
                "message"       => "libgmailer: Using the default gmail/googlemail domain."
            );
            array_unshift($this->return_status, $a);
            return;
        }
        $success = true;
        if (preg_match("/gmail\.com|googlemail\.com/i",$my_domain)) {
            $a = array(
                "action"        => "set hosted domain info",
                "status"        => "failed",
                "message"       => "libgmailer: cannot use \"$my_domain\" as a hosted domain.  The default gmail.com or googlemail.com will be used."
            );
            array_unshift($this->return_status, $a);
            return;
        }
        $this->domain = $my_domain;
/*      $this->GM_LNK_GMAIL         = "https://mail.google.com/hosted/".$my_domain; */
/*      $this->GM_LNK_GMAIL_HTTP    = "http://mail.google.com/hosted/".$my_domain; */
/*      $this->GM_LNK_LOGIN         = "https://www.google.com/hosted/".$my_domain."/LoginAction"; */
/*      $this->GM_LNK_LOGIN_REFER   = "https://www.google.com/hosted/".$my_domain."/ServiceLogin?service=mail&passive=true&rm=false&continue=http%3A%2F%2Fmail.google.com%2Fhosted%2F".$my_domain."&ltmpl=yj_blanco&ltmplcache=2"; */
        // GFYD urls changed to GAFYD; Neerav; 1 Sept 2006
        $this->GM_LNK_GMAIL         = "https://mail.google.com/hosted/".$my_domain;
        $this->GM_LNK_GMAIL_HTTP    = "http://mail.google.com/hosted/".$my_domain;
        $this->GM_LNK_LOGIN         = "https://www.google.com/a/".$my_domain."/LoginAction";
        $this->GM_LNK_LOGIN_REFER   = "https://www.google.com/a/".$my_domain."/ServiceLogin?service=mail&passive=true&rm=false&continue=http%3A%2F%2Fmail.google.com%2Fhosted%2F".$my_domain."&ltmpl=yj_blanco&ltmplcache=2";
        $this->GM_LNK_INVITE_REFER  = "";

        $a = array(
            "action"        => "set hosted domain info",
            "status"        => "success",
            "message"       => "libgmailer: Domain set."
        );
        array_unshift($this->return_status, $a);
    }

    /**
    * Setting proxy server.
    *
    * Example:
    * <code>
    * <?php
    *    // proxy server requiring login
    *    $gmailer->setProxy("proxy.company.com", "my_name", "my_pwd");
    * 
    *    // proxy server without login
    *    $gmailer->setProxy("proxy2.company.com", "", "");
    * ? >
    * </code>
    *   
    * @return void
    * @param string $host Proxy server's hostname
    * @param string $username User name if login is required
    * @param string $pwd Password if required
    */
    function setProxy($host, $username, $pwd) {
        if (strlen($this->proxy_host) > 0) {
            $this->proxy_host = $host;
            if (strlen($username) > 0 || strlen($pwd) > 0) {
                $this->proxy_auth = $username.":".$pwd;
            }
            $a = array(
                "action"        => "set proxy",
                "status"        => "success",
                "message"       => "libgmailer: Proxy set."
            );
            array_unshift($this->return_status, $a);
        } else {
            $a = array(
                "action"        => "set proxy",
                "status"        => "failed",
                "message"       => "libgmailer: no hostname supplied."
            );
            array_unshift($this->return_status, $a);
        }
    }
    
    /**
    * Setting session management method.
    * 
    * You have to select a session management method so that GMailer would "remember"
    * your identity. Method has to be one of the following values:
    * 1. {@link GM_USE_COOKIE} | !{@link GM_USE_PHPSESSION} (if your server does not have PHP Session installed)
    * 2. !{@link GM_USE_COOKIE} | {@link GM_USE_PHPSESSION} (if your server have PHP Session installed, and don't want to set browser's cookie)
    * 3. {@link GM_USE_COOKIE} | {@link GM_USE_PHPSESSION} (if your server have PHP Session installed, and would like to use cookie to store session)
    *
    * @return void
    * @param int $method    
    */
    function setSessionMethod($method) {
        if ($method & GM_USE_PHPSESSION) {
            if (!extension_loaded('session')) {
                // Added to gracefully handle multithreaded servers; by Neerav; 8 July 2005
                if (isset($_ENV["NUMBER_OF_PROCESSORS"]) and ($_ENV["NUMBER_OF_PROCESSORS"] > 1)) {
                    $this->setSessionMethod(GM_USE_COOKIE | !GM_USE_PHPSESSION);  // forced to use custom cookie
                    $a = array(
                        "action"        => "load PHP session extension",
                        "status"        => "failed",
                        "message"       => "Using a multithread server. Ensure php_session.dll has been enabled (uncommented) in your php.ini."
                    );
                    array_unshift($this->return_status, $a);
                    return;
                } else {
                    // Changed extension loading; by Neerav; 18 Aug 2005
                    //if (!dl('php_session.dll') && !dl('session.so')) {
                    if (dl(((PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '') . 'session.' . PHP_SHLIB_SUFFIX)) {
                        $a = array(
                            "action"        => "load PHP session extension",
                            "status"        => "failed",
                            "message"       => "unable to load PHP session extension."
                        );
                        array_unshift($this->return_status, $a);
                        $this->setSessionMethod(GM_USE_COOKIE | !GM_USE_PHPSESSION);  // forced to use custom cookie
                        return;
                    }
                }
            }
            if (!($method & GM_USE_COOKIE)) {
                @ini_set("session.use_cookies",  0);
                @ini_set("session.use_trans_sid", 1);                    
                $a = array(
                    "action"        => "session",
                    "status"        => "success",
                    "message"       => "no using cookie"
                );
                array_unshift($this->return_status, $a);
            } else {
                @ini_set("session.use_cookies",  1);
                @ini_set("session.use_trans_sid", 0);
                $a = array(
                    "action"        => "session",
                    "status"        => "success",
                    "message"       => "using cookie"
                );
                array_unshift($this->return_status, $a);
            }
            @ini_set("arg_separator.output", '&amp;');

            error_reporting(E_ALL ^ E_NOTICE);
            session_start();
            error_reporting(E_ALL);

            $a = array(
                "action"        => "session",
                "status"        => "success",
                "message"       => "using PHP session"
            );
            array_unshift($this->return_status, $a);
            $this->use_session = true;            
        } else {
            //@ini_set("session.use_only_cookies", 1);
            @ini_set("session.use_cookies",  1);
            @ini_set("session.use_trans_sid", 0);
            $a = array(
                "action"        => "session",
                "status"        => "success",
                "message"       => "using cookie"
            );
            array_unshift($this->return_status, $a);
            $this->use_session = false;
        }
    }           

    /**
    * @return binary image
    * @desc 
    */
/*  function retrieveCaptcha($login, $logintoken) { */
/*      Debugger::say("retLogin: ".$login); */
/*      Debugger::say("retToken: ".$logintoken); */
/*       */
/*      $login = str_replace("@gmail.com",$login); */
/*      $c = curl_init(); */
/*  */
/*      curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); */
/*      curl_setopt($c, CURLOPT_BINARYTRANSFER, 1); */
/*      curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1); */
/*      curl_setopt($c, CURLOPT_URL, "https://www.google.com/accounts/Captcha?ctoken=".urlencode($logintoken)."&amp;email=".$login."@gmail.com"); */
/*      curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2); */
/*      curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT); */
/*      //  curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);              */
/*      $this->CURL_PROXY($c); */
/* //       curl_setopt($c, CURLOPT_HEADER, 1); */
/*      curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE); */
/*      curl_setopt($c, CURLOPT_REFERER, $this->GM_LNK_LOGIN); */
/*      $this->gmail_data = curl_exec($c); */
/*      curl_close($c); */
/*       */
/*      return $this->gmail_data; */
/*      //return 1; */
/*  } */

    /**
    * Connect to Gmail without setting any session/cookie
    *
    * @return bool Connect to Gmail successfully or not
    */
    function connectNoCookie() {
        $postdata  = "";
        if ($this->domain) {
            $postdata .= "&at=null";
            $postdata .= "&continue=".urlencode($this->GM_LNK_GMAIL);
            $postdata .= "&service=mail";
            $postdata .= "&userName=".urlencode($this->login);
            $postdata .= "&password=".urlencode($this->pwd);
        } else {
            // pre 31 Aug 2006
/*          $postdata .= "service=mail"; */
/*          $postdata .= "&Email=".urlencode($this->login); */
/*          $postdata .= "&Passwd=".urlencode($this->pwd); */
/*          $postdata .= "&null=Sign%20in"; */
/*          $postdata .= "&continue=".urlencode($this->GM_LNK_GMAIL); */
/*          // Added by Neerav; 28 June 2005 */
/*          $postdata .= "&rm=false";   // not required but appears */
/*          $postdata .= "&hl=en"; */
            // "fixed"?!?; by Neerav; 1 Sept 2006
            $postdata .= "&ltmpl=yj_blanco";
            $postdata .= "&ltmplcache=2";
            $postdata .= "&continue=".urlencode($this->GM_LNK_GMAIL);
            $postdata .= "&service=mail";
            $postdata .= "&rm=false";
            $postdata .= "&ltmpl=yj_blanco";
            $postdata .= "&hl=en";
            $postdata .= "&Email=".urlencode($this->login);
            $postdata .= "&Passwd=".urlencode($this->pwd);
            $postdata .= "&rmShown=1";
            $postdata .= "&null=Sign+in";

        }

/*      Debugger::say(print_r($this->pwd,true)); */
/*      Debugger::say(print_r(urlencode($this->pwd),true)); */
/*      Debugger::say(print_r(urlencode(urldecode($this->pwd)),true)); */
/*      Debugger::say(print_r($postdata,true)); */
/*      exit; */

        // Check for valid hosted domains; Added by Neerav; 10 June 2006
        if ($this->domain) {
            if (preg_match("/(^yahoo\.)|(^hotmail\.)|(^msn\.com$)|(^gala\.net$)|(^mail\.com$)|(^sayni\.net$)|(^gmail\.com$)|(^googlemail\.com$)/i",$this->domain)) {
                $a = array(
                    "action"        => "sign in",
                    "status"        => "failed",
                    "message"       => $this->domain." cannot be used as a 'Gmail for your domain' (hosted) domain.",
                    "login_error"   => "invalid_domain"
                );
                array_unshift($this->return_status, $a);
                return false;
            }

            // Domain test
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_LOGIN_REFER, 
                "", 
                'get', "",
                'nocookie', "", 
                false, // update cookie
                true // follow
            );

            ///Debugger::say("check for valid GFYD: ".print_r($this->gmail_data,true));
            ///exit;
            
            if (
                    strpos($this->gmail_data,"<p>Domain does not exist</p>") > 0
                        or
                    strpos($this->gmail_data,"Gmail for your domain - Server error") > 0
                ) {

                $a = array(
                    "action"        => "sign in",
                    "status"        => "failed",
                    "message"       => "Gmail for your domain - Server error -- Domain does not exist",
                    "login_error"   => "domain_nonexist"
                );
                array_unshift($this->return_status, $a);
                return false;
            }       
        }
        
        // Added by Neerav; 8 July 2005
        // login challenge
        //id="logintoken" value="cpVIYkaTDTkVZ9ZHNM_384GVV79tjExj-ac2NFVgS3AVbm7lEn7Q967JHKe_sDzMP7plluysBDJRyUwkjuHQFw:D0cwussDwRyIgJGSdeMMnA" name="logintoken"> 
        if (isset($this->logintoken)   and $this->logintoken != "")   $postdata .= "&logintoken=".$logintoken;
        if (isset($this->logincaptcha) and $this->logincaptcha != "") $postdata .= "&logincaptcha=".$logincaptcha;

        // just for testing
/*      $this->gmail_data = GMailer::execute_curl( */
/*          "https://www.google.com/accounts/ServiceLogin?service=mail&passive=true&rm=false&continue=http%3A%2F%2Fmail.google.com%2Fmail%2F%3Fui%3Dhtml%26zy%3Dl&ltmpl=yj_wsad&ltmplcache=2",  */
/*          "",  */
/*          'get',  */
/*          "",  */
/*          'nocookie', "",true,false */
/*      ); */
/*      Debugger::say("temp: ".print_r($this->gmail_data,true)); */
/*      $cookies = GMailer::get_cookies($this->gmail_data); */
/*      Debugger::say("cookies: $cookies"); */
/*      exit; */

        // The GMAIL_LOGIN cookie is now required since 31 Aug 2006; by Neerav; 1 Sept 2006
        $time = time();
        // we fake that the user loaded the browser 8-20 seconds ago
        $time_past = $time - rand(8,20);
        // create the cookie
        $cookie = "GMAIL_LOGIN=T$time_past/$time_past/$time";
        
        // added in case it's needed in the future; Neerav; 1 Sept 2006
/*      if ($this->domain) { */
/*          // Gmail's "user ping" */
/*          // pre first phase, sending word that this user is about to sign in. */
/*          $this->gmail_data = GMailer::execute_curl( */
/*              $this->GM_LNK_GMAIL."?gxlu=".urlencode($this->login)."&zx=".(time()-rand(2,6)).rand(100,999),  */
/*              (($this->domain) ? $this->GM_LNK_LOGIN : $this->GM_LNK_LOGIN_REFER),  */
/*              'get',  */
/*              "",  */
/*              'cookie', "GMAIL_LOGIN=T$time_past/$time_past/$time" */
/*          ); */
/*          Debugger::say("pre first phase: ".print_r($this->gmail_data,true)); */
/*      } */

        $this->gmail_data = GMailer::execute_curl(
            $this->GM_LNK_LOGIN, 
            (($this->domain) ? $this->GM_LNK_LOGIN : $this->GM_LNK_LOGIN_REFER), 
            'post', 
            $postdata, 
            // changed to use the required time cookie; by Neerav; 1 Sept 2006
            //'nocookie', ""
            (($this->domain) ? 'nocookie': 'cookie'), (($this->domain) ? '' : "$cookie")
        );

/*      Debugger::say("first phase: ".print_r($this->gmail_data,true)); */
/*      exit; */

        $a = array(
            "action"        => "connecting to Gmail (without cookie)",
            "status"        => (($this->gmail_data != "") ? "success" : "failed"),
            "message"       => (($this->gmail_data != "") ? "connected to Gmail (without cookie)" : "no response"),
            "login_error"   => (($this->gmail_data != "") ? "" : "no response")
        );
        array_unshift($this->return_status, $a);
        if ($this->gmail_data == "") return false;

        /** from here we have to perform "cookie-handshaking"... **/        
        $cookies = GMailer::get_cookies($this->gmail_data);
        ///Debugger::say("first phase cookies: ".print_r($cookies,true));
        ///print_r($cookies);
        ///exit;
        
        $this->logintoken   = "";
        $this->logincaptcha = "";

        // updated if condition for hosted domains; by Neerav; 8 June 2006
        if ((
                strpos($this->gmail_data, "errormsg_0_Passwd") > 0
                    or 
                strpos($this->gmail_data, "errormsg_0_password") > 0
            ) and 
                strpos($this->gmail_data, "Username and password do not match") > 0
            ){

            $this->cookie_str = "";
            $this->cookie_ik_str = "";
            
            // Added appropriate error message; by Neerav; 8 July 2005
            // Added error message for suggested username; by Neerav; 28 July 2006
            if (preg_match("/Did you mean(.*?)\?\)/i",$this->gmail_data,$userpass_match)) {
                $suggest = trim($userpass_match[1]);
                $a = array(
                    "action"        => "sign in",
                    "status"        => "failed",
                    "message"       => "Username and password do not match. (Did you mean ".$suggest." ?)",
                    "login_error"   => "userpass_suggest",
                    "login_suggest" => $suggest
                );
            } else {
                $a = array(
                    "action"        => "sign in",
                    "status"        => "failed",
                    "message"       => "Username and password do not match. (You provided ".$this->login.")",
                    "login_error"   => "userpass"
                );
            }
            array_unshift($this->return_status, $a);
            return false;

        // Blank username or password; Added by Neerav; 8 June 2006
        } elseif (
                (
                    strpos($this->gmail_data, "errormsg_0_password") > 0
                        or
                    strpos($this->gmail_data, "errormsg_0_userName") > 0
                        or 
                    strpos($this->gmail_data, "errormsg_0_username") > 0
                )   and 
                    strpos($this->gmail_data, "Required field must not be blank") > 0
            ) {
            $this->cookie_str = "";
            $this->cookie_ik_str = "";
            $a = array(
                "action"        => "sign in",
                "status"        => "failed",
                "message"       => "Required field must not be blank",
                "login_error"   => "blank"
            );
            array_unshift($this->return_status, $a);
            return false;

        // Added to support login challenge; by Neerav; 8 July 2005
        } elseif (strpos($this->gmail_data, "errormsg_0_logincaptcha") > 0) {
            $this->cookie_str = "";
            $this->cookie_ik_str = "";
            //id="logintoken" value="cpVIYkaTDTkVZ9ZHNM_384GVV79tjExj-ac2NFVgS3AVbm7lEn7Q967JHKe_sDzMP7plluysBDJRyUwkjuHQFw:D0cwussDwRyIgJGSdeMMnA" name="logintoken"> 
            ereg("id=\"logintoken\" value=\"([^\"]*)\" name=\"logintoken\"", $this->gmail_data, $matches);
            //Debugger::say("Connect FAILED: login challenge: ".$this->gmail_data);
            //Debugger::say("ErrorLogin: ".$this->login);
            //Debugger::say("ErrorToken: ".$matches[1]);
            //Debugger::say("logintoken: ".print_r($matches,true));
            // Added appropriate error message; by Neerav; 8 July 2005
            $a = array(
                "action"        => "sign in",
                "status"        => "failed",
                "message"       => "login challenge",
                "login_token"   => $matches[1],
                //"login_token_img" => urlencode("Captcha?ctoken=".$matches[1]."&amp;email=".$this->login."%40gmail.com"),
                //"login_token_img" => $login_img,
                //"login_cookie"    => $login_cookie,
                "login_error"   => "challenge"
                
            );
            array_unshift($this->return_status, $a);
            return false;

        // Check if the Gmail URL has changed; Added by Neerav; 14 Sept 2005
        } elseif (strpos($this->gmail_data, "Invalid request.")) {
            $this->cookie_str = "";
            $this->cookie_ik_str = "";
            
            $a = array(
                "action"        => "sign in",
                "status"        => "failed",
                "message"       => "Gmail: Invalid request. (libgmailer: Gmail seems to have changed the URL again.)",
                "login_error"   => "URL"
            );
            array_unshift($this->return_status, $a);
            return false;

        // Check for a cookie as a way to check the Gmail URL; Added by Neerav; 14 Sept 2005
        } elseif ($cookies == "") {
            $this->cookie_str = "";
            $this->cookie_ik_str = "";
            
            $a = array(
                "action"        => "sign in",
                "status"        => "failed",
                "message"       => "libgmailer: Phase one cookie not obtained. Gmail may be down.",
                "login_error"   => "cookie"
            );
            array_unshift($this->return_status, $a);
            return false;

        }
            
        $a = array(
            "action"        => "phase one cookie",
            "status"        => "success",
            "message"       => "Received: ".$cookies
        );
        array_unshift($this->return_status, $a);
        
        // Phase for hosted domains
        if (strpos($cookies, "HID=") !== false) {
            $preg = preg_match("/HID=([^ ;]*)/",$cookies,$hosted_auth);

            $a = array(
                "action"        => "phase 'hosted domain'",
                "status"        => "status",
                "message"       => "Redirect: to ".$this->GM_LNK_GMAIL_HTTP."?auth=[deleted]"
            );
            array_unshift($this->return_status, $a);
            
            ///Debugger::say("hosted phase auth: ".print_r($hosted_auth,true));
            ///print_r("hosted phase auth: ".print_r($hosted_auth,true));
            //exit;
            
            // execute hosted domain phase of signin
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL.'?auth='.$hosted_auth[1], 
                "", // no referrer 
                'get', "", 
                "cookie", $cookies,
                true,
                false // do not follow location
            );
    
            // hosted phase cookies
            //$data = GMailer::get_cookies($this->gmail_data);
            ///Debugger::say("hosted phase: ".print_r($this->gmail_data,true));
            ///Debugger::say("hosted phase cookies: ".print_r($data,true));
            //exit;         

            // status
            $a = array(
                "action"        => "phase 'hosted domain'",
                "status"        => "status",
                "message"       => "done"
            );
            array_unshift($this->return_status, $a);
        }

        // Forward url is now absolute instead of relative; Fixed by Gan; 27 July 2005
        $a = strpos($this->gmail_data, "Location: ");
        $b = strpos($this->gmail_data, "\n", $a);
        $forward = substr($this->gmail_data, $a+10, $b-($a+10));

        $a = array(
            "action"        => "redirecting",
            "status"        => "success",
            "message"       => "Redirecting to: ".$forward
        );
        array_unshift($this->return_status, $a);
        ///Debugger::say("Redirecting to: ".$forward);
            
        // Added extra required cookie; by Neerav; 4 Apr 2006
        $second = GMailer::execute_curl(
            $forward, 
            $this->GM_LNK_LOGIN_REFER, 
            'get', "", 
            "cookie", "GoogleAccountsLocale_session=en; ".$cookies
        );
        
        $data = GMailer::get_cookies($second);
        ///Debugger::say("second phase: ".print_r($second,true));
        ///Debugger::say("second phase cookies: ".print_r($data,true));
        
        $a = array(
            "action"        => "phase two cookie",
            "status"        => "success",
            "message"       => "Obtained: ".$data
        );
        array_unshift($this->return_status, $a);
                 
/*      $this->cookie_str = $cookies.";".$d;  // the cookie string obtained from gmail */

        // Third phase required for some accounts.  Added by Neerav; April 2006     
        if (strpos($second, "SetSID") !== false) {
            $forward = preg_match("/<meta content=\"0;\s*url='?([^\"\']*)'?\"/",$second,$matches);
            $a = array(
                "action"        => "phase three required",
                "status"        => "status",
                "message"       => "Redirect: ".str_replace("&amp;","&",$matches[1])
            );
            array_unshift($this->return_status, $a);
            
            ///print_r($forward."<br />\n");
            ///print_r($matches);

            // execute third phase of signin
            $third = GMailer::execute_curl(
                str_replace("&amp;","&",$matches[1]), 
                "", // no referrer 
                'get', "", 
                "nocookie", "");
            // third phase cookies
            $data = GMailer::get_cookies($third);
            ///Debugger::say("third phase: ".print_r($third,true));
            ///Debugger::say("third phase cookies: ".print_r($data,true));

            // status
            $a = array(
                "action"        => "phase three cookie",
                "status"        => "success",
                "message"       => "Obtained: ".$data
            );
            array_unshift($this->return_status, $a);            
        }

        $data = (($data) ? $data : $cookies)."; TZ=".$this->timezone;
        ///Debugger::say("cookies (pre-clean): ".print_r($data,true));

        // remove duplicate cookies
        $data = preg_replace("/GX=.*?;\s?GX=/","GX=",$data);
        // remove unnecessary LSID, if it still exists.
        $data = trim(preg_replace("/LSID=mail[^;]*?;/","",$data));
        ///Debugger::say("final cookies (corrected/cleaned): ".print_r($data,true));

        $this->cookie_str = $data;  

        $a = array(
            "action"        => "final gmail cookie",
            "status"        => "status",
            "message"       => "Cookie: ".$data
        );
        array_unshift($this->return_status, $a);            
        
        return true;
        
    }       
    
    /**
    * Connect to GMail with default session management settings.
    *
    * @return bool Connect to Gmail successfully or not
    */
    function connect() {
        if ($this->use_session === 2)
            $this->setSessionMethod(GM_USE_COOKIE | GM_USE_PHPSESSION);   // by default
        
        // already logged in
        if ($this->login == 0 && $this->pwd == 0) {
            if (!$this->getSessionFromBrowser()) {            
                return $this->connectNoCookie() && $this->saveSessionToBrowser();
            } else {
                $a = array(
                    "action"        => "connect",
                    "status"        => "success",
                    "message"       => "Connect completed by getting cookie/session from browser/server."
                );
                array_unshift($this->return_status, $a);
                return true;
            }

        // log in
        } else {
            // Changed to support login challenge; by Neerav; 8 July 2005 
            //return $this->connectNoCookie() && $this->saveSessionToBrowser();
            if ($this->connectNoCookie()) {
                return $this->saveSessionToBrowser();
            } else {
                return false;
            }
        }
    }
    
    /**
    * See if it is connected to GMail.
    *
    * @return bool
    */
    function isConnected() {
        return (strlen($this->cookie_str) > 0);
    }

    /**
    * Last action's action, status, message, and other info
    *
    * @param string $request What information you would like to request. Default is "message".
    * @return string
    */
    function lastActionStatus($request = "message") {
        if ($request == "message") {
            return preg_replace("/(\s|&nbsp;)*<(a|span)[^>]*[^<]*<\/(a|span)>/","",$this->return_status[0]["$request"]);
        } else {
            return $this->return_status[0]["$request"];
        }
    }

    /**
    * Append a random string to url to fool proxy
    *
    * @param string $type Set to "nodash" if you do not want a dash ("-") in random string. Otherwise just leave it blank.
    * @access private
    * @return string Complete URL
    * @author Neerav
    * @since June 2005
    */
    function proxy_defeat($type = "") {
        $length = 12;
        $seeds = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $string = '';
        $seeds_count = strlen($seeds);
         
        // Generate
        // Changed to also use without dash; by Neerav; 11 Aug 2005
        if ($type == "nodash") {
            for ($i = 0; $length > $i; $i++) {
                $string .= $seeds{mt_rand(0, $seeds_count - 1)};
            }
        } else {
            for ($i = 0; $length > $i; $i++) {
                $string .= $seeds{mt_rand(0, $seeds_count - 1)};
                if ($i == 5) $string .= "-";    // Added by Neerav; 28 June 2005
            }
        }
     
        return "&zx=".$string;
    }

    /**
    * Fetch contents by URL query. 
    *
    * This is a "low-level" method. Please use {@link GMailer::fetchBox()} for fetching standard contents.
    *
    * @param string $query URL query string
    * @return bool Success or not
    */
    function fetch($query) {
        if ($this->isConnected() == true) {
            Debugger::say("Start fetching query: ".$query);
            $query .= $this->proxy_defeat();     // to fool proxy

            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?".$query,
                GM_LNK_REFER,
                'get'
            );
            GMailer::parse_gmail_response($this->gmail_data);

            Debugger::say("Fetch completed.");
            return 1;
        
        } else {      // not logged in yet               
            Debugger::say("Fetch FAILED: not connected.");
            return 0;
            
        }
    }
    
    /**
    * Fetch contents from Gmail by type.
    *
    * Content can be one of the following categories:
    * 1. {@link GM_STANDARD}: For standard mail-boxes like Inbox, Sent Mail, All, etc. In such case, $box should be the name of the mail-box: "inbox", "all", "sent", "draft", "spam", or "trash". $paramter would be used for paged results.
    * 2. {@link GM_LABEL}: For user-defined label. In such case, $box should be the name of the label.
    * 3. {@link GM_CONVERSATION}: For conversation. In such case, $box should be the conversation ID and $parameter should be the mailbox/label in which the message is found (if supplied 0, it will default to "inbox").
    * 4. {@link GM_QUERY}: For search query. In such case, $box should be the query string.
    * 5. {@link GM_PREFERENCE}: For Gmail preference. In such case, $box = "".
    * 6. {@link GM_CONTACT}: For contact list. In such case, $box can be either "all", "search", "detail", "group", or "group detail". When $box = "detail", $parameter is the Contact ID. When $box = "search", $parameter is the search query string.
    *
    * @return bool Success or not
    * @param constant $type Content category 
    * @param mixed $box Content type
    * @param int $parameter Extra parameter. See above.
    * @see GM_STANDARD, GM_LABEL, GM_CONVERSATION, GM_QUERY, GM_PREFERENCE, GM_CONTACT
    */
    function fetchBox($type, $box, $parameter) {
        if ($this->isConnected() == true) {
            switch ($type) {
                case GM_STANDARD:
                    $q = "search=".strtolower($box)."&view=tl&start=".$parameter;
                    break;
                case GM_LABEL:
                    $q = "search=cat&cat=".$box."&view=tl&start=".$parameter;
                    break;
                case GM_CONVERSATION:
                    if ($parameter === 0 or $parameter == "") $parameter = "inbox";
                    if (in_array(strtolower($parameter),$this->gmail_reserved_names)) {
                        $q = "search=".urlencode($parameter)."&ser=1&view=cv";
                    } else {
                        $q = "search=cat&cat=".urlencode($parameter)."&ser=1&view=cv";
                    }
                    if (is_array($box)) {
                        $q .= "&th=".$box[0];
                        for ($i = 1; $i < count($box); $i++)
                            $q .= "&msgs=".$box[$i];
                    } else {
                        $q .= "&th=".$box;
                    }
                    break;
                case GM_QUERY:
                    $q = "search=query&q=".urlencode($box)."&view=tl&start=".$parameter;
                    break;
                case GM_PREFERENCE:
                    $q = "view=pr&pnl=g";
                    break;
                case GM_CONTACT:
                    if (strtolower($box) == "all")
                        $q = "view=cl&search=contacts&pnl=a";
                    elseif (strtolower($box) == "search")   // Added by Neerav; 15 June 2005
                        $q = "view=cl&search=contacts&pnl=s&q=".urlencode($parameter);
                    elseif (strtolower($box) == "detail")   // Added by Neerav; 1 July 2005
                        $q = "search=contacts&ct_id=".$parameter."&cvm=2&view=ct".$this->proxy_defeat();
                    elseif (strtolower($box) == "group_detail") // Added by Neerav; 6 Jan 2006
                        $q = "search=contacts&ct_id=".$parameter."&cvm=1&view=ctl".$this->proxy_defeat();
                    elseif (strtolower($box) == "group")
                        $q = "view=cl&search=contacts&pnl=l";
                    else // frequently mailed
                        $q = "view=cl&search=contacts&pnl=p";
                    break;                      
                default:
                    $q = "search=inbox&view=tl&start=0&init=1";
                    break;
            }
            $this->fetch($q);
            return true;
        } else {
            return false;
        }
    }        
    
    /**
    * Save all attaching files of conversations to a path.
    *
    * Random number will be appended to the new filename if the file already exists.
    *
    * @return string[] Name of the files saved. False if failed.
    * @param string[] $convs Conversations.
    * @param string $path Local path.
    */
    function getAttachmentsOf($convs, $path) {
        if ($this->isConnected() == true) {
            if (!is_array($convs)) {
                $convs = array($convs);  // array wrapper
            }
            $final = array();
            foreach ($convs as $v) {
                if (count($v["attachment"]) > 0) {
                    foreach ($v["attachment"] as $vv) {
                        $f = $path."/".$vv["filename"];
                        while (file_exists($f)) {
                            $f = $path."/".$vv["filename"].".".round(rand(0,1999));
                        }
                        if ($this->getAttachment($vv["id"],$v["id"],$f,false)) {
                            array_push($final, $f);
                        }
                    }
                }
            }
            return $final;
        } else {
            return false;
        }
    }                               
    
    /**
    * Save attachment with attachment ID $attach_id and message ID $msg_id to file with name $filename.
    *
    * @return bool Success or not.
    * @param string $attach_id Attachment ID.
    * @param string $msg_id Message ID.
    * @param string $filename File name.
    * @param bool $action {zip = Save all attachments into a zip file; thumb = retrieve picture thumbnail; convert = view html converted form of doc/pdf/xls/rtf/ppt}.
    * @param bool $thumbnail download the attachment's thumbnail.
    */
    function getAttachment($attach_id, $msg_id, $filename, $action = "") {
        if ($this->isConnected() == true) {
            Debugger::say("Start getting attachment...");
            
            if ($action == "thumb") {
                // view thumbnail; Added by Neerav; 22 Aug 2006
                $query = $this->GM_LNK_GMAIL."?view=att&disp=thd&attid=".urlencode($attach_id)."&th=".urlencode($msg_id);
            } elseif ($action == "zip") {
                // all attachments zipped
                $query = $this->GM_LNK_GMAIL."?view=att&disp=zip&th=".urlencode($msg_id);
            } elseif ($action == "convert") {
                // pdf, rtf, xls, doc, ppt converted into html
                // ** using Gmail Mobile to retrieve this **
                $query = "x/".str_replace("&zx=","",$this->proxy_defeat("nodash"))."-/?disp=vah&attid=".urlencode($attach_id)."&th=".urlencode($msg_id)."&v=att";
                $this->gmail_data = GMailer::execute_curl(
                    $this->GM_LNK_GMAIL_HTTP.$query,
/*                  $this->GM_LNK_GMAIL_HTTP."x/".str_replace("&zx=","",$this->proxy_defeat("nodash"))."-/?mi=0&th=".urlencode($msg_id)."&v=att", */
                    "",//$this->GM_LNK_GMAIL_HTTP."x/".$this->proxy_defeat("nodash")."-/?v=cmf",
                    'get',"",
                    "noheader"
                );
                //Debugger::say(print_r($this->gmail_data,true));
                if (strpos($this->gmail_data,'gmail_error=') !== false) {
                    preg_match("/gmail_error=(\d+);/",$this->gmail_data,$error_num);
                    $error = (isset($error_num[1]))  ? $error_num[1] : "" ;
                    if (    $error != 25    // 
                        and $error != 15    // attachment cannot be viewed, e.g. trying to convert a jpeg into html!
                        and $error != 58    // server error (what's the real reason?)
                        and $error != 7     // attachment doesn't exist (gmail gives "server error")
                        ) {
                        Debugger::say(print_r($this->gmail_data,true),"error_log.glib.as_html.php");
                    }

                    // tries to regex the error message from Gmail's response
                    if ($error == 58 or $error == 7) {
                        preg_match("@<p>(.*?)</p>@is",$this->gmail_data,$match_error);
                    } else {
                        preg_match('@<p><font size="-1">(.*?)</font></p>@is',$this->gmail_data,$match_error);
                    }

                    if (isset($match_error[1]) and trim($match_error[1]) != "") {
                        $message = trim($match_error[1]);
                    } else {
                        Debugger::say("The error message did not match the regex: \n".print_r($this->gmail_data,true),"error_log.glib.as_html.php");
                        $message = "Gmail reported an unknown error.<br/><br/><b>DO NOT REPEAT OR RELOAD.</b>";
                    }

                    $a = array(
                        "action"    => "view as html",
                        "status"    => "failed",
                        "message"   => "<p>$message</p>",
                        "error"     => $error
                    );
                    array_unshift($this->return_status, $a);
                    return false;
                } 
                preg_match("/<body>(.*)<\/body>/",$this->gmail_data,$match);
                //Debugger::say(print_r($match[1],true));
                $a = array(
                    "action"    => "view as html",
                    "status"    => "success",
                    "message"   => "attachment converted to html and received",
                    "error"     => 0,
                    "as_html"   => $match[1]
                );
                array_unshift($this->return_status, $a);
                return true;
            } else {
                // download attachment
                $query = $this->GM_LNK_GMAIL."?view=att&disp=attd&attid=".urlencode($attach_id)."&th=".urlencode($msg_id);
            }

            // view all images
/*              $query = $this->GM_LNK_GMAIL."?view=att&disp=imgs&th=".urlencode($msg_id); */
            // view image inline
/*              $query = $this->GM_LNK_GMAIL."?view=att&disp=inline&attid=".urlencode($attach_id)."&th=".urlencode($msg_id); */


            $fp = fopen($filename, "wb");
            if ($fp) {
                $c = curl_init();
                curl_setopt($c, CURLOPT_FILE, $fp);
                curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
                curl_setopt($c, CURLOPT_URL, $query);
                curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
                $this->CURL_PROXY($c);
                curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);  
                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
                curl_setopt($c, CURLOPT_REFERER, GM_LNK_REFER);
                curl_exec($c);
                curl_close($c);
                fclose($fp);
            } else {
                Debugger::say("FAILED to get attachment: cannot fopen the file.");
                return false;
            }
            Debugger::say("Completed getting attachment.");
            return true;
        } else {
            Debugger::say("FAILED to get attachment: not connected.");
            return false;
        }
    }           
            
    /**
    * Dump everything to output.
    *
    * This is a "low-level" method. Use the method {@link GMailer::fetchBox()} to fetch standard contents from Gmail.
    *
    * @return string Everything received from Gmail.
    * @param string $query URL query string.
    */
    function dump($query) {
        if ($this->isConnected() == true) {
            Debugger::say("Dumping...");
            $query .= $this->proxy_defeat();     // to fool proxy
            $this->gmail_data = GMailer::execute_curl( 
                $this->GM_LNK_GMAIL."?".$query, 
                GM_LNK_REFER, 
                'get', "", "noheader", ""
            );
            Debugger::say("Finished dumping ".strlen($this->gmail_data)." bytes.");           
            return $this->gmail_data;
        } else {      // not logged in yet               
            Debugger::say("FAILED to dump: not connected.");
            return "";
        }
    }        
    
    /**
    * Send Gmail. Or save a draft email.
    *
    * Examples:
    * <code>
    * <?php
    *    // Simplest usage: send a new mail to one person:
    *    $gmailer->send("who@what.com", "Hello World", "Cool!\r\nFirst mail!");
    *
    *    // More than one recipients. And with CC:
    *    $gmailer->send("who@what.com, boss@company.com",
    *                   "Hello World",
    *                   "This is second mail.",
    *                   "carbon-copy@company.com");
    *
    *    // With file attachment
    *    $gmailer->send("who@what.com", 
    *                   "Your file", 
    *                   "Here you are!", 
    *                   "", "", "", "", 
    *                   array("path/to/file.zip", "path/to/another/file.tar.gz"));
    *
    *    // more examples...
    * ? >
    * </code>
    *
    * @since 9 September 2005
    * @return bool Success or not. If returned false, please check {@link GMailer::$return_status} or {@link GMailer::lastActionStatus()} for error message.
    * @param string $to Recipient's address. Separated by comma for multiple recipients.
    * @param string $subj Subject line of email.
    * @param string $body Message body of email.
    * @param string $cc Address for carbon-copy (CC). Separated by comma for multiple recipients. $cc = "" for none.
    * @param string $bcc Address for blind-carbon-copy (BCC). Separated by comma for multiple recipients. $bcc = "" for none.
    * @param string $mid Message ID of the replying email. $mid = "" if this is a newly composed email.
    * @param string $tid Conversation (thread) ID of the replying email. $tid = "" if this is a newly composed email.   
    * @param string[] $files File names of files to be attached.
    * @param bool $draft Indicate this email is saved as draft, or not.
    * @param string $orig_df If this email is saved as a <i>modified</i> draft, then set $orig_df as the draft ID of the original draft.
    * @param bool $is_html HTML/RTF-formatted mail, or not.
    * @param array $attachments Attachments (forwards) in the form of 0_messageIDthatContainedTheAttachment_attachmentID (e.g. 0_17ab83d2f68n2b_0.1 , 0_17ab83d2f68n2b_0.2)
    * @param string $from Send mail as this email address (personality). $from = "" to use default address in your settings. Note: the "default" behavior changed on 29 April 2006.  Note: you will NOT send your mail successfully if you do not register this address in your Gmail settings panel.
    */
    function send($to, $subj, $body, $cc="", $bcc="", $mid="", $tid="", $files=0, $draft=false, $orig_df="", $is_html=0, $from="", $attachments = array()) {
        if ($this->isConnected()) {                     
            $postdata = array();
            if ($draft == true) {
                $postdata["view"]   = "sd";
                $postdata["draft"]  = $orig_df;
                $postdata["rm"]     = $mid;
                $postdata["th"]     = $tid;         
            } else {
                $postdata["view"]   = "sm";
                $postdata["draft"]  = $orig_df;
                $postdata["rm"]     = $mid;
                $postdata["th"]     = $tid;                               
            }

            $postdata["at"] = $this->at_value();

            // These are in the POST form, but do not know what they are
            // or what their values should be
            // Send works ok despite these being left out.
/*          $postdata["wid"]    = 8; */
/*          $postdata["jsid"]   = xxxxxxxxxx; */
/*          $postdata["ov"]     = ""; */
            //$postdata["cmid"]         = 1;          

            if (strlen($from) > 0) {
               $postdata["from"] = $from;
            }
            $postdata["to"]         = stripslashes($to);
            $postdata["cc"]         = stripslashes($cc);
            $postdata["bcc"]        = stripslashes($bcc);
            $postdata["subject"]    = stripslashes($subj);
            $postdata["ishtml"]     = ($is_html) ? 1 : 0;
            $postdata["msgbody"]    = stripslashes($body);
            
            // Added attachment/forward support; by Neerav; 22 Oct 2005
            // should be POST, but we fake it in GET
            $getdata = "";
            if (count($attachments) > 0) {
                for ($i=0; $i<count($attachments); $i++) {
                    $getdata .= "&attach=".$attachments[$i];
                }
            }  
            
            $new_attach = 0;
            if (is_array($files)) {
                // an array of files supplied
                $new_attach = count($files);
                for ($i = 0; $i < $new_attach; $i++) {
                    $postdata["file".$i] = "@".realpath($files[$i]);
                }
            } elseif ($files != 0) {
                // only one file attachment supplied
                $new_attach = 1;
                $postdata["file"] = "@".realpath($files);
            }
            //echo $postdata;
            // Changed to add attachment/forward support ($getdata); by Neerav; 22 Oct 2005
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?&search=inbox&qt=&cmid=&newatt=".$new_attach."&rematt=0".$getdata,
                $this->GM_LNK_GMAIL."?&view=cv&search=inbox&th=".$tid/* ."&lvp=4&cvp=1" */."&qt=".$this->proxy_defeat("nodash"),
                'post',
                $postdata
            );
            GMailer::parse_gmail_response($this->gmail_data);

            // Added by Neerav; 12 July 2005
            $status = (isset($this->raw["sr"][2])) ? $this->raw["sr"][2] : false;
            $a = array(
                "action"    => "send email",
                // $this->raw["sr"][1] // what is this?? // always 1
                "status"    => ($status ? "success" : "failed"),
                "message"   => (isset($this->raw["sr"][3]) ? $this->raw["sr"][3] : ""),
                "thread_id" => (isset($this->raw["sr"][4]) ? $this->raw["sr"][4] : ""),
                // $this->raw["sr"][5] // what is this?? // always 0
                // $this->raw["sr"][6] // what is this?? // always an empty array
                // $this->raw["sr"][7] // what is this?? // always 0
                // $this->raw["sr"][8] // what is this?? // always 0
                // $this->raw["sr"][9] // what is this?? // always 0
                // $this->raw["sr"][10] // what is this?? // always blank (or false)
                // $this->raw["sr"][11] // what is this?? // some kind of message/server id, but doesn't match any header
                // $this->raw["sr"][12] // what is this?? // always 0
                "sent_num"  => ((isset($this->raw["aa"][1])) ? count($this->raw["aa"][1]) : 0)
            );
            array_unshift($this->return_status, $a);
            // Changed by Neerav; 12 July 2005
            return $status;
        } else {
            // Added by Neerav; 12 July 2005
            $a = array(
                "action"        => "send email",
                "status"        => "failed",
                "message"       => "libgmailer: not connected.",
                "thread_id"     => $tid,
                "sent_num"      => 0
            );
            array_unshift($this->return_status, $a);

            return false;
        }
    }
            
    /**
    * Perform action on messages.
    *
    * Examples:
    * <code>
    * <?php
    *    // Apply label to $message_id
    *    $gmailer->performAction(GM_ACT_APPLYLABEL, $message_id, "my_label");
    *
    *    // Star $message_id
    *    $gmailer->performAction(GM_ACT_STAR, $message_id);
    *
    *    // more examples...
    * ? >
    * </code>
    *
    * @return bool Success or not. If returned false, please check {@link GMailer::$return_status} or {@link GMailer::lastActionStatus()} for error message.
      Additional return: Gmail returns a full datapack in response
    * @param constant $act Action to be performed.
    * @param string[] $id Message ID.
    * @param string $para Action's parameter:
    * 1. {@link GM_ACT_APPLYLABEL}, {@link GM_ACT_REMOVELABEL}: Name of the label.
    * @param string[] $mailbox Standard/Label mailbox name.  If this left out, actions will only work on messages in the Inbox.
    */
    function performAction($act, $id, $para="", $mailbox="") {
        // Fixed (un)trash, added delTrashedMsgs action; by Neerav; 27 Feb 2006
/*              $this->gmail_data = GMailer::execute_curl( */
/*                  $this->GM_LNK_GMAIL."?".$link */
/*                  $this->GM_LNK_GMAIL."?".$referrer, */
/*                  'post', */
/*                  $postdata */
/*              ); */
        if ($this->isConnected()) {         
            $postdata = "";
            $referrer = GM_LNK_REFER;
            $action_codes = array(
                "ib",   // nothing / placeholder
                "ac_",  // GM_ACT_APPLYLABEL
                "rc_",  // GM_ACT_REMOVELABEL
                "st",   // GM_ACT_STAR
                "xst",  // GM_ACT_UNSTAR
                "sp",   // GM_ACT_SPAM
                "us",   // GM_ACT_UNSPAM
                "rd",   // GM_ACT_READ
                "ur",   // GM_ACT_UNREAD
                "tr",   // GM_ACT_TRASH
                "dl",   // GM_ACT_DELFOREVER
                "rc_^i", // GM_ACT_ARCHIVE
                "ib",   // GM_ACT_INBOX
                "ib",   // GM_ACT_UNTRASH
                "dd",   // GM_ACT_UNDRAFT
                "dm",   // GM_ACT_TRASHMSG
                "dl",   // GM_ACT_DELSPAM
                "dl",   // GM_ACT_DELTRASHED
                "rtr",  // GM_ACT_UNTRASHMSG
                "dt"    // GM_ACT_DELTRASHEDMSGS
            );

            if ($act == GM_ACT_DELFOREVER)
                $this->performAction(GM_ACT_TRASH, $id, 0, $mailbox);   // trash it before
            
            //$postdata .= "ik=".$this->cookie_ik_str;

            $postdata .= "&act=";
            
            $postdata .= (isset($action_codes[$act])) ? $action_codes[$act] : $action_codes[GM_ACT_INBOX];
            if ($act == GM_ACT_APPLYLABEL || $act == GM_ACT_REMOVELABEL) {
                $postdata .= $para;
            }
            $postdata .= "&at=".$this->at_value();
            
            if ($act == GM_ACT_TRASHMSG || $act == GM_ACT_UNTRASHMSG) {
                $postdata .= "&m=".$id;
            } else {
                if (is_array($id)) {
                    foreach ($id as $t) {
                        $postdata .= "&t=".$t;
                    }
                } else {
                    $postdata .= "&t=".$id;
                }
                if ($act != GM_ACT_DELTRASHEDMSGS) {
                    $postdata .= "&vp=";
                    $postdata .= "&msq=";           // Added by Neerav; 25 Nov 2005
                    $postdata .= "&ba=false";       // Added by Neerav; 25 Nov 2005
                }
            }
            
            if ($act == GM_ACT_UNTRASH || $act == GM_ACT_DELFOREVER || $act == GM_ACT_DELTRASHED) {
                $query = "&search=trash";
            } elseif ($act == GM_ACT_DELSPAM) {
                $query = "&search=spam";
            } elseif ($mailbox != "") {
                switch ($mailbox) {
                    case "inbox":   $box_type = "std";  break;
                    case "starred": $box_type = "std";  break;
                    case "sent":    $box_type = "std";  break;
                    case "drafts":  $box_type = "std";  break;
                    case "all":     $box_type = "std";  break;
                    case "spam":    $box_type = "std";  break;
                    case "trash":   $box_type = "std";  break;
                    case "chats":   $box_type = "std";  break;
                    default:        $box_type = "label";    break;
                }

                if ($box_type == "std") {
                    $query = "&search=".$mailbox;
                } else {
                    $query = "&search=cat&cat=".urlencode($mailbox);
                    $referrer = $this->GM_LNK_GMAIL."?&search=cat&cat=".urlencode($mailbox)."&view=tl&start=0".$this->proxy_defeat();
                }
            } else {
                $query = "&search=query&q=";
            }

            if ($act == GM_ACT_TRASHMSG || $act == GM_ACT_UNTRASHMSG || $act == GM_ACT_DELTRASHEDMSGS) {
                $this->gmail_data = GMailer::execute_curl(
                    $this->GM_LNK_GMAIL."?"."&qt=".$query."&view=up".$postdata.$this->proxy_defeat(),
                    "",
                    'get'
                );
            } else {
                $this->gmail_data = GMailer::execute_curl(
                    $this->GM_LNK_GMAIL."?".$query."&view=tl&start=0",
                    $referrer,
                    'post',
                    $postdata
                );
            }
            GMailer::parse_gmail_response($this->gmail_data);
            
            // Added additional return info; by Neerav; 13 July 2005
            $status  = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            $a = array(
                "action"        => "message action",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);
            return $status;
        } else {
            // Added by Neerav; 12 July 2005
            $a = array(
                "action"        => "message action",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);

            return false;
        }
    }           
    
    /**
    * @return bool Success or not.
    * @desc Recover session information.
    */
    function getSessionFromBrowser() {
        Debugger::say("Start getting session from browser...");
        
        if (!$this->use_session) {
            return $this->getCookieFromBrowser();
        }
        // Changed to support IK; by Neerav; 13 July 2005
        // Last modified by Neerav; 14 Aug 2005
        if (isset($_SESSION[GM_COOKIE_KEY])) {
            $this->cookie_str = base64_decode($_SESSION[GM_COOKIE_KEY]);
            Debugger::say("Completed getting session from server: ".$this->cookie_str);

            if (isset($_SESSION['id_key'])) {
                $this->cookie_ik_str = $_SESSION['id_key'];
                Debugger::say("Completed getting ik from server: ".$this->cookie_ik_str);
            } else {
                Debugger::say("FAILED to read id_key from server.");
            }
            return true;
        } else {
            Debugger::say("FAILED to read ".GM_COOKIE_KEY." or ".'id_key'." from server.");
/*          Debugger::say("FAILED to read cookie ".GM_COOKIE_KEY." from browser."); */
            return false;
        }
    }
    
    /**
    * @return bool Success or not.
    * @desc Get cookies from browser.
    */
    function getCookieFromBrowser() {
        Debugger::say("Start getting cookie from browser...");
        
        if (!isset($_COOKIE)) {
            Debugger::say("FAILED to get any cookie from browser.");
            return false;
        }
        if (count($_COOKIE) == 0) {
            Debugger::say("FAILED to get non-empty cookie array from browser.");
            return false;
        }
        // Changed to support IK cookie; by Neerav; 8 July 2005
        // Disabled IK cookie requirement
        //if (isset($_COOKIE[GM_COOKIE_KEY]) and isset($_COOKIE[GM_COOKIE_IK_KEY])) {
        if (isset($_COOKIE[GM_COOKIE_KEY]) and $_COOKIE[GM_COOKIE_KEY]) {
            $this->cookie_str = base64_decode($_COOKIE[GM_COOKIE_KEY]);
            Debugger::say("Completed getting cookie from browser: ".$this->cookie_str);

            if (isset($_COOKIE[GM_COOKIE_IK_KEY]) and $_COOKIE[GM_COOKIE_IK_KEY]) {
                $this->cookie_ik_str = base64_decode($_COOKIE[GM_COOKIE_IK_KEY]);
                Debugger::say("Completed getting ik cookie from browser: ".$this->cookie_ik_str);
            }
            return true;
        } else {
            //Debugger::say("FAILED to read cookie ".GM_COOKIE_KEY." or ".GM_COOKIE_IK_KEY." from browser.");
            Debugger::say("FAILED to read cookie ".GM_COOKIE_KEY." from browser.");
            return false;
        }
    }        
    
    /**
    * @return bool Success or not.
    * @desc Save session data.
    */
    // Replaced Debugger calls with detailed status info; by Neerav; 2 May 2006
    function saveSessionToBrowser() {       
        if ($this->isConnected()) {
            if (!$this->use_session)
                return $this->saveCookieToBrowser();                
            
            $_SESSION[GM_COOKIE_KEY] = base64_encode($this->cookie_str);
            $a = array(
                "action"        => "save cookie to server",
                "status"        => "success",
                "message"       => "Saved cookie to server"
            );
            array_unshift($this->return_status, $a);
            return true;
        }

        $a = array(
            "action"        => "save cookie to server",
            "status"        => "failed",
            "message"       => "not connected"
        );
        array_unshift($this->return_status, $a);
        return false;
    }
    
    /**
    * @return bool Success or not.
    * @desc Save (send) cookies to browser.
    */
    // Replaced Debugger calls with detailed status info; by Neerav; 2 May 2006
    function saveCookieToBrowser() {              
        if ($this->isConnected()) {
            
            if (strpos($_SERVER["HTTP_HOST"],":"))
                $domain = substr($_SERVER["HTTP_HOST"],0,strpos($_SERVER["HTTP_HOST"],":"));
            else
                $domain = $_SERVER["HTTP_HOST"];

            // Fixed cookie expiration bug; by Neerav; 1 May 2006
            //header("Set-Cookie: ".GM_COOKIE_KEY."=".base64_encode($this->cookie_str)."; Domain=".$domain.";");
            setcookie(GM_COOKIE_KEY, base64_encode($this->cookie_str), time()+GM_COOKIE_TTL, "", $domain);
            $a = array(
                "action"        => "save gmail cookie to browser",
                "status"        => "success",
                "message"       => "Saved cookie with domain: ".$domain
            );
            array_unshift($this->return_status, $a);
            return true;
        }
        $a = array(
            "action"        => "save gmail cookie to browser",
            "status"        => "failed",
            "message"       => "not connected"
        );
        array_unshift($this->return_status, $a);
        return false;
    }
    
    /**
    * @return bool Success or not.
    * @desc Remove all session information related to Gmailer.
    */
    // Replaced Debugger calls with detailed status info; by Neerav; 2 May 2006
    function removeSessionFromBrowser() {
        if (!$this->use_session)
            return $this->removeCookieFromBrowser();
        
        // Changed/Added by Neerav; 6 July 2005
        // determines whether session should be preserved or normally destroyed
        if (GM_USE_LIB_AS_MODULE) {
            // if this lib is used as a Gmail module in some other app (e.g. 
            //     "online office"), don't destroy session

            // Let's unset session variables
            if (isset($_SESSION[GM_COOKIE_KEY])) unset($_SESSION[GM_COOKIE_KEY]);
            if (isset($_SESSION['id_key'])) unset($_SESSION['id_key']);
            $a = array(
                "action"        => "clear session from browser",
                "status"        => "success",
                "message"       => "Cleared libgmailer related session info. Session preserved for other use."
            );
            array_unshift($this->return_status, $a);
        } else {
            // otherwise (normal) unset and destroy session
            @session_unset();
            @session_destroy();
            $a = array(
                "action"        => "destroy session from browser",
                "status"        => "success",
                "message"       => "Removed session: ".GM_COOKIE_KEY.". Finished removing session from browser."
            );
            array_unshift($this->return_status, $a);
        }
        return true;
    }
    
    /**
    * @return bool
    * @desc Remove all related cookies stored in browser.
    */
    // Replaced Debugger calls with detailed status info; by Neerav; 2 May 2006
    function removeCookieFromBrowser() {
        if (isset($_COOKIE)) {
            // Changed to include IK cookie; by Neerav; 8 July 2005
            if (isset($_COOKIE[GM_COOKIE_KEY]) or isset($_COOKIE[GM_COOKIE_IK_KEY])) {
                // libgmailer cookies exist
                if (strpos($_SERVER["HTTP_HOST"],":"))
                    $domain = substr($_SERVER["HTTP_HOST"],0,strpos($_SERVER["HTTP_HOST"],":"));
                else
                    $domain = $_SERVER["HTTP_HOST"];
                
/*              header("Set-Cookie: ".GM_COOKIE_KEY."=0; Discard; Domain=".$domain.";"); */
/*              header("Set-Cookie: ".GM_COOKIE_IK_KEY."=0; Discard; Domain=".$domain.";"); */
                // Fixed cookie expiration bug; by Neerav; 1 May 2006
                setcookie(GM_COOKIE_KEY, "", 1, "", $domain);
                setcookie(GM_COOKIE_IK_KEY, "", 1, "", $domain);
                $a = array(
                    "action"        => "remove cookies",
                    "status"        => "success",
                    "message"       => "Removed cookies: ".GM_COOKIE_KEY." and ".GM_COOKIE_IK_KEY."with domain: ".$domain
                );
                array_unshift($this->return_status, $a);
                return true;
            } else {
                $a = array(
                    "action"        => "remove cookies",
                    "status"        => "failed",
                    "message"       => "Cannot find libgmailer cookies: ".GM_COOKIE_KEY." or ".GM_COOKIE_IK_KEY
                );
                array_unshift($this->return_status, $a);
                return false;
            }
        } else {
            $a = array(
                "action"        => "remove cookies",
                "status"        => "failed",
                "message"       => "Cannot find any cookie from browser."
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }                    
    
    /**
    * @return void
    * @desc Disconnect from Gmail.
    */
    function disconnect() {
        Debugger::say("Start disconnecting...");
        
        /** logout from mail.google.com too **/
        $this->gmail_data = GMailer::execute_curl(
            // Updated by Neerav; 28 June 2005
            $this->GM_LNK_GMAIL."?logout&hl=en".$this->proxy_defeat("nodash"),
            $this->GM_LNK_GMAIL."?&ik=&search=inbox&view=tl&start=0&init=1".$this->proxy_defeat("nodash"),
            'get'
        );
        //GMailer::parse_gmail_response($this->gmail_data);
        //Debugger::say("logout: ".$this->gmail_data);

        Debugger::say("Logged-out from GMail.");
        
        $this->removeSessionFromBrowser();
        $this->cookie_str = "";
        $this->cookie_ik_str = "";  // Added to support IK; by Neerav; 13 July 2005
        
        Debugger::say("Completed disconnecting.");
    }
    
    /**
    * Get {@link GMailSnapshot} by type.
    *
    * Examples:
    * <code>
    * <?php
    *    // For "Inbox"
    *    $gmailer->fetchBox(GM_STANDARD, "inbox", 0);
    *    $snapshot = $gmailer->getSnapshot(GM_STANDARD);
    *
    *    // For conversation
    *    $gmailer->fetchBox(GM_CONVERSATION, $thread_id, 0);
    *    $snapshot = $gmailer->getSnapshot(GM_CONVERSATION);
    * ? >
    * </code>
    *
    * @return GMailSnapshot
    * @param constant $type
    * @see GMailSnapshot
    * @see GM_STANDARD, GM_LABEL, GM_CONVERSATION, GM_QUERY, GM_PREFERENCE, GM_CONTACT  
    */
    function getSnapshot($type) {
        // Comment by Neerav; 9 July 2005
        // $type slowly will be made unnecessary as we move towards included all response
        //     fields in the snapshot
        
        if (!($type & (GM_STANDARD|GM_LABEL|GM_CONVERSATION|GM_QUERY|GM_PREFERENCE|GM_CONTACT))) {
            // if not specified, assume normal by default
            $type = GM_STANDARD;
        }

        // Changed by Neerav; use_session Fix by Dave DeLong <daveATdavedelongDOTcom>; 9 July 2005
        // Added $this->gmail_data to handle http errors; by Neerav; 16 Sept 2005
        return new GMailSnapshot($type, $this->raw, $this->use_session,$this->gmail_data);
    }
    
    /**
    * Send an invite
    *
    * @return bool Success or not. Note that it will still be true even if $email is an illegal address.
    * @param string $email
    * @desc Send Gmail invite to $email
    */
    function invite($email) {
        // Invalid feature for hosted domains; Added by Neerav; 9 June 2006
        if ($this->domain) {
            $a = array(
                "action"        => "invite",
                "status"        => "failed",
                "message"       => "libgmailer: feature not available for hosted domains"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
        
        if ($this->isConnected()) {         
            $postdata = "act=ii&em=".urlencode($email);
            $postdata .= "&at=".$this->at_value();

            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?view=ii",
                $this->GM_LNK_INVITE_REFER,
                'post',
                $postdata
            );
            // Added status message parsing and return; by Neerav; 6 Aug 2005
            GMailer::parse_gmail_response($this->gmail_data);
            
            // Added by Neerav; 6 Aug 2005
            $status  = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            $a = array(
                "action"        => "invite",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);

            return $status;
        } else {
            // Added by Neerav; 6 Aug 2005
            $a = array(
                "action"        => "invite",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);

            return false;
        }
    }
    
    /**
    * Get names of standard boxes.
    *
    * @static
    * @return string[]
    * @deprecated
    */
    function getStandardBox() {
        return array("Inbox","Starred","Sent","Drafts","All","Spam","Trash");
    }        
    
    /**
    * Get raw packet Gmailer::$raw
    *
    * @access private
    * @return mixed
    */
    function dump_raw() {
        return $this->raw;
    }        

    /**
    * Get full contents of $gmail_data (complete response from Gmail)
    *
    * @access private
    * @return mixed
    * @author Neerav
    * @since 13 Aug 2005
    */
    function debug_gmail_response() {
        return $this->gmail_data;
    }
         
    /**
    * cURL "helper" for proxy.
    *
    * @access private
    * @return void
    * @param curl_descriptor $cc
    */
    function CURL_PROXY($cc) {
        if (strlen($this->proxy_host) > 0) {
            curl_setopt($cc, CURLOPT_PROXY, $this->proxy_host);
            if (strlen($this->proxy_auth) > 0)
                curl_setopt($cc, CURLOPT_PROXYUSERPWD, $this->proxy_auth);
        }
    }
    
    /**
    * Extract cookies from HTTP header.
    *
    * @return string Cookies string
    * @param string $header HTTP header
    * @access private
    * @static
    */
    function get_cookies($header) {
        $match = "";
        $cookie = "";

        preg_match_all('!Set-Cookie: ([^;\s]+)($|;)!', $header, $match);
        //Debugger::say("New cookies: ".print_r($match,true)); 

        foreach ($match[1] as $val) {
            if ($val{0} == '=') continue;
            // Skip over "expired" cookies (and undesired cookies) which were causing problems; by Neerav; 4 Apr 2006
            if (    (strpos($val,"EXPIRED") !== false) 
                        or 
                    (strpos($val,"GoogleAccountsLocale_session") !== false)
            ) continue;
            $cookie .= $val . "; ";
        }
        return substr($cookie, 0, -2);
    }

    function getAddressbook($gmail_acc, $gmail_pwd)
    {
        if ($this->created)
        {                   
            $my_timezone = "";    
            $this->setLoginInfo($gmail_acc, $gmail_pwd, $my_timezone);

            if ($this->connect()) 
            {

                $this->fetchBox(GM_CONTACT, "all", "");
                $snapshot = $this->getSnapshot(GM_CONTACT);

                for($i = 0; $i < count($snapshot->contacts); $i++)
                {
                    $store=$snapshot->contacts[$i];
                    $name=$store["name"];
                    $nam=str_replace("string","name",$name);
                    $email=$store["email"];
                    $emal=str_replace("string","email",$email);
                    $result['name'][]=$nam;
                    $result['email'][]=$emal;
                }

            }
            else 
            {
                die("Fail to connect because: ".$this->lastActionStatus());
            }
        } 
        else 
        {
            die("Failed to create GMailer because: ".$this->lastActionStatus());
        }
        return $result;
    }
    /**
    * Process Gmail data packets.
    *
    * @access private
    * @static
    * @return mixed[]
    * @param string $input
    * @param int& $offset
    */
    function parse_data_packet($input, &$offset) {
        $output = array();
        
        // state variables
        $isQuoted = false;      // track when we are inside quotes
        $dataHold = "";         // temporary data container
        $lastCharacter = " ";

        // walk through the entire string
        for($i=1; $i < strlen($input); $i++) {
            switch($input[$i]) {
                case "[":   // handle start of array marker
                    if(!$isQuoted) {
                        // recurse any nested arrays
                        array_push($output, GMailer::parse_data_packet(substr($input,$i), $offset));
                        
                        // the returning recursive function write out the total length of the characters consumed
                        $i += $offset;
                        
                        // assume that the last character is a closing bracket
                        $lastCharacter = "]";
                    } else {
                        $dataHold .= "[";
                    }
                    break;

                case "]":   // handle end of array marker
                    if(!$isQuoted) {
                        if($dataHold != "") {
                            array_push($output, $dataHold);
                        }
                        
                        // determine total number of characters consumed (write to reference)
                        $offset = $i;
                        return $output;
                    } else {
                        $dataHold .= "]";
                        break;
                    }

                case '"':   // toggle quoted state
                    if($isQuoted) {
                        $isQuoted = false;
                    } else {
                        $isQuoted = true;
                        $lastCharacter = '"';
                    }
                    break;

                case ',':   // find end of element marker and add to array
                    if(!$isQuoted) {
                        if($dataHold != "") {   // this is to filter out adding extra elements after an empty array
                            array_push($output, $dataHold);
                            $dataHold = "";
                        } else if($lastCharacter == '"') {   // this is to catch empty strings
                            array_push($output, "");
                        }
                    } else {
                        $dataHold .= ",";
                    }
                    break;
                    
                case '\\':
                    if ($i < strlen($input) - 1) { 
                        switch($input[$i+1]) {
                            case "\\":                          /* for the case \\ */
                                // Added by Neerav; June 2005
                                // strings that END in \ are now handled properly
                                if ($i < strlen($input) - 2) { 
                                    switch($input[$i+2]) {
                                        case '"':                           /* for the case \\" */
                                            $dataHold .= '\\';
                                            $lastCharacter = '\\"';
                                            $i += 1;
                                            break;
                                        case "'":                           /* for the case \\' */
                                            $dataHold .= "\\";
                                            $lastCharacter = "\\'";
                                            $i += 1;
                                            break;
                                        default:
                                    }                            
                                } else {
                                    $dataHold .= '\\';
                                    $lastCharacter = '\\';
                                }
                                break;
                            case '"':                           /* for the case \" */
                                $dataHold .= '"';
                                $lastCharacter = '\"';
                                $i += 1;
                                break;
                            case "'":                           /* for the case \' */
                                $dataHold .= "'";
                                $lastCharacter = "\'";
                                $i += 1;
                                break;
                            case "n":                           /* for the case \n */
                                $dataHold .= "\n";
                                $lastCharacter = "\n";
                                $i += 1;
                                break;
                            case "r":                           /* for the case \r */                               
                                $dataHold .= "\r";
                                $lastCharacter = "\r";
                                $i += 1;
                                break;
                            case "t":                           /* for the case \t */
                              $dataHold .= "\t";
                              $lastCharacter = "\t";
                              $i += 1;
                              break;
                            default:
                        }                            
                    }
                    break;

                default:      // regular characters are added to the data container
                    $dataHold .= $input[$i];
                    break;
            }
        }    
        return $output;
    }

    /**
    * Create/edit contact.
    *
    * Examples:
    * <code>
    * <?php
    *    // Add a new one
    *    $gmailer->editContact(-1, 
    *                          "John", 
    *                          "john@company.com", 
    *                          "Supervisor of project X", 
    *                          "");
    *
    *    // Add a new one with lots of details
    *    $gmailer->editContact(
    *       -1, 
    *       "Mike G. Stone",
    *       "mike@company.com",
    *       array(
    *          array(
    *             "phone"       => "123-45678",
    *             "mobile"      => "987-65432",
    *             "fax"         => "111-11111",
    *             "pager"       => "222-22222",
    *             "im"          => "34343434",
    *             "company"     => "22nd Century Fox",
    *             "position"    => "CEO",
    *             "other"       => "Great football player!",
    *             "address"     => "1 Fox Rd",
    *             "detail_name" => "Work"
    *           ),
    *          array(
    *             "phone"       => "1-23-4567",
    *             "mobile"      => "9-87-6543",
    *             "email"       => "mike.at.home@home.net",
    *             "im"          => "stonymike (yahoo)",
    *             "im"          => "stonymike@hotmail.com",
    *             "other"       => "Has huge collection of World Cup t-shirts",
    *             "address"     => "1 Elm Street",
    *             "detail_name" => "Home"
    *           )
    *        );
    *
    *    // Modified an existing one
    *    $gmailer->editContact($contact_id, 
    *                          "Old Name", 
    *                          "new_mail@company.com", 
    *                          "Old notes"
    *                       );
    * ? >
    * </code>
    *
    * Note: You must supply the old name even if you are not going to modify it, or it will
    * be changed to empty!
    *
    * @return bool Success or not.
      Extended return: array(bool success/fail, string message, string contact_id)
    * @param string $contact_id  Contact ID for editing an existing one, or -1 for creating a new one
    * @param string $name Name
    * @param string $email Email address
    * @param string $notes Notes
    * @param mixed[][] $details Detailed information
    * @author Neerav
    * @since 15 Jun 2005
    */
    function editContact($contact_id, $name, $email, $notes, $details=array()) {
        if ($this->isConnected()) {         
            $postdata = array();
            $postdata["act"]    = "ec";
            $postdata["ct_id"]  = "$contact_id";
            $postdata["ct_nm"]  = $name;
            $postdata["ct_em"]  = $email;
            $postdata["ctf_n"]  = $notes;

            // Added by Neerav; 1 July 2005
            // contact details
            if (count($details) > 0) {
                $i = 0;             // the detail number
                $det_num = '00';    // detail number padded to 2 numbers for gmail
                foreach ($details as $detail1) {
                    $postdata["ctsn_"."$det_num"] = "Unnamed";  // default name if none defined later
                    $address = "";                              // default address if none defined later
                    $k = 0;                                     // the field number supplied to Gmail
                    $field_num = '00';                          // must be padded to 2 numbers for gmail
                    foreach ($detail1 as $detail) {
                        $field_type = "";
                        switch (strtolower($detail["type"])) {
                            case "phone":       $field_type = "p";  break;
                            case "email":       $field_type = "e";  break;
                            case "mobile":      $field_type = "m";  break;
                            case "fax":         $field_type = "f";  break;
                            case "pager":       $field_type = "b";  break;
                            case "im":          $field_type = "i";  break;
                            case "company":     $field_type = "d";  break;
                            case "position":    $field_type = "t";  break;  // t = title
                            case "other":       $field_type = "o";  break;
                            case "address":     $field_type = "a";  break;
                            case "detail_name": $field_type = "xyz";    break;
                            default:            $field_type = "o";  break;  // default to other
                            //default:          $field_type = $detail["type"];  break;  // default to the unknown detail
                        }
                        if ($field_type == "xyz") {
                            $postdata["ctsn_"."$det_num"] = $detail["info"];
                        } elseif ($field_type == "a") {
                            $address = $detail["info"];
                        } else {
                            // e.g. ctsf_00_00_p for phone
                            $postdata["ctsf_"."$det_num"."_"."$field_num"."_"."$field_type"] = $detail["info"];
                            // increments the field number and pads it
                            $k++;
                            $field_num = str_pad($k, 2, '0', STR_PAD_LEFT);
                        }
                    }               
                    // Address field needs to be last
                    // if more than one address was given, the last one found will be used
                    if ($address != "") $postdata["ctsf_"."$det_num"."_"."$field_num"."_a"] = $address;

                    // increment detail number
                    $i++;
                    $det_num = str_pad($i, 2, '0', STR_PAD_LEFT);
                }
            }

            $postdata["at"] = $this->at_value();
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?view=up",
                $this->GM_LNK_GMAIL."?&search=contacts&ct_id=1&cvm=2&view=ct",
                'post',
                $postdata
            );
            GMailer::parse_gmail_response($this->gmail_data);
            
            $orig_contact_id = $contact_id;
            if ($orig_contact_id == -1 and $this->raw["ar"][1]) {
                if (isset($this->raw["cov"][1][1])) $contact_id = $this->raw["cov"][1][1];
                elseif (isset($this->raw["a"][1][1])) $contact_id = $this->raw["a"][1][1];
                elseif (isset($this->raw["cl"][1][1])) $contact_id = $this->raw["cl"][1][1];
            }

            $status = $this->raw["ar"][1];
            $a = array(
                "action"        => (($orig_contact_id == -1) ? "add contact": "edit contact"),
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $this->raw["ar"][2],
                "contact_id"    => "$contact_id"
            );
            array_unshift($this->return_status, $a);

            return $status;

        } else {
            $a = array(
                "action"        => (($orig_contact_id == -1) ? "add contact": "edit contact"),
                "status"        => "failed",
                "message"       => "libgmailer: not connected",
                "contact_id"    => "$contact_id"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    /**
    * Add message's senders to contact list.
    *
    * @return bool
    * @param string $message_id Message ID
    * @author Neerav
    * @since 14 Aug 2005
    */
    function addSenderToContact($message_id) {
        if ($this->isConnected()) {         
            $query  = "";
            //$query .= "&ik=".$this->cookie_ik_str;
            $query .= "&search=inbox";
            $query .= "&view=up";
            $query .= "&act=astc";
            $query .= "&at=".$this->at_value();
            $query .= "&m=".$message_id;
            $query .= $this->proxy_defeat();     // to fool proxy

            set_time_limit(150);
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $this->GM_LNK_GMAIL."?".$query);
            // NOTE: DO NOT SEND REFERRER
            $this->CURL_PROXY($c);
            curl_setopt($c, CURLOPT_HEADER, 1);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
            curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
            $this->gmail_data = curl_exec($c);
            GMailer::parse_gmail_response($this->gmail_data);
            curl_close($c);
            
            $a = array(
                "action"        => "add sender to contact list",
                "status"        => "success",
                "message"       => ""
            );
            array_unshift($this->return_status, $a);
            return true;
        } else {
            $a = array(
                "action"        => "add sender to contact list",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    /**
    * Star/unstar a message quickly.
    *
    * @return bool Success or not.
      Extended return: array(bool success/fail, string message, string contact_id)
    * @param string $message_id
    * @param string $action Either "star" or "unstar".
    * @author Neerav
    8 @since 18 Aug 2005
    */
    function starMessageQuick($message_id, $action) {
        if ($this->isConnected()) {         
            $query  = "";
            $query .= "&ik=".$this->cookie_ik_str;
            $query .= "&search=inbox";
            $query .= "&view=up";
            if ($action == "star") {
                $query .= "&act=st";
            } else {
                $query .= "&act=xst";
            }
            $query .= "&at=".$this->at_value();
            $query .= "&m=".$message_id;
            $query .= $this->proxy_defeat();     // to fool proxy

            set_time_limit(150);
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, $this->GM_LNK_GMAIL."?".$query);
            // NOTE: DO NOT SEND REFERRER
            $this->CURL_PROXY($c);
            curl_setopt($c, CURLOPT_HEADER, 1);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
            curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
            curl_setopt($c, CURLOPT_COOKIE, $this->cookie_str);
            $this->gmail_data = curl_exec($c);
            GMailer::parse_gmail_response($this->gmail_data);
            curl_close($c);
            
            $status  = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            $a = array(
                "action"        => "$action message",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);
            return $status;
        } else {
            $a = array(
                "action"        => "$action message",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    /**
    * Delete contacts.
    *
    * @return bool Success or not.
      Extended return: array(bool success/fail, string message)
    * @param string[] $id Contact ID to be deleted
    * @author Neerav
    * @since 15 Jun 2005
    */
    function deleteContact($id) {
        if ($this->isConnected()) {                     
            $query   = "";

            if (is_array($id)) {
                //Post: act=dc&at=xxxxx-xxxx&cl_nw=&cl_id=&cl_nm=&c=0&c=3d
                $query .= "&act=dc&cl_nw=&cl_id=&cl_nm=";
                foreach ($id as $indexval => $contact_id) {
                    $query .= "&c=".$contact_id;
                }
            } else {
                $query  .= "search=contacts";
                $query  .= "&ct_id=".$id;
                $query  .= "&cvm=2";
                $query  .= "&view=up";
                $query  .= "&act=dc";
            }

            $query .= "&at=".$this->at_value();
            if (!is_array($id)) {
                $query .= "&c=".$id;
                $query .= $this->proxy_defeat();     // to fool proxy
            }

            if (is_array($id)) {
                $this->gmail_data = GMailer::execute_curl(
                    $this->GM_LNK_GMAIL."?view=up",
                    $this->GM_LNK_GMAIL."?view=cl&search=contacts&pnl=a",
                    'post',
                    $query
                );
            } else {
                $this->gmail_data = GMailer::execute_curl(
                    $this->GM_LNK_GMAIL."?".$query,
                    $this->GM_LNK_GMAIL."?view=cl&search=contacts&pnl=a",
                    'get'
                );
            }
            GMailer::parse_gmail_response($this->gmail_data);
            
            $status = $this->raw["ar"][1];
            $a = array(
                "action"        => "delete contact",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $this->raw["ar"][2]
            );
            array_unshift($this->return_status, $a);
            return $status;
        } else {
            $a = array(
                "action"        => "delete contact",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    /**
    * Create, edit or remove label.
    * 
    * @return bool Success or not.
      Extended return: array (boolean success/fail, string message)
    * @param string $label
    * @param string $action Either "create", "delete" or "rename"
    * @param string $renamelabel New name if renaming label
    * @author Neerav
    * @since 7 Jun 2005
    */
    function editLabel($label, $action, $renamelabel) {
        if ($this->isConnected()) {         
            //Debugger::say("ik value: ".$this->cookie_ik_str);
                
            $postdata = array();
            if ($action == "create") {
                $postdata["act"] = "cc_".$label;
            } elseif ($action == "rename") {
                $postdata["act"] = "nc_".$label."^".$renamelabel;
            } elseif ($action == "remove") {
                $postdata["act"] = "dc_".$label;
            } else {
                // Changed by Neerav; 28 June 2005
                // was boolean, now array(boolean,string)
                $a = array(
                    "action"        => "$action label",
                    "status"        => (($status) ? "success" : "failed"),
                    "message"       => "libgmailer error: unknown action in editLabel()"
                );
                array_unshift($this->return_status, $a);
                return false;
            }
            
            $postdata["at"] = $this->at_value();

            //Debugger::say($this->GM_LNK_GMAIL_HTTP."?&ik=".$_SESSION['id_key']."&view=pr&pnl=l".$this->proxy_defeat());
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL_HTTP."?&ik=&view=up",
                $this->GM_LNK_GMAIL_HTTP."?"./* "&ik=".$_SESSION['id_key']. */"&view=pr&pnl=l".$this->proxy_defeat(),
                'post',
                $postdata
            );
            GMailer::parse_gmail_response($this->gmail_data);

            // Changed by Neerav; 28 June 2005
            $status  = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            $a = array(
                "action"        => "$action label",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);

            return $status;
        } else {
            // Added by Neerav; 12 July 2005
            $a = array(
                "action"        => "$action label",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);

            return false;
        }
    }
    
    /**
    * Create/edit a filter.
    *
    * @return bool Success or not.
      Extended return: array(bool,string message)
    * @param integer $filter_id Filter ID to be edited, or "0" for creating a new one
    * @param string $from
    * @param string $to
    * @param string $subject
    * @param string $has
    * @param string $hasnot
    * @param bool   $hasAttach
    * @param bool   $archive
    * @param bool   $star
    * @param bool   $label
    * @param string $label_name
    * @param bool   $forward
    * @param string $forwardto
    * @param bool   $trash
    * @author Neerav
    * @since 25 Jun 2005
    */
    function editFilter($filter_id, $from, $to, $subject, $has, $hasnot, $hasAttach,
                $archive, $star, $label, $label_name, $forward, $forwardto, $trash, $applyfilternow = false, $test_filter = false) {

        if ($test_filter) {
            $action = "test";
        } elseif ($filter_id == 0) {
            $action = "create";
        } else {
            $action = "edit";
        }
        
        if ($this->isConnected()) {         
            $query = "";

            if ($action != "test") {
                $query .= "view=pr";
                $query .= "&pnl=f";
                $query .= "&at=".$this->at_value();
            }
            if ($action == "create") {
                // create new filter
                $query .= "&act=cf";
                $query .= "&cf_t=cf";
            } elseif ($action == "test") {
                $query .= "&search=cf";
                $query .= "&view=tl";
                $query .= "&start=0";
                $query .= "&cf_f=cf1";
                $query .= "&cf_t=cf2";
            } else {
                // edit existing filter
                $query .= "&act=rf";
                $query .= "&cf_t=rf";
            }
            
            $query .= "&cf1_from="  . urlencode($from);
            $query .= "&cf1_to="    . urlencode($to);
            $query .= "&cf1_subj="  . urlencode($subject);
            $query .= "&cf1_has="   . urlencode($has);
            $query .= "&cf1_hasnot=". urlencode($hasnot);
            $query .= "&cf1_attach="; $query .= ($hasAttach == true) ? "true" : "false" ;
            $query .= "&cf2_ar="    ; $query .= ($archive == true)  ? "true" : "false" ;
            $query .= "&cf2_st="    ; $query .= ($star == true)     ? "true" : "false" ;
            $query .= "&cf2_cat="   ; $query .= ($label == true)    ? "true" : "false" ;
            $query .= "&cf2_sel="   . urlencode($label_name);
/*          if ($action == "test") { */
/*              $query .= "&cf2_emc="   ; $query .= ($forward == true)  ? "true" : "" ; */
/*          } else { */
                $query .= "&cf2_emc="   ; $query .= ($forward == true)  ? "true" : "" ;
/*          } */
            if ($action == "test") {
                $query .= "&cf2_email=" ; $query .= urlencode($forwardto);
            } else {
                $query .= "&cf2_email=" ; $query .= ($forwardto=="") ? 'email%20address': urlencode($forwardto);
            }
            $query .= "&cf2_tr="    ; $query .= ($trash == true)    ? "true" : "false" ;

            // in edit, maybe also create? not in test.
            //&th=10d16a8192667aa2&ww=1015&qt=&prf=1&rq=xm&
            if ($action == "edit" or ($action == "test" and $filter_id > 0)) {
                $query .= "&ofid=".$filter_id;
            }

            $refer = "";
            if ($action == "test") {
                // no referrer for test
                $query .= $this->proxy_defeat("nodash");     // to fool proxy
            } else {
                // apply the filter to matched conversations;  Added by Neerav; 28 Aug 2006
                $query .= "&irf="       ; $query .= ($applyfilternow == true) ? "true" : "false" ;

                // create referer
                $refer .= "&pnl=f";
                $refer .= "&search=cf";
                $refer .= "&view=tl";
                $refer .= "&start=0";
                $refer .= "&cf_f=cf1";
                $refer .= "&cf_t=cf2";
                $refer .= "&cf1_from="  . urlencode($from);
                $refer .= "&cf1_to="    . urlencode($to);
                $refer .= "&cf1_subj="  . urlencode($subject);
                $refer .= "&cf1_has="   . urlencode($has);
                $refer .= "&cf1_hasnot=". urlencode($hasnot);
                $refer .= "&cf1_attach="; $refer .= ($hasAttach == true)    ? "true" : "false" ;
                if ($action == "edit") {
                    $refer .= "&cf2_ar="    ; $refer .= ($archive == true)  ? "true" : "false" ;
                    $refer .= "&cf2_st="    ; $refer .= ($star == true)     ? "true" : "false" ;
                    $refer .= "&cf2_cat="   ; $refer .= ($label == true)    ? "true" : "false" ;
                    $refer .= "&cf2_sel="   . urlencode($label_name);
                    $refer .= "&cf2_emc="   ; $refer .= ($forward == true)  ? "true" : "" ;
                    $refer .= "&cf2_email=" . urlencode($forwardto);
                    $refer .= "&cf2_tr="    ; $refer .= ($trash == true)    ? "true" : "false" ;
                    $refer .= "&ofid="      . urlencode($filter_id);
                }
                $refer .= $this->proxy_defeat();     // to fool proxy
                $query .= $this->proxy_defeat("nodash");     // to fool proxy
            }
            
/*          Debugger::say("<br />\n$query\n$refer\n"); */
/*           */
/*          exit; */
/*  */
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?".$query,
                (($refer !== "") ? $this->GM_LNK_GMAIL."?".$refer : ""),
                'get'
            );
            GMailer::parse_gmail_response($this->gmail_data);
            
            Debugger::say(print_r($this->gmail_data,true));
            Debugger::say(print_r($this->raw,true));
            
            exit;

            //$updated_snapshot = new GMailSnapshot(GM_PREFERENCE, $this->raw, $this->use_session);
            if ($action == "test") {
                $status = (isset($this->raw["ts"]) and isset($this->raw["t"])) ? 1 : 0;
                $message = (isset($this->raw["ts"][5])) ? $this->raw["ts"][5] : "";
            } else {
                $status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
                $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            }
            $a = array(
                "action"        => "$action filter",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);

            return $status;
        } else {
            $a = array(
                "action"        => "$action filter",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }


    /**
    * Retrieve a web clipping
    *
    * @author Neerav
    * @since 22 April 2006
    */
    function webClip($action = "get", $thread_id = 0, $mailbox = "") {
        if ($this->isConnected()) {         
            $query   = "";
            $update_cookie = false;
            if ($action == "get") {
                $query  .= "&view=fb";
            } elseif ($action == "thread") {
                //$query    .= "&ik=".$ik.value.here;
                $query  .= "&view=ad";
                $query  .= (($thread_id) ? "&th=".$thread_id : "");
                $query  .= "&mg=".urlencode("3,2"); //what is this junk? &mg=3,2,2,2,2,2,2,2,3,4,3,4
                if ($mailbox === 0 or $mailbox == "") $mailbox = "inbox";
                if (in_array(strtolower($mailbox),$this->gmail_reserved_names)) {
                    $query .= "&search=".urlencode($mailbox);
                } else {
                    $query .= "&search=cat&cat=".urlencode($mailbox);
                }
                $query  .= "&qt=";
                $query  .= "&act=rd";
                $query  .= (($thread_id) ? "&t=".$thread_id : "");
                $query  .= "&at=".$this->at_value();
            } elseif ($action == "off" or $action == "on") {
                // turn webclips on/off; Added by Neerav; 13 May 2006
                //$query    .= "&ik=".$ik.value.here;
                $query  .= "&view=up";
                $query  .= "&act=co_".(($action == "off") ? 0 : 1);
                $query  .= "&at=".$this->at_value();
                $query  .= "&rq=xm";
                $query  .= $this->proxy_defeat("nodash");
                $update_cookie = true;
            } else {
                $a = array(
                    "action"        => "web clip: ".$action,
                    "status"        => "failed",
                    "message"       => "invalid action"
                );
                array_unshift($this->return_status, $a);
                return array();
            }
            $query  .= $this->proxy_defeat();
                    
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?".$query,
                "",
                'get',
                $query, "","", $update_cookie
            );
            GMailer::parse_gmail_response($this->gmail_data,true);
            //Debugger::say("web clip response: ".print_r($this->gmail_data,true));
            //Debugger::say("web clip response raw: ".print_r($this->raw,true));
            if ($action == "get" or $action == "thread") {
                GMailSnapshot::web_clip_snapshot($this->raw["1"][1][3]);
                //Debugger::say("web clip response raw: ".print_r($this->raw,true));
                return $this->web_clips;            
            } else {
                //Debugger::say("web clip response: ".print_r($this->gmail_data,true));
/*              $a = array( */
/*                  "action"        => "retrieve web clip", */
/*                  "status"        => "failed", */
/*                  "message"       => "libgmailer: not connected" */
/*              ); */
/*              array_unshift($this->return_status, $a); */
                return true;
            }

        } else {
            $a = array(
                "action"        => "retrieve web clip",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    /**
    * Delete a filter.
    *
    * @return bool Success or not.
      Extended return: array(bool success/fail, string message)
    * @param string $id Filter ID to be deleted
    * @author Neerav
    * @since 25 Jun 2005
    */
    function deleteFilter($id) {
        if ($this->isConnected()) {         
            $query   = "";
            $query  .= "act=df_".$id;
            $query  .= "&at=".$this->at_value();
                    
            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?ik=&view=up",
                $this->GM_LNK_GMAIL."?pnl=f&view=pr".$this->proxy_defeat(),
                'post',
                $query
            );
            GMailer::parse_gmail_response($this->gmail_data);
            
            //$updated_snapshot = new GMailSnapshot(GM_PREFERENCE, $this->raw, $this->use_session);
            $status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            $a = array(
                "action"        => "delete filter",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);
            return $status;
        } else {
            $a = array(
                "action"        => "delete filter",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }


    /**
    * Edit contact groups.
    *
    * @return bool Success or not.
      Extended return: array(bool success/fail, string message)
    * @param string $id Contact group ID to "edit" (-1 if creating a new group)
    * @param string $name Contact group's name
    * @param string $action Action to be performed on Contact group (rename, create, delete, remove_from, add_to)
    * @param string $data Data required for Action (optional depending on the Action)
    * @author Neerav
    * @since 10 Jan 2006
    */
    function editGroup($id,$name,$action,$data = "") {
        if ($this->isConnected()) {         
            if ($action == "rename") {
                $refer = $this->GM_LNK_GMAIL."?search=contacts&ct_id=".$id."&cvm=1&view=ctl".$this->proxy_defeat();

                $query['act'] = "rcl_".$id."^".$data;
                $query['at'] = $this->at_value();
                $query['cpt'] = "cpta";
                $query['cl_id'] = $id;
                $query['cl_nm'] = $name;

            } elseif ($action == "delete") {
                $refer = $this->GM_LNK_GMAIL."?view=cl&search=contacts&pnl=l".$this->proxy_defeat();

                $query['act']   = "dcal";
                $query['at']    = $this->at_value();
                $query['cpt']   = "";
                $query['cl_nw'] = "";
                $query['cl_id'] = "";
                $query['cl_nm'] = "";
                $query['cl']    = $id;

            } elseif ($action == "create") {
                $refer = $this->GM_LNK_GMAIL."?view=nctl&search=contacts".$this->proxy_defeat();

                $query['act']   = "ancl";   // add new contact list
                $query['at']    = $this->at_value();
                $query['cl_nm'] = $name;
                $query['ce']    = (is_array($data)) ? implode(", ",$data) : $data;

            } elseif ($action == "remove_from") {
                $refer = $this->GM_LNK_GMAIL."?search=contacts&ct_id=".$id."&cvm=1&view=ctl".$this->proxy_defeat();

                $query = "&act=rfcl";       // remove from contact list
                $query .= "&at=".$this->at_value();
                $query .= "&cpt=cpta";
                $query .= "&cl_id=".$id;
                $query .= "&cl_nm=".$name;
                $add_count = count($data);
                for ($i = 0; $i < $add_count; $i++) {
                    $query .= "&cr=".$data[$i]['id']."/".$data[$i]['email'];
                }

            } elseif ($action == "add_to") {
                $refer = $this->GM_LNK_GMAIL."?&search=contacts&ct_id=".$id."&cvm=1&view=ctl".$this->proxy_defeat();

                $query['act']   = "atcl";   // add to contact list
                $query['at']    = $this->at_value();
                $query['cpt']   = "cpta";
                $query['cl_id'] = $id;
                $query['cl_nm'] = $name;
                $query['ce']    = implode(", ",$data);

            } else {
                $a = array(
                    "action"        => $action." contact group",
                    "status"        => "failed",
                    "message"       => "editGroup(): invalid ACTION"
                );
                array_unshift($this->return_status, $a);
                return false;
            }

            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?ik=&view=up",
                $refer,
                'post',
                $query
            );
            GMailer::parse_gmail_response($this->gmail_data);

            $status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            $a = array(
                "action"        => $action." contact group",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);
            return $status;
        } else {
            $a = array(
                "action"        => $action." contact group",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }
    /**
    * Set general, forwarding and POP settings of Gmail account.
    *
    * @return bool Success or not.
      Extended return: array(bool status, string message)
    * @param bool $use_outgoing_name Use outgoing name (instead of the default)?
    * @param string $outgoing_name Outgoing name
    * @param bool $use_reply_email Use replying email address (instead of the default)?
    * @param string $reply_to Replying email address
    * @param string $language Language
    * @param int $page_length Page length: either 25, 50 or 100
    * @param bool $shortcut Enable keyboard shortcut?
    * @param bool $indicator Enable personal level indicator?
    * @param bool $snippet Enable snippet?
    * @param bool $custom_signature Enable custom signature?
    * @param string $signature Custom signature
    * @param bool $utf_encode Use utf-8 encoding?
    * @param bool $use_forwarding Forward all incoming messages?
    * @param string $forward_to Forward to this email address
    * @param string $forward_action What to do with forwarded message? (selected, archive, trash)
    * @param int $use_pop Enable POP access? {0 = disabled, 1 = enabled, 2 = from now, 3 = all}
    * @param int $pop_action What to do with forwarded message? {0 = keep, 1 = archive, 2 = trash}
    * @param bool $rich_text Use rich text formatting?
    * @param bool $expand_label_box Expand label box?
    * @param bool $expand_invite_box Expand invite box?
    * @param bool $vacation_on Vacation responder - ON/OFF
    * @param string $vacation_subject Vacation responder - Subject
    * @param string $vacation_message Vacation responder - Message
    * @param bool $vacation_contacts_only Vacation responder - Send vacation message only to those in Contacts list?
    * @param bool $chat_archive Save chat scripts
    * @param bool $aa_unknown ??
    * @author Neerav
    * @since 29 Jun 2005
    */
    function setSetting(
                $language, $page_length, $shortcut, $indicator, $snippet, $custom_signature, 
                $signature, $msg_encoding,
                $use_forwarding, $forward_to, $forward_action,
                $use_pop, $pop_action, $rich_text,
                $expand_label_box = 1, $expand_invite_box = 1,
                $vacation_on = 0, $vacation_subject = "", $vacation_message = "", $vacation_contacts_only = 0,
                $expand_talk_box = 1, $chat_archive = 0

        ) {

        /*  
    end vacation NOW:
GET http://mail.google.com/mail/?&ik=xxxxxxx&search=inbox&view=tl&start=0&act=prefs&at=xxxx-xxxx&p_bx_ve=0&zx=ferur3mmq41e HTTP/1.1
Referer: http://mail.google.com/mail/?&ik=xxxxxxx&view=pr&pnl=g&zx=xorfqampe0ml

        // general      
        "bx_hs"     // (boolean) keyboard shortcuts {0 = off, 1 = on}
        "bx_show0"  // (boolean) labels box {0 = collapsed, 1 = expanded}
        "ix_nt"     // (integer) msgs per page (maximum page size)
        "sx_dl"     // (string) display language (en = English, en-GB = British-english, etc)
        "bx_sc"     // (boolean) personal level indicators {0 = no indicators, 1 = show indicators}
        "bx_show1"  // (boolean) invite box {0 = collapsed, 1 = expanded}
        "sx_sg"     // (string) signature
        "bx_ns"     // (boolean) no snippets {0 = show snippets, 1 = no snippets}
        "bx_cm"     // (boolean) rich text composition {0 = plain text, 1 = rich text}
        "bx_en"     // (boolean) outgoing message encoding {0 = default, 1 = utf-8}
        "bx_ve":    // (boolean) vacation message enabled {0 = OFF, 1 = ON}
        "sx_vs":    // (string) vacation message subject
        "sx_vm":    // (string) vacation message text
        "bx_vc":    // SPECIAL CASE (string to boolean) vacation message, send only to contacts list 
        "bx_show3"  // (boolean) gtalk box {0 = collapsed, 1 = expanded}

        // forwarding and pop
        "sx_em"         // (string) forward to email address
        "sx_at"         // (string) action after forwarding {selected, archive, trash} (selected means "keep")
        "bx_pe"         // (integer) pop enabled {0 = disabled, 1 = enabled, 2 = from now, 3 = all}
        "ix_pd"         // (integer) action after pop access {0 = keep, 1 = archive, 2 = trash}

        // mobile
        "sx_pf"         // (string) list of mailboxes to display in Gmail Mobile
        
        // other
        "bx_cm"         // (boolean) rich text composition {0 = plain text, 1 = rich text}
        "bx_aa"         // ??

        // Chat
        "ix_ca"         // (boolean) save chat archives? {0 = off, 1 = on}

        // deprecated
        //"sx_dn"   // (string) display name 
        //"sx_rt"   // (string) reply to email address
        */

        if ($this->isConnected()) {         
            $post_fields = array();
            $post_url = "";
            $query = "";

            //$query .= "&ik=".IKVALUE;
            $post_url .= "&view=up";
            $post_fields['act'] = "prefs";
            $post_url .= "&act=prefs";
            $post_fields['at'] = $this->at_value();
            $post_url .= "&at=".$this->at_value();
            $post_fields['search'] = "";

            $query .= "&sx_dl="     . $language;
            $query .= "&ix_nt="     . $page_length;
            $query .= "&bx_hs=";        $query .= ($shortcut) ? "1" : "0" ;
            $query .= "&bx_sc=";        $query .= ($indicator) ? "1" : "0" ;
            $query .= "&bx_ns=";        $query .= ($snippet) ? "0" : "1" ; // REVERSED because we originally reversed it for convenience
            $query .= "&sx_sg=";        $query .= $custom_signature;
            $query .= "&sx_sg=";        $query .= ($custom_signature)   ? urlencode($signature)         : urlencode("\n\r") ;
            $query .= "&bx_ve=";        $query .= ($vacation_on) ? "1" : "0" ;
            $query .= "&sx_vs=";        $query .= urlencode($vacation_subject) ;
            $query .= "&sx_vm=";        $query .= urlencode($vacation_message) ;
            $query .= "&bx_en=";        $query .= ($msg_encoding) ? "1" : "0" ;
            $query .= "&ix_ca=";        $query .= ($chat_archive) ? "1" : "0" ;
/*          $query .= "&ix_ql=10";       */
/*          $query .= "&bx_lq=0";        */
/*          $query .= "&bx_aa=1";        */


            $post_fields['p_bx_hs'] =       ($shortcut) ? "1" : "0" ;
            $post_fields['p_bx_show0'] =    ($expand_label_box) ? "1" : "0" ;
            $post_fields['p_ix_nt'] =       $page_length;
            $post_fields['p_bx_pe'] =       ($use_pop >= 0 and $use_pop <= 3) ? $use_pop : "0" ;
            $post_fields['p_bx_show1'] =    ($expand_invite_box) ? "1" : "0" ;
            $post_fields['p_bx_ve'] =       ($vacation_on) ? "1" : "0" ;
            $post_fields['p_bx_cm'] =       ($rich_text) ? "1" : "0" ;
            $post_fields['p_bx_en'] =       ($msg_encoding) ? "1" : "0" ;
            $post_fields['p_ix_pd'] =       ($pop_action >= 0 and $pop_action <= 2) ? $pop_action : "0" ;
/*          $post_fields['p_ix_fv'] =       "true"; */
            $post_fields['p_bx_show3'] =    ($expand_talk_box) ? "1" : "0" ;
            $post_fields['p_sx_vm'] =       $vacation_message;
            $post_fields['p_sx_sg'] =       ($custom_signature)     ? $signature        : "\n\r" ;
            $post_fields['p_sx_dl'] =       $language;
            $post_fields['p_bx_sc'] =       ($indicator) ? "1" : "0" ;
            $post_fields['p_sx_vs'] =       $vacation_subject ;
            $post_fields['p_bx_ns'] =       ($snippet) ? "0" : "1" ; // REVERSED because we originally reversed it for convenience
            $post_fields['p_sx_em'] =       ($use_forwarding)   ? $forward_to       : "" ;
            $post_fields['p_ix_ca'] =       ($chat_archive) ? "1" : "0" ;
/*          $post_fields['p_bx_aa'] =1; */
/*          $post_fields['p_ix_ql'] =10; */
/*          $post_fields['p_bx_lq'] =0; */
            $post_fields['p_sx_at'] =       ((   $forward_action == "selected"
                                                    or $forward_action == "archive"
                                                    or $forward_action == "trash"
                                              ) ? $forward_action : "selected" 
                                            );
            // vacation responder; by Neerav; 21 Dec 2005
            // includes p_sx_vm p_sx_vs and p_bx_ve
            if ($vacation_contacts_only) {
                $post_fields['p_bx_vc'] = "true";
            } else {
                $post_fields['dp'] = "bx_vc";
            }

            //$post_fields['p_bx_aa'] = $aa_unknown;
            //http://mail.google.com/mail/?&ik=xxxxx&view=up&act=prefs&at=xxxx-xxxx

            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?".$post_url,
                $this->GM_LNK_GMAIL."?&view=pr&pnl=g".$this->proxy_defeat(),
                'post',
                $post_fields
            );
            GMailer::parse_gmail_response($this->gmail_data);
            
            // get updated cookie
            ereg("S=gmail=([^\:]*):gmail_yj=([^\:]*):gmproxy=([^\;]*);",$this->gmail_data,$matches);
            $this->cookie_str = ereg_replace(
                                    "S=gmail=([^\:]*):gmail_yj=([^\:]*):gmproxy=([^\;]*);", 
                                    "S=gmail=".$matches[1].":gmail_yj=".$matches[2].":gmproxy=".$matches[3].";", 
                                    $this->cookie_str
                                );
            // save updated cookie
            GMailer::saveSessionToBrowser();

            $status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0;
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";
            $a = array(
                "action"        => "set settings",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);
            return $status;
        } else {
            $a = array(
                "action"        => "set settings",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    function execute_curl($url, $referrer, $method, $post_data = "", $extra_type = "", $extra_data = "", $update_cookie = true, $follow = true) {
        $message = '';
        
        if ($method != "get" and $method != "post") {
            $message = 'The cURL method is invalid.';
        }
        if ($url == "") {
            $message = 'The cURL url is blank.';
        }
/*      if ($referrer == "") { */
/*          $message = 'The cURL referrer is blank.'; */
/*      } */
/*      if ($method == "post" and (!is_array($data) or count($data) == 0)) { */
/*          $message = 'The cURL post data  for POST is empty or invalid.'; */
/*      } */

        // error
        if ($message != '') {
            array_unshift($this->return_status, array("action" => "execute cURL", "status" => "failed", "message" => $message));
            return;
        }
        
        set_time_limit(150);
        $c = curl_init();
        if ($method == "get") {
            curl_setopt($c, CURLOPT_URL, $url);
            if ($referrer != "") {
                curl_setopt($c, CURLOPT_REFERER, $referrer);
            }
            $this->CURL_PROXY($c);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            if ($follow) {
                curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
            } else {
                curl_setopt($c, CURLOPT_FOLLOWLOCATION, 0);
            }
            curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
            if ($extra_type != "noheader") {
                curl_setopt($c, CURLOPT_HEADER, 1);
            }
            if ($extra_type != "nocookie") {
                curl_setopt($c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $this->cookie_str));             
            }

        } elseif ($method == "post") {
            curl_setopt($c, CURLOPT_URL, $url);
            curl_setopt($c, CURLOPT_POST, 1);
            curl_setopt($c, CURLOPT_POSTFIELDS, $post_data);
            if ($referrer != "") {
                curl_setopt($c, CURLOPT_REFERER, $referrer);
            }
            $this->CURL_PROXY($c);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            if ($extra_type == "nocookie" or !$follow) {
                curl_setopt($c, CURLOPT_FOLLOWLOCATION, 0);
            } else {
                curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
            }
            curl_setopt($c, CURLOPT_USERAGENT, GM_USER_AGENT);
            curl_setopt($c, CURLOPT_HEADER, 1);
            if ($extra_type != "nocookie") {
                curl_setopt($c, CURLOPT_COOKIE, (($extra_type == "cookie") ? $extra_data : $this->cookie_str));             
            }
        }
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST,  2);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);

        // debugging cURL
        if (GM_DEBUG_CURL) {
            $fd = fopen("debug_curl.txt", "a+");
            curl_setopt($c, CURLOPT_VERBOSE, 1);
            curl_setopt($c, CURLOPT_STDERR, $fd);
        }

        $gmail_response = curl_exec($c);
        curl_close($c);

        // close debugging file
        if (GM_DEBUG_CURL and isset($fd)) @fclose($fd);

        if ($update_cookie) GMailer::update_cookies($gmail_response);
        
        return $gmail_response;
    }

    /**
    * Update Gmail cookie
    *
    * @return bool true
    * @param string raw HTML header
    * @access private
    * @static
    * @author Neerav
    * @since 19 Apr 2006
    */
    function update_cookies ($data = "") {
        GMailer::get_cookies($data);
        //Debugger::say("old cookie: ".print_r($this->cookie_str,true));

        //S=gmail=j8lv94EXSjI:gmail_yj=Fs3UajIqvjY:gmproxy=ZwcQ86EuvyY;
        ereg("S=gmail=([^\:]*):gmail_yj=([^\:]*):gmproxy=([^\;]*);",(($data == "") ? $this->gmail_data : $data),$matches);
        //Debugger::say("cookie matches: ".print_r($matches,true));         
        $this->cookie_str = ereg_replace(
            "S=gmail=([^\:]*):gmail_yj=([^\:]*):gmproxy=([^\;]*);", 
            "S=gmail=".$matches[1].":gmail_yj=".$matches[2].":gmproxy=".$matches[3].";", 
            $this->cookie_str
        );

        // save updated cookie
        GMailer::saveSessionToBrowser();
        //Debugger::say("new cookie: ".print_r($this->cookie_str,true));
        
        return true;
    }


    /**
    * Set Mobile settings of Gmail account.
    *
    * @return bool Success or not.
      Extended return: array(bool status, string empty message)
    * @param array $mobile_display Array of standard boxes and labels to "display"
    * @author Neerav
    * @since 23 Dec 2005
    */
    function setMobileSetting($mobile_display) {

        if ($this->isConnected()) {         
            // should be POST, but we are faking it with a GET encoded POST
            //$post = array();
            //$post['nvp_bu_done'] = "Save";
            $get = "";
            $get .= "nvp_bu_done=Save";

            $post_url = "x/".$this->proxy_defeat("nodash")."-/?a=cfa";
            $post_url .= "&at=".$this->at_value();

            $count_mob_display = count($mobile_display);
            for ($i = 0; $i < $count_mob_display; $i++) {
                if (isset($mobile_display[$i]) and $mobile_display[$i] != "") {
                    // should be POST, but we are faking it with a GET encoded POST
                    // because the @ and other URL special symbols do not make it properly
                    //$post['cfvc_'.$i] = $mobile_display[$i];
                    $get .= "&cfvc_".$i."=".urlencode($mobile_display[$i]);
                }
            }

            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL_HTTP.$post_url,
                $this->GM_LNK_GMAIL_HTTP."x/".$this->proxy_defeat("nodash")."-/?v=cmf",
                'post',
                // should be POST, but we are faking it with a GET encoded POST
                //$post
                $get
            );
            
            $status = (strstr($this->gmail_data,'<a href="?v=cmf">more views</a>') === false) ? 0 : 1;
            $a = array(
                "action"        => "set mobile settings",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => ""
            );
            array_unshift($this->return_status, $a);
            return $status;
        } else {
            $a = array(
                "action"        => "set mobile settings",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    /**
    * Change Gmail Language
    *
    * @return bool Success or not.
    * @param string $old_lang Current language
    * @param string $new_lang New language
    * @author Neerav
    * @since 27 Nov 2005
    */
    function changeLanguage($new_lang, $old_lang = "") {

        if ($this->isConnected()) {         
            $query = "";
            $refer = "";

            //$query .= "&ik=".IKVALUE;
            $query .= "&view=lpc&gfl=".(($old_lang != "")? $old_lang:"en")."&gtl=".$new_lang;
            //$refer .= "&ik=".IKVALUE;
            $refer .= "&view=pr&pnl=g";
            $refer .= $this->proxy_defeat();     // to fool proxy

            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?".$query,
                $this->GM_LNK_GMAIL."?".$refer,
                'get'
            );          
            
            GMailer::update_cookies();

            // GMAIL DOES NOT RESPOND WITH A STATUS MESSAGE

            $a = array(
                "action"        => "change language",
                "status"        => "success",
                "message"       => "(no message)"
            );
            array_unshift($this->return_status, $a);
            return true;
        } else {
            $a = array(
                "action"        => "change language",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);
            return false;
        }
    }

    /**
    * Personalities / Custom FROM addresses
    *
    * @return bool Success or not.
    * @param string $action An action to perform: [set this address to] default, delete, reply_sent, reply_default, edit, edit_google, send_verify, verify_code
    * @param string $email
    * @param string $name
    * @param string $reply_to
    * @param integer $code Verification code for an added custom from address
    * @desc Custom From Address features
    * @author Neerav
    * @since 25 Feb 2006
    */

    function customFrom($action, $email, $name = "", $reply_to = "", $code = "") {
        if ($this->isConnected()) {
            $url = "&ik=&view=up";
            $refer = "&view=pr&pnl=g".$this->proxy_defeat();
            $method = 'post';

            switch($action) {
                case "default":
                    $postdata    = "act=mcf_".urlencode($email);
                    $postdata   .= "&at=".$this->at_value();
                    break;
                case "delete":          
                    $postdata    = "act=dcf_".urlencode($email);
                    $postdata   .= "&at=".$this->at_value();
                    break;
                case "reply_sent":      
                    $postdata    = "act=crf_1";
                    $postdata   .= "&at=".$this->at_value();
                    $postdata   .= "&search=";
                    break;
                case "reply_default":   
                    $postdata    = "act=crf_0";
                    $postdata   .= "&at=".$this->at_value();
                    $postdata   .= "&search=";
                    break;
                case "edit":
                    $postdata   = "cfrp=1&cfe=1&cfn=".urlencode($name)."&cfrt=".urlencode($reply_to);
                    $url        = "&view=cf&cfe=true&cfa=".urlencode($email).$this->proxy_defeat();
                    $refer      = $url;
                    break;
                case "edit_google":
                    $postdata['cfrp']   = 1;
                    $postdata['cfe']    = 1;
                    $postdata['cfgnr']  = (($name != "")?1:0);
                    $postdata['cfgn']   = $name;
                    $postdata['cfrt']   = $reply_to;
                    $url        = "&view=cf&cfe=true&cfa=".urlencode($email).$this->proxy_defeat();
                    $refer      = $url;
                    break;
/*              case "add_pre": */
/*                  $postdata   = ""; */
/*                  $url        = "&view=cf".$this->proxy_defeat(); */
/*                  $method     = 'get'; */
/*                      break; */
/*              case "add": */
/*                  $postdata           = array(); */
/*                  $postdata['cfrp']   = 1; */
/*                  $postdata['cfn']    = $name; */
/*                  $postdata['cfa']    = $email; */
/*                  $postdata['cfrt']   = $reply_to; */
/*                  $url                = "&view=cf"; */
/*                  $refer              = $url; */
/*                      break; */
                case "send_verify":
                    $postdata           = array();
                    $postdata['cfrp']   = 2;
                    $postdata['cfn']    = $name;
                    $postdata['cfa']    = $email;
                    $postdata['cfrt']   = $reply_to;
                    $postdata['submit'] = "Send Verification";
                    $url                = "&view=cf";
                    $refer              = $url;
                    break;
                case "verify_code":
                    $postdata           = array();
                    $postdata['cfrp']   = 3;
                    $postdata['cfrs']   = "false";
                    $postdata['cfn']    = $name;
                    $postdata['cfa']    = $email;
                    $postdata['cfrt']   = $reply_to;
                    $postdata['cfvc']   = $code;
                    $url                = "&view=cf";
                    $refer              = $url;
                    break;
                default:            
                    array_unshift(
                        $this->return_status, 
                        array("action" => "custom from: $action",
                             "status" => "failed",
                             "message" => "libgmailer: Invalid action"
                        )
                    );
                    return 0;
            }

            $this->gmail_data = GMailer::execute_curl(
                $this->GM_LNK_GMAIL."?".$url,
                $this->GM_LNK_GMAIL."?".$refer,
                $method,
                $postdata
            );
            GMailer::parse_gmail_response($this->gmail_data);

            if ($action == "send_verify" or $action == "add" or $action == "add_pre" or $action == "verify_code") {
                $status  = 1;
            } else {
                $status  = (isset($this->raw["cfs"])) ? 1 : 0;
            }

/*          $status = (isset($this->raw["ar"][1])) ? $this->raw["ar"][1] : 0; */
            $message = (isset($this->raw["ar"][2])) ? $this->raw["ar"][2] : "";

            $a = array(
                "action"        => "custom from: $action",
                "status"        => (($status) ? "success" : "failed"),
                "message"       => $message
            );
            array_unshift($this->return_status, $a);

            return $status;
        } else {
            $a = array(
                "action"        => "custom from",
                "status"        => "failed",
                "message"       => "libgmailer: not connected"
            );
            array_unshift($this->return_status, $a);

            return false;
        }
    }

    /**
    * Parse Gmail responses.
    *
    * @access private
    * @static
    * @return bool 
    * @param string $raw_html
    * @author Neerav
    * @since 27 Nov 2005
    */
    function at_value() {
        $at_value = "";
        $cc = split(";", $this->cookie_str);
        foreach ($cc as $cc_part) {
            $cc_parts = split("=", $cc_part);
            if (trim($cc_parts[0]) == "GMAIL_AT") {
                $at_value = $cc_parts[1];
                break;
            }
        }
        
        return $at_value;
    }

    /**
    * Parse Gmail responses.
    *
    * @access private
    * @static
    * @return bool 
    * @param string $raw_html
    * @since 7 Jun 2005
    */
    function parse_gmail_response($raw_html,$non_standard = false) {
        $raw_html = str_replace("\n", "", $raw_html);
        $raw_html = str_replace("D([", "\nD([", $raw_html);
        $raw_html = str_replace("]);", "]);\n", $raw_html);
        // Fix Gmail's conversion of = and /; by Neerav; 18 Dec 2005
        $raw_html = str_replace(array('u003d','u002f'),array('=','/'),$raw_html);
/*      $raw_html = preg_replace(array('/(<[^>]*?u003d[^>]*?'.'>)/e'),array('str_replace("u003d","=",\1)'),$raw_html); */
/*      $raw_html = str_replace(array('\\\\u003d\\\\','\\\\u002f\\\\'),array('=','/'),$raw_html); */
/*      $raw_html = preg_replace(array('/(<[^>]*?u003d[^>]*?'.'>)/e'),array('str_replace("u003d","=",\1)'),$raw_html); */
/*      $raw_html = preg_replace('/(\w)u003d\"/','\\1=\\2',&$raw_html); */
/*      $raw_html = str_replace(array('u003d\"','u002f'),array('=\"','/'),$raw_html); */
/*      $raw_html = preg_replace(array('/(<[^>]*?)u003d/'),array('\\1='),$raw_html); */
        
        $regexp = (!$non_standard) ? "|D\(\[(.*)\]\);|U" : "/\[(.*)\]/"; 
        $matches = "";   
        preg_match_all($regexp, $raw_html, $matches, PREG_SET_ORDER); 
        $packets = array();
        for ($i = 0; $i < count($matches); $i++) {
            $off = 0;
            $tmp = GMailer::parse_data_packet("[".$matches[$i][1]."]", $off);
            if (array_key_exists($tmp[0], $packets) || ($tmp[0]=="mi"||$tmp[0]=="mb"||$tmp[0]=="di")) {
                // Added cl as alternate contact datapack; by Neerav; 15 June 2005
                if ($tmp[0]=="t" || $tmp[0]=="ts" || $tmp[0]=="a" || $tmp[0]=="cl")
                    $packets[$tmp[0]] = array_merge($packets[$tmp[0]], array_slice($tmp, 1));
                if ($tmp[0]=="mi" || $tmp[0]=="mb" || $tmp[0]=="di") {
                    if (array_key_exists("mg", $packets))
                        array_push($packets["mg"],$tmp);
                    else
                        $packets["mg"] = array($tmp);
                }                                     
            } else {
                $packets[$tmp[0]] = $tmp;
            }
        }
        $this->raw = $packets;
        return 1;
    }
}

/**
 * Class GMailSnapshot allows you to read information about Gmail in a structured way.
 * 
 * There is no creator for this class. You must use {@link GMailer::getSnapshot()} to obtain
 * a snapshot.
 *
 * @package GMailer
*/
class GMailSnapshot {
    var $created;

    /**
    * Constructor.
    *
    * Note: you are not supposed to create a GMailSnapshot object yourself. You should
    * use {@link GMailer::getSnapshot()} and/or {@link GMailer::webClip()} instead.
    *
    * @return GMailSnapshot
    * @param constant $type
    * @param array $raw
    */
    function GMailSnapshot($type, $raw, $use_session, $raw_html) {
        // input: raw packet generated by GMailer
        
        // Invalid datapack checking
        // snapshot_error Added; by Neerav;  3 Aug 2005
        // Added http errors; by Neerav; 16 Sept 2005
        if ((!is_array($raw)) or (count($raw) == 0)) {
            $this->created = 0;
        
            if (ereg('gmail_error=([0-9]{3})',$raw_html,$matches)) {
                $this->snapshot_error = $matches[1];
                if ($matches[1] != 500) {
                    Debugger::say("libgmailer: Gmail http error (".$matches[1]."), dump RAW HTML:\n".print_r($raw_html,true));
                }
/*              $this->snapshot_error_no = $matches[1]; */
/*              ereg('<p><font size="-1">(.*?)</font></p>',$raw_html,$matches); */
/*              $this->snapshot_error = $matches[1]; */

            } elseif (ereg('<title>([0-9]{3}) Server Error</title>',$raw_html,$matches)) {
                $this->snapshot_error = $matches[1];
                if ($matches[1] != 502) {
                    Debugger::say("libgmailer: Gmail http error (".$matches[1]."), dump RAW HTML:\n".print_r($raw_html,true));
                }

            } elseif (strpos($raw_html,'500 Internal Server Error') !== false) {
                $this->snapshot_error = 500;

            } elseif (strpos($raw_html,'gmail_error=8;') !== false) {
                $this->snapshot_error = 'lockdown';

            } elseif (strpos($raw_html,'gmail_error=') !== false) {
                $this->snapshot_error = "Unknown gmail error";
                Debugger::say("libgmailer: unknown gmail error, dump RAW HTML:\n".print_r($raw_html,true));

            } elseif (strpos($raw_html,'top.location="https://www.google.com/accounts/ServiceLogin') !== false) {
                // Added error; by Neerav; 12 Oct 2005
                //libgmailer: Gmail redirect to login screen
                $this->snapshot_error = "libg110";

            } elseif ($raw_html == "") {
                $this->snapshot_error = "No response from Gmail";

            } elseif (!is_array($raw)) {
                $this->snapshot_error = "Invalid response from Gmail (not an array)";
                Debugger::say("libgmailer: invalid datapack -- not an array, dump RAW HTML:\n".print_r($raw_html,true));

            } elseif (count($raw) == 0) {
                $this->snapshot_error = "Invalid response from Gmail (empty)";

            }

            return null;
        }

        // Gmail version
        if (isset($raw["v"][1])) $this->gmail_ver = $raw["v"][1];
        //$raw["v"][2]  // What is this?  Another version number?
        //$raw["v"][3]  // What is this?

        // IdentificationKey (ik)
        // Added by Neerav; 6 July 2005
        if ($use_session) {
            if (!isset($_SESSION['id_key']) or ($_SESSION['id_key'] == "")) {
                Debugger::say("Snapshot: Using Sessions, saving id_key(ik)...");
                if (isset($raw["ud"][3])) {
                    $_SESSION['id_key'] = $raw["ud"][3];
                    Debugger::say("Snapshot: Session id_key saved: " . $_SESSION['id_key']);
                } else {
                    Debugger::say('Snapshot: Session id_key NOT saved.  $raw["ud"][3] not found.');
                }
            }
        } else {
            if (!isset($_COOKIE[GM_COOKIE_IK_KEY]) or ($_COOKIE[GM_COOKIE_IK_KEY] == 0)) {
                Debugger::say("Snapshot: Using Cookies, saving id_key(ik)...");
                if (isset($raw["ud"][3])) {
                    if (strpos($_SERVER["HTTP_HOST"],":"))
                        $domain = substr($_SERVER["HTTP_HOST"],0,strpos($_SERVER["HTTP_HOST"],":"));
                    else
                        $domain = $_SERVER["HTTP_HOST"];
                    Debugger::say("Saving id_key as cookie ".GM_COOKIE_IK_KEY." with domain=".$domain);
                        
/*                  header("Set-Cookie: ".GM_COOKIE_IK_KEY."=".base64_encode($raw["ud"][3])."; Domain=".$domain.";"); */
                    setcookie(GM_COOKIE_IK_KEY, base64_encode($raw["ud"][3]), time()+GM_COOKIE_TTL, "", $domain);
                    Debugger::say("Snapshot: Cookie id_key saved: ".GM_COOKIE_IK_KEY."=".base64_encode($raw["ud"][3]));
                } else {
                    Debugger::say('Snapshot: Cookie id_key NOT saved.  $raw["ud"][3] not found.');
                }
            }
        }
        
        // other "UD"
        // Added by Neerav; 6 July 2005
        if (isset($raw["ud"])) {
            // account email address
            // your app SHOULD cache this in session or cookie for use across pages
            // Added by Neerav; 6 May 2005
            $this->gmail_email = $raw["ud"][1];
            //$raw["ud"][2]     // keyboard shortcuts
            //$raw["ud"][3]     // Identification Key, set above    
            //$raw["ud"][4]     // What is this?  referrer?
            //$raw["ud"][5]     // What is this?
            //$raw["ud"][6]     // What is this?
            //$raw["ud"][7]     // What is this?
            //$raw["ud"][8]     // What is this? calendar link/url
            //$raw["ud"][9]     // What is this? manage account link/url
            //$raw["ud"][10]    // What is this? The service currently being used?
            //$raw["ud"][11]    // What is this? Array of increasing numbers
        }
        
        // su
        //$raw["su"][1]     // What is this? (matches $raw["v"][2]) 
        //$raw["su"][2]     // What is this? (?? array of text strings for invites)
        
        // cp
        //$raw["cp"][1]     // What is this? (always 1)
        //$raw["cp"][2]     // What is this? (always 0)

        // csm
        //$raw["csm"][1]        // What is this? (always 1)

        // cld
        //$raw["cld"]           // What is this? (empty)

        // COUntry
        // Added by Neerav; 20 Dec 2005
        $this->country = ((isset($raw["cou"][1]))?$raw["cou"][1]:"");

        // Google Accounts' name
        // your app SHOULD cache this in session or cookie for use across pages
        //     it's bandwidth expensive to retrieve preferences just for this
        // Added by Neerav; 2 July 2005
        if (isset($raw["gn"][1])) $this->google_name = $raw["gn"][1];

        // Signature
        // your app SHOULD cache this in session or cookie for use across pages
        //     it's bandwidth expensive to retrieve preferences just for this
        // Added by Neerav; 6 July 2005
        if (isset($raw["p"])) {
            for ($i = 0; $i < count($raw["p"]); $i++) {
                if ($raw["p"][$i][0] == "sx_sg") {
                    // can be undefined ?!?!
                    $this->signature = (isset($raw["p"][$i][1])) ? $raw["p"][$i][1] : "" ;
                    break;  
                }
            }
        }

        
        // Invites
        if (isset($raw["i"][1])) {
            $this->have_invit = $raw["i"][1];
        } else {
            $this->have_invit = 0;
        }

        // QUota information
        if (isset($raw["qu"])) {
            // Space used as xx MB
            $this->quota_mb  = $raw["qu"][1];
            // Total space allotted as xxxx MB
            $this->quota_tot = $raw["qu"][2];   // Added by Neerav; 6 May 2005
            // Space used as xx%
            $this->quota_per = $raw["qu"][3];   // Added by Neerav; 6 May 2005
            // html color as #aabbcc (normally a green color, but red when nearly full)
            $this->quota_col = $raw["qu"][4];   // Added by Neerav; 6 July 2005
        }

        // Footer Tips or Fast Tips
        // Added by Neerav; 6 July 2005
        if (isset($raw["ft"][1])) $this->gmail_tip = $raw["ft"][1];

        // cfs; Compose from source
        // Added by Neerav: 30 Aug 2005; Modified by Gan: 9 Sep 2005
        $this->personality = array();
        $this->personality_unverify = array();
        if (isset($raw["cfs"])) {
            if (isset($raw["cfs"][1])) {
                $person_verified = count($raw["cfs"][1]);
                for($i = 0; $i < $person_verified; $i++) {
                    $this->personality[] = array(
                        "name"      => $raw["cfs"][1][$i][0],
                        "email"     => $raw["cfs"][1][$i][1],
                        "default"   => (($raw["cfs"][1][$i][2]==0) ? false : true),
                        "reply-to"  => ((isset($raw["cfs"][1][$i][3])) ? $raw["cfs"][1][$i][3] : ""), // [not available to everyone yet (Gan: 9 Sept)]
                        "verified"  => true
                    );
                }
                $person_unverified = count($raw["cfs"][2]);
                for($i = 0; $i < $person_unverified; $i++) {
                    $this->personality_unverify[] = array(
                        "name"      => $raw["cfs"][2][$i][0],
                        "email"     => $raw["cfs"][2][$i][1],
                        "default"   => (($raw["cfs"][2][$i][2]==0) ? false : true),
                        "reply-to"  => ((isset($raw["cfs"][2][$i][3])) ? $raw["cfs"][2][$i][3] : ""), // [not available to everyone yet (Gan: 9 Sept)]
                        "verified"  => false
                    );
                }
            }
        }

        // web clips and advertisements
        $this->web_clips = array();
        if (isset($raw["ad"]) and isset($raw["ad"][1][3])) {
            /* $this->web_clips =  */GmailSnapshot::web_clip_snapshot($raw["ad"][1][3]);
        }
/*      Debugger::say("final web clip: ".print_r($this->web_clips,true)); */

        // What is this?
        // $raw["df"][1]  // shows ?false?
        // $raw["ms"]
        // $raw["e"]
        // $raw["pod"]
        // $raw["te"]
        // $raw["csm"][1]

        if ($type & (GM_STANDARD|GM_LABEL|GM_CONVERSATION|GM_QUERY)) {
            // Added by Neerav; 6 May 2005
            if (isset($raw["p"]) and !isset($this->signature)) {
                for ($i = 1; $i < count($raw["p"]); $i++) {
                    if ($raw["p"][$i][0] == "sx_sg") {
                        // can be undefined ?!?!
                        $this->signature = (isset($raw["p"][$i][1])) ? $raw["p"][$i][1] : "" ;
                        break;  
                    }
                }
            }
            if (!isset($this->signature)) $this->signature = "";

            // when a conversation does not exist, neither does ds; Fix by Neerav; 1 Aug 2005
            if (isset($raw["ds"])) {
                if (!is_array($raw["ds"])) {
                    $this->created = 0;
                    $this->snapshot_error = "libgmailer: invalid datapack";
                    return null;
                }
                // Fix for change in format of unread messages in some accounts; by Neerav; 2 Feb 2006
                if (is_array($raw["ds"][1])) {
                    $this->std_box_new = array(0,0,0,0,0,0,0);
                    $std_boxes = array("inbox","starred","sent","drafts","all","spam","trash");
                    foreach ($raw["ds"][1] as $std_box) {
                        $name = $std_box[0];
                        $which_box = array_search($name,$std_boxes);
                        if ($which_box !== false and $which_box !== "") {
                            $this->std_box_new[$which_box] = $std_box[1];
                        }
                    }
                } else {
                    $this->std_box_new = array_slice($raw["ds"],1);
                }
            } else {
                $this->created = 0;
                if (isset($raw["tf"])) {
                    $this->snapshot_error = $raw["tf"][1];
                } else {
                    $this->snapshot_error = "libgmailer: unknown but fatal datapack error";
                    Debugger::say("ds AND tf undefined, dumping raw: ". print_r($raw,true));
                }
                return null;
            }

            $this->label_list = array();
            $this->label_new = array();

            // Last changed by Neerav; 12 July 2005
            if ((isset($raw["ct"][1])) and (count($raw["ct"][1]) > 0)) {
                foreach ($raw["ct"][1] as $v) {
                    array_push($this->label_list, $v[0]);
                    array_push($this->label_new, $v[1]);
                }            
            } elseif (isset($raw["ct"]) and !isset($raw["ct"][1])) {
                Debugger::say('ct[1] not set, raw[ct] dump: '.print_r($raw["ct"],true));
            } 
                                    
            // Thread Summary
            if (isset($raw["ts"])) {
                $this->view      = (GM_STANDARD|GM_LABEL|GM_QUERY);
                $this->box_name  = $raw["ts"][5];       // name of box/label/query
                $this->box_total = $raw["ts"][3];       // total messages found
                $this->box_pos   = $raw["ts"][1];       // starting message number

                // Added by Neerav; 6 July 2005
                $this->box_display      = $raw["ts"][2];    // max number of messages to display on the page
                $this->box_query        = $raw["ts"][6];    // gmail query for box
                $this->queried_results  = $raw["ts"][4];    // was this a search query (bool)
                //$this->??         = $raw["ts"][7];        // what is this?? some id number?
                //$this->??         = $raw["ts"][8];        // what is this?? total number of messages in account? or repeat of [3]
                //$this->??         = $raw["ts"][9];        // what is this?? serial number, id number, VERY LONG!
                //$this->??         = $raw["ts"][10];       // what is this?? always blank
            }

            $this->box = array();
            if (isset($raw["t"])) {                   
                foreach ($raw["t"] as $t) {
                    if ($t == "t") continue;
                    
                    // Fix for 12 OR 13 fields!!; by Neerav; 23 July 2005
                    //$less  = (count($t) == 12) ? 1 : 0 ;
                    // Changed to 12 or 13 vs. 14 fields; by Neerav; 25 Oct 2005
                    // is this permanent??  did Gmail increase the size of the array?? Why?  What?
                    //$less  = (count($t) == 12 or count($t) == 13) ? 1 : 0 ;

                    // Gmail increased the length of the array on/before 25 Oct 2005
                    // Instead of relying on array size, we look for the labels array
                    // Update: (15 Apr 2006) now there are upto 16 fields.
                    if (count($t) < 12) {
                        $less = 0;
                        
                        $tb["id"]       = $t[0];
                        $tb["is_read"]  = 0;
                        $tb["is_starred"]= 0;
                        $tb["date"]     = "(error)";
                        $tb["sender"]   = "(error)";
                        $tb["flag"]     = "";
                        $tb["subj"]     = "(error)";
                        //$tb["snippet"]    = ((count($t) == 12) ? "" : $t[7] );
                        $tb["snippet"]  = "(error)";
                        $tb["msgid"]    = "(error)";
                        $tb["labels"]   = array();  // gives an array even if 0 labels
                        $tb["attachment"]= array();
                        //$tb["??"]     = $t[10-$less]; 
                        //$tb["??"]     = $t[11-$less];
                        $tb["long_date"]    = "(error)";
                        $tb["long_time"]    = "(error)";
                        $tb["is_chat"]      = 0;
                        $tb["chat_length"]  = "";
                        //$tb["??"]     = $t[15-$less];
                        array_push($this->box, $tb);
                        continue;
                    
                    } elseif (is_array($t[8])) {
                        // normal
                        $less = 0;
                    } elseif (is_array($t[7])) {
                        // without snippet
                        $less = 1;
                    } elseif (is_array($t[9])) {
                        // just here for future compatibility
                        $less = -1;
                    } elseif (is_array($t[6])) {
                        // just here for future compatibility
                        $less = 2;
                    } else {
                        $less = 0;
                    }
                    
                    
                    // Added by Neerav; 6 July 2005
                    $long_date = "";
                    $long_time = "";
                    $date_time = explode("_",$t[12-$less]);
                    if (isset($date_time[0])) $long_date = $date_time[0];
                    if (isset($date_time[1])) $long_time = $date_time[1]; 
                                            
                    // Added labels for use in multiple languages; by Neerav; 7 Aug 2005
                    //$label_array_lang = $t[8-$less];  

                    // Added by Neerav; 6 July 2005
                    // Gives an array of labels and substitutes the standard names
                    // Changed to be language compatible; by Neerav; 8 Aug 2005
                    $label_array = array();
                    foreach($t[8-$less] as $label_entry) {
                        switch ($label_entry) {
                            //case "^i":    $label_array[] = "Inbox";       break;
                            //case "^s":    $label_array[] = "Spam";        break;
                            //case "^k":    $label_array[] = "Trash";       break;
                            case "^t":  /* Starred */                   break;
                            //case "^r":    $label_array[] = "Draft";       break;
                            default:    $label_array[] = $label_entry;  break;
                        }
                    }

                    $tb = array();
                    $tb["id"]       = $t[0];
                    $tb["is_read"]  = (($t[1] == 1) ? 1 : 0);
                    $tb["is_starred"]= (($t[2] == 1) ? 1 : 0);
                    $tb["date"]     = strip_tags($t[3]);
                    $tb["sender"]   = strip_tags($t[4],"<b>");
                    $tb["flag"]     = $t[5];
                    $tb["subj"]     = strip_tags($t[6],"<b>");
                    //$tb["snippet"]    = ((count($t) == 12) ? "" : $t[7] );
                    $tb["snippet"]  = (($less) ? "" : $t[7] );
                    $tb["msgid"]        = $t[10-$less];

                    // Added by Neerav; 7 Aug 2005
                    //$tb["labels_lang"]= $label_array_lang;    // for use with languages
                    // Added/Changed by Neerav; 6 July 2005
                    $tb["labels"]   = $label_array; // gives an array even if 0 labels
                    $tb["attachment"]= ((strlen($t[9-$less]) == 0) ? array() : explode(",",$t[9-$less]));// Changed to give an array even if 0 attachments
                    //$tb["??"]     = $t[10-$less];     // what is this?? repeat of id??
                    //$tb["??"]     = $t[11-$less];         // what is this?? always 0
                    $tb["long_date"]    = $long_date;       // added
                    $tb["long_time"]    = $long_time;       // added
                    // some accounts have chat, some do not
                    if (isset($t[13-$less])) {
                        $tb["is_chat"]      = $t[13-$less];     // Added by (Gmail) Neerav; 16 Feb 2006;
                        $tb["chat_length"]  = $t[14-$less];     // Added by (Gmail) Neerav; 16 Feb 2006;
                        //$tb["??"]     = $t[15-$less];         // Added by (Gmail) Neerav; 16 Feb 2006; what is this?? always 0
                    } else {
                        $tb["is_chat"]      = 0;            // Added by (Gmail) Neerav; 16 Feb 2006;
                        $tb["chat_length"]  = "";           // Added by (Gmail) Neerav; 16 Feb 2006;
                        //$tb["??"]     = $t[15-$less];     // Added by (Gmail) Neerav; 16 Feb 2006; what is this?? always 0
                    }

                    array_push($this->box, $tb);
                }
            }

            // Conversation Summary
            if (isset($raw["cs"])) {
                //Debugger::say("cs exists: ".print_r($raw["cs"],true));
                //Debugger::say("cs exists, dumping raw: ".print_r($raw,true));

                // Fix for 14 OR 12 fields!!; by Neerav; 25 July 2005
                $less_cs  = (count($raw["cs"]) == 12) ? 2 : 0 ;
                // Fix for variable fields based on field type; by Neerav; 21 Apr 2006
/*              if (is_array($raw["cs"][5]) and is_array($raw["cs"][6])) { */
/*                  $less_cs = 0; */
/*              } elseif (is_array($raw["cs"][6]) and is_array($raw["cs"][7])) { */
/*                  $less_cs = -1; */
/*              } else { */
/*                  $less_cs = 0; */
/*              } */
                
/*              $less_cs  = (count($raw["cs"]) == 12) ? 2 : 0 ; */
/*              if (count($raw["cs"]) > 14) Debugger::say("extra cs fields: ".print_r($raw["cs"],true)); */

                $this->view = GM_CONVERSATION;              
                $this->conv_id = $raw["cs"][1];
                $this->conv_title = $raw["cs"][2];
                // $raw["cs"][3]        // what is this??  escape/html version of 2?
                // $raw["cs"][4]        // what is this?? empty
                // $raw["cs"][5]        // (array) conversation labels, below
                // $raw["cs"][6]        // what is this?? array
                // $raw["cs"][7]        // what is this?? integer/bool?
                $this->conv_total = $raw["cs"][8];
                // (count($t) == 14) $raw["cs"][9]  // may be missing! what is this?? long id number?
                // (count($t) == 14) $raw["cs"][10] // may be missing! what is this?? empty
                // $raw["cs"][11-$less_cs]      // may be 9 what is this?? repeat of id 1?
                // $raw["cs"][12-$less_cs]      // may be 10 what is this?? array
                // $raw["cs"][13-$less_cs]      // may be 11 what is this?? integer/bool?
                // $raw["cs"][14-$less_cs]      // alwasy one less than 8 (one less than total messages in conv)

                $this->conv_labels = array ();
                $this->conv_starred = false;

                // Added labels for use in multiple languages; by Neerav; 7 Aug 2005
                //$this->conv_labels_lang = $raw["cs"][5];  // for use with languages

                // Changed to give translated label names; by Neerav; 6 July 2005
                // Changed back to be language compatible; by Neerav; 8 Aug 2005
                //$this->conv_labels_temp = (count($raw["cs"][5])==0) ? array() : $raw["cs"][5];    
                $temp_array = $raw["cs"][5];
                foreach($raw["cs"][5] as $label_entry) {
                    switch ($label_entry) {
                        //case "^i":    $this->conv_labels[] = "Inbox";     break;
                        //case "^s":    $this->conv_labels[] = "Spam";      break;
                        //case "^k":    $this->conv_labels[] = "Trash";     break;
                        case "^t":  $this->conv_starred  = true;        break;
                        //case "^r":    $this->conv_labels[] = "Draft";     break;
                        default:    $this->conv_labels[] = $label_entry; break;
                    }
                }
                
                $this->conv = array();
                             
                if (!isset($raw["mg"])) {
                    // Added error; by Neerav; 24 Sept 2005
                    // libg102 error: a specific message has been requested, but must actually be 
                    // taken from the thread (message may be a draft or other expanded message)
                    $this->snapshot_error = "libg102"; 
                    $this->created = 0;
                    return null;
                } else {
                    $mg_count = count($raw["mg"]);
                    for ($i = 0; $i < $mg_count; $i++) {
                        if ($raw["mg"][$i][0] == "mb" && $i > 0) {
                            if (isset($raw["mg"][$i][1])) {
                                $b["body"] .= $raw["mg"][$i][1];
                            } else {
                                // Added error; by Neerav; 9 Feb 2006
                                // THIS ERROR OCCURS BECAUSE ??
                                $this->snapshot_error = "libg101";
                                $this->created = 0;
                                return null;
                            }
                            if (isset($raw["mg"][$i][2])) {
                                if ($raw["mg"][$i][2] == 0) {
                                    array_push($this->conv, $b);
                                    unset($b);
                                }
                            } else {
                                // Added error; by Neerav; 9 Feb 2006
                                // THIS ERROR OCCURS BECAUSE OF IMPROPER DATAPACK PARSING
                                $this->snapshot_error = "libg101";
                                $this->created = 0;
                                return null;
                            }
                        } elseif (($raw["mg"][$i][0] == "mi") or ($raw["mg"][$i][0] == "di")) {
                            // to account for an added 20th index with a phishing warning
                            // Added by Neerav; 1 Dec 2005
/*                          $more  = (isset($raw["mg"][$i][26]) and is_array($raw["mg"][$i][26])) ? 1 : 0 ; */
                            // Changed by Neerav; 24 Mar 2006
                            $more  = (isset($raw["mg"][$i][20]) and strpos($raw["mg"][$i][20],"<font color=\"#ffffff\">") !== false) ? 1 : 0 ;
                            
                            // Changed to merge "di" and "mi" routines; by Neerav; 11 July 2005
                            if (isset($b)) {
                                array_push($this->conv, $b);
                                unset($b);
                            }
                            $b = array();
                            // $raw["mg"][$i][0] is mi or di
                            $b["mbox"]          = $raw["mg"][$i][1];    // Added by Neerav; 11 July 2005
                            $b["is_trashed"]    = ((int)$raw["mg"][$i][1] & 128)    ? true : false; // Added by Neerav; 23 Feb 2006
                            $b["is_html"]       = ((int)$raw["mg"][$i][1] &(16|32)) ? true : false; // Added by Neerav; 23 Feb 2006
                            $b["html_images"]   = ((int)$raw["mg"][$i][1] & 32)     ? true : false; // Added by Neerav; 23 Feb 2006
                            $b["index"]         = $raw["mg"][$i][2];
                            $b["id"]            = $raw["mg"][$i][3];
                            $b["is_star"]       = $raw["mg"][$i][4];
                            if ($b["is_star"] == 1) $this->conv_starred = true;
                            $b["draft_parent"]  = $raw["mg"][$i][5];    // was only defined in draft, now both; Changed by Neerav; 11 July 2005
                            $b["sender"]        = $raw["mg"][$i][6];
                            $b["sender_short"]  = $raw["mg"][$i][7];    // Added by Neerav; 11 July 2005
                            $b["sender_email"]  = str_replace("\"", "", $raw["mg"][$i][8]);     // remove annoying d-quotes in address
                            // 9 is the short stringed names, e.g. "me, john, bob"
                            //$b["recv"]            = strip_tags($raw["mg"][$i][9]);
                            // gmail changed from string to array and added email address and conversation number; by Neerav; 25 July 2006
                            if (is_string($raw["mg"][$i][9])) {
                                $b["recv"]      = strip_tags($raw["mg"][$i][9]);
                            } else {
                                $b["recv"]      = "";
                                $count_9 = count($raw["mg"][$i][9]);
                                for ($mg9i = 0; $mg9i < $count_9; $mg9i++) {
                                    $count_sub9 = count($raw["mg"][$i][9][$mg9i]);
                                    if ($count_sub9 > 0) {
                                        for ($mg9j = 0; $mg9j < $count_sub9; $mg9j++) {
                                            if (isset($raw["mg"][$i][9][$mg9i][$mg9j][0])) {
                                                $b["recv"] .= $raw["mg"][$i][9][$mg9i][$mg9j][0]. ", ";
                                            }
                                        }
                                    }
                                    $b["recv"]      = preg_replace("/\, $/","",$b["recv"]);
                                }
                                //Debugger::say("raw[mg][x][9]:\n".print_r($raw["mg"][$i][9],true)."\nFixed: ".$b["recv"]);
                            }
                            //Debugger::say("raw[mg][x][9]:\n".print_r($raw["mg"][$i][9],true)."\n\nraw[mg][x][11]:\n".print_r($raw["mg"][$i][11],true));
                            $b["recv_email"]    = str_replace("\"", "", $raw["mg"][$i][11]);
                            $b["cc_email"]      = str_replace("\"", "", $raw["mg"][$i][12]);    // was only defined in draft, now both; Changed by Neerav; 11 July 2005
                            $b["dt_easy"]       = $raw["mg"][$i][10];
                            if (    isset($raw["mg"][$i][15]) 
                                and isset($raw["mg"][$i][16])
                                and isset($raw["mg"][$i][13])
                                and isset($raw["mg"][$i][14])
                                and isset($raw["mg"][$i][17])
                                ) {
                                $b["bcc_email"]     = str_replace("\"", "", $raw["mg"][$i][13]);    // was only defined in draft, now both; Changed by Neerav; 11 July 2005                         
                                $b["reply_email"]   = str_replace("\"", "", $raw["mg"][$i][14]);
                                $b["dt"]            = $raw["mg"][$i][15];
                                $b["subj"]          = $raw["mg"][$i][16];
                            } else {
                                // Added error; by Neerav; 9 Jan 2006
                                // THIS ERROR OCCURS BECAUSE OF IMPROPER DATAPACK PARSING
                                $this->snapshot_error = "libg101";
                                $this->created = 0;
                                return null;
                            }
                            $b["snippet"]           = $raw["mg"][$i][17];
                            $b["sender_in_contact"] = $raw["mg"][$i][19];   // (0,1) sender already in the contacts list; Added by Neerav; 6 Mar 2006
                            $b["attachment"]        = array();
                            if (isset($raw["mg"][$i][18])) {    // attachments
                                if (!is_array($raw["mg"][$i][18])) {
                                    // Added error; by Neerav; 24 Sept 2005
                                    // THIS ERROR OCCURS BECAUSE OF IMPROPER DATAPACK PARSING
                                    $this->snapshot_error = "libg101";
                                    $this->created = 0;
                                    return null;
                                } else {
                                    foreach ($raw["mg"][$i][18] as $bb) {
                                        array_push(
                                            $b["attachment"], 
                                            array("id"      => $bb[0],
                                                "filename"  => $bb[1],
                                                "type"      => str_replace("\"", "", $bb[2]),    // updated to remove the "'s; by Neerav; 19 Jan 2006
                                                "size"      => $bb[3],
                                                //,""       => $bb[4]   // always -1, what is this?? 
                                                //,""       => $bb[5]   // repeat of [0]?, what is this??
                                                // attachment has thumbnail; Added by Neerav; 22 Aug 2006
                                                "has_thumb" => (isset($raw["ma"][1][1]) and (strpos($raw["ma"][1][1],"&disp=thd&attid=".$bb[0]."&") !== false) ? 1 : 0)
                                                )
                                        );
                                        if (!isset($bb[1])) {
                                            Debugger::say("undefined attachment info, dumping message: ", print_r($raw["mg"][$i],true));
                                            Debugger::say("undefined attachment info, dumping raw: ".print_r($raw,true));
                                            Debugger::say("undefined attachment info, dumping raw_html: ".print_r($raw_html,true));
                                        }
                                    }
                                }
                            }
                            if ($raw["mg"][$i][0] == "mi") {
                                $b["is_draft"]      = false;
                                $b["body"]          = "";
                                $b["warning"]       = (($more == 1) ? $raw["mg"][$i][20]: "");      // phishing WARNING from Gmail  // Added by Neerav; 1 Dec 2005
                                // $raw["mg"][$i][20+$more];  // ?? repeated date in unix-like format with an _ // Added by Neerav; 11 July 2005
                                $b["quote_str"]     = $raw["mg"][$i][21+$more];
                                $b["quote_str_html"]= $raw["mg"][$i][22+$more];

                                // Added the following indexes; Neerav; 1 Dec 2005
                                // $raw["mg"][$i][23+$more];  // What is this?? sender's domain?
                                // $raw["mg"][$i][24+$more];  // always blank What is this??
                                // $raw["mg"][$i][25+$more];  // always array(,,1) What is this??
                                // $raw["mg"][$i][26+$more];  // always blank What is this??
                                // $raw["mg"][$i][27+$more];  // array(,,0) or blank What is this??
                                // $raw["mg"][$i][28+$more];  // always 0 What is this??
                                // $raw["mg"][$i][29+$more];  // header: Sender (real sender: don't need this) // 6 Mar 2006
                                // $raw["mg"][$i][30+$more];  // header: Message-ID (don't need this in snapshot)  // 3 Mar 2006
                                // $raw["mg"][$i][31+$more];  // always 0 What is this??
                                $b["to_custom_from"] = (isset($raw["mg"][$i][32+$more])?$raw["mg"][$i][32+$more]:"");  // Custom From which this message was sent to
                                // $raw["mg"][$i][33+$more];  // always 0 What is this??
                                // $raw["mg"][$i][34+$more];  // In reply to...
                                // $raw["mg"][$i][35+$more];  // always 0 What is this??
                                
                            } elseif ($raw["mg"][$i][0] == "di") {
                                $b["is_draft"]      = true;
                                $b["body"]          = $raw["mg"][$i][20];
                                // $raw["mg"][$i][21];  // ?? repeated date slightly different format  // Added by Neerav; 11 July 2005
                                if (isset($raw["mg"][$i][22]) and isset($raw["mg"][$i][23])) {
                                    $b["quote_str"]     = $raw["mg"][$i][22];
                                    $b["quote_str_html"]= $raw["mg"][$i][23];
                                } else {
                                    // Added error; by Neerav; 9 Jan 2006
                                    // THIS ERROR OCCURS BECAUSE OF IMPROPER DATAPACK PARSING
                                    $this->snapshot_error = "libg101";
                                    $this->created = 0;
                                    return null;
                                }                           

                                // Added to match additions to "mi"; by Neerav; 1 Dec 2005
                                $b["warning"]       = "";
                            }
                        }
                    }
                }
                if (isset($b)) array_push($this->conv, $b);
            }
        }
        
        // Changed from elseif to if; by Neerav; 5 Aug 2005
        if  ($type & GM_CONTACT) {
            $this->contacts = array();
            $this->contact_groups = array();    // Added by Neerav; 20 Dec 2005
            $this->contacts_total = 0;          // Added by Neerav; 5 Jan 2006
                        
            // general contacts information; Added by Neerav; 5 Jan 2006
            if (isset($raw["cls"])) {
                $this->contacts_total = $raw["cls"][1];     // total number of contacts
                //$raw["cls"][2]                            // array, type of contacts
                    //$raw["cls"][2][i][0]                  // Gmail code for type of contacts: p=frequent, a=all, l=group, s=search
                    //$raw["cls"][2][i][1]                  // Human readable button text for the above code type
                $this->contacts_shown = $raw["cls"][3];     // Gmail code for type of contacts currently shown/retrieved
                //$this->contacts_total = $raw["cls"][4]    // is 4 ?? What is this??
            }
            
            // Added by Neerav; 29 June 2005
            // Since gmail changes their Contacts array often, we need to see which
            //    latest flavor (or flavour) they are using!
            // Some accounts use "a" for both lists and details
            //    whereas some accounts use "cl" for lists and "cov" for details
            $type = "";
            $c_grp_det = "";
            if (isset($raw["a"])) {
                Debugger::say("uses 'a' for contacts: ".print_r($raw,true));
                $c_array = "a";
                // determine is this is a list or contact detail
                if ((count($raw["a"]) == 2) and isset($raw["a"][1][6])) {
                    $type       = "detail";
                    $c_id       = 0;
                    $c_name     = 1;
                    $c_email    = 3;
                    $c_groups   = 4;
                    $c_notes    = 5;
                    $c_detail   = 6;
                } else {
                    $c_email    = 3;
                    $c_notes    = 4;
                    $type       = "list";
                    //$c_addresses  = 5;
                }
            } elseif (isset($raw["cl"])) {  // list
                $c_array        = "cl";
                $c_email        = 4;
                $c_notes        = 5;
                $c_addresses    = 6;
                $type           = "list";
            } elseif (isset($raw["cov"])) { // contact detail in accounts using "cl"
                $c_array        = "cov";
                $type           = "detail";
                $c_id           = 1;
                $c_name         = 2;
                $c_email        = 4;
                $c_groups       = 6;
                $c_notes        = 7;
                $c_detail       = 8;
            } elseif (isset($raw["clv"])) { // group detail in accounts using "cl" // added by Neerav; 6 Jan 2006
                $c_array        = "clv";
                //$c_grp_det        = "cle";
                $type           = "detail";
                $c_id           = 1;
                $c_name         = 2;
                $c_email        = 6;
                $c_total        = 3;
                $c_detail       = 5;
                $c_members      = 4;
                $c_notes        = 0;
            } else {
                array_push(
                    $this->contacts, 
                    array("id"   => "error",
                         "name"  => "libgmailer Error",
                         "email" => "libgmailer@error.nonexistant",
                         "is_group" => 0,
                         "notes" => "libgmailer could not find the Contacts information "
                            . "due to a change in the email service (again!).  Please contact " 
                            . "the author of this program (which uses libgmailer) for a fix."
                    )
                );
            }

            // Changed by Neerav; 
            // from "a" to "cl" 15 June 2005
            // from "cl" to whichever exists 29 June 2005
            if ($type == "list") {
                // An ordinary list of contacts
                for ($i = 1; $i < count($raw["$c_array"]); $i++) {
                    $a = $raw["$c_array"][$i];
                    $b = array(
                        "id"    => $a[1],               // contact id; Added by Neerav; 6 May 2005
                        "name"  => (isset($a[2])?$a[2]:""),
                        "email" => str_replace("\"", "", $a[$c_email])  // Last Changed by Neerav; 29 June 2005
                    );
                    // Last Changed by Neerav; 29 June 2005
                    if (isset($a[$c_notes])) {
                        // Contact groups support; 5 Jan 2006
                        if (is_array($a[$c_notes])) {
                            $b["notes"] = "";
                            $b["is_group"] = true;
                            // email addresses for groups are in a different location and format
                            // "Name" <email@address.net>, "Name2" <email2@address.net>, etc
                            // and needs to be "simply" re-created for backwards compatibility
                            $gr_count = count($a[$c_notes]);
                            $group_addresses = array();
                            for ($gr_entry = 0; $gr_entry < $gr_count; $gr_entry++) {
                                $group_addresses[] = $a[$c_notes][$gr_entry][1];
                            }
                            $b["email"] = implode(", ",$group_addresses);
                            
                            //$b["email"]   = str_replace("\"", "", $a[$c_addresses]);
                            $b["group_names"] = $a[$c_email];
                            $b["group_total"] = $a[3];
                            $b["group_email"] = (count($a[$c_notes]) > 0) ? $a[$c_addresses] : array();
                        } else {
                            $b["notes"] = $a[$c_notes];
                            $b["is_group"] = false;
                            $b["groups"] = $a[$c_addresses];
                        }
                    }
                    array_push($this->contacts, $b);
                }
            } elseif ($type == "detail") {
                //Debugger::say("raw: ".print_r($raw,true));
                $details = array();
                if ($c_array == "clv") {
                    // Added by Neerav; 6 Jan 2006
                    // Group details
                    $cov["is_group"]    = true;                             // is this a group?
                    $cov["id"]          = $raw["$c_array"][1][$c_id];       // group id
                    $cov["name"]        = $raw["$c_array"][1][$c_name];     // group name
                    $gr_count = count($raw["$c_array"][1][$c_detail]);
                    $cov["group_names"] = $raw["$c_array"][1][$c_members];  // string of names of group members
                    $cov["group_total"] = $raw["$c_array"][1][$c_total];    // string, total number of members in group
                    $cov["group_email"] = str_replace("\"", "", $raw["$c_array"][1][$c_email]); // formatted list of addresses as: Name <address>
                    $cov["notes"]       = "";                               // no notes for groups... yet!
                    $group_addresses = array();                             // string of flattened email addresses
                    $group_members = array();                               // array of group members
                    for ($gr_entry = 0; $gr_entry < $gr_count; $gr_entry++) {
                        $group_addresses[] = $raw["$c_array"][1][$c_detail][$gr_entry][1];
                        $cov["members"][] = array(
                            "id"    =>  $raw["$c_array"][1][$c_detail][$gr_entry][0],
                            "name"  =>  (isset($raw["$c_array"][1][$c_detail][$gr_entry][2])?$raw["$c_array"][1][$c_detail][$gr_entry][2]:""),
                            "email" =>  $raw["$c_array"][1][$c_detail][$gr_entry][1]
                        );
                    }
                    $cov["email"] = (count($group_addresses) > 0) ? implode(", ",$group_addresses) : "";

                    
                } else {
                    // Added by Neerav; 1 July 2005
                    // Contact details (advanced contact information)
                    // used when a contact id was supplied for retrieval
                    $cov = array();
                    $cov["is_group"]= false;
                    $cov["id"]      = $raw["$c_array"][1][$c_id];
                    $cov["name"]    = $raw["$c_array"][1][$c_name];
                    $cov["email"]   = str_replace("\"", "", $raw["$c_array"][1][$c_email]);
                    $cov["groups"]  = $raw["$c_array"][1][$c_groups];
                    if (isset($raw["$c_array"][1][$c_notes][0])) {
                        $cov["notes"] = ($raw["$c_array"][1][$c_notes][0] == "n") ? $raw["$c_array"][1][$c_notes][1] : "";
                    } else {
                        $cov["notes"] = "";
                    }
                    $num_details = count($raw["$c_array"][1][$c_detail]);
                    if ($num_details > 0) {
                        for ($i = 0; $i < $num_details; $i++) {
                            $details[$i][] = array(
                                    "type"  => "detail_name",
                                    "info"  => $raw["$c_array"][1][$c_detail][$i][0]
                            );
                            if (isset($raw["$c_array"][1][$c_detail][$i][1])) {
                                $temp = $raw["$c_array"][1][$c_detail][$i][1];
                            } else {
                                $temp = array();
                                Debugger::say('$raw['.$c_array.'][1]['.$c_detail.']['.$i.'][1] not defined libgmailer: 2548, dumping raw: '. print_r($raw,true));
                            }
                            for ($j = 0; $j < count($temp); $j += 2) {
                                switch ($temp[$j]) {
                                    case "p": $field = "phone";     break;
                                    case "e": $field = "email";     break;
                                    case "m": $field = "mobile";    break;
                                    case "f": $field = "fax";       break;
                                    case "b": $field = "pager";     break;
                                    case "i": $field = "im";        break;
                                    case "d": $field = "company";   break;
                                    case "t": $field = "position";  break;  // t = title
                                    case "o": $field = "other";     break;
                                    case "a": $field = "address";   break;
                                    default:  $field = $temp[$j];   break;  // default to the field type
                                }
                                $details[$i][] = array(
                                        "type" => $field, 
                                        "info" => $temp[$j+1]
                                );
                            }
                        }
                    }
                    $cov["details"] = $details;
                }

                array_push($this->contacts, $cov);
            }
            
            // Contact groups
            // Added by Neerav; 20 Dec 2005
            if (isset($raw["cla"])) {
                for ($i = 1; $i < count($raw["cla"][1]); $i++) {
                    $a = $raw["cla"][1][$i];
                    $b = array(
                        "id"        => $a[0],
                        "name"      => $a[1],
                        "addresses" => ((isset($a[2])) ? str_replace("\"", "", $a[2]) : "")
                    );
                    array_push($this->contact_groups, $b);
                }
            }

            $this->view = GM_CONTACT;

        }
        
        // Changed from elseif to if; by Neerav; 5 Aug 2005
        if ($type & (GM_PREFERENCE)) {          
            // go to Preference Panel
            // Added by Neerav; 6 July 2005
            if (isset($raw["pp"][1])) {
                switch ($raw["pp"][1]) {
                    case "g":   $this->goto_pref_panel = "general";     break;
                    case "l":   $this->goto_pref_panel = "labels";      break;
                    case "f":   $this->goto_pref_panel = "filters";     break;
                    default:    $this->goto_pref_panel = $raw["pp"][1]; break;
                }
            }

            // SETTINGS (NON-Filters, NON-Labels)
            // Added by Neerav; 29 Jun 2005
            
            $this->setting_gen = array();
            $this->setting_fpop = array();
            $this->setting_other = array();
            $this->setting_mobile = array();
            $this->setting_chat = array();

            if (isset($raw["p"])) {
                // GENERAL SETTINGS
                $gen = array(
                    //"use_cust_name" => 0,
                    "name_google"   => ((isset($raw["gn"][1])) ? $raw["gn"][1] : ""),
                    //"name_display"    => "",
                    //"use_reply_to"    => 0,
                    //"reply_to"        => "",
                    "language"          => "en",
                    "page_size"         => 25,
                    "shortcuts"         => 0,
                    "p_indicator"       => 0,
                    "show_snippets"     => 0,
                    "use_signature"     => 0,
                    "signature"         => "",
                    "encoding"          => 0,
                    "vacation_enabled"  => 0,
                    "vacation_subject"  => "",
                    "vacation_message"  => "",
                    "vacation_contact"  => 0,
                );
    
                // FORWARDING AND POP
                $fpop = array(
                    "forward"       => 0,
                    "forward_to"    => "",
                    "forward_action"=> "selected",
                    "pop_enabled"   => 0,
                    "pop_action"    => 0
                );
    
                // MOBILE
                $mobile = array(
                    "display_boxes"     => array("inbox","starred","sent","drafts","all","spam","trash")
                );

                // CHAT
                $chat = array(
                    "save_chat"             => 0        // added by Neerav; 10 Feb 2006
                );

                // OTHER
                $other = array(
                    "google_display_name"   => (isset($raw["gn"][1])?$raw["gn"][1]:""),
                    "google_reply_to"       => "",
                    "expand_labels"         => 1,
                    "expand_invites"        => 1,
                    "expand_talk"           => 1,
                    "reply_from_sent"       => 0,
                    "rich_text"             => 0,       // not used yet or has been removed
                    "save_chat"             => 0        // added by Neerav; 10 Feb 2006
                );
    
                if (isset($raw["gn"][1])) {
                    $gen["name_google"] = $raw["gn"][1];
                }
                
                for ($i = 1; $i < count($raw["p"]); $i++) {
                    $pref = $raw["p"][$i][0];
                    $value = (isset($raw["p"][$i][1])) ? $raw["p"][$i][1] : "";

                    switch ($pref) {
                    // GENERAL SETTINGS
                        //case "sx_dn": $gen["name_display"] = $value;      break;  // (string) name on outgoing mail (display name)
                        //case "sx_rt": $gen["reply_to"] = $value;          break;  // (string) reply to email address
                        case "sx_dl":   $gen["language"] = $value;          break;  // (string) display language
                        case "ix_nt":   $gen["page_size"] = $value;         break;  // (integer) msgs per page (maximum page size)
                        case "bx_hs":   $gen["shortcuts"] = $value;         break;  // (boolean) keyboard shortcuts {0 = off, 1 = on}
                        case "bx_sc":   $gen["p_indicator"] = $value;       break;  // (boolean) personal level indicators {0 = no indicators, 1 = show indicators}
                        case "bx_ns":   $gen["show_snippets"] = !$value;    break;  // (boolean) no snippets {0 = show snippets, 1 = no snippets}
                                                                                    //      we INVERSE this for convenience
                        case "sx_sg":   $gen["signature"] = $value;         break;  // (string) signature
                        case "bx_en":   $gen["encoding"] = $value;          break;  // (boolean) outgoing message encoding {0 = default, 1 = utf-8}
                        // added by Neerav; 20 Dec 2005
                        case "bx_ve":   $gen["vacation_enabled"] = $value;  break;  // (boolean) vacation message enabled {0 = OFF, 1 = ON}
                        case "sx_vs":   $gen["vacation_subject"] = $value;  break;  // (string) vacation message subject
                        case "sx_vm":   $gen["vacation_message"] = $value;  break;  // (string) vacation message text
                        case "bx_vc":   $gen["vacation_contact"] = (($value == "true" or $value === true) ? true : false);  break;  // (string to boolean) vacation message, send only to contacts list 
                    // FORWARDING AND POP
                        case "sx_em":   $fpop["forward_to"] = $value;       break;  // (string) forward to email
                        case "sx_at":   $fpop["forward_action"] = $value;   break;  // (string) forwarding action {selected (keep), archive, trash}
                        case "bx_pe":   $fpop["pop_enabled"] = $value;      break;  // (integer) pop enabled {0 = disabled, 2 = enabled from now, 3 = enable all}
                        case "ix_pd":   $fpop["pop_action"] = $value;       break;  // (integer) action after pop access {0 = keep, 1 = archive, 2 = trash}
                    // SIDE BOXES
                        case "bx_show0": $other["expand_labels"] = $value;  break;  // (boolean) labels box {0 = collapsed, 1 = expanded}
                        case "bx_show1": $other["expand_invites"] = $value; break;  // (boolean) invite box {0 = collapsed, 1 = expanded}
                        case "bx_show3": $other["expand_talk"] = $value;    break;  // (boolean) gtalk box {0 = collapsed, 1 = expanded}
                    // ACCOUNT
                        case "bx_rf":   $other["reply_from_sent"] = $value; 
                                        break;  // (boolean) use reply from [sent] {0 = use default address, 1 = use address message was sent to}
                        // added by Neerav; 4 Mar 2006
                        case "sx_dn":   $other["google_display_name"] = $value; 
                                        break;  // (string) Google accounts "from" display name
                        case "sx_rt":   $other["google_reply_to"] = $value; 
                                        break;  // (string) Google accounts "from" reply-to address
                        // Chat
                        // added by Neerav; 10 Feb 2006
                        case "ix_ca":   $chat["save_chat"] = $value;        
                                        $other["save_chat"] = $value;
                                        break;  // (boolean) save chat archive {0 = off, 1 = on}
                        // added by Neerav; 26 Feb 2006
                        case "ix_ql":   $chat["ix_ql"] = $value;            break;  // (integer)
                        case "bx_aa":   $chat["bx_aa"] = $value;            break;  // (boolean)
                        case "bx_lq":   $chat["bx_lq"] = $value;            break;  // (boolean)
                    // MOBILE
                        // added by Neerav; 20 Dec 2005
                        case "sx_pf":   if ($value != "") {
                                            $mobile = array();
                                            $temp_mobile = explode('#,~',$value);
                                            for ($mob = 0; $mob < count($temp_mobile); $mob++) {
                                                if ($temp_mobile[$mob] != "") $mobile['display_boxes'][] = $temp_mobile[$mob];
                                            }
                                        }
                                                                            break;
                    // OTHER
                        //      not used yet or has been removed from Gmail
                        case "bx_cm":   $other["rich_text"] = $value;       break;  // (boolean) rich text composition {0 = plain text, 1 = rich text}
                        // added by Neerav; 20 Dec 2005
                        //case "bx_aa": $other["unknown"] = $value;         break;  // 
                                                                                    
                        default:        $other["$pref"] = $value;           break;
                    }
                }
            
                // set useful implicit boolean settings
                //if ($gen["name_display"] != "") $gen["use_cust_name"] = 1;
                //if ($gen["reply_to"] != "")   $gen["use_reply_to"]  = 1;
                if ($gen["signature"] != "")    $gen["use_signature"] = 1;
                if ($fpop["forward_to"] != "")  $fpop["forward"]      = 1;

                $this->setting_gen      = $gen;
                $this->setting_fpop     = $fpop;
                $this->setting_other    = $other;
                $this->setting_mobile   = $mobile;
                $this->setting_chat     = $chat;
            }

            // LABELS
            // Added $list_created to prevent overwriting of label_list; by Neerav; 27 April 2006
            if (!isset($this->label_list)) {
                $this->label_list = array();
                $list_created = true;
            } else {
                $list_created = false;
            }
            $this->label_total = array();
            if (isset($raw["cta"][1])) {
                foreach ($raw["cta"][1] as $v) {
                    if ($list_created) array_push($this->label_list, $v[0]);
                    array_push($this->label_total, $v[1]);
                }
            } elseif (isset($raw["cta"])) {
                Debugger::say('cta[1] not set, printing cta: '.print_r($raw["cta"],true));
            }
            
            // FILTERS
            $this->filter = array();
            if (isset($raw["fi"][1])) {
                foreach ($raw["fi"][1] as $fi) {
                    // Changed/Added by Neerav; 23 Jun 2005
                    // filter rules/settings
                    //     (The "() ? :" notation is used because empty/false fields at the end of an
                    //         array are not always defined)
                    $b = array(
                        // (integer) filter id number
                        "id"        =>                      $fi[0],
                        // (string) gmail's filter summary
                        "query"     => ((isset($fi[1]))    ? $fi[1] : ""),                      
                        // (string) from field has...
                        "from"      => ((isset($fi[2][0])) ? $fi[2][0] : ""),
                        // (string) to field has...
                        "to"        => ((isset($fi[2][1])) ? $fi[2][1] : ""),
                        // (string) subject has...
                        "subject"   => ((isset($fi[2][2])) ? $fi[2][2] : ""),
                        // (string) msg has the words...
                        "has"       => ((isset($fi[2][3])) ? $fi[2][3] : ""),
                        // (string) msg doesn't have the words...
                        "hasnot"    => ((isset($fi[2][4])) ? $fi[2][4] : ""),
                        // (boolean) has an attachment
                        "hasattach" => ((isset($fi[2][5]) and ($fi[2][5] == "true" or $fi[2][5] === true)) ? true : false),
                        // (boolean) archive (skip the inbox)
                        "archive"   => ((isset($fi[2][6]) and ($fi[2][6] == "true" or $fi[2][6] === true)) ? true : false), 
                        // (boolean) apply star
                        "star"      => ((isset($fi[2][7]) and ($fi[2][7] == "true" or $fi[2][7] === true)) ? true : false),
                        // (boolean) apply label
                        "label"     => ((isset($fi[2][8]) and ($fi[2][8] == "true" or $fi[2][8] === true)) ? true : false),
                        // (string) label name to apply
                        "label_name"=> ((isset($fi[2][9])) ? $fi[2][9] : ""),
                        // (boolean) forward
                        "forward"   => ((isset($fi[2][10]) and ($fi[2][10] == "true" or $fi[2][10] === true)) ? true : false),
                        // (string email) forward to email address
                        "forwardto" => ((isset($fi[2][11])) ? $fi[2][11]: ""),
                        // (boolean) trash the message
                        "trash"     => ((isset($fi[2][12]) and ($fi[2][12] == "true" or $fi[2][12] === true)) ? true : false)
                    );
                    array_push($this->filter, $b);
                }
            }
            $this->view = GM_PREFERENCE;
        } /* else { */
/*          $this->created = 0; */
/*          $this->snapshot_error = "libgmailer: no snapshot type specified";  // Added by Neerav; 3 Aug 2005 */
/*          return null; */
/*      } */

        $this->created = 1;
        return 1;
    }                

    /**
    * Constructor.
    *
    * Note: you are not supposed to create a GMailSnapshot object yourself. You should
    * use {@link GMailer::getSnapshot()} and/or {@link GMailer::webClip()} instead.
    *
    * @return GMailWebclipSnapshot
    * @param array $raw
    */
    function web_clip_snapshot($raw) {
        $clips = array();
        foreach ($raw as $webclip) {
            if (count($webclip) == 10) { 
                $ad_site = 4;
                $ad_date = 5;
                $ad_sub  = 6;
                $ad_type = 8;
                $ad_cat  = 9;
            } elseif (count($webclip) == 6) {
                $ad_site = 0;   // not really, but will be blank
                $ad_date = 0;   // not really, but will be blank
                $ad_sub  = 0;   // not really, but will be blank
                $ad_type = 4;
                $ad_cat  = 5;
            } elseif (count($webclip) == 7) {
                $ad_site = 4;
                $ad_date = 0;   // not really, but will be blank
                $ad_sub  = 0;   // not really, but will be blank
                $ad_type = 5;
                $ad_cat  = 6;
            } else {
                Debugger::say("odd web clips: ".print_r($webclip,true));
                continue;
            }
            $clip = array();
            //$clip[''] = $webclip[0]; // ??
            //$clip[''] = $webclip[1]; // clip id??
            $clip['url']        = $webclip[2];
            $clip['title']      = $webclip[3];
            $clip['site']       = (($ad_site != 0) ? $webclip[$ad_site]: "");
            $clip['date']       = (($ad_date != 0) ? $webclip[$ad_date]: "");
            $clip['sub']        = (($ad_sub != 0) ? $webclip[$ad_sub]: "");
            //$clip['']         = (($less) ? "" : $webclip[7]); // ?? empty
            $clip['type']       = $webclip[$ad_type]; // web clip, advertisement, gmail tip, etc.
            //$clip['category']     = $webclip[$ad_cat]; // a number
                // 1 = Gmail Tip, 2 = News, 3 = Business, 4 = Lifestyle
                // 5 = Fun, 6 = Tech, 7 = Sports, 8 = custom
            $clips[] = $clip;           
        }
        $this->web_clips = $clips;
        return 1;
    }
    
}


/**
 * Class Debugger
 *
 * @package GMailer 
*/
class Debugger {    
   /**
    * Record debugging message.
    *
    * @param string $str Message to be recorded
    * @return void
    * @static
   */
    function say($str) {
        global $D_FILE, $D_ON;
        if ($D_ON) {
            $fd = fopen($D_FILE, "a+");
            $str = str_replace("*/", "*", $str);   // plug possible security hole
            fwrite($fd, "<?php /** ".$str." **/ ?".">\n");
            fclose($fd);
        }
    }
}    

define("GM_DEBUG_CURL",         false);     // Added by Neerav; 19 Apr 2006


?>
