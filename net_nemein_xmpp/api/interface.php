<?php
/**
 * @package net_nemein_xmpp
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * API interface
 *
 * @package net_nemein_xmpp
 */
interface net_nemein_xmpp_api_interface
{    
    public function __construct($host, $port, $user, $password, $resource, $server=null, $printlog=false, $loglevel=null);
    
    public function connect($persistent=false, $sendinit=true);
    public function disconnect();
    public function message($to, $body, $type='chat', $subject=null);
    public function presence($status=null, $show='available', $to=null);
    
    public function process_until($event, $timeout=-1);
}
?>