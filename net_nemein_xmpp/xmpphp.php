<?php
/**
 * @package net_nemein_xmpp
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

include ('api/xmpphp/xmpp.php');

/**
 * XMPPHP wrapper
 *
 * @package net_nemein_xmpp
 */
class net_nemein_xmpp_xmpphp implements net_nemein_xmpp_api_interface
{
    protected $api;
    
    public function __construct($host, $port, $user, $password, $resource, $server=null, $printlog=false, $loglevel=null)
    {
        $this->api = new XMPP($host, $port, $user, $password, $resource, $server, $printlog, $loglevel);
    }
    
    public function connect($persistent=false, $sendinit=true)
    {        
        $this->api->connect($persistent, $sendinit);
    }

    public function disconnect()
    {        
        $this->api->disconnect();
    }

    public function message($to, $body, $type='chat', $subject=null)
    {
        $this->api->message($to, $body, $type, $subject);
    }
    
    public function presence($status=null, $show='available', $to=null)
    {
        $this->api->presence($status, $show, $to);
    }
    
    public function processUntil($event, $timeout=-1)
    {
        $this->api->processUntil($event, $timeout);
    }

}
?>