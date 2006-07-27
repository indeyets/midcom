<?php
/**
 * @package net.nemein.payment
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Payment library MidCOM interface class.
 * 
 * Startup loads only the factory classes required to create payment handler
 * instances and the payment handler base class.
 * 
 * @package net.nemein.payment
 */
class net_nemein_payment_interface extends midcom_baseclasses_components_interface
{
    
    function net_nemein_payment_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.payment';
        $this->_purecode = true;
        $this->_autoload_files = Array('handler.php', 'factory.php');
    }
}

/**
 * Payment return code for a successful Payment.
 */
define ('NET_NEMEIN_PAYMENT_OK', 0);

/**
 * Payment return code for a Payment which has been rejected by the 
 * E-Banking service provider.
 */
define ('NET_NEMEIN_PAYMENT_REJECTED', 1);

/**
 * Payment return code set when the user cancelled the payment.
 */
define ('NET_NEMEIN_PAYMENT_CANCELLED', 2);

/**
 * Payment return code set when an internal, unrecoverable error was detected.
 */
define ('NET_NEMEIN_PAYMENT_ERROR', 3);

/**
 * Payment return code for an accepted Payment which has not been executed yet.
 * This is the default return code for example for an pay-by-invoice handler.
 */
define ('NET_NEMEIN_PAYMENT_OK_BUT_PENDING', 4);



?>