<?php
/**
 * Weblog pinger library based on
 * Weblog_Pinger PHP Class Library by Rogers Cadenhead
 * http://www.cadenhead.org/workbench/weblog-pinger
 * 
 * @package net.nemein.ping
 */
class net_nemein_ping_interface extends midcom_baseclasses_components_interface
{
    
    function net_nemein_ping_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.ping';
        $this->_purecode = true;
        $this->_autoload_files = Array('main.php');
    }
    
    function _on_initialize()
    {
        // Include PEAR XML-RPC library
        error_reporting(E_ERROR);
        include_once("XML/RPC.php");
        error_reporting(E_ALL);

        return class_exists('XML_RPC_Client');
    }
    
    function _on_watched_dba_update($article)
    {
        // Ping requested article
        $pinger = new net_nemein_ping_pinger();
        $pinger->ping_object($article);
    }
    
}
?>