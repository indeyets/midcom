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
        $deliverable->supplier = $product->supplier;

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

        // Copy tags from product
        $tagger = new net_nemein_tag_handler();
        $tagger->copy_tags($product, $deliverable);

        return $deliverable;
    }

    /**
     * Looks up a deliverable to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
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

        // Check if the product has components
        $component_qb = org_openpsa_products_product_member_dba::new_query_builder();
        $component_qb->add_constraint('product', '=', $this->_product->id);
        if ($component_qb->count() == 0)
        {
            // All conditions are fine, create the deliverable
            $this->_deliverable = $this->_create_deliverable($this->_product);

            if ($this->_deliverable)
            {
                // Go to deliverable view
                $_MIDCOM->relocate("deliverable/{$this->_deliverable->guid}/");
                // This will exit.
            }
            else
            {
                // Get user back to the sales project
                // TODO: Add UImessage on why this failed
                $_MIDCOM->relocate("salesproject/{$this->_salesproject->guid}/");
                // This will exit.
            }
        }

        // Otherwise we present checkbox list of components to select
        if (   array_key_exists('components', $_POST)
            && is_array($_POST['components']))
        {
            // All conditions are fine, create the deliverable
            $this->_deliverable = $this->_create_deliverable($this->_product);

            if ($this->_deliverable)
            {
                // Add per selection
                foreach ($_POST['components'] as $product_id => $values)
                {
                    if (   array_key_exists('add', $values)
                        && $values['add'] == 1)
                    {
                        $product = new org_openpsa_products_product_dba($product_id);
                        $this->_create_deliverable($product, $this->_deliverable->id, $values['pieces']);
                    }
                }

                // Go to deliverable view
                $_MIDCOM->relocate("deliverable/{$this->_deliverable->guid}/");
                // This will exit.
            }
            else
            {
                // Get user back to the sales project
                // TODO: Add UImessage on why this failed
                $_MIDCOM->relocate("salesproject/{$this->_salesproject->guid}/");
                // This will exit.
            }
        }

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "salesproject/{$this->_salesproject->guid}.html",
            MIDCOM_NAV_NAME => $this->_salesproject->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "deliverable/add/{$this->_salesproject->guid}.html",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('add products to %s'), $this->_salesproject->title),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    function _show_add($handler_id, &$data)
    {
        $data['product']  = $this->_product;
        $data['salesproject'] = $this->_salesproject;
        midcom_show_style('show-deliverable-add');
    }
}
?>