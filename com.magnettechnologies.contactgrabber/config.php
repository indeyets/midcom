<?php
/**
 * @package com.magnettechnologies.contactgrabber
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

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
  */

// Write full path to this installation directory
// example: '/home/user/public_html/contacts' (linux)
//
//$DIR_PATH = "/home/uploads/public_html/contacts";
$DIR_PATH = "";

// Write the path of curl installation
// example: '/usr/local/bin/curl' (linux)
//
//$CURL_PATH = "/usr/local/bin/curl";
$CURL_PATH = "/usr/bin/curl";

// NOTE: make sure that the 'tmp' folder have write permission.

$ERROR_LOGIN = "<br />Login Error...";

?>
