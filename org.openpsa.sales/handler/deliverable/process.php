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
class org_openpsa_sales_handler_deliverable_process extends midcom_baseclasses_components_handler
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
    function org_openpsa_sales_handler_deliverable_process()
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
    }


    /**
     * Looks up an deliverable to display.
     */
    function _handler_process($handler_id, $args, &$data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
        }

        $this->_deliverable = new org_openpsa_sales_salesproject_deliverable($args[0]);
        if (!$this->_deliverable)
        {
            return false;
        }

        $this->_salesproject = new org_openpsa_sales_salesproject($this->_deliverable->salesproject);
        if (!$this->_salesproject)
        {
            return false;
        }

        $this->_product = new org_openpsa_products_product_dba($this->_deliverable->product);
        if (!$this->_product)
        {
            return false;
        }

        // Check what status change user requested
        if (array_key_exists('mark_proposed', $_POST))
        {
            if (!$this->_deliverable->propose())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Failed to mark the deliverable as proposed, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }
        }
        elseif (array_key_exists('mark_declined', $_POST))
        {
            if (!$this->_deliverable->decline())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Failed to mark the deliverable as declined, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }
        }
        elseif (array_key_exists('mark_ordered', $_POST))
        {
            if (!$this->_deliverable->order())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Failed to mark the deliverable as ordered, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }
        }
        elseif (array_key_exists('mark_delivered', $_POST))
        {
            if (!$this->_deliverable->deliver())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Failed to mark the deliverable as delivered, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }
        }
        elseif (   array_key_exists('mark_invoiced', $_POST)
                && array_key_exists('invoice', $_POST))
        {
            if (!$this->_deliverable->invoice($_POST['invoice']))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Failed to mark the deliverable as invoiced, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'No procedure specified, aborting.');
        }

        // Get user back to the sales project
        $_MIDCOM->relocate("salesproject/{$this->_salesproject->guid}/");
        // This will exit.
    }
}
?>