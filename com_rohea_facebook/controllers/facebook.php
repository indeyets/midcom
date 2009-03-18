<?php
/**
 * @package com_rohea_facebook
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic controller
 *
 * @package com_rohea_facebook
 */
class com_rohea_facebook_controllers_facebook
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    public function action_login($route_id, &$data, $args)
    {
        $qb = new midgard_query_builder('midgard_page');
        $qb->add_constraint('component', '=', 'com_rohea_facebook');
        $res = $qb->execute();
        if (count($res) > 0)
        {
           $data['registration_url'] = $_MIDCOM->dispatcher->generate_url('registration', array(), $res[0]);
        }
        
        $data['api_key'] = trim($this->configuration->get("facebook_api_key"));
    }
}
?>