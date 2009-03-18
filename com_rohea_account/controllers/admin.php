<?php
/**
 * @package com_rohea_mjumpaccount
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic controller admin
 *
 * @package com_rohea_mjumpaccount
 */
class com_rohea_account_controllers_admin
{


    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    

    public function action_admin($route_id, &$data, $args)
    {
        //TODO: Admin ACL check !!!
        
        $data['name'] = "com_rohea_account";
        
        $qb = new midgard_query_builder('midgard_person');
        $persons = $qb->execute();
        $registered_persons = array();
        foreach($persons as $p)
        {
            if($p->username != $p->guid)
            {
                $registered_persons[] = $p;
            }        
        }
        $data['registered_persons'] = $registered_persons;
    }
    
    
}
?>
