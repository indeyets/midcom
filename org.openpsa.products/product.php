<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class org_openpsa_products_product_dba extends __org_openpsa_products_product_dba
{
    function org_openpsa_products_product_dba($id = null)
    {
        return parent::__org_openpsa_products_product_dba($id);
    }
    
    function get_parent_guid_uncached()
    {
        return null;
    }
    
    function list_products($list_components = false)
    {
        $component_list = Array();
        $qb = org_openpsa_products_product_dba::new_query_builder();
        $qb->add_order('productGroup');
        $qb->add_order('code');
        $qb->add_order('title');
        $qb->add_constraint('start', '<=', time());
        $qb->begin_group('OR');
            /*
             * List products that either have no defined end-of-market dates
             * or are still in market
             */
            $qb->add_constraint('end', '=', 0);
            $qb->add_constraint('end', '>=', time());
        $qb->end_group();
        
        if (!$list_components)
        {
            $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT);
        }
        
        $components = $qb->execute();
        foreach ($components as $component)
        {
            $component_list[$component->id] = "{$component->code} {$component->title}";
        }
        return $component_list;
    }
}
?>