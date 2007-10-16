<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package org.openpsa.products
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

        if ($this->up)
        {
            $qb->add_constraint('up', '=', $this->up);
        }

        $result = $qb->execute_unchecked();
        if (count($result) > 0)
        {
            return true;
        }
        return false;
    }
    
    /**
     *
     *
     * @access static public
     * @param mixed $up            Either the ID or GUID of the product group
     * @param string $prefix       Prefix for the code
     * @param string $keyproperty  
     */
    function list_groups($up = 0, $prefix = '', $keyproperty = 'id', $order_by_score = false)
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
        
        // Check for the request and change GUID to int if required
        if (mgd_is_guid($up))
        {
            $group = new org_openpsa_products_product_group_dba($up);
            
            // Error message on failure
            if (empty($group))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to get the configured product root group');
                // This will exit
            }
            
            $up = $group->id;
        }

        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $qb->add_constraint('up', '=', $up);
        
        // Order by score if required
        if ($order_by_score)
        {
            $qb->add_order('metadata.score', 'DESC');
        }
        
        $qb->add_order('code');
        $qb->add_order('title');
        $groups = $qb->execute();
        
        // Get the properties of each group
        foreach ($groups as $group)
        {
            $group_list[$keyproperty][$group->$keyproperty] = "{$prefix}{$group->code} {$group->title}";

            org_openpsa_products_product_group_dba::list_groups($group->id, "{$prefix} > ", $keyproperty);
        }

        return $group_list[$keyproperty];
    }
}
?>