<?php
/**
 * @package org.maemo.gforgeprofileupdater
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * SOAP User Include - this file contains wrapper functions for the SOAP interface
 *
 * Copyright 2004 (c) GForge, LLC
 * http://gforge.org
 *
 * @version   $Id: user.php,v 1.5 2005/04/29 20:38:09 tperdue Exp $
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 *
 */

require_once('common/include/Error.class');
require_once('common/include/User.class');

// Add The definition of a user object
$server->wsdl->addComplexType(
	'User',
	'complexType',
	'struct',
	'sequence',
	'',
	array(
	'user_id' => array('name'=>'user_id', 'type' => 'xsd:int'),
	'user_name' => array('name'=>'user_name', 'type' => 'xsd:string'),
	'title' => array('name'=>'title', 'type' => 'xsd:string'),
	'firstname' => array('name'=>'firstname', 'type' => 'xsd:string'),
	'lastname' => array('name'=>'lastname', 'type' => 'xsd:string'),
	'email' => array('name'=>'email', 'type' => 'xsd:string'),
	'jabber_address' => array('name'=>'jabber_address', 'type' => 'xsd:string'),
	'address' => array('name'=>'address', 'type' => 'xsd:string'),
	'address2' => array('name'=>'address2', 'type' => 'xsd:string'),
	'phone' => array('name'=>'phone', 'type' => 'xsd:string'),
	'fax' => array('name'=>'fax', 'type' => 'xsd:string'),
	'status' => array('name'=>'status', 'type' => 'xsd:string'),
	'timezone' => array('name'=>'timezone', 'type' => 'xsd:string'),
	'country_code' => array('name'=>'country_code', 'type' => 'xsd:string'),
	'add_date' => array('name'=>'add_date', 'type' => 'xsd:int'),
	'language_id' => array('name'=>'language_id', 'type' => 'xsd:int')
	) );

// Array of users
$server->wsdl->addComplexType(
    'ArrayOfUser',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:User[]')),
    'tns:User');

//getUsers (id array)
$server->register(
    'getUsers',
    array('session_ser'=>'string','user_ids'=>'tns:ArrayOfint'),
    array('userResponse'=>'tns:ArrayOfUser'),
    $uri,
    $uri.'#getUsers','rpc','encoded'
);

//getUsersByName (unix_name array)
$server->register(
    'getUsersByName',
    array('session_ser'=>'string','user_ids'=>'tns:ArrayOfstring'),
    array('userResponse'=>'tns:ArrayOfUser'),
    $uri,
    $uri.'#getUsersByName','rpc','encoded'
);

//getGroups (id array)
$server->register(
    'userGetGroups',
    array('session_ser'=>'string','user_id'=>'xsd:int'),
    array('groupResponse'=>'tns:ArrayOfGroup'),
    $uri,
    $uri.'#userGetGroups','rpc','encoded'
);


//get user objects for array of user_ids
function &getUsers($session_ser,$user_ids) {
	continue_session($session_ser);
	$usrs =& user_get_objects($user_ids);
	if (!$usrs) {
		return new soap_fault ('3001','user','Could Not Get Users By Id','Could Not Get Users By Id');
	}

	return users_to_soap($usrs);
}

//get user objects for array of unix_names
function &getUsersByName($session_ser,$user_names) {
	continue_session($session_ser);
	$usrs =& user_get_objects_by_name($user_names);
	if (!$usrs) {
		return new soap_fault ('3002','user','Could Not Get Users By Name','Could Not Get Users By Name');
	}

	return users_to_soap($usrs);
}

//get groups for user_id
function &userGetGroups($session_ser,$user_id) {
	continue_session($session_ser);
	$user =& user_get_object($user_id);
	if (!$user) {
		return new soap_fault ('3003','user','Could Not Get Users Groups','Could Not Get Users Groups');
	}
	return groups_to_soap($user->getGroups());
}

/*
	Converts an array of User objects to soap data
*/
function &users_to_soap($usrs) {
	$return = array();
	for ($i=0; $i<count($usrs); $i++) {
		if ($usrs[$i]->isError()) {
			return new soap_fault ('','User to soap',$usrs[$i]->getErrorMessage(),$usrs[$i]->getErrorMessage());
			//skip it if it had an error
		} else {
			//build an array of just the fields we want
			$return[] = array(
			'user_id'=>$usrs[$i]->data_array['user_id'],
			'user_name'=>$usrs[$i]->data_array['user_name'],
			'title'=>$usrs[$i]->data_array['title'],
			'firstname'=>$usrs[$i]->data_array['firstname'],
			'lastname'=>$usrs[$i]->data_array['lastname'],
			'email'=>$usrs[$i]->data_array['email'],
			'jabber_address'=>$usrs[$i]->data_array['jabber_address'],
			'address'=>$usrs[$i]->data_array['address'],
			'address2'=>$usrs[$i]->data_array['address2'],
			'phone'=>$usrs[$i]->data_array['phone'],
			'fax'=>$usrs[$i]->data_array['fax'],
			'status'=>$usrs[$i]->data_array['status'],
			'timezone'=>$usrs[$i]->data_array['timezone'],
			'country_code'=>$usrs[$i]->data_array['ccode'],
			'add_date'=>$usrs[$i]->data_array['add_date'],
			'language_id'=>$usrs[$i]->data_array['language_id']
			);
		}

	}
	return $return;
}


