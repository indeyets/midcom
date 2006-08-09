<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class org_openpsa_products_product_group_dba extends __org_openpsa_products_product_group_dba
{
    function org_openpsa_products_product_group_dba($id = null)
    {
        return parent::__org_openpsa_products_product_group_dba($id);
    }
    
    function get_parent_guid_uncached()
    {
        return null;
    }
}
?>