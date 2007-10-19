<?php
/**
 * @package net.nehmer.static
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for objects
 *
 * @package net.nehmer.static
 */
class net_nehmer_static_link_dba extends __net_nehmer_static_link_dba
{
    /**
     * Connect to the parent class constructor and give a possibility for
     * fetching a record by ID or GUID
     * 
     * @access public
     * @param mixed $id
     */
    function net_nehmer_static_link_dba($id = null)
    {
        return parent::__net_nehmer_static_link_dba($id);
    }
    
    /**
     * Check if all the fields contain required information upon creation
     * 
     * @access public
     * @return boolean Indicating success
     */
    function _on_creating()
    {
        if (   !$this->topic
            || !$this->article)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Permission denied for creating the link, either $link->topic or $link->article was undefined');
            debug_pop();
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if all the fields contain required information upon creation
     * 
     * @access public
     * @return boolean Indicating success
     */
    function _on_updating()
    {
        if (   !$this->topic
            || !$this->article)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Permission denied for creating the link, either $link->topic or $link->article was undefined');
            debug_pop();
            
            return false;
        }
        
        return true;
    }
}
?>