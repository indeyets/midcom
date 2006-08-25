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
    
    function list_groups($up = 0, $prefix = '')
    {
        static $group_list = array();
        
        if (count($group_list) == 0)
        {
            $group_list[0] = $_MIDCOM->i18n->get_string('toplevel', 'org.openpsa.products');
        }   
        
        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $qb->add_constraint('up', '=', $up);
        $qb->add_order('code');
        $qb->add_order('title');
        $groups = $qb->execute();
        
        foreach ($groups as $group)
        {
            $group_list[$group->id] = "{$prefix}{$group->code} {$group->title}";
            org_openpsa_products_product_group_dba::list_groups($group->id, "{$prefix} > ");
        }
        
        return $group_list;
    }
}
?>