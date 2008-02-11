<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.openpsa.products
 */
class org_openpsa_products_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_openpsa_products_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.openpsa.products';
        $this->_autoload_files = Array(
            'viewer.php',
            'navigation.php',
            'product.php',
            'product_group.php',
            'product_member.php',
            'businessarea.php',
            'businessarea_member.php',
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

    function _on_initialize()
    {
        // Define delivery types
        define('ORG_OPENPSA_PRODUCTS_DELIVERY_SINGLE', 1000);
        define('ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION', 2000);

        // Define product types
        // Professional services
        define('ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SERVICE', 1000);
        // Material goods
        define('ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_GOODS', 2000);
        // Solution is a nonmaterial good
        define('ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_SOLUTION', 2001);
        // Component that a product is based on, usually something
        // acquired from a supplier
        define('ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT', 3000);

        return true;
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $product = new org_openpsa_products_product_dba($guid);
        if ($product->guid)
        {
            if ($product->code)
            {
                return "product/{$product->code}/";
            }
            else
            {
                return "product/{$product->guid}/";
            }
        }

        $product_group = new org_openpsa_products_product_group_dba($guid);
        if ($product_group->guid)
        {
            if ($product_group->code)
            {
                return "{$product_group->code}/";
            }
            else
            {
                return "{$product_group->guid}/";
            }
        }

        return null;
    }
}
?>