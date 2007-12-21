<?php
/**
 * @package org.openpsa.queries
 */
/**
 * MidCOM wrapped class for access to stored queries
 */
class midcom_org_openpsa_query extends __midcom_org_openpsa_query
{
    function midcom_org_openpsa_query($id = null)
    {
        return parent::__midcom_org_openpsa_query($id);
    }

    /**
     * By default all authenticated users should be able to do
     * whatever they wish with query objects, later we can add
     * restrictions on object level as necessary.
     */
    function get_class_magic_default_privileges()
    {
        $privileges = parent::get_class_magic_default_privileges();
        /* The owner below should cover for these
        $privileges['USERS']['midgard:update']      = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:parameters']  = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:attachments'] = MIDCOM_PRIVILEGE_ALLOW;
        */
        $privileges['USERS']['midgard:owner']       = MIDCOM_PRIVILEGE_ALLOW;
        // Just to be sure
        $privileges['USERS']['midgard:read']        = MIDCOM_PRIVILEGE_ALLOW;
        $privileges['USERS']['midgard:create']      = MIDCOM_PRIVILEGE_ALLOW;
        return $privileges;
    }

    /**
     * Autopurge after delete
     */
    function _on_deleted()
    {
        if (!method_exists($this, 'purge'))
        {
            return;
        }
        $this->purge();
    }
}

class org_openpsa_queries_query extends midcom_org_openpsa_query
{
    function org_openpsa_queries_query($id = null)
    {
        return parent::midcom_org_openpsa_query($id);
    }
}

?>