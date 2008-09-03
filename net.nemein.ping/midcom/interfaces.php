<?php
/**
 * @package net.nemein.ping
 */

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
        parent::__construct();
        
        $this->_component = 'net.nemein.ping';
        $this->_purecode = true;
        $this->_autoload_files = array
        (
            'main.php'
        );
        $this->_autoload_libraries = array
        (
            'midcom.services.at',
        );
    }
    
    function _on_initialize()
    {
        // Include PEAR XML-RPC library
        error_reporting(E_ERROR);
        include_once("XML/RPC.php");
        error_reporting(E_ALL);
        
        if (!class_exists('XML_RPC_Client'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("XML_RPC_Client class not found, skipping pinger", MIDCOM_LOG_WARN);
            debug_pop();
        }

        return true;
    }
    
    function _on_watched_dba_update($article)
    {
        $pinger = new net_nemein_ping_pinger($article);
        if (!$pinger->check_pingability())
        {
            return;
        }
        
        // Register the ping to midcom.services.at instead of running interactively
        $args = array
        (
            'article' => $article->guid,
        );
        $atstat = midcom_services_at_interface::register(time(), 'net.nemein.ping', 'ping', $args);
        if (!$atstat)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to register at service to ping about changes in article {$article->guid}", MIDCOM_LOG_WARN);
            debug_pop();
        }
        
        $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.ping', 'net.nemein.ping'), sprintf($_MIDCOM->i18n->get_string('article %s has been registered for weblog pinging', 'net.nemein.ping'), $article->title), 'ok');
    }
    
    /**
     * AT handler for handling subscription cycles.
     * @param array $args handler arguments
     * @param object &$handler reference to the cron_handler object calling this method.
     * @return boolean indicating success/failure
     */
    function ping($args, &$handler)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!isset($args['article']))
        {
            $msg = 'Article GUID not set, aborting';
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }
        
        $article = new midcom_db_article($args['article']);
        if (!$article)
        {
            $msg = "Article {$args['article']} not found, error " . mgd_errstr();
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }
        
        // Ping the article
        $pinger = new net_nemein_ping_pinger($article);
        $stat = $pinger->ping_object();
        debug_pop();
        
        return $stat;
    }
    
}
?>