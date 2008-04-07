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
class org_openpsa_products_handler_group_edit extends midcom_baseclasses_components_handler
{
    /**
     * The product to display
     *
     * @var midcom_db_group
     * @access private
     */
    var $_group = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_products_handler_group_edit()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['group'] =& $this->_group;

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
        foreach ($this->_request_data['schemadb_group'] as $schema)
        {
            // No need to add components to a component
            if (array_key_exists('components', $schema->fields)
                && (   $this->_group->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT
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
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();
        if ($this->_group->up != 0)
        {
            $group = new org_openpsa_products_product_group_dba($this->_group->up);
            while ($group)
            {
                $parent = $group->get_parent();
        if ($group->get_parent() != NULL){
                    $tmp[] = array
                    (
                    MIDCOM_NAV_URL => "{$parent->code}/{$group->code}",
                    MIDCOM_NAV_NAME => $group->title,
                    );
        }
        else
        {
                    $tmp[] = array
                    (
                    MIDCOM_NAV_URL => "{$group->code}/",
                    MIDCOM_NAV_NAME => $group->title,
                    );

        }
                $group = $parent;
            }
        }

        $tmp = array_reverse($tmp);

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "{$this->_group->guid}.html",
            MIDCOM_NAV_NAME => $this->_group->title,
        );

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "edit/{$this->_group->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Looks up a product to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_group = new org_openpsa_products_product_group_dba($args[0]);
        if (!$this->_group)
        {
            return false;
        }

        $this->_modify_schema();

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('simple');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_group'];
        $this->_request_data['controller']->set_storage($this->_group);
        if (! $this->_request_data['controller']->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for product {$this->_group->id}.");
            // This will exit.
        }

        switch ($this->_request_data['controller']->process_form())
        {
            case 'save':

                if ($this->_config->get('index_groups'))
                {
                    // Index the group
                    $indexer =& $_MIDCOM->get_service('indexer');
                    org_openpsa_products_viewer::index($this->_request_data['controller']->datamanager, $indexer, $this->_topic);
                }
            case 'cancel':
                $_MIDCOM->relocate("{$this->_group->guid}.html");
                // This will exit.
        }

        $this->_update_breadcrumb_line();
        $this->_prepare_request_data();
        $this->_view_toolbar->bind_to($this->_group);

        $_MIDCOM->set_26_request_metadata($this->_group->revised, $this->_group->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_group->title}");

        return true;
    }

    /**
     * Shows the loaded product.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['view_group'] = $this->_request_data['controller']->datamanager->get_content_html();
        midcom_show_style('product_group_edit');
    }
}
?>