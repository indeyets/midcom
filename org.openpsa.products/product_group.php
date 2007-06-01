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
        if ($this->up != 0)
        {
            $parent = new org_openpsa_products_product_group_dba($this->up);
            return $parent->guid;
        }
        else
        {
            return null;
        }
    }

    function _on_creating()
    {
        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {
            if ($this->_check_duplicates($this->code))
            {
                mgd_set_errno(MGD_ERR_OBJECT_NAME_EXISTS);
                return false;
            }
        }
        return true;
    }

    function _on_updating()
    {
        if (version_compare(mgd_version(), '1.8.0alpha1', '>'))
        {    
            if ($this->_check_duplicates($this->code))
            {
                mgd_set_errno(MGD_ERR_OBJECT_NAME_EXISTS);
                return false;
            }
        }
        return true;
    }

    function _check_duplicates($code)
    {
        if (!$code)
        {
            return false;
        }

        // Check for duplicates
        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $qb->add_constraint('code', '=', $code);

        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }

        $result = $qb->execute_unchecked();
        if (count($result) > 0)
        {
            return true;
        }
        return false;
    }

    function list_groups($up = 0, $prefix = '', $keyproperty = 'id')
    {
        static $group_list = array();
        
        if (!array_key_exists($keyproperty, $group_list))
        {
            $group_list[$keyproperty] = array();
        }

        if (count($group_list[$keyproperty]) == 0)
        {
            if ($keyproperty == 'id')
            {
                $group_list[$keyproperty][0] = $_MIDCOM->i18n->get_string('toplevel', 'org.openpsa.products');
            }
            else
            {
                $group_list[$keyproperty][''] = $_MIDCOM->i18n->get_string('toplevel', 'org.openpsa.products');
            }
        }

        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $qb->add_constraint('up', '=', $up);
        $qb->add_order('code');
        $qb->add_order('title');
        $groups = $qb->execute();

        foreach ($groups as $group)
        {
            $group_list[$keyproperty][$group->$keyproperty] = "{$prefix}{$group->code} {$group->title}";

            org_openpsa_products_product_group_dba::list_groups($group->id, "{$prefix} > ", $keyproperty);
        }

        return $group_list[$keyproperty];
    }
}
?>