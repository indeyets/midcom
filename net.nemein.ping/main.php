<?php
/* 
 * @package net.nemein.ping
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 *
 * MidCOM Weblog Pinger library, based on
 * Weblog_Pinger PHP Class Library by Rogers Cadenhead
 * Version 1.2
 * Web: http://www.cadenhead.org/workbench/weblog-pinger  
 * 
 * Copyright (C) 2004 Rogers Cadenhead
 * 
 * The Weblog_Pinger class can send a ping message over XML-RPC to
 * weblog notification services such as Weblogs.Com, Blo.gs,
 * and Technorati.
 * 
 * This class should be stored in a directory accessible to
 * the PHP scripts that will use it.
 * 
 * This software requires the XML-RPC for PHP class library by
 * Usefulinc: http://xmlrpc.usefulinc.com/php.html. 
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
 
class net_nemein_ping_pinger extends midcom_baseclasses_components_purecode
{
    /**
     * Initializes the class and stores the selected person to be shown
     * The argument should be a MidgardPerson object. In the future DM
     * Array format will also be supported.
     * 
     * @param mixed $person Person to display either as MidgardPerson or Datamanager array
     */
    function net_nemein_ping_pinger()
    {
        $this->_component = 'net.nemein.ping';
    
        parent::midcom_baseclasses_components_purecode();
    }

    /* Multi-purpose ping for any XML-RPC server that supports the Weblogs.Com interface. */
    function ping($xml_rpc_server, $xml_rpc_port, $xml_rpc_path, $weblog_name, $weblog_url, $changes_url=null, $category=null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Build the required parameters
        $parameters = array();
        $parameters[] = new XML_RPC_Value($weblog_name, 'string');
        $parameters[] = new XML_RPC_Value($weblog_url, 'string');
        
        // Add optional parameters if provided
        if ($changes_url)
        {
            $parameters[] = new XML_RPC_Value($changes_url, 'string');
            
            if ($category)
            {
                $parameters[] = new XML_RPC_Value($category, 'string');
            }
        }
            
        // Create the message
        $message = new XML_RPC_Message('weblogUpdates.ping', $parameters);
        
        // Start up the client
        $client = new XML_RPC_Client($xml_rpc_path, $xml_rpc_server, $xml_rpc_port);
        
        // Make the request
        $response = $client->send($message);
        
        // Check error conditions
        if ($response == 0) 
        {
            debug_add("XML-RPC communication error with {$xml_rpc_server}: {$client->errno} {$client->errstring}");
            debug_pop();
            return false;
        }
        if ($response->faultCode() != 0)  
        {
            debug_add("Error pinging {$xml_rpc_server}: ".$response->faultCode()." ".$response->faultString());
            debug_pop();
            return false;
        }
        
        debug_add("Successfully pinged {$xml_rpc_server}");
        debug_pop();
        return true;     
    }
    
    function ping_object($object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($this->_config->get('enable_weblog_pings'))
        {
            if (!$_MIDCOM->auth->can_do('midgard:read', $object, 'EVERYONE'))
            {
                debug_add("This object isn't publicly readable, don't ping");
                debug_pop();
                return false;
            }
        
            if (array_key_exists('view_contentmgr', $GLOBALS))
            {
                // FIXME: This isn't exactly pretty
                debug_add("We're in AIS, instantiate NAP for its context");
                $nav = new midcom_helper_nav($GLOBALS['view_contentmgr']->_context);
            }
            else
            {
                $nav = new midcom_helper_nav();
            }
            $node = $nav->get_node($object->topic);
            
            if (!$node)
            {
                debug_add("Failed to resolve the object's topic into NAP node");
                debug_print_r('Object', $object);
                debug_pop();
                return false;
            }
            
            if (in_array($node[MIDCOM_NAV_COMPONENT], $this->_config->get('components_to_ping')))
            {
                debug_add("The component {$node[MIDCOM_NAV_COMPONENT]} is one of the components to ping about");
                
                $ping_servers = $this->_config->get('weblog_ping_servers');
                foreach ($ping_servers as $service_name => $service)
                {
                    debug_add("Pinging {$service_name}...");
                    $this->ping($service['server'], $service['port'], $service['path'], $node[MIDCOM_NAV_NAME], $node[MIDCOM_NAV_FULLURL]);
                }
            }
            
            debug_pop();
            return true;
        }
        else
        {
            debug_pop();
            return false;
        }
    }
}
?>