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
class org_openpsa_sales_handler_deliverable_view extends midcom_baseclasses_components_handler
{
    /**
     * The deliverable to display
     *
     * @var org_openpsa_sales_salesproject_deliverable
     * @access private
     */
    var $_deliverable = null;

    /**
     * The salesproject of the deliverable
     *
     * @var org_openpsa_sales_salesproject
     * @access private
     */
    var $_salesproject = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_sales_handler_deliverable_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['deliverable'] =& $this->_deliverable;
        $this->_request_data['salesproject'] =& $this->_salesproject;

        // Populate the toolbar
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "deliverable/edit/{$this->_deliverable->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_deliverable->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );


        /*if ($this->_salesproject->can_do('midgard:delete'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "salesproject/delete/{$this->_salesproject->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            ));
        }*/
    }

    function _load_schema()
    {
        $this->_request_data['schemadb_salesproject_dm2'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_salesproject_dm2'));
        $this->_request_data['schemadb_salesproject_deliverable'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_deliverable'));
        /*
        foreach ($this->_request_data['schemadb_salesproject'] as $schema)
        {
            // No need to add components to a component
            if (array_key_exists('components', $schema->fields)
                && (   $this->_salesproject->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_PRODUCT_TYPE_COMPONENT
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
     * Looks up a salesproject to display.
     */
    function _handler_view($handler_id, $args, &$data)
    {
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

        $this->_load_schema();

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_salesproject_deliverable'];
        $this->_request_data['controller']->set_storage($this->_deliverable);
        $this->_request_data['controller']->process_ajax();

        $tmp =  org_openpsa_sales_viewer::update_breadcrumb_line($this->_deliverable);
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_prepare_request_data();

        $_MIDCOM->bind_view_to_object($this->_deliverable);

        $_MIDCOM->set_26_request_metadata($this->_deliverable->revised, $this->_deliverable->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_salesproject->title}: {$this->_deliverable->title}");

        return true;
    }

    /**
     * Shows the loaded salesproject.
     */
    function _show_view($handler_id, &$data)
    {
        // For AJAX handling it is the controller that renders everything
        $this->_request_data['view_deliverable'] = $this->_request_data['controller']->get_content_html();

        if ($this->_deliverable->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
        {
            midcom_show_style('show-deliverable-subscription');
        }
        else
        {
            midcom_show_style('show-deliverable');
        }
    }
}
?>