<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Product database create product handler
 *
 * @package org.openpsa.products
 */
class org_openpsa_products_handler_product_create extends midcom_baseclasses_components_handler
{
    /**
     * The article which has been created
     *
     * @var org_openpsa_products_product_dba
     * @access private
     */
    var $_product = null;

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema to use for the new article.
     *
     * @var string
     * @access private
     */
    var $_schema = 'default';

    /**
     * The defaults to use for the new article.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * Simple default constructor.
     */
    function org_openpsa_products_handler_product_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['indexmode'] =& $this->_indexmode;
        $this->_request_data['schema'] =& $this->_schema;
        $this->_request_data['schemadb'] =& $this->_schemadb;
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb_product'];

        $this->_defaults['productGroup'] = $this->_request_data['up'];
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_product = new org_openpsa_products_product_dba();
        $this->_product->productGroup = $this->_request_data['up'];

        if (! $this->_product->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_product);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to create a new product under product group #{$this->_request_data['up']}, cannot continue. Error: " . mgd_errstr());
            // This will exit.
        }

        return $this->_product;
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article,
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_request_data['up'] = (int) $args[0];

        if ($this->_request_data['up'] == 0)
        {
            $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_products_product_dba');
        }
        else
        {
            $parent = new org_openpsa_products_product_group_dba($this->_request_data['up']);
            if (!$parent)
            {
                return false;
            }
            $parent->require_do('midgard:create');
        }

        $data['selected_schema'] = $args[1];
        if (!array_key_exists($data['selected_schema'], $data['schemadb_product']))
        {
            return false;
        }
        $this->_schema =& $data['selected_schema'];

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("product/{$this->_product->guid}/");
                // This will exit.

            case 'cancel':
                if ($this->_request_data['up'] == 0)
                {
                    $_MIDCOM->relocate('');
                }
                else
                {
                    $_MIDCOM->relocate("{$this->_request_data['up']}/");
                }
                // This will exit.
        }

        $this->_prepare_request_data();

        if ($this->_product)
        {
            $_MIDCOM->set_26_request_metadata($this->_product->revised, $this->_product->guid);
        }
        $this->_request_data['view_title'] = sprintf($this->_l10n_midcom->get('create %s'), $this->_schemadb[$this->_schema]->description);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_request_data['view_title']}");

        return true;
    }

    /**
     * Shows the loaded article.
     */
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('product_create');
    }
}
?>