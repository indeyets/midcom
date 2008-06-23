<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: products.php 3991 2006-09-07 11:28:16Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT.'/midcom/baseclasses/components/handler/dataexport.php');
/**
 * @package org.openpsa.products
 */
class org_openpsa_products_handler_product_csv extends midcom_baseclasses_components_handler_dataexport
{
    function org_openpsa_products_handler_product_csv()
    {
        parent::midcom_baseclasses_components_handler_dataexport();
    }

    function _load_schemadb($handler_id, &$args, &$data)
    {
        $_MIDCOM->skip_page_style = true;
        if(isset($args[0]))
        {
            $data['schemadb_to_use'] = str_replace('.csv', '', $args[0]);
            $data['filename'] = $data['schemadb_to_use'] . '_' . date('Y-m-d') . '.csv';
        }
        elseif(    isset($_POST)
                && array_key_exists('org_openpsa_products_export_schema', $_POST))
        {
            //We do not have filename in URL, generate one and redirect
            $schemaname = $_POST['org_openpsa_products_export_schema'];
            if(strpos($_MIDGARD['uri'], '/', strlen($_MIDGARD['uri'])-2))
            {
                $_MIDCOM->relocate("{$_MIDGARD['uri']}{$schemaname}");
            }
            else
            {
                $_MIDCOM->relocate("{$_MIDGARD['uri']}/{$schemaname}");
            }
            // This will exit
        }
        else
        {
            $this->_request_data['schemadb_to_use'] = $this->_config->get('csv_export_schema');
        }

        $this->_schema = $this->_config->get('csv_export_schema');

        if (isset($this->_request_data['schemadb_product'][$this->_request_data['schemadb_to_use']]))
        {
            $this->_schema = $this->_request_data['schemadb_to_use'];
        }

        return $this->_request_data['schemadb_product'];
    }

    function _load_data($handler_id, &$args, &$data)
    {
        $qb = org_openpsa_products_product_dba::new_query_builder();

        $qb->add_order('code');
        $qb->add_order('title');
        
        $products = array();
        
        $root_group_guid = $this->_config->get('root_group');
        if ($root_group_guid)
        {
            $root_group = new org_openpsa_products_product_group_dba($root_group_guid);
            $qb->add_constraint('productGroup', 'INTREE', $root_group->id);
        }

        $all_products = $qb->execute();
        foreach ($all_products as $product)
        {
            $schema = $product->get_parameter('midcom.helper.datamanager2', 'schema_name');
            if ($schema != $this->_schema)
            {
                continue;
            }
            
            $products[] = $product;
        }

        return $products;
    }
}
?>