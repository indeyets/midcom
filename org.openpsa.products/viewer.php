<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which url's should be handled by this module.
 *
 * @package org.openpsa.products
 */

class org_openpsa_products_viewer extends midcom_baseclasses_components_request
{
    function org_openpsa_products_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        // *** Prepare the request switch ***
        /*  */
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_configuration', 'configdm'),
            'schemadb' => 'file:/org/openpsa/products/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /
        if ($this->_config->get('search_first'))
        {
            $this->_request_switch['index'] = Array
            (
                'handler' => Array('org_openpsa_products_handler_product_search', 'search_redirect'),
            );
        }
        else
        {
            $this->_request_switch['index'] = Array
            (
                'handler' => Array('org_openpsa_products_handler_group_list', 'list'),
            );
        }

        // Handle /<group guid>
        $this->_request_switch['list'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_group_list', 'list'),
            'variable_args' => 1,
        );

        // Handle /edit/<product_group guid>
        $this->_request_switch['edit_product_group'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_group_edit', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );

        // Handle /create/<group id>/<schema name>
        $this->_request_switch['create_group'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_group_create', 'create'),
            'fixed_args' => Array('create'),
            'variable_args' => 2,
        );

        // Handle /product/create/<group id>/<schema name>
        $this->_request_switch['create_product'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_create', 'create'),
            'fixed_args' => Array('product', 'create'),
            'variable_args' => 2,
        );

        // Handle /product/edit/<product guid>
        $this->_request_switch['edit_product'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_edit', 'edit'),
            'fixed_args' => Array('product', 'edit'),
            'variable_args' => 1,
        );

        // Handle /product/<product guid>
        $this->_request_switch['view_product'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_view', 'view'),
            'fixed_args' => Array('product'),
            'variable_args' => 1,
        );
        
        // Handle /updated/<N>
        $this->_request_switch['updated_products'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_latest', 'updated'),
            'fixed_args' => Array('updated'),
            'variable_args' => 1,
        );
        
        // Handle /rss.xml
        $this->_request_switch['updated_products_feed'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_latest', 'feed'),
            'fixed_args' => Array('rss.xml'),
        );
 
        // Handle /businessarea/
        $this->_request_switch['index_businessarea'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_businessarea_list', 'list'),
            'fixed_args' => Array('businessarea'),
        );
               
        // Handle /businessarea/<businessarea guid>
        $this->_request_switch['list_businessarea'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_businessarea_list', 'list'),
            'fixed_args' => Array('businessarea'),
            'variable_args' => 1,
        );
        
        // Handle /product/create/<group id>/<schema name>
        $this->_request_switch['create_businessarea'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_businessarea_create', 'create'),
            'fixed_args' => Array('businessarea', 'create'),
            'variable_args' => 2,
        );

        // Handle /search/
        $this->_request_switch['view_search_redirect'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_search', 'search_redirect'),
            'fixed_args' => Array('search'),
        );

        // Handle /search/<product schema>
        $this->_request_switch['view_search'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_search', 'search'),
            'fixed_args' => Array('search'),
            'variable_args' => 1,
        );
        
        // Handle /search/raw/<product schema>
        $this->_request_switch['view_search_raw'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_search', 'search'),
            'fixed_args' => Array('search', 'raw'),
            'variable_args' => 1,
        );
        
        // Handle /api/product/get/<guid>
        $this->_request_switch['api_product_get'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_api', 'product_get'),
            'fixed_args' => Array('api', 'product', 'get'),
            'variable_args' => 1,
        );
        
        // Handle /api/product/list/
        $this->_request_switch['api_product_list_all'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_api', 'product_list'),
            'fixed_args' => Array('api', 'product', 'list'),
        );
        
        // Handle /api/product/list/<product_group>
        $this->_request_switch['api_product_list'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_api', 'product_list'),
            'fixed_args' => Array('api', 'product', 'list'),
            'variable_args' => 1,
        );

        // Handle /api/product/create/
        $this->_request_switch['api_product_create'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_api', 'product_create'),
            'fixed_args' => Array('api', 'product', 'create'),
        );

        // Handle /api/product/update/<guid>
        $this->_request_switch['api_product_update'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_api', 'product_update'),
            'fixed_args' => Array('api', 'product', 'update'),
            'variable_args' => 1,
        );
        
        // Handle /api/product/delete/<guid>
        $this->_request_switch['api_product_delete'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_api', 'product_delete'),
            'fixed_args' => Array('api', 'product', 'delete'),
            'variable_args' => 1,
        );
        
        // Handle /api/product/csv
        $this->_request_switch['api_product_csv'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_csv', 'csv'),
            'fixed_args' => Array('api', 'product', 'csv'),
        );
        
        // Handle /api/product/csv/<filename>
        $this->_request_switch['api_product_csv_filename'] = Array
        (
            'handler' => Array('org_openpsa_products_handler_product_csv', 'csv'),
            'fixed_args' => Array('api', 'product', 'csv'),
            'variable_args' => 1,
        );

    }

    /**
     * Indexes a product
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encaspulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (is_object($topic))
        {
            $tmp = new midcom_db_topic($topic);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
                // This will exit.
            }
            $topic = $tmp;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);
        $author = $_MIDCOM->auth->get_user($dm->storage->object->creator);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->component = $topic->component;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->author = $author->name;
        $document->created = $dm->storage->object->created;
        $document->edited = $dm->storage->object->revised;
        $indexer->index($document);
    }

    /**
     * Populates the node toolbar depending on the users rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        /*
        foreach (array_keys($this->_request_data['schemadb_businessarea']) as $name)
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "businessarea/create/0/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get($this->_request_data['schemadb_businessarea'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                )
            );
        }
        */
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }

    }

    /**
     * The handle callback populates root group information
     */
    function _on_can_handle($handler, $args)
    {
        if ($this->_config->get('root_group') === 0)
        {
            $this->_request_data['root_group'] = 0;
        }
        else
        {
            $root_group = new org_openpsa_products_product_group_dba($this->_config->get('root_group'));
            if (!$root_group)
            {
                return false;
            }
            $this->_request_data['root_group'] = $root_group->id;
        }
        return true;
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb_group'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_group'));
        $this->_request_data['schemadb_product'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_product'));
        $this->_request_data['schemadb_businessarea'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_businessarea'));

        $this->_populate_node_toolbar();

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_l10n->get('updated products'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss.xml',
            )
        );

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     * @param org_openpsa_products_product object
     */
    function update_breadcrumb_line($object)
    {
        $tmp = Array();

        while ($object)
        {
            if (get_class($object) == 'org_openpsa_products_product_dba')
            {
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "product/{$object->guid}/",
                    MIDCOM_NAV_NAME => $object->title,
                );
            }
            else
            {
                $tmp[] = array
                (
                    MIDCOM_NAV_URL => "{$object->guid}/",
                    MIDCOM_NAV_NAME => $object->title,
                );
            }
            $object = $object->get_parent();
        }
        $tmp = array_reverse($tmp);
        return $tmp;
    }
}

?>
