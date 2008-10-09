<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:member.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Membership record with framework support.
 *
 * Note, as with all MidCOM DB layer objects, you should not use the GetBy*
 * operations directly, instead, you have to use the constructor's $id parameter.
 *
 * Also, all QueryBuilder operations need to be done by the factory class
 * obtainable as midcom_application::dbfactory.
 *
 * This class uses an auto-generated base class provided by midcom_services_dbclassloader.
 *
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_member extends __midcom_baseclasses_database_member
{
    function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * Returns the group the membership record is associated with. This allows group
     * owners to manage their members.
     *
     * @return midcom_baseclasses_database_group The owning group or null if the gid is undefined.
     */
    function get_parent_guid_uncached()
    {
        if ($this->gid)
        {
            $parent = new midcom_baseclasses_database_group($this->gid);
            if (! $parent)
            {
                debug_add("Could not load Group ID {$this->gid} from the database, aborting.",
                    MIDCOM_LOG_INFO);
                debug_pop();
                return null;
            }
            return $parent->guid;
        }
        else
        {
            return null;
        }
    }

    /**
     * Invalidate person's cache when a member record changes
     */
    function _invalidate_person_cache()
    {
        if (!$this->uid)
        {
            return;
        }
        $person = new midcom_db_person();
        $person->get_by_id($this->uid);
        if (!$person->guid)
        {
            return;
        }
        $_MIDCOM->cache->invalidate($person->guid);
    }

    function _on_created()
    {
        $this->_invalidate_person_cache();
    }

    function _on_updated()
    {
        $this->_invalidate_person_cache();
    }

    function _on_deleted()
    {
        $this->_invalidate_person_cache();

        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("gid={$this->gid}, sitegroup={$this->sitegroup}");
        // Workaround http://trac.midgard-project.org/ticket/206
        if ($this->gid == 0)
        {
            debug_add("gid:{$this->gid} == 0, purging membership #{$this->id}", MIDCOM_LOG_INFO);
            $this->purge();
        }
        if ($this->sitegroup != 0)
        {
            $sg = mgd_get_sitegroup($this->sitegroup);
            debug_add("sg #{$sg->id} admingroup={$sg->admingroup}");
            if ($this->gid == $sg->admingroup)
            {
                debug_add("gid:{$this->gid} == admingroup:{$sg->admingroup}, purging membership #{$this->id}", MIDCOM_LOG_INFO);
                $this->purge();
            }
        }
        debug_pop();
    }
}

?>