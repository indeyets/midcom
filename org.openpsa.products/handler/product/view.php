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
     * Simple default constructor.
     */
    function org_openpsa_products_handler_product_view()
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

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "product/edit/{$this->_product->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_product->can_do('midgard:update'),
            )
        );

        /*
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
        }*/

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
     * Looks up a product to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        if (preg_match('/_raw$/', $handler_id))
        {
            $_MIDCOM->skip_page_style = true;
        }

        $qb = org_openpsa_products_product_dba::new_query_builder();
        if (preg_match('/^view_product_intree/', $handler_id))
        {
            $group_qb = org_openpsa_products_product_group_dba::new_query_builder();
            if (mgd_is_guid($args[0]))
            {
                $group_qb->add_constraint('guid', '=', $args[0]);
            }
            else
            {
                $group_qb->add_constraint('code', '=', $args[0]);
            }
            $groups = $group_qb->execute();

            if (empty($groups))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Product group {$args[0]} not found" );
                // This will exit
            }

            $categories_qb = org_openpsa_products_product_group_dba::new_query_builder();
            $categories_qb->add_constraint('up', '=', $groups[0]->id);
            $categories = $categories_qb->execute();
            $categories_in = array();
            if (count($categories) == 0){
                /* No matching categories belonging to this group
                 * So we can search for the application using only
                 * this group id
                 */
                $qb->add_constraint('productGroup', 'INTREE', $groups[0]->id);
            }
            else
            {
                for ($i = 0; $i < count($categories); $i++)
                {
                    $categories_in[$i] = $categories[$i]->id;
                }
                $qb->add_constraint('productGroup', 'IN', $categories_in);
            }

            if (mgd_is_guid($args[1]))
            {
                $qb->add_constraint('guid', '=', $args[1]);
            }
            else
            {
                $qb->add_constraint('code', '=', $args[1]);
            }
        }
        else
        {
            if (mgd_is_guid($args[0]))
            {
                $qb->add_constraint('guid', '=', $args[0]);
            }
            else
            {
                $qb->add_constraint('code', '=', $args[0]);
            }
        }

        $qb->add_constraint('start', '<=', time());
        $qb->begin_group('OR');
            /*
             * List products that either have no defined end-of-market dates
             * or are still in market
             */
            $qb->add_constraint('end', '=', 0);
            $qb->add_constraint('end', '>=', time());
        $qb->end_group();
        $results = $qb->execute();

        if (!empty($results))
        {
            $this->_product = $results[0];
        }
        else
        {
            if (preg_match('/^view_product_intree/', $handler_id))
            {
                if (!mgd_is_guid($args[1]))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Product {$args[1]} not found" );
                    // This will exit
                }
                $this->_product = new org_openpsa_products_product_dba($args[1]);
            }
            else
            {
                if (!mgd_is_guid($args[0]))
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Product {$args[0]} not found" );
                    // This will exit
                }
                $this->_product = new org_openpsa_products_product_dba($args[0]);
            }
        }
        if (   !$this->_product
            || !isset($this->_product->guid)
            || empty($this->_product->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Fell through to last product sanity-check and failed");
            // This will exit
        }

        $this->_modify_schema();

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_product'];
        $this->_request_data['controller']->set_storage($this->_product);
        $this->_request_data['controller']->process_ajax();

        $this->_prepare_request_data();
        $_MIDCOM->bind_view_to_object($this->_product, $this->_request_data['controller']->datamanager->schema->name);

        $breadcrumb = org_openpsa_products_viewer::update_breadcrumb_line($this->_product);
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        $_MIDCOM->set_26_request_metadata($this->_product->metadata->revised, $this->_product->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_product->title}");

        return true;
    }

    /**
     * Shows the loaded product.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        // For AJAX handling it is the controller that renders everything
        $data['view_product'] = $data['controller']->get_content_html();
        $data['datamanager'] =& $data['controller']->datamanager;
        midcom_show_style('product_view');
    }
}
?>