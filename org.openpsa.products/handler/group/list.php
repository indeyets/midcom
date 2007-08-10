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
     * Can-Handle check against the current group GUID. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     */
    function _can_handle_list($handler_id, $args, &$data)
    {
        if ($handler_id == 'index')
        {
            // We're in root-level product index
            $data['group'] = null;
            $data['parent_group'] = $data['root_group'];
            $data['view_title'] = $this->_l10n->get('product database');
        }
        else
        {
            // We're in some level of groups
            $qb = org_openpsa_products_product_group_dba::new_query_builder();
            $qb->add_constraint('code', '=', $args[0]);
            $results = $qb->execute();
            if (count($results) == 0)
            {
                if (!mgd_is_guid($args[0]))
                {
                    return false;
                }

                $data['group'] = new org_openpsa_products_product_group_dba($args[0]);
                if (   !$data['group']
                    || !$data['group']->guid)
                {
                    return false;
                }
            }
            else
            {
                $data['group'] = $results[0];
            }

            $data['parent_group'] = $data['group']->id;
            $data['view_title'] = "{$data['group']->code} {$data['group']->title}";
            $data['acl_object'] = $data['group'];
        }
        
        return true;
    }

    /**
     * The handler for the group_list article.
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     *
     */
    function _handler_list($handler_id, $args, &$data)
    {
        // Query for sub-objects
        $group_qb = org_openpsa_products_product_group_dba::new_query_builder();
        $group_qb->add_constraint('up', '=', $data['parent_group']);
        $group_qb->add_order('code');
        $group_qb->add_order('title');
        $data['groups'] = $group_qb->execute();

        $data['products'] = array();
        if ($this->_config->get('group_list_products'))
        {
            $product_qb = new org_openpsa_qbpager('org_openpsa_products_product_dba', 'org_openpsa_products_product_dba');
            $product_qb->results_per_page = $this->_config->get('products_per_page');
            $product_qb->add_constraint('productGroup', '=', $data['parent_group']);
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
            $data['products'] = $product_qb->execute();
            $data['products_qb'] =& $product_qb;
        }

        // Prepare datamanager
        $data['datamanager_group'] = new midcom_helper_datamanager2_datamanager($data['schemadb_group']);
        $data['datamanager_product'] = new midcom_helper_datamanager2_datamanager($data['schemadb_product']);

        // Populate toolbar
        if ($this->_request_data['group'])
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "edit/{$this->_request_data['group']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_request_data['group']->can_do('midgard:update'),
                )
            );
        }

        $allow_create = false;
        if ($data['group'])
        {
            $allow_create_group = $data['group']->can_do('midgard:create');
            $allow_create_product = $data['group']->can_do('midgard:create');
        }
        else
        {
            $allow_create_group = $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_products_product_group_dba');
            $allow_create_product = $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_products_product_dba');
        }

        foreach (array_keys($this->_request_data['schemadb_group']) as $name)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "create/{$data['parent_group']}/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get($this->_request_data['schemadb_group'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                    MIDCOM_TOOLBAR_ENABLED => $allow_create_group,
                )
            );
        }

        foreach (array_keys($this->_request_data['schemadb_product']) as $name)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "product/create/{$data['parent_group']}/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get($this->_request_data['schemadb_product'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                    MIDCOM_TOOLBAR_ENABLED => $allow_create_product,
                )
            );
        }

        if ($this->_request_data['group'])
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_group'];
            $this->_request_data['controller']->set_storage($this->_request_data['group']);
            $this->_request_data['controller']->process_ajax();
            $_MIDCOM->bind_view_to_object($this->_request_data['group'], $this->_request_data['controller']->datamanager->schema->name);
        }

        /***
         * Set the breadcrumb text
         */
        $this->_update_breadcrumb_line();
        
        // Set the active leaf
        if (   $this->_config->get('display_navigation')
            && $this->_request_data['group'])
        {
            $group =& $this->_request_data['group'];
            
            // Loop as long as it is possible to get the parent group
            while ($group->guid)
            {
                // Break to the requested level (probably the root group of the products content topic)
                if (   $group->id === $this->_config->get('root_group')
                    || $group->guid === $this->_config->get('root_group'))
                {
                    break;
                }
                $temp = $group->id;
                $group = new org_openpsa_products_product_group_dba($group->up);
            }
            
            if (isset($temp))
            {
                // Active leaf of the topic
                $this->_component_data['active_leaf'] = $temp;
            }
        }

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
    function _show_list($handler_id, &$data)
    {
        if ($this->_request_data['group'])
        {
            $this->_request_data['view_group'] = $this->_request_data['controller']->get_content_html();
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        midcom_show_style('group_header');

        if (count($data['groups']) > 0)
        {
            midcom_show_style('group_subgroups_header');

            foreach ($data['groups'] as $group)
            {
                $data['group'] = $group;
                if (! $data['datamanager_group']->autoset_storage($group))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for group #{$group->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $group);
                    debug_pop();
                    continue;
                }
                $data['view_group'] = $data['datamanager_group']->get_content_html();

                if ($group->code)
                {
                    $data['view_group_url'] = "{$prefix}{$group->code}/";
                }
                else
                {
                    $data['view_group_url'] = "{$prefix}{$group->guid}/";
                }

                midcom_show_style('group_subgroups_item');
            }

            midcom_show_style('group_subgroups_footer');
        }

        if (count($data['products']) > 0)
        {
            midcom_show_style('group_products_header');

            foreach ($data['products'] as $product)
            {
                $data['product'] = $product;
                if (! $data['datamanager_product']->autoset_storage($product))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for product #{$product->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $product);
                    debug_pop();
                    continue;
                }
                $data['view_product'] = $data['datamanager_product']->get_content_html();

                if ($product->code)
                {
                    $data['view_product_url'] = "{$prefix}product/{$product->code}/";
                }
                else
                {
                    $data['view_product_url'] = "{$prefix}product/{$product->guid}/";
                }

                midcom_show_style('group_products_item');
            }

            midcom_show_style('group_products_footer');
        }

        midcom_show_style('group_footer');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $group = $this->_request_data['group'];
        $root_group = $this->_config->get('root_group');

        if (!$group)
        {
            return false;
        }

        $parent = $group;
        
        while ($parent)
        {
            $group = $parent;
            
            if ($group->guid === $root_group)
            {
                break;
            }

            if ($group->code)
            {
                $url = "{$group->code}/";
            }
            else
            {
                $url = "{$group->guid}/";
            }
            

            $tmp[] = Array
            (
                MIDCOM_NAV_URL => $url,
                MIDCOM_NAV_NAME => $group->title,
            );
            $parent = $group->get_parent();
        }
        
        // If navigation is configured to display product groups, remove the lowest level
        // parent to prevent duplicate entries in breadcrumb display
        if (   $this->_config->get('display_navigation')
            && isset($tmp[count($tmp) - 1]))
        {
            unset($tmp[count($tmp) - 1]);
        }
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', array_reverse($tmp));
    }
}
?>