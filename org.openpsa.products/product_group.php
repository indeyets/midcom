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
     *
     *
     * @access public
     * @static
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

    function list_groups_by_up($up = 0)
    {
        static $group_list = array();
		$up_code = 0 ;

		//FIXME: Maemo specific hack
		if (isset($_MIDGARD["argv"][3])
			&& isset($_MIDGARD["argv"][2])
			&& ($_MIDGARD["argv"][2] == "create"))
		{
			$up_code=$_MIDGARD["argv"][3];
		}
		else
		{
			$session = new midcom_service_session();
			if ($session->exists("target_os"))
			{
				$up_code = str_replace("IT OS ","OS",$session->get("target_os"));
			}
		}

		if ((int)$up_code > 0)
		{
			$qb2 = org_openpsa_products_product_group_dba::new_query_builder();
			$qb2->add_constraint('id', '=', $up_code);
        	$qb2->add_order('id');
        	$up_group = $qb2->execute();
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
        	$up_group = $qb2->execute();
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
        $groups = $qb->execute();

        foreach ($groups as $group)
        {
            $group_list['id'][$group->id] = "{$group->code} {$group->title}";

            //org_openpsa_products_product_group_dba::list_groups($group->id, "{$prefix} > ", $keyproperty);
        }

        return $group_list['id'];
    }

    function list_groups_parent($up = 0)
    {
        static $group_list = array();
		$up_code = 0;

		//FIXME: Maemo specific hack
		if (isset($_MIDGARD["argv"][3])
			&& isset($_MIDGARD["argv"][2])
			&& ($_MIDGARD["argv"][2] == "create"))
		{
			$up_code=$_MIDGARD["argv"][3];
		}
		else
		{
			$session = new midcom_service_session();
			if ($session->exists("target_os"))
			{
				$up_code = str_replace("IT OS ","OS",$session->get("target_os"));
			}
		}

		if ((int)$up_code > 0)
		{
			$qb2 = org_openpsa_products_product_group_dba::new_query_builder();
			$qb2->add_constraint('id', '=', $up_code);
        	$qb2->add_order('id');
        	$up_group = $qb2->execute();
			if (isset($up_group[0])
				&& isset($up_group[0]->up))
			{
				$up = $up_group[0]->up;
			}
		}
		else if ($_MIDGARD['admin'])
		{
			$up = 0;
		}
		else
		{
			$qb2 = org_openpsa_products_product_group_dba::new_query_builder();
			$qb2->add_constraint('code', '=', $up_code);
        	$qb2->add_order('code');
        	$up_group = $qb2->execute();
			if (isset($up_group[0])
				&& isset($up_group[0]->id))
			{
				$up = $up_group[0]->id;
			}
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
        $groups = $qb->execute();

        foreach ($groups as $group)
        {
            $group_list[$group->code] = "{$group->title}";

            //org_openpsa_products_product_group_dba::list_groups($group->id, "{$prefix} > ", $keyproperty);
        }

        return $group_list;
    }

}
?>