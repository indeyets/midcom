<?php
/**
 * @package com_rohea_mjumpaccount
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic controller
 *
 * @package com_rohea_mjumpaccount
 */
class com_rohea_account_controllers_index
{

    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;   
    }
  
    public function action_index($route_id, &$data, $args)
    {
        $current_person = null;
        $data['logged_in'] = false;
        if ($_MIDCOM->authentication->is_user())
        {
            $current_person = $_MIDCOM->authentication->get_person();
            $data['logged_in'] = true;            
        }
    }
         
    public function action_login($route_id, &$data, $args)
    {
        $current_person = null;
        if ($_MIDCOM->authentication->is_user())
        {
            $current_person = $_MIDCOM->authentication->get_person();
            //header("Location: /home/{$person->username}/stream/");
            //exit();
        }

        $data['facebook_enabled'] = false;
        if ($this->configuration->facebook_enabled)
        {
            $data['facebook_enabled'] = true;
            $qb = new midgard_query_builder('midgard_page');
            $qb->add_constraint('component', '=', 'com_rohea_facebook');
            $res = $qb->execute();
            if(count($res) == 1)
            {
                $data['fb_page_instance_guid'] = $res[0]->guid;
            }
            else
            {
                throw new Exception("com_rohea_account_index: Too many or no instances of com_rohea_facebook component found");
            }
            
        }
        // Link to registration form
        $qb = new midgard_query_builder('midgard_page');
        $qb->add_constraint('component', '=', 'com_rohea_account');
        $res = $qb->execute();
        if(count($res) == 1)
        {
            $data['registration_url'] = $_MIDCOM->dispatcher->generate_url('registration', array(), $res[0]);
        }
        else
        {
            throw new Exception("com_rohea_account_login_index: Too many instances of com_rohea_account component found");
        }
    }
    
    public function action_info($route_id, &$data, $args)
    {
        $data['current_person'] = null;
        $data['logged_in'] = false;
        if ($_MIDCOM->authentication->is_user())
        {
            $data['current_person'] = $_MIDCOM->authentication->get_person();
            $data['logged_in'] = true;
        }
    }
}
?>
