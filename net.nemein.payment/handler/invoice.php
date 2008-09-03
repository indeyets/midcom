<?php

/**
 * @package net.nemein.payment
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Simple payment handler used for having an "Pay by Invoice" alternative available.
 * It does not have any payment mechanism whatsovere but will always return
 * NET_NEMEIN_PAYMENT_OK_BUT_PENDING during the payment processing run.
 * 
 * It has no configuration options whatsoever.
 * 
 * @package net.nemein.payment
 */

 
class net_nemein_payment_handler_invoice extends net_nemein_payment_handler
{
    /**
     * Constructor, relays to base class.
     * 
     * @param Array $config Configuration data.
     */
    function net_nemein_payment_handler_invoice($config)
    {
        parent::__construct($config);
    }
    
    /**
     * Renders the payment link, this is a simple href to the return URL.
     */
    function _on_render_payment_link()
    {
        echo "<a href='{$this->_return_url}'>" . $_MIDCOM->i18n->get_string('pay by invoice', 'net.nemein.payment') . '</a>';
    }
    
    /**
     * Process the payment and populate the payment status variables.
     * 
     * It will automatically set the status to NET_NEMEIN_PAYMENT_OK_BUT_PENDING
     * unconditionally.
     */
    function _on_process_payment()
    {
        $this->result = NET_NEMEIN_PAYMENT_OK_BUT_PENDING;
        $this->message = 'Invoice registered at: ' . gmstrftime('%Y-%m-%d %T GMT', time());
        $this->machine_response['INVOCICE_REGISTERED'] = time();
        $this->machine_response['result'] = $this->result;
    }
}

?>