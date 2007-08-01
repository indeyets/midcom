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

class myspace
{
    var $dir_path = "";
    var $error_msg = "";
    
	function grabMyspace()
    {
    	require_once('./config.php');
    	 $this->dir_path = $DIR_PATH;
    	 $this->error_msg = $ERROR_LOGIN;
    }
    
	function getAddressbook($YOUR_EMAIL,$YOUR_PASSWORD)
	{
		$ch = curl_init();
		
		// setup and configure
		$randnum = rand(1,9999999);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->dir_path."/tmp/cookiejar-$randnum");
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->dir_path."/tmp/cookiejar-$randnum");
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 0);
		
		// get homepage for login page token
		curl_setopt($ch, CURLOPT_URL,"http://www.myspace.com");
		$page = curl_exec($ch);
		
		// find it....
		preg_match("/MyToken=([^\"]+)\"/",$page,$token);
		$token = $token[1];
		
		// do login
		curl_setopt($ch, CURLOPT_URL,"http://login.myspace.com/index.cfm?fuseaction=login.process&MyToken={$token}");
		curl_setopt($ch, CURLOPT_REFERER, "http://www.myspace.com");
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded"));
		curl_setopt($ch, CURLOPT_POST, 1);
		$postfields = "email=" . urlencode($YOUR_EMAIL);
		$postfields .= "&password=" . urlencode($YOUR_PASSWORD);
		$postfields .= '&ctl00%24Main%24SplashDisplay%24login%24loginbutton.x=38&ctl00%24Main%24SplashDisplay%24login%24loginbutton.y=15';
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
		$page = curl_exec($ch);
		
		
		$old_token=$token;
		$token=NULL;
		
		// find redirect url
		preg_match("/fuseaction=user&Mytoken=(.*)\"/",$page,$token);
		
		$token = $token[1];
		$redirpage="http://home.myspace.com/index.cfm?fuseaction=user&MyToken=$token";
		
		
		// do the redirect
		curl_setopt($ch, CURLOPT_REFERER,"http://login.myspace.com/index.cfm?fuseaction=login.process&MyToken=$token");
		curl_setopt($ch, CURLOPT_URL,$redirpage);
		curl_setopt($ch, CURLOPT_POST, 0);
		$page = curl_exec($ch);
		
		
		// check login error
		if(strpos($page,"You Must Be Logged-In to do That!") !== false){
		   echo  $this->error_msg;
		   exit;
		}
		else 
		{
			//echo "Login Successfully...........<br /><br />";
		}
		
		
		// LOGGED IN, now let's play
		preg_match("/ id=\"ctl00_Main_ctl00_Welcome1_AddressBookHyperLink\" href=\"([^\"]+)\"/",$page,$redirpage);
		$redirpage = $redirpage[1];
		
		// go there (Addredd Book)
		curl_setopt($ch, CURLOPT_URL, $redirpage);
		
		$page = curl_exec($ch);
		
		
		//pars page to get user name and email from addressbook
		$regexp = "<a href=\"#\" onclick=\"[^\"]*\" title=\"View this Contact\">(.*?)<\/a>";
		preg_match_all("/$regexp/s", $page, $username);
		
		$regexp = "<td class=\"email\">(.*?)<\/td>";
		preg_match_all("/$regexp/s", $page, $emails);
		
		$regexp = "href=\"([^\"]*)\"><font[^>]*>SignOut";
		preg_match_all("/$regexp/s", $page, $logout);
		
		curl_setopt($ch, CURLOPT_URL, $logout[1][0]);
		$page = curl_exec($ch);
		
		curl_close($ch);
		@unlink("/tmp/cookiejar-$randnum");
		
               
		$result['name'] =$username[1];
		$result['email'] = $emails[1];
		//$result=array_merge($username[1],$emails[1]);
		return $result;
	}
}
?>
