<?php

/**
 * @package net.nemein.payment
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Payment handler base class.
 * 
 * This class serves as a basic interface for all payment handlers in the system.
 * 
 * Configuration is done internally by the component, you don't need to worry about
 * that when using this library.
 * 
 * Use render_payment_link() to display the link to the payment processing system;
 * this method will yield block-level HTML, as sometimes there is need for HTTP
 * POST information so FORMS might be drawn at this point.
 * 
 * If the user executes payment, the user will be redirected to the specified return
 * URL which must include a call to process_payment().
 * 
 * Check the documentation of these functions for both general usage and handler specific
 * details.
 *
 * <b>Core handler configuration options:</b>
 * 
 * - <i>_handler</i>: The name of the Payment handler to use, this must match a class
 *   and file name from the handler subdirectory of the component. See the various
 *   defined handlers for details. Example: Use 'nordea' to get the Nordea handler
 *   defined in nordea.php (named net_nemein_payment_handler_nordea).
 * 
 * <b>Notes for Payment Handler authors:</b>
 * 
 * The return URL will be preparsed prior the invocation of on_render_payment_link: The
 * ID of the handler will automatically be appended as GET parameter, so that the handler
 * can assume that the URL already has a question mark in it, appending new parameters
 * in the style of "&param=value" without further thought. 
 * 
 * All GET/POST parameters returned from the external site added by a handler should
 * be in the net_nemeina_payment namespace. You don't need further namesapcing, as you can
 * safely assume only one handler is running at a given time. The parameter
 * "net_nemein_payment_id" is reserved for ID usage to identify the correct handler in
 * the factory class..
 * 
 * You may define arbitrary configuration options for the handler array, as long as they do 
 * not start with an underscore, which is reserved for library core parameters.
 * 
 * Any handler specific l10n strings should be prefixed with the name of the handler.
 * 
 * ...
 * 
 * @see net_nemein_payment_factory
 * @package net.nemein.payment
 */

 
class net_nemein_payment_handler extends midcom_baseclasses_components_purecode
{
    /**
     * The handler configuration array.
     * 
     * @var Array
     */
    var $_handler_config = Array();
    
    /**#@+
	 * Variables concerning the actual payment, set during render_payment_link.
	 * 
	 * @access protected
	 */
    
    /**
     * The amount that is being invoiced.
     * 
     * @var double
     */
    var $_amount = 0.0;
    
    /**
     * The URL to return to after processing the payment. This must be a fully
     * qualified URL which calls the process_payment handler function.
     * 
     * @var string
     */
    var $_return_url = '';
    
    /**
     * The payment reference associated with this record. The exact allowed 
     * characters for this field varies with the actual handler, refer to their
     * class documentations for details.
     * 
     * In general you should be on the safe side if you stick to some midgard
     * object ID (not guid, as this is too long at least for the Nordea E-Payment
     * plugin).
     * 
     * Note, that despite the leading underscore, this variable is public.
     * 
     * @var string
     * @access public
     */
    var $_reference = '';
    
    /**
     * An arbitrary message to the user, shown during payment processing (if supported
     * by the payment handler).
     * 
     * @var string
     */
    var $_message = '';
    
    /**#@-*/
    
    /**#@+
	 * Variables indicating the state of the payment processing, set during
	 * process_payment, the callee can use these variables to determine the
	 * state of the payment.
	 * 
	 * @access public
	 */
   
    /**
     * Status code, one of NET_NEMEIN_PAYMENT_OK, NET_NEMEIN_PAYMENT_REJECTED,
     * NET_NEMEIN_PAYMENT_CANCELLED or NET_NEMEIN_PAYMENT_ERROR.
     * 
     * @var int
     */
    var $result = NET_NEMEIN_PAYMENT_ERROR;
    
    /**
     * Human readable message about the processed payment. It is recommended that
     * you store this information at the related order.
     * 
     * Storage handlers should not safe any confidential information (like credit
     * card numbers) at this order. Storage should be in a longtext capable
     * field.
     * 
     * The string should be plain-text, rendering should be done using nl2br.
     * 
     * @var string
     */
    var $message = '';
    
