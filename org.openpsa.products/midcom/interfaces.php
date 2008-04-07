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
            'midcom.helper.datamanager2',
            'org.openpsa.qbpager',
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
        
        define('ORG_OPENPSA_PRODUCTS_PRODUCT_GROUP_TYPE_SMART', 1000);

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

    /**
     * Iterate over all articles and create index record using the datamanager indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !$config->get('index_products')
            && !$config->get('index_groups'))
        {
            debug_add("No indexing to groups and products, skipping", MIDCOM_LOG_WARN);
            debug_pop();
            return true;
        }
        $dms = array();
        $schemadb_group = midcom_helper_datamanager2_schema::load_database($config->get('schemadb_group'));
        $dms['group'] = new midcom_helper_datamanager2_datamanager($schemadb_group);
        if (!is_a($dms['group'], 'midcom_helper_datamanager2_datamanager'))
        {
            debug_add("Failed to instance DM2 from schema path " . $config->get('schemadb_group') . ", aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $schemadb_product = midcom_helper_datamanager2_schema::load_database($config->get('schemadb_product'));
        $dms['product'] = new midcom_helper_datamanager2_datamanager($schemadb_product);
        if (!is_a($dms['product'], 'midcom_helper_datamanager2_datamanager'))
        {
            debug_add("Failed to instance DM2 from schema path " . $config->get('schemadb_product') . ", aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $topic_root_group_guid = $topic->get_parameter('org.openpsa.products','root_group');
        if (!mgd_is_guid($topic_root_group_guid))
        {
            $qb->add_constraint('up', '=', 0);
        }
        else
        {
            $root_group = new org_openpsa_products_product_group_dba($topic_root_group_guid);
            $qb->add_constraint('id', '=', $root_group->id);
        }
        $root_groups = $qb->execute();
        foreach ($root_groups as $group)
        {
            $this->_on_reindex_tree_iterator($indexer, $dms, $topic, $group);
        }

        debug_pop();
        return true;
    }

    function _on_reindex_tree_iterator(&$indexer, &$dms, &$topic, &$group)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($dms['group']->autoset_storage($group))
        {
            if ($config->get('index_groups')
            {
                org_openpsa_products_viewer::index($dms['group'], $indexer, $topic);
            }
        }
        else
        {
            debug_add("Warning, failed to initialize datamanager for product group {$group->id}. Skipping it.", MIDCOM_LOG_WARN);
        }

        if ($config->get('index_products')
        {
            $qb_products = org_openpsa_products_product_dba::new_query_builder();
            $qb_products->add_constraint('productGroup', '=', $group->id);
            $products = $qb_products->execute();
            unset($qb_products);
            if (is_array($products))
            {
                foreach ($products as $product)
                {
                    if (!$dms['product']->autoset_storage($product))
                    {
                        debug_add("Warning, failed to initialize datamanager for product {$product->id}. Skipping it.", MIDCOM_LOG_WARN);
                        continue;
                    }
                    org_openpsa_products_viewer::index($dms['product'], $indexer, $topic);
                    unset($product);
                }
            }
            unset($products);
        }

        $subgroups = array();
        $qb_groups = org_openpsa_products_product_group_dba::new_query_builder();
        $qb_groups->add_constraint('up', '=', $group->id);
        $subgroups = $qb_groups->execute();
        unset($qb_groups);
        if (!is_array($subgroups))
        {
            debug_pop();
            return true;
        }
        foreach ($subgroups as $subgroup)
        {
            $this->_on_reindex_tree_iterator($indexer, $dms, $topic, $subgroup);
            unset($subgroup);
        }
        unset($subgroups);

        debug_pop();
        return true;
    }
}
?>