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

require_once("libgmailer.php");
   
$gmail_acc = $_POST['username']."@".$_POST['domain'];
$gmail_pwd = $_POST['password'];
$my_timezone = 0;
 
$gmailer = new GMailer();
if ($gmailer->created)
{
	$gmailer->setLoginInfo($gmail_acc, $gmail_pwd, $my_timezone);
	
	//uncomment if you need it
	//$gmailer->setProxy("proxy.company.com");
	
	if ($gmailer->connect()) 
	{
		// GMailer connected to Gmail successfully.
		// Do something with it.
		
		//Get the contacts
		// For "Inbox"
		
		$gmailer->fetchBox(GM_CONTACT, "all", "");
		
		$snapshot = $gmailer->getSnapshot(GM_CONTACT);
		//$w=count($snapshot->contacts);
		echo "<table border='1'>
		<tr><td align='center'><b>Name</b></td><td align='center'><b>Email Address</b></td></tr>";
		for($i=0;$i<=count($snapshot->contacts);$i++)
		{
			$store=$snapshot->contacts[$i];
			$name=$store["name"];
			$nam=str_replace("string","name",$name);
			$email=$store["email"];
			$emal=str_replace("string","email",$email);
			print("<tr><td style='Font-Family:verdana;Font-Size:14'>$nam</td><td style='Font-Family:verdana;Font-Size:14'>$emal</td></tr>");
		}
		echo "</table>";
		//Outputs the number of contacts
		//var_dump($snapshot->contacts_total);
	
	}
	else 
	{
		die("Fail to connect because: ".$gmailer->lastActionStatus());
	}
} 
else 
{
	die("Failed to create GMailer because: ".$gmailer->lastActionStatus());
}

?>