    /**
     * Machine readable data about the processed payment. This is usually a serialized 
     * string.
     * 
     * Right now, further usage of this data is not implemented, but stuff like refunding
     * will depend on this data, which is why it is already in place for future
     * usage. For future usage like this, this data should contain all information 
     * necessary to "reconstruct" the payment.
     * 
     * This information should be stored by the library client if any post-processing
     * of the order might be added in the future. Storage should be in a longtext capable
     * field.
     * 
     * The array returned should be stored serialized, as usual with data like this. 
     * 
     * @var Array
     */
    var $machine_response = Array();

	/**#@-*/
        
    /**
     * Initializes the class, populates the config member variable.
     * 
     * @param Array $config The associative configuration array for this handler.
     */
    function net_nemein_payment_handler ($config)
    {
     	$this->_component = 'net.nemein.payment';
    	parent::midcom_baseclasses_components_purecode();
        
        $this->_handler_config = $config;
    }
    
    /**
     * Renders a link (this might be a FORM actually, so take this into account
     * in your layout code) to pay the specified amount. The Return URL must lead to
     * a piece of code which calls process_payment.
     * 
     * Any invalid parameter will call generate_error.
     * 
     * You should not call this function directly, instead, use the render_payment_links
     * function of the net_nemein_payment_factory class.
     * 
     * @param double $amount The amount that needs to be deducted.
     * @param string $return_url The fully qualified URL to return to.
     * @param string $reference The Payment reference code.
     * @param string $message The message shown during payment processing, can be an empty string.
     * @see _on_render_payment_link()
     * @see net_nemein_payment_factory::render_payment_links()
     */
    function render_payment_link($amount, $return_url, $reference, $message)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (! is_double($amount))
        {
            $_MIDCOM->generate_error(MIDCOM_ERR_CRIT, "n.n.payments: The amount '{$amount}' is not a valid floating point type.");
            // This will exit            
        }
        if ($return_url == '' || $reference == '')
        {
            $_MIDCOM->generate_error(MIDCOM_ERR_CRIT, "n.n.payments: Reference and Return URL must not be empty.");
            // This will exit            
        }
        
        $separator = (strpos($return_url, '?') === false) ? '?' : '&';
        
        $this->_return_url = "{$return_url}{$separator}net_nemein_payment_id={$this->_handler_config['_id']}";
        
        $this->_amount = $amount;
        $this->_reference = $reference;
        $this->_message = $message;
        
        // Call the event handler.
        $this->_on_render_payment_link();
        
        debug_pop();
    }
    
    /**
     * Processes the payment that has been rendered previously.
     * 
     * The payment processing state variables will be populated after calling this
     * function.
     * 
     * You should not call this function directly, instead, use the process_payment
     * function of the net_nemein_payment_factory class.
     * 
     * The current handler configuration will automatically be added to the machine
     * readable response after processing the payment as '_handler_config', so that a later
     * restoration of the correct handler is possible (not yet implemented).
     * 
     * @see $machine_response
     * @see $message
     * @see $result
     * @see _on_process_payment()
     * @see net_nemein_payment_factory::process_payment()
     */
    function process_payment()
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        
        // Call the event handler
        $this->_on_process_payment();
        
        // Add the configuration information to the machine-readable data.
        $this->machine_response['_handler'] = $this->_handler_config['_handler'];
        
        debug_pop();
    }
    
    /**
     * Render the payment link for the current configuration.
     * 
     * Note for handler authors: You do need to call debug_push in the
     * subclass, this is already done by prior the handler invocation.
     * 
     * Must be overridden.
     */
    function _on_render_payment_link()
    {
        die ('The method net_nemein_payment_handler::_on_render_payment_link must be overridden.');
    }
    
    /**
     * Process the payment and populate the payment status variables.
     * 
     * Note for handler authors: You do need to call debug_push in the
     * subclass, this is already done by prior the handler invocation.
     * 
     * Must be overridden.
     */
    function _on_process_payment()
    {
        die ('The method net_nemein_payment_handler::_on_process_payment must be overridden.');
    }
}

?>