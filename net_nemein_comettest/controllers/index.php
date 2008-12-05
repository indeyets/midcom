<?php
/**
 * @package net_nemein_comettest
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comet test controller
 *
 * @package net_nemein_comettest
 */
class net_nemein_comettest_controllers_index
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    public function action_index($route_id, &$data, $args)
    {
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/net_nemein_comettest/pi.js");
    }
    
    public function action_unixtime($route_id, &$data, $args)
    {
        $type = null;
        $name = null;
        
        if (isset($_GET["cometType"]))
        {
            $type = $_GET["cometType"];
        }
        
        if (isset($_GET["cometName"]))
        {
            $name = $_GET["cometName"];
        }

    	if (ob_get_level() == 0)
    	{
    	    ob_start();
    	}
    	
    	while (true)
    	{
    		net_nemein_comettest_pusher::pushdata(time(), $type, $name);
    		ob_flush();
    		flush();
    		sleep(1);
    	}
    }
}
?>