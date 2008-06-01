<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Person viewer NAP interface class.
 * 
 * Does not deliver any leaves as person address listings can grow quite big.
 * 
 * @package net.nemein.personnel
 */
class net_nemein_personnel_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, which ties to the parent class constructor
     * 
     * @access public
     */
    function net_nemein_personnel_navigation() 
    {
        parent::midcom_baseclasses_components_navigation();
    }
    
    /**
     * Determine what should be displayed in the navigation
     * 
     * @access public
     * @return mixed Array containing details of the leaves
     */
    function get_leaves()
    {
        $this->_root_group = new midcom_db_group($this->_config->get('group'));
        
        switch ($this->_config->get('display_in_navigation'))
        {
            case 'personnel':
                return $this->_get_personnel();
                break;
            
            case 'groups':
                return $this->_get_groups();
                break;
            
            default:
                return array ();
        }
    }
    
    /**
     * Get the list of subgroups that belong to the master group.
     * 
     * @access private
     * @return mixed Array containing details of the leaves
     */
    function _get_groups()
    {
        $leaves = array ();
        
        $qb = midcom_db_group::new_query_builder();
        $qb->add_constraint('owner', '=', $this->_root_group->id);
        $qb->add_constraint('metadata.hidden', '<>', 1);
        $qb->add_order('metadata.score', 'DESC');
        
        foreach ($qb->execute() as $group)
        {
            // Forge the leaf information
            $leaves[$group->guid] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => "group/{$group->guid}/",
                    MIDCOM_NAV_NAME => ($group->official) ? $group->official : $group->name,
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => $group->guid,
                MIDCOM_NAV_OBJECT => $group,
                MIDCOM_META_CREATOR => $group->metadata->creator,
                MIDCOM_META_EDITOR => $group->metadata->revisor,
                MIDCOM_META_CREATED => $group->metadata->created,
                MIDCOM_META_EDITED => $group->metadata->revised,
            );
        }
        
        return $leaves;
    }
    
    /**
     * Get the list of personnel belonging to the content topic. This method will determine
     * first if it is requested to get members from all of the sub groups or just the master
     * group. Then it will do Midgard version spesific treatment to sort the memberships
     * according to the configuration and finally generate an array of the personnel, which
     * will be returned for the navigation access point
     *
     * @access private
     * @return mixed Array containing details of the leaves
     */
    function _get_personnel()
    {
        if (   !$this->_config->get('group')
            || !mgd_is_guid($this->_config->get('group')))
        {
            return array ();
        }
        
        // Get the sorting order (will be displayed at least as the navigation name
        $sorting = array();
        foreach ($this->_config->get('index_order') as $order)
        {
            if (substr($order, 0, 4) === 'uid.')
            {
                $sorting[] = substr($order, 4);
            }
        }
        
        if (count($order) === 0)
        {
            $sorting[] = 'lastname';
            $sorting[] = 'firstname';
        }
        
        // Get the master group
        $group = new midcom_db_group($this->_config->get('group'));
        
        if (   !$group
            || !$group->id)
        {
            return array ();
        }
        
        // Get the groups
        $qb_groups = midcom_db_group::new_query_builder();
        $qb_groups->add_constraint('owner', '=', $group->id);
        
        // Get the memberships
        $qb = midcom_db_member::new_query_builder();
        
        // Get the memberships of all of the sub groups if configured
        // to do so, otherwise get only of the configured master group
        if ($this->_config->get('sort_order') === 'sorted and grouped')
        {
             $qb->begin_group('OR');
                 $qb->add_constraint('gid', '=', $group->id);
                 
                 // Get all the sub groups
                 foreach ($qb_groups->execute_unchecked() as $subgroup)
                 {
                     $qb->add_constraint('gid', '=', $subgroup->id);
                 }
             $qb->end_group();
        }
        else
        {
             $qb->add_constraint('gid', '=', $group->id);
        }
        
        // Different Midgard versions require different kind of magic
        if (version_compare(mgd_version(), '1.8.2', '>='))
        {
            foreach ($this->_config->get('index_order') as $order)
            {
                if (stristr($order, 'reverse'))
                {
                    $qb->add_order(ereg_replace('reverse[[:space:]]+', '', $order), 'DESC');
                    continue;
                }
                
                $qb->add_order($order);
            }
            
            $persons = array();
            
            foreach ($qb->execute_unchecked() as $membership)
            {
                $persons[] =& new midcom_db_person($membership->uid);
            }
        }
        else
        {
            $temp = array ();
            
            // Sort by score
            if (stristr($this->_config->get('sort_order'), 'sorted'))
            {
                foreach ($qb->execute_unchecked() as $membership)
                {
                    $temp[$membership->guid] = $membership->get_parameter('net.nemein.personnel', 'score');
                }
                
                asort($temp);
            }
            else
            {
                foreach ($qb->execute_unchecked() as $membership)
                {
                    $person = new midcom_db_person($membership->uid);
                    $personnel[$membership->guid] =& $person;
                    $temp[$membership->guid] = '';
                    
                    foreach ($sorting as $sort)
                    {
                        $temp[$membership->guid] .= $person->$sort . ' ';
                    }
                }
                
                asort($temp);
                
                $persons = array();
                
                foreach ($temp as $membership_guid => $string)
                {
                    $persons[] = $personnel[$membership_guid];
                }
            }
            
        }
        
        // Finally loop through the person records and forge the final array
        foreach ($persons as $person)
        {
            $name = '';
            
            // Get all the elements requested for sorting
            foreach ($sorting as $sort)
            {
                $name .= $person->$sort . ' ';
            }
            
            $url = net_nemein_personnel_viewer::get_url($person);
            
            if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
            {
                $created = $person->metadata->created;
                $creator = $person->metadata->creator;
                $revised = $person->metadata->revised;
                $revisor = $person->metadata->revisor;
            }
            else
            {
                $created = $person->created;
                $creator = $person->creator;
                $revised = $person->revised;
                $revisor = $person->revisor;
            }
            
            // Forge the leaf information
            $leaves[$person->guid] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => $url,
                    MIDCOM_NAV_NAME => trim($name),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => $person->guid,
                MIDCOM_NAV_OBJECT => $person,
                MIDCOM_META_CREATOR => $creator,
                MIDCOM_META_EDITOR => $revisor,
                MIDCOM_META_CREATED => $created,
                MIDCOM_META_EDITED => $revised,
            );
        }
        
        return $leaves;
    }
}

?>