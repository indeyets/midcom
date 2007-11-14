<?php
/**
 * @package net.nemein.shoppingcart
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.shoppingcart
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nemein.shoppingcart
 */
class net_nemein_shoppingcart_handler_checkout_email  extends midcom_baseclasses_components_handler 
{
    var $_schemadb = false;
    var $_datamanager = false;
    var $_controller = false;
    var $_defaults = array();
    var $_sendto = false;
    var $_mail = false;

    /**
     * Simple default constructor.
     */
    function net_nemein_shoppingcart_handler_checkout_email()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $_MIDCOM->componentloader->load('org.openpsa.mail');
        $this->_sendto = $this->_config->get('backend_email_sendto');
        if (empty($this->_sendto))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, '"backend_email_sendto" is not configured');
            // This will exit
        }
        if (!net_nemein_shoppingcart_viewer::get_items($this->_request_data))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not initialize cart');
            // This will exit
        }
        $this->_initialize_datamanager();
        $data['l10n'] =& $this->_l10n;
    }

    function _initialize_defaults()
    {
        if (!$_MIDCOM->auth->user)
        {
            return;
        }
        switch(true)
        {
            // Lets use org_openpsa_contacts_person if we can (more/better properties)
            case (   $_MIDCOM->componentloader->load_graceful('org.openpsa.contacts')
                  && class_exists('org_openpsa_contacts_person')):
                $person = new org_openpsa_contacts_person($_MIDGARD['user']);
                break;
            default:
                $person = $_MIDCOM->auth->user->get_storage();
        }
        // Safety
        if (!is_object($person))
        {
            return;
        }
        // PHP4 can't foreach objects, OTOH this works fine
        while (list ($property, $value) = each($person))
        {
            $this->_defaults[$property] = $value;
        }
    }

    function _initialize_datamanager()
    {
        $this->_initialize_defaults();
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->set_schemadb($this->_schemadb);
        $this->_controller->schemaname = 'email';
        $this->_controller->defaults = $this->_defaults;
        if (!$this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize DM2");
            //This will exit
        }
        $this->_datamanager =& $this->_controller->datamanager;
        if (!isset($this->_datamanager->types['email']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Schema must have field 'email'");
            //This will exit
        }
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    /**
     * Handler for starting simple email checkout
     *
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * @param array $data reference to request_data
     */
    function _handler_phase1($handler_id, $args, &$data)
    {
        if (empty($data['items']))
        {
            // Cart is empty, can't checkout...
            $_MIDCOM->relocate('contents.html');
            // This will exit
        }
        $data['title'] = $this->_l10n->get('check out');
        $_MIDCOM->set_pagetitle($data['title']);
        $this->_update_breadcrumb_line($handler_id);
        $data['total_value'] = 0;
        $data['permalinks'] = new midcom_services_permalinks();

        switch ($this->_controller->process_form())
        {
            case 'save':
                if (!$this->_send_mail())
                {
                    // TODO: Nicer error handling ?
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to send order by email, please contact {$this->_sendto}");
                    // This will exit
                }
                // Clear cart
                $session = new midcom_service_session();
                $session->remove('cartdata');
                $_MIDCOM->relocate('checkout/email-ok.html');
                break;
            case 'cancel':
                // Cancelled, return to cart edit view
                $_MIDCOM->relocate('');
                // This will exit
                break;
        }

        return true;
    }

    function _send_mail()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_mail = new org_openpsa_mail();
        $mail =& $this->_mail;
        $mail->to = $this->_sendto;
        // TODO: Override sender ??
        $mail->from = $this->_datamanager->types['email']->value;
        // TODO: cc vs separate copy
        $mail->cc = $this->_datamanager->types['email']->value;
        $mail->subject = $this->_l10n->get($this->_config->get('backend_email_subject'));
        // This may be a hack, but it allows us tons more control in rendering the email
        $_MIDCOM->style->enter_context(0);
        ob_start();
        midcom_show_style('backend-email-body');
        $mail->body = ob_get_contents();
        ob_end_clean();
        $_MIDCOM->style->leave_context();
        
        debug_pop();
        //return false;
        return $mail->send();
    }

    /**
     * This function does the output.
     *  
     */
    function _show_phase1($handler_id, &$data)
    {
        midcom_show_style('checkout-email-phase1');
    }

    /**
     * Handler for starting simple email checkout
     *
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * @param array $data reference to request_data
     */
    function _handler_phase2($handler_id, $args, &$data)
    {
        $data['title'] = $this->_l10n->get('order sent');
        $_MIDCOM->set_pagetitle($data['title']);
        $this->_update_breadcrumb_line($handler_id);
        return true;
    }

    /**
     * This function does the output.
     *  
     */
    function _show_phase2($handler_id, &$data)
    {
        midcom_show_style('checkout-email-ok');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line(&$handler_id)
    {
        $data =& $this->_request_data;
        $tmp = Array();

        switch ($handler_id)
        {
            case 'checkout-email-ok':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => 'checkout/email-ok.html',
                    MIDCOM_NAV_NAME => $data['title'],
                );
                break;
            default:
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => 'contents.html',
                    MIDCOM_NAV_NAME => $this->_l10n->get('view cart'),
                );
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => 'checkout/email.html',
                    MIDCOM_NAV_NAME => $data['title'],
                );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
