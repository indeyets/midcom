<?php
/**
 * Created on 2006-08-09
 * @author Henri Bergius
 * @package org.openpsa.products
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 * 
 */

class org_openpsa_products_handler_group_list  extends midcom_baseclasses_components_handler 
{
    /*
     * The midcom_baseclasses_components_handler class defines a bunch of helper vars
     * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
     */
    
    /**
     * Simple default constructor.
     */
    function org_openpsa_products_handler_group_list()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * The handler for the group_list article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_list($handler_id, $args) 
    {
        if ($handler_id == 'index')
        {
            // We're in root-level product index
            $this->_request_data['group'] = null;
            $parent_group = 0;
            $this->_request_data['view_title'] = $this->_l10n->get('product database');
            $acl_object = $this->_topic;
        }
        else
        {
            // We're in some level of groups
            $this->_request_data['group'] = new org_openpsa_products_product_group_dba($args[0]);
            if (!$this->_request_data['group'])
            {
                return false;
            }
            
            $parent_group = $this->_request_data['group']->id;
            $this->_request_data['view_title'] = "{$this->_request_data['group']->code} {$this->_request_data['group']->title}";
            $acl_object = $this->_request_data['group'];
            
            if ($this->_request_data['group']->up == 0)
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
                $parent = new org_openpsa_products_product_group_dba($this->_request_data['group']->up);
                
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
            
            $this->_view_toolbar->bind_to($this->_request_data['group']);
        }
        
        // Query for sub-objects
        $group_qb = org_openpsa_products_product_group_dba::new_query_builder();
        $group_qb->add_constraint('up', '=', $parent_group);
        $group_qb->add_order('code');
        $group_qb->add_order('title');
        $this->_request_data['groups'] = $group_qb->execute();

        $product_qb = org_openpsa_products_product_dba::new_query_builder();
        $product_qb->add_constraint('productGroup', '=', $parent_group);
        $product_qb->add_order('code');
        $product_qb->add_order('title');
        $product_qb->add_constraint('start', '<=', time());
        $product_qb->begin_group('OR');
            /*
             * List products that either have no defined end-of-market dates
             * or are still in market
             */
            $product_qb->add_constraint('end', '=', 0);
            $product_qb->add_constraint('end', '>=', time());
        $product_qb->end_group();
        $this->_request_data['products'] = $product_qb->execute();
    
        // Populate toolbar
        if ($acl_object->can_do('midgard:create'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "create/{$parent_group}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get('product group')
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                )
            );

            foreach (array_keys($this->_request_data['schemadb_product']) as $name)
            {
                $this->_node_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "product/create/{$parent_group}/{$name}.html",
                        MIDCOM_TOOLBAR_LABEL => sprintf
                        (
                            $this->_l10n_midcom->get('create %s'),
                            $this->_l10n->get($this->_request_data['schemadb_product'][$name]->description)
                        ),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    )
                );
            }
        }
        
        if ($this->_request_data['group'])
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_group'];
            $this->_request_data['controller']->set_storage($this->_request_data['group']);
            $this->_request_data['controller']->process_ajax();
        }
        
        /***
         * Set the breadcrumb text
         */
        $this->_update_breadcrumb_line($handler_id);
        /**
         * change the pagetitle. (must be supported in the style)
         */
        $_MIDCOM->set_pagetitle($this->_request_data['view_title']);
        return true;
    }
    
    /**
     * This function does the output.
     *  
     */
    function _show_list() 
    {
        if ($this->_request_data['group'])
        {
            $this->_request_data['view_group'] = $this->_request_data['controller']->get_content_html();
        }
        
        midcom_show_style('group_list');
    }
    
    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        // FIXME: Handle product hierarchy here
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
