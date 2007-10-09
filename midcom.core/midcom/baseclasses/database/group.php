<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:group.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Group record with framework support.
 * 
 * Note, as with all MidCOM DB layer objects, you should not use the GetBy*
 * operations directly, instead, you have to use the constructor's $id parameter.
 * 
 * Also, all QueryBuilder operations need to be done by the factory class 
 * obtainable as midcom_application::dbfactory.
 * 
 * This class uses a auto-generated base class provided by midcom_services_dbclassloader.
 * 
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_group extends __midcom_baseclasses_database_group
{
    function midcom_baseclasses_database_group($id = null)
    {
        parent::__midcom_baseclasses_database_group($id);
    }

    /**
     * Updates all computed members.
     *
     * @access protected
     */
    function _on_loaded()
    {
        if (empty($this->official))
        {
            $this->official = $this->name;
        }
        
        if (empty($this->official))
        {
            $this->official = "Group #{$this->id}";
        }
        return true;
    }

    /**
     * Gets the parent object of the current one. 
     * 
     * Groups that have an owner group return the owner group as a parent.
     * 
     * @return midcom_baseclasses_database_group Owner group or null if there is none.
     */
    function get_parent_guid_uncached()
    {
        if ($this->owner == 0)
        {
            return null;
        }
        
        $parent = new midcom_baseclasses_database_group($this->owner);
        if (! $parent)
        {
        	debug_push_class(__CLASS__, __FUNCTION__);
        	debug_add("Could not load Group ID {$this->owner} from the database, aborting.", 
                MIDCOM_LOG_INFO);
        	debug_pop();
            return null;
        }
        
        return $parent->guid;
    }
}

?>