<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 4840 2006-12-29 06:25:07Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Helper class for fetching ordered memberships
 *
 * @package net.nemein.personnel
 */
class net_nemein_personnel_sorted_groups
{
    
    /**
     * Master group for listing
     * 
     * @access private
     * @var midcom_db_group
     */
    var $master_group = null;
    
    /**
     * List of groups
     * 
     * @access private
     * @var mixed Array containing midcom_db_group objects
     */
    var $groups = array();
    
    /**
     * Simple constructor
     * 
     * @param $guid GUID of the master group
     * @param $multilevel boolean switch to determine if descending groups are included
     */
    function net_nemein_personnel_sorted_groups($guid, $multilevel = false)
    {
        $this->master_group = new midcom_db_group($guid);
        
        if (!$multilevel)
        {
            $this->groups[] = &$this->master_group;
        }
        else
        {
            $this->groups = $this->_get_groups();
        }
    }
    
    function _get_groups()
    {
        // Temporary storage for sorting by score
        $temp = array ();
        
        $qb = midcom_db_group::new_query_builder();
        $qb->add_constraint('owner', '=', (int) $this->master_group->id);
        
        if (version_compare(mgd_version(), '1.8.2', '>='))
        {
            $qb->add_order('metadata.score', 'DESC');
            $groups = $qb->execute_unchecked();
            $groups['unsorted'] =& $this->master_group;
            
            return $groups;
        }
        
        foreach ($qb->execute_unchecked() as $group)
        {
            $temp[$group->guid] = $group->get_parameter('net.nemein.personnel', 'score');
        }
        
        arsort($temp);
        
        foreach ($temp as $guid => $score)
        {
             $groups[] = new midcom_db_group($guid);
        }
        
        $groups['unsorted'] =& $this->master_group;
        
        return $groups;
    }
    
    /**
     * Get sorted memberships, sort them by score and index by group and membership GUIDs
     * 
     * Returned array will be formed like this
     * 
     * array[<midcom_db_topic ID>][<midcom_db_member GUID>] = <midcom_db_person OBJECT>
     * 
     * @return mixed Array
     */
    function get_sorted_members()
    {
        $members = array();
        
        if (is_null($this->groups))
        {
            $this->_get_groups();
        }
        
        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid', '=', $this->master_group->id);
        if (version_compare(mgd_version(), '1.8.2', '>='))
        {
            $qb->add_order('metadata.score', 'DESC');
            $qb->add_order('uid.lastname');
            $qb->add_order('uid.firstname');
            
            $results = $qb->execute_unchecked();
        }
        else
        {
            $temp = array ();
            foreach ($qb->execute_unchecked() as $membership)
            {
                $temp[$membership->guid] = $membership->get_parameter('net.nemein.personnel', 'score');
            }
            
            arsort($temp);
            
            foreach ($temp as $guid => $score)
            {
                $results[] = new midcom_db_member($guid);
            }
        }
        
        // The simpliest solution when there is less than two found groups
        if (count($this->groups) < 2)
        {
            $members = array ();
            foreach ($results as $membership)
            {
                $members[$membership->gid][$membership->guid] = new midcom_db_person($membership->uid);
            }
            
            return $members;
        }
        
        // Check if the master group membership can be found somewhere else
        foreach ($results as $membership)
        {
            $qb = midcom_db_member::new_query_builder();
            $qb->add_constraint('uid', '=', $membership->uid);
            
            // Get the first membership from any of the found groups
            $qb->begin_group('OR');
            foreach ($this->groups as $key => $group)
            {
                if ($group->guid === $this->master_group->guid)
                {
                    continue;
                }
                
                $qb->add_constraint('gid', '=', $group->id);
                
                // Initialize the member array to the correct order on the first pass
                if (!isset($members[$group->id]))
                {
                    $members[$group->id] = array ();
                }
            }
            $qb->end_group();
            $qb->set_limit(1);
            
            // If no occurences were found, place under the master group
            if ($qb->count() === 0)
            {
                $members[$this->master_group->id][$membership->guid] = new midcom_db_person($membership->uid);;
                continue;
            }
            
            // Place the membership under the grouped
            $result = $qb->execute_unchecked();
            $members[$result[0]->gid][$result[0]->guid] = new midcom_db_person($result[0]->uid);
        }
        
        return $members;
    }
}
?>