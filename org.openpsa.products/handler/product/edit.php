<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Product editing class
 *
 * @package org.openpsa.products
 */
class org_openpsa_products_handler_product_edit extends midcom_baseclasses_components_handler
{
    /**
     * The product to display
     *
     * @var midcom_db_product
     * @access private
     */
    var $_product = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_products_handler_product_edit()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['product'] =& $this->_product;
        $this->_request_data['enable_components'] = $this->_config->get('enable_components');

        if ($this->_product->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT)
        {
            $this->_request_data['enable_components'] = false;
        }

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/legacy.css",
            )
        );
    }

    function _modify_schema()
    {
        /*
        foreach ($this->_request_data['schemadb_product'] as $schema)
        {
            // No need to add components to a component
            if (array_key_exists('components', $schema->fields)
                && (   $this->_product->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT
                    || !$this->_config->get('enable_components')
                    )
                )
            {
                unset($schema->fields['components']);
            }
        }
        */
    }

    /**
     * Looks up a product to display.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_product = new org_openpsa_products_product_dba($args[0]);
        if (!$this->_product)
        {
            return false;
        }

        $this->_modify_schema();

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('simple');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_product'];
        $this->_request_data['controller']->set_storage($this->_product);
        if (! $this->_request_data['controller']->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for product {$this->_product->id}.");
            // This will exit.
        }

        switch ($this->_request_data['controller']->process_form())
        {
            case 'save':
                // Invalidate cache for this product.
                $_MIDCOM->cache->invalidate($this->_product->guid);
                // *** FALL-THROUGH ***
            case 'cancel':
                $_MIDCOM->relocate("product/{$this->_product->guid}.html");
                // This will exit.
        }

        $breadcrumb = org_openpsa_products_viewer::update_breadcrumb_line($this->_product);
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "product/edit/{$this->_product->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        $this->_prepare_request_data();
        $this->_view_toolbar->bind_to($this->_product);

        $_MIDCOM->set_26_request_metadata($this->_product->revised, $this->_product->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_product->title}");

        return true;
    }

    /**
     * Shows the loaded product.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['view_product'] = $this->_request_data['controller']->datamanager->get_content_html();
        midcom_show_style('product_edit');
    }
}
?>