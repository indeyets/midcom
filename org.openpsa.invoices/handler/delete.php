<?php
/**
 * @package org.openpsa.invoices
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: edit.php,v 1.3 2006/06/01 15:28:20 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Invoice delete handler
 *
 * @package org.openpsa.invoices
 */
class org_openpsa_invoices_handler_delete extends midcom_baseclasses_components_handler
{
    var $_datamanager = null;

    function __construct()
    {
        parent::__construct();
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    function _initialize_datamanager($schemadb_snippet)
    {
        // Initialize the datamanager with the schema
        $this->_datamanager = new midcom_helper_datamanager($schemadb_snippet);

        if (!$this->_datamanager) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }
    }

    function _load_invoice($identifier)
    {
        if (!isset($this->_datamanager))
        {
            $this->_initialize_datamanager($this->_config->get('schemadb'));
        }

        $this->_request_data['invoice'] = new org_openpsa_invoices_invoice_dba($identifier);

        if (!is_object($this->_request_data['invoice']))
        {
            return false;
        }

        //$this->_modify_schema();

        // Load the project to datamanager
        if (!$this->_datamanager->init($this->_request_data['invoice']))
        {
            return false;
        }

        return $this->_request_data['invoice'];
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['invoice'] = $this->_load_invoice($args[0]);
        $this->_request_data['invoice']->require_do('midgard:delete');

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('invoice') . ' ' . $this->_request_data['invoice']->invoiceNumber);

        if (array_key_exists('org_openpsa_invoices_deleteok', $_REQUEST))
        {
            // Deletion confirmed.
            if (! $this->_request_data['invoice']->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete invoice {$args[0]}, last Midgard error was: " . mgd_errstr());
                // This will exit.
            }

            // Update the index
            $indexer =& $_MIDCOM->get_service('indexer');
            $indexer->delete($this->_request_data['invoice']->guid);

            // Delete ok, relocating to welcome.
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('org_openpsa_invoices_deletecancel', $_REQUEST))
        {
            // Redirect to view page.
            $_MIDCOM->relocate("invoice/{$this->_request_data['invoice']->guid}/");
            // This will exit()
        }

        $this->_view_toolbar->bind_to($this->_request_data['invoice']);

        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager/columned.css",
            )
        );

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_delete($handler_id, &$data)
    {
        $this->_request_data['invoice_dm']  = $this->_datamanager;
        midcom_show_style('show-invoice-delete');
    }
}

?>