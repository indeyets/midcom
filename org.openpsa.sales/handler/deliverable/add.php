<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Product display class
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_handler_deliverable_add extends midcom_baseclasses_components_handler
{
    /**
     * The deliverable to display
     *
     * @var org_openpsa_sales_salesproject_deliverable
     * @access private
     */
    var $_deliverable = null;
    
    /**
     * The salesproject the deliverable is connected to
     *
     * @var org_openpsa_sales_salesproject_deliverable
     * @access private
     */
    var $_salesproject = null;

    /**
     * The product to deliver
     *
     * @var org_openpsa_products_product
     * @access private
     */
    var $_product = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_sales_handler_deliverable_add()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['deliverable'] =& $deliverable;
        $this->_request_data['enable_components'] = $this->_config->get('enable_components');
    }
    
    function _create_deliverable($product, $up = 0, $units = 1)
    {
        $deliverable = new org_openpsa_sales_salesproject_deliverable();
        $deliverable->product = $product->id;
        $deliverable->salesproject = $this->_salesproject->id;
        $deliverable->up = $up;
        
        // Copy values from product
        $deliverable->units = $units;
        $deliverable->unit = $product->unit;
        $deliverable->pricePerUnit = $product->price;
        $deliverable->costPerUnit = $product->cost;
        $deliverable->costType = $product->costType;
        $deliverable->title = $product->title;
        $deliverable->description = $product->description;
        
        $deliverable->state = ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_NEW;
        
        $deliverable->orgOpenpsaObtype = $product->delivery;
        
        if (!$deliverable->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $deliverable);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new deliverable, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        
        // Set schema based on product type
        if ($product->delivery == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
        {
            $deliverable->parameter('midcom.helper.datamanager2', 'schema_name', 'subscription');
        }
        
        // Handle product components
        $component_qb = org_openpsa_products_product_member_dba::new_query_builder();
        $component_qb->add_constraint('product', '=', $product->id);
        $components = $component_qb->execute();
        foreach ($components as $component)
        {
            $component_product = new org_openpsa_products_product_dba($component->component);
            if ($component_product)
            {
                // Create a sub-deliverable for each
                $this->_create_deliverable($component_product, $deliverable->id, $component->pieces);
            }
        }
        
        return $deliverable;
    }

    /**
     * Looks up an deliverable to display.
     */
    function _handler_add($handler_id, $args, &$data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
        }
        
        $this->_salesproject = new org_openpsa_sales_salesproject($args[0]);
        if (!$this->_salesproject)
        {
            return false;
        }
        $this->_salesproject->require_do('midgard:create');
        
        if (!array_key_exists('product', $_POST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'No product specified, aborting.');
        }
        
        $this->_product = new org_openpsa_products_product_dba($_POST['product']);
        if (!$this->_product)
        {
            return false;
        }
        
        // All conditions are fine, create the deliverable
        $this->_deliverable = $this->_create_deliverable($this->_product);
        
        // Get user back to the sales project
        $_MIDCOM->relocate("salesproject/{$this->_salesproject->guid}/");
        // This will exit.

        return true;
    }
}
?>