<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Product display class
 *
 * @package org.openpsa.products
 */
class org_openpsa_products_handler_product_view extends midcom_baseclasses_components_handler
{
    /**
     * The product to display
     *
     * @var midcom_db_product
     * @access private
     */
    var $_product = null;

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

        if ($this->_request_data['product']->productGroup == 0)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('product database'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        else
        {
            $parent = new org_openpsa_products_product_group_dba($this->_request_data['product']->productGroup);
            
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$parent->id}/",
                    MIDCOM_TOOLBAR_LABEL => $parent->title,
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        /*
        // Populate the toolbar
        if ($this->_product->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "product/edit/{$this->_product->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            ));
        }

        if ($this->_product->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "product/delete/{$this->_product->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            ));
        }*/
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
     * Simple default constructor.
     */
    function org_openpsa_products_handler_product_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Looks up an product to display.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_product = new org_openpsa_products_product_dba($args[0]);
        if (!$this->_product)
        {
            return false;
        }
        
        $this->_modify_schema();
        
        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_product'];
        $this->_request_data['controller']->set_storage($this->_product);
        $this->_request_data['controller']->process_ajax();

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "product/{$this->_product->guid}.html",
            MIDCOM_NAV_NAME => $this->_product->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $this->_prepare_request_data();
        $this->_view_toolbar->bind_to($this->_product);

        $_MIDCOM->set_26_request_metadata($this->_product->revised, $this->_product->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_product->title}");

        return true;
    }

    /**
     * Shows the loaded product.
     */
    function _show_view($handler_id, &$data)
    {
        // For AJAX handling it is the controller that renders everything
        $this->_request_data['view_product'] = $this->_request_data['controller']->get_content_html();
        midcom_show_style('product_view');
    }
}
?>