/**
 * RAMBO
 */

$server->register
(
    'updateUser',
    array
    (
        'session_ser' => 'xsd:string',
        'userdata' => 'tns:User'
    ),
    array('updateResponse' => 'xsd:string'),
    $uri,
    $uri.'#updateUser'
);

function &updateUser($session_ser, $userdata)
{
	continue_session($session_ser);
    session_require(array('isloggedin'=>'1'));
    // get global users vars
    $logged_user =& user_get_object(user_getid());
    $authorized = false;

    // Verify privileges so that only user itself and gforge admins can change the data
    if ($logged_user->getID() == $userdata['user_id'])
    {
        $authorized = true;
    }
    else
    {
        $groups = $logged_user->getGroups();
        foreach ($groups as $group)
        {
            if ($group->getID() == 1)
            {
                // Member of group #1, the master race...
                $authorized = true;
                break;
            }
        }
    }

    if (!$authorized)
    {
        return new soap_fault ('3996','user','Only user itself or admin may update user data','Only user itself or admin may update user data');
    }


    $user =& user_get_object($userdata['user_id']);
    if (   !$user
        || $user->getID() != $userdata['user_id'])
    {
        return new soap_fault ('3997','user',"Could not find user #{$userdata['user_id']}","Could not find user #{$userdata['user_id']}");
    }

    /* These won't work for some reason,
    if ($user->getEmail() != $userdata['email'])
    {
        // Email has changed, update (for weirdest reason can't be handled by the update() -method)
        if (!$user->setEmail($userdata['email']))
        {
            return new soap_fault ('3999','user','User email update failed: ' . $user->getErrorMessage(),'User email update failed: ' . $user->getErrorMessage());
        }
    }
    if (   !$user->update
            (
                $userdata['firstname'],
                $userdata['lastname'],
                $userdata['language_id'],
                $userdata['timezone'],
                $user->getMailingsPrefs('site'),
                $user->getMailingsPrefs('va'),
                $user->usesRatings(),
                $userdata['jabber_address'],
                $user->getJabberOnly(),
                $user->getThemeID(),
                $userdata['address'],
                $userdata['address2'],
                $userdata['phone'],
                $userdata['fax'],
                $userdata['title'],
                $userdata->country_code
            )
        )
    {
        return new soap_fault ('3998','user','User update failed: ' . $user->getErrorMessage(),'User update failed: ' . $user->getErrorMessage());
    }
    */

    // We shouldn't have to do this on the raw, but seems we have no choice as the "real API" methods throw unexplainable errors at us
    if (!db_begin())
    {
        return new soap_fault ('3995','user','Could not db_begin()','Could not db_begin()');
    }
    $query = "UPDATE users SET ";
    $query .= "realname='" . htmlspecialchars($userdata['firstname'] . ' ' . $userdata['lastname']) . "', ";
    $query .= "firstname='" . htmlspecialchars($userdata['firstname']) . "', ";
    $query .= "lastname='" . htmlspecialchars($userdata['lastname']) . "', ";
    if (   isset($userdata['language_id'])
        && !empty($userdata['language_id']))
    {
        $query .= "language='{$userdata['language_id']}', ";
    }
    $query .= "timezone='{$userdata['timezone']}', ";
    $query .= "jabber_address='{$userdata['jabber_address']}', ";
    $query .= "email='{$userdata['email']}', ";
    $query .= "address='" . htmlspecialchars($userdata['address']) . "', ";
    $query .= "address2='" . htmlspecialchars($userdata['address2']) . "', ";
    $query .= "phone='" . htmlspecialchars($userdata['phone']) . "', ";
    $query .= "fax='" . htmlspecialchars($userdata['fax']) . "', ";
    $query .= "title='" . htmlspecialchars($userdata['title']) . "', ";
    $query .= "ccode='{$userdata['country_code']}' ";
    $query .= "WHERE user_id='{$userdata['user_id']}'; ";

    $res = db_query($query);

    if (!$res)
    {
        $error = db_error();
        db_rollback();
        //return new soap_fault ('3993','user',"Could not update user, db_error: {$error}","Could not update user, db_error: {$error}");
        return new soap_fault ('3993','user',"Could not update user, db_error: {$error}, query: {$query}","Could not update user, db_error: {$error}, query: {$query}");
    }

    if (!db_commit())
    {
        return new soap_fault ('3994','user','Could not db_commit()','Could not db_commit()');
    }

    //return "OK, query: {$query}";
    return "OK";
}

?>
