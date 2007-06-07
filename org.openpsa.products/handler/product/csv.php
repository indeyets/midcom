<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: products.php 3991 2006-09-07 11:28:16Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
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
    
    function _load_schemadb()
    {
        return $this->_request_data['schemadb_product'];
    }

    function _load_data($handler_id)
    {   
        $qb = org_openpsa_products_product_dba::new_query_builder();
                
        $qb->add_order('code');
        $qb->add_order('title');
        
        $products = $qb->execute();
        
        return $products;
    }
}
?>