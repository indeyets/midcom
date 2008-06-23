<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for objects
 *
 * @package net.nehmer.blog
 */
class net_nehmer_blog_link_dba extends __net_nehmer_blog_link_dba
{
    /**
     * Connect to the parent class constructor and give a possibility for
     * fetching a record by ID or GUID
     * 
     * @access public
     * @param mixed $id
     */
    function __construct($id = null)
    {
        return parent::__construct($id);
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
            debug_add('Cannot create the link, either $link->topic or $link->article is empty', MIDCOM_LOG_WARN);
            debug_pop();
            mgd_set_errno(MGD_ERR_NOT_EXISTS);
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
            debug_add('Cannot update the link, either $link->topic or $link->article is empty', MIDCOM_LOG_WARN);
            debug_pop();
            mgd_set_errno(MGD_ERR_NOT_EXISTS);
            return false;
        }
        
        return true;
    }
}
?>