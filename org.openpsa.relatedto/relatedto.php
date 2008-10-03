<?php
/**
 * @package org.openpsa.relatedto
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: relatedto.php,v 1.3 2006/05/12 16:51:29 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped base class, keep logic here
 *
 * @package org.openpsa.relatedto
 */
class org_openpsa_relatedto_relatedto_dba extends __org_openpsa_relatedto_relatedto_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function _on_creating()
    {
        if (!$this->status)
        {
            $this->status = ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED;
        }
        //PONDER: Should we call check_db() here and prevent creation of multiple very similar links ??
        return true;
    }

    function _on_loaded()
    {
        if (!$this->status)
        {
            $this->status = ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED;
        }
        return true;
    }

    function _on_updating()
    {
        if (!$this->status)
        {
            $this->status = ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED;
        }
        return true;
    }


    /**
     * Check database for essentially same relatedto object and returns id if found
     */
    function check_db($check_status = true)
    {
        $qb = new midgard_query_builder('org_openpsa_relatedto');
        $qb->add_constraint('fromClass', '=', $this->fromClass);
        $qb->add_constraint('toClass', '=', $this->toClass);
        $qb->add_constraint('fromGuid', '=', $this->fromGuid);
        $qb->add_constraint('toGuid', '=', $this->toGuid);
        $qb->add_constraint('fromComponent', '=', $this->fromComponent);
        $qb->add_constraint('toComponent', '=', $this->toComponent);
        if ($check_status)
        {
            $qb->add_constraint('status', '=', $this->status);
        }
        $ret = @$qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            return $ret[0]->id;
        }

        return false;
    }

    /**
     * By default all authenticated users should be able to do
     * whatever they wish with relatedto objects, later we can add
     * restrictions on object level as necessary.
     */
    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        $privileges['USERS']['midgard:create']  = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:update']  = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:read']    = MIDCOM_PRIVILEGE_ALLOW;
        return $privileges;
    }
}

?>