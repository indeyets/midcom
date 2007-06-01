<?php
/**
 * MidCOM wrapped class for access to stored queries
 */

class org_openpsa_products_businessarea_member_dba extends __org_openpsa_products_businessarea_member_dba
{
    function org_openpsa_products_businessarea_member_dba($id = null)
    {
        return parent::__org_openpsa_products_businessarea_member_dba($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->businessarea != 0)
        {
            $parent = new org_openpsa_products_businessarea_dba($this->businessarea);
            return $parent->guid;
        }
        else
        {
            return null;
        }
    }
}
?>