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

include('msn_contact_grab.class.php');

$msn2 = new msn;

$username = $_POST['username']."@".$_POST['domain'];
$returned_emails = $msn2->qGrab($username, $_POST['password']);
echo "<table border='1'>
<tr><td align='center'><b>Name</b></td><td align='center'><b>Email Address</b></td></tr>";
        foreach($returned_emails as $row){
	print("<tr><td style='Font-Family:verdana;Font-Size:14'>$row[1]</td><td style='Font-Family:verdana;Font-Size:14'>$row[0]</td></tr>");
       
        }
echo "</table>";
?>

