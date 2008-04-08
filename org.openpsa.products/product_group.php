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
    function __construct($id = null)
    {
        return parent::__construct($id);
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
     * Helper to make an array usable with DM2 select datatype for selecting product groups
     *
     * @access public
     * @static
     * @param mixed $up            Either the ID or GUID of the product group
     * @param string $prefix       Prefix for the code
     * @param string $keyproperty  Property to use as the key of the resulting array
     * @param array $label_fields  Object properties to show in the label (will be shown space separated)
     * @return array
     */
    function list_groups($up = 0, $prefix = '', $keyproperty = 'id', $order_by_score = false, $label_fields = array('code', 'title'))
    {
        static $result_cache = array();
        $cache_key = md5($up . $keyproperty . $prefix . $order_by_score . implode('', $label_fields));
        if (isset($result_cache[$cache_key]))
        {
            return $result_cache[$cache_key]; 
        }

        $result_cache[$cache_key] = array();
        $ret =& $result_cache[$cache_key];

        if (empty($up))
        {
            // TODO: use reflection to see what kind of property this is ?
            if ($keyproperty == 'id')
            {
                $ret[0] = $_MIDCOM->i18n->get_string('toplevel', 'org.openpsa.products');
            }
            else
            {
                $ret[''] = $_MIDCOM->i18n->get_string('toplevel', 'org.openpsa.products');
            }
        }
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

        $mc = org_openpsa_products_product_group_dba::new_collector('up', (int)$up);
        
        $mc->add_value_property('title');
        $mc->add_value_property('code');
        $mc->add_value_property('id');
        if ($keyproperty !== 'id')
        {
            $mc->add_value_property($keyproperty);
        }
        foreach($label_fields as $fieldname)
        {
            if (   $fieldname == 'id'
                || $fieldname == $keyproperty)
            {
                continue;
            }
            $mc->add_value_property($fieldname);
        }
        unset($fieldname);

        // Order by score if required
        if ($order_by_score)
        {
            $mc->add_order('metadata.score', 'DESC');
        }
        $mc->add_order('code');
        $mc->add_order('title');
        $mc->execute();
        $mc_keys = $mc->list_keys();
        foreach($mc_keys as $mc_key => $dummy)
        {
            $id = $mc->get_subkey($mc_key, 'id');
            $key = $mc->get_subkey($mc_key, $keyproperty);
            //$ret[$key] = "{$prefix}{$code} {$title}";
            $ret[$key] = $prefix;
            foreach ($label_fields as $fieldname)
            {
                $field_val = $mc->get_subkey($mc_key, $fieldname);
                $ret[$key] .= "{$field_val} ";
            }
            unset($fieldname, $field_val);
            $ret = $ret + org_openpsa_products_product_group_dba::list_groups($id, "{$prefix} > ", $keyproperty, $label_fields);
            unset($id, $key);
        }
        unset($mc, $mc_keys, $dummy, $mc_key);

        return $ret;
    }

    function list_groups_by_up($up = 0)
    {
        //FIXME rewrite to use collector, rewrite to use (per request) result caching
        static $group_list = array();
        $up_code = 0 ;
        $create_or_edit = -1;

        //FIXME: Maemo specific hack

        if ($_MIDGARD["admin"])
        {
            return org_openpsa_products_product_group_dba::list_groups(0, " > ", 'id');
        }

        // very ugly, but is needed to work around a midcom argv bug which casts the guid to int.
        global $midgard;

        if (isset($midgard->argv[3])
            && isset($midgard->argv[2])
            && (($midgard->argv[2] == "create") || ($midgard->argv[1] == "create")))
        {
            if ($midgard->argv[1] == "create")
            {
                $up_code=$midgard->argv[2];
            }
            else
            {
                $up_code=$midgard->argv[3];
            }
            $create_or_edit = 1;
        }

        if (isset($midgard->argv[2])
            && isset($midgard->argv[1])
            && (($midgard->argv[2] == "edit") || ($midgard->argv[1] == "edit")))
        {

            if ($midgard->argv[1] == "edit")
            {
                $guid=$midgard->argv[2];
            }
            else
            {
                $guid=$midgard->argv[3];
            }

            $qb3 = org_openpsa_products_product_dba::new_query_builder();
            $qb3->add_constraint('guid', '=', $guid);
            $lookup = $qb3->execute_unchecked();

            if (isset($lookup[0])
                && isset($lookup[0]->productGroup))
            {
                $up_code = $lookup[0]->productGroup;
            }
            $create_or_edit = 1;
        }

        if ($create_or_edit == -1)
        {
            $group_list=array();
            $group_list[0] = " ";
            if ($up_code == 0) return $group_list;
        }

        if ((int)$up_code > 0)
        {
            $qb2 = org_openpsa_products_product_group_dba::new_query_builder();
            $qb2->add_constraint('id', '=', $up_code);
            $qb2->add_order('id');
            $up_group = $qb2->execute_unchecked();
            if (isset($up_group[0])
                && isset($up_group[0]->up))
            {
                $up = $up_group[0]->up;
            }
        }
        else
        {
            $qb2 = org_openpsa_products_product_group_dba::new_query_builder();
            $qb2->add_constraint('code', '=', $up_code);
            $qb2->add_order('code');
            $up_group = $qb2->execute_unchecked();
            if (isset($up_group[0])
                && isset($up_group[0]->id))
            {
                $up = $up_group[0]->id;
            }
        }


        // END Maemo specific hack

        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $qb->add_constraint('up', '=', $up);
        $qb->add_order('code');
        $qb->add_order('title');
        $groups = $qb->execute_unchecked();

        foreach ($groups as $group)
        {
            $group_list['id'][$group->id] = "{$group->title}";
        }

        return $group_list['id'];
    }

    function list_groups_parent($up = 0)
    {
        //FIXME rewrite to use collector, rewrite to use (per request) result caching
        static $group_list = array();
        $up_code = 0;
        $create_or_edit = -1;

        //FIXME: Maemo specific hack
        if (isset($_MIDGARD["argv"][3])
            && isset($_MIDGARD["argv"][2])
            && (($_MIDGARD["argv"][2] == "create") || ($_MIDGARD["argv"][1] == "create")))
        {
            if ($_MIDGARD["argv"][1] == "create")
            {
                $up_code=$_MIDGARD["argv"][2];
            }
            else
            {
                $up_code=$_MIDGARD["argv"][3];
            }
            $create_or_edit = 1;
        }
        // very ugly, but is needed to work around a midcom argv bug which casts the guid to int.
        global $midgard;

        if (isset($midgard->argv[2])
            && isset($midgard->argv[1])
            && (($midgard->argv[2] == "edit") || ($midgard->argv[1] == "edit")))
        {

            if ($midgard->argv[1] == "edit")
            {
                $guid=$midgard->argv[2];
            }
            else
            {
                $guid=$midgard->argv[3];
            }
            $qb3 = org_openpsa_products_product_dba::new_query_builder();
            $qb3->add_constraint('guid', '=', $guid);
            $lookup = $qb3->execute_unchecked();

            if (isset($lookup[0])
                && isset($lookup[0]->productGroup))
            {
                $up_code = $lookup[0]->productGroup;
            }
            $create_or_edit = 1;
        }

        if ($create_or_edit == -1)
        {
            $group_list=array();
            $group_list[0] = " ";
            if ($up_code == 0) return $group_list;
        }

        if ((int)$up_code > 0)
        {
            $qb2 = org_openpsa_products_product_group_dba::new_query_builder();
            $qb2->add_constraint('id', '=', $up_code);
            $qb2->add_order('id');
            $up_group = $qb2->execute_unchecked();
            if (isset($up_group[0])
                && isset($up_group[0]->up))
            {
                $up = $up_group[0]->up;
            }
        }
        else
        {
            $qb2 = org_openpsa_products_product_group_dba::new_query_builder();
            $qb2->add_constraint('code', '=', $up_code);
            $qb2->add_order('code');
            $up_group = $qb2->execute_unchecked();
            if (isset($up_group[0])
                && isset($up_group[0]->id))
            {
                $up = $up_group[0]->id;
            }
        }

        if ($_MIDGARD['admin'])
        {
            $up = 0;
        }
        // END Maemo specific hack

        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        if ($_MIDGARD['admin']) {
            $qb->add_constraint('up', '=', $up);
        }
        else
        {
            $qb->add_constraint('id', '=', $up);
        }
        $qb->add_order('code');
        $qb->add_order('title');
        $groups = $qb->execute_unchecked();

        foreach ($groups as $group)
        {
            $group_list[$group->code] = "{$group->title}";

            //org_openpsa_products_product_group_dba::list_groups($group->id, "{$prefix} > ", $keyproperty);
        }

        return $group_list;
    }

}
?>