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
        $_MIDCOM->head->enable_jsmidcom();
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/helpers/comet.js");
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
    
    public function action_saver($route_id, &$data, $args)
    {
        if (isset($_POST['string']))
        {
            $sess = new midcom_core_services_sessioning();
            $sess->set("string", $_POST['string']);
            
            echo $_POST['string'];
        }
    }
    
    public function action_echoer($route_id, &$data, $args)
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

        // if (! $session->exists("string"))
        // {
        //     $session->set("string", false);
        // }
        
        if (ob_get_level() == 0)
        {
            ob_start();
        }
        
        $session = new midcom_core_services_sessioning();
        
        $last_push = '';
        while (true)
        {
            if (   $session->exists("string")
                && $session->get("string") != '') //$last_push
            {                    
                $last_push = $session->get("string");
                $session->remove("string");
                net_nemein_comettest_pusher::pushdata($last_push, $type, $name);
            }
            else
            {
                net_nemein_comettest_pusher::pushdata('', $type, $name);
            }
            //net_nemein_comettest_pusher::pushdata(time(), $type, $name);
            ob_flush();
            flush();
            sleep(1);
        }
    }
    
}
?>