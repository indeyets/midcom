<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 */
class org_openpsa_products_product_member_dba extends __org_openpsa_products_product_member_dba
{
    function org_openpsa_products_product_member_dba($id = null)
    {
        return parent::__org_openpsa_products_product_member_dba($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->product != 0)
        {
            $parent = new org_openpsa_products_product_dba($this->product);
            return $parent->guid;
        }
        else
        {
            return null;
        }
    }
}
?>