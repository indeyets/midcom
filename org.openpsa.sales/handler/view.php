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
class org_openpsa_sales_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * The salesproject to display
     *
     * @var midcom_db_salesproject
     * @access private
     */
    var $_salesproject = null;

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['salesproject'] =& $this->_salesproject;

        // Populate the toolbar
        if ($this->_salesproject->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "salesproject/edit/{$this->_salesproject->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            ));
        }

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
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_salesproject = new org_openpsa_sales_salesproject($args[0]);
        if (!$this->_salesproject)
        {
            return false;
        }

        $this->_load_schema();

        $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
        $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb_salesproject_dm2'];
        $this->_request_data['controller']->set_storage($this->_salesproject);
        $this->_request_data['controller']->process_ajax();
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "salesproject/{$this->_salesproject->guid}.html",
            MIDCOM_NAV_NAME => $this->_salesproject->title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $this->_prepare_request_data();

        $_MIDCOM->set_26_request_metadata($this->_salesproject->revised, $this->_salesproject->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_salesproject->title}");

        // FIXME: This is a rather ugly hack
        // $_MIDCOM->style->enter_context(0);

        // List deliverables
        $deliverable_qb = org_openpsa_sales_salesproject_deliverable::new_query_builder();
        $deliverable_qb->add_constraint('salesproject', '=', $this->_request_data['salesproject']->id);
        $deliverable_qb->add_constraint('up', '=', 0);

        if ($this->_request_data['salesproject']->status != ORG_OPENPSA_SALESPROJECTSTATUS_LOST)
        {
            $deliverable_qb->add_constraint('state', '<>', ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED);
        }

        $deliverable_qb->add_order('created', 'ASC');
        $deliverables = $deliverable_qb->execute();
        foreach ($deliverables as $deliverable)
        {
            $this->_controllers[$deliverable->id] =& midcom_helper_datamanager2_controller::create('ajax');
            // TODO: Modify schema's "price per unit" to readonly if the product has components
            $this->_controllers[$deliverable->id]->schemadb =& $this->_request_data['schemadb_salesproject_deliverable'];
            $this->_controllers[$deliverable->id]->set_storage($deliverable);
            $this->_controllers[$deliverable->id]->process_ajax();
            $this->_request_data['deliverables_objects'][$deliverable->guid] = $deliverable;
        }
        $relatedto_button_settings = org_openpsa_relatedto_handler::common_toolbar_buttons_defaults();
        $relatedto_button_settings['wikinote']['wikiword'] = sprintf($this->_request_data['l10n']->get($this->_config->get('new_wikinote_wikiword_format')), $this->_request_data['salesproject']->title, date('Y-m-d H:i'));
        //TODO: make wiki node configurable
        //TODO: make documents node configurable
        org_openpsa_relatedto_handler::common_node_toolbar_buttons($this->_view_toolbar, $this->_request_data['salesproject'], $this->_component, $relatedto_button_settings);

        $_MIDCOM->bind_view_to_object($this->_salesproject);

        return true;
    }

    /**
     * Shows the loaded salesproject.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        // For AJAX handling it is the controller that renders everything
        $this->_request_data['view_salesproject'] = $this->_request_data['controller']->get_content_html();
        midcom_show_style('show-salesproject');

        $this->_request_data['products'] = org_openpsa_products_product_dba::list_products();
        if (count($this->_request_data['products']) > 0)
        {
            // We have products defined in the system, add deliverable support
            midcom_show_style('show-salesproject-deliverables-header');

            if (array_key_exists('deliverables_objects', $this->_request_data))
            {
                foreach ($this->_request_data['deliverables_objects'] as $deliverable)
                {
                    $this->_request_data['deliverable'] = $this->_controllers[$deliverable->id]->get_content_html();
                    $this->_request_data['deliverable_object'] = $deliverable;
                    $this->_request_data['deliverable_toolbar'] = '';

                    switch ($deliverable->state)
                    {
                        case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED:
                            break;
                        case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_STARTED:
                            if ($deliverable->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
                            {
                                $this->_request_data['deliverable_toolbar'] .= "<p>" . sprintf($data['l10n']->get('next invoice will be sent on %s'), strftime('%x', $deliverable->_calculate_cycle_next(time()))) . "</p>\n";
                            }
                            break;
                        case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED:
                            $this->_request_data['deliverable_toolbar'] .= "<input type=\"submit\" class=\"deliver\" name=\"mark_delivered\" value=\"" . $this->_l10n->get('mark delivered') . "\" />\n";
                            break;
                        case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED:
                            $this->_request_data['deliverable_toolbar'] .= "<input type=\"submit\" class=\"invoice\" name=\"mark_invoiced\" value=\"" . $this->_l10n->get('invoice') . "\" />\n";
                            $this->_request_data['deliverable_toolbar'] .= "<input type=\"text\" size=\"5\" name=\"invoice\" value=\"{$deliverable->price}\" />\n";
                            break;
                        case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED:
                            $invoice_value = $deliverable->price - $deliverable->invoiced;
                            if ($invoice_value > 0)
                            {
                                $this->_request_data['deliverable_toolbar'] .= "<input type=\"submit\" class=\"invoice\" name=\"mark_invoiced\" value=\"" . $this->_l10n->get('invoice') . "\" />\n";
                                $this->_request_data['deliverable_toolbar'] .= "<input type=\"text\" size=\"5\" name=\"invoice\" value=\"{$invoice_value}\" />\n";
                            }
                            break;
                        case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_NEW:
                        case ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_PROPOSED:
                        default:
                            $this->_request_data['deliverable_toolbar'] .= "<input type=\"submit\" class=\"order\" name=\"mark_ordered\" value=\"" . $this->_l10n->get('mark ordered') . "\" />\n";
                            $this->_request_data['deliverable_toolbar'] .= "<input type=\"submit\" class=\"decline\" name=\"mark_declined\" value=\"" . $this->_l10n->get('mark declined') . "\" />\n";
                    }

                    if ($deliverable->orgOpenpsaObtype == ORG_OPENPSA_PRODUCTS_DELIVERY_SUBSCRIPTION)
                    {
                        midcom_show_style('show-salesproject-deliverables-subscription');
                    }
                    else
                    {
                        midcom_show_style('show-salesproject-deliverables-item');
                    }
                }
            }
            midcom_show_style('show-salesproject-deliverables-footer');
        }

        midcom_show_style('show-salesproject-related');
    }
}
?>