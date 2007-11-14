<?php

/**
 * @package net.nemein.payment
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Nordea E-payment service payment handler
 * 
 * This handler implements the Finnish Nordea E-payment service protocol version
 * "0003" as described in the corresponding specification found on 
 * http://www.nordea.fi/eng/yri/tuki/soloverkkopalvelut.asp?navi=kasikirjat&item=soloverkkopalvelut
 * 
 * The PDF file is also part of the static files distributed with this component,
 * in case the above URL changes. It is Copyright Nordea 2005.  
 * 
 * Read this specification prior deploying this service, as I use the same terms
 * in this documentation. 
 * 
 * <b>Required configuration directives</b>
 * 
 * - <i>SOLOPMT_RCV_ID</i>: Customer ID
 * - <i>MAC_SEED</i>: Message authentication code seed (also called "Service provider's MAC")
 * 
 * <b>Optional configuration directives</b>
 * 
 * - <i>SOLOPMT_RCV_ACCOUNT</i>: Account number, if different from the default
 * - <i>SOLOPMT_RCV_NAME</i>: Name, if different from the default
 * - <i>SOLOPMT_LANGUAGE</i>: Language, if omitted, autodetection is attempted (which is recommended
 *   as autodetect ties the remote interface as good as possible to the local site language).
 * - <i>SOLOPMT_REF_SUFFIX</i>: An integer, which will be appended to the reference number, in case
 *   you need to separate different systems from each other. This is unset by default, which means
 *   that the Reference is constructed directly from the order ID.
 * 
 * <b>Limitations of this handler</b>
 * 
 * The WAP payment methods are not yet supported.
 * 
 * The Payment references must be integers shorter then 19 characters.
 * 
 * @package net.nemein.payment
 */

 
class net_nemein_payment_handler_nordea extends net_nemein_payment_handler
{
    /**#@+
	 * Payment handling specific internal variable.
	 * 
	 * @access private
	 */
    
    /**
     * Payment interface version.
     * 
     * @var string
     */
    var $_version = '0003';
    
    /**
     * Receiver Account, if different from the default.
     * 
     * @var string
     */
    var $_rcv_account = '';
     
    /**
     * Service Provider Name, if different from the default.
     * 
     * @var string
     */
    var $_rcv_name = '';
    
    /**
     * Payment interface language (1 = Finnish, 2 = Swedish, 3 = English).
     * 
     * This usually is autodetected out of the current site language.
     * 0 indicates that auto-detection should be attempted.
     * 
     * @var int
     */
    var $_language = 0;
    
    /**
     * Payment due date.
     * 
     * @var string
     */
    var $_date = 'EXPRESS';
    
    /**
     * Payment confirmation (YES adds additional, required information to the
     * response).
     * 
     * @var string
     */
    var $_payment_confirmation = 'YES';
    
    /**
     * Key version (I suspect that this relates to the MAC).
     * 
     * @var string 
     */
    var $_key_version = '0003';
    
    /**
     * Currency code, Nordea currently only supports EUR.
     * 
     * @var string
     */
    var $_currency = 'EUR';
    
    /**
     * Payment reference number, constructed out of the reference id by adding
     * the check digit.
     * 
     * @var string
     */
    var $_payment_reference = '';

    /**
     * Payment reference number suffix, used to distinguish different
     * installations from each other.
     * 
     * @var string
     */
    var $_payment_reference_suffix = '';
    
    /**
     * String representation of the amount used in interaction with the
     * Nordea servers.
     * 
     * @var string
     */
    var $_amount_string = '';
    
    /**
     * The stamp identifying the request. This is a unique number which is used
     * to prevent double processing on the server side. It is built using the
     * current timestamp and the order ID.
     * 
     * @var string
     */
    var $_stamp = '';
    
    /**
     * The Message authentication code, computed as outlined in Chapter 3.3
     * of the Nordea specs. This is an MD5 sum.
     * 
     * @var string
     */
    var $_mac = '';
    
    /**
     * Return address following successful payment.
     * 
     * @var string
     */
    var $_ok_url = '';
    
    /**
     * Return address if payment was cancelled.
     * 
     * @var string
     */
    var $_cancel_url = '';
    
    /**
     * Return address if payment was rejected.
     * 
     * @var string
     */
    var $_rejected_url = '';
    
    /**
     * The payment code as returned by the bank's system. 
     */
    var $_response_payment_code = '';
    
    /**#@-*/
    
    /**
     * Constructor, relays to base class.
     * 
     * @param Array $config Configuration data.
     */
    function net_nemein_payment_handler_nordea($config)
    {
        parent::net_nemein_payment_handler($config);
        
        if (array_key_exists('SOLOPMT_RCV_ACCOUNT', $config))
        {
            $this->_rcv_account = $config['SOLOPMT_RCV_ACCOUNT'];
        }
        if (array_key_exists('SOLOPMT_RCV_NAME', $config))
        {
            $this->_rcv_name = $config['SOLOPMT_RCV_NAME'];
        }
        if (array_key_exists('SOLOPMT_LANGUAGE', $config))
        {
            $this->_language = $config['SOLOPMT_LANGUAGE'];
        }
        if (   array_key_exists('SOLOPMT_REF_SUFFIX', $config)
            && is_numeric($config['SOLOPMT_REF_SUFFIX']))
        {
            $this->_payment_reference_suffix = $config['SOLOPMT_REF_SUFFIX'];
        }
        
        if ($this->_language == 0)
        {
            $this->_detect_language();
        }
    }

    /**
     * Renders the payment link, preparation in thie order:
     * 
     * 1. Format the amount string
     * 2. Comput the payment reference with its check digit
     * 3. Mask all text strings (currently this affects only the message)
     * 4. Generate the return URLs with ther corresponding GET parameters
     * 5. Calculate the MD5 Message Authentication Code.
     * 6. Print the form.
     */    
    function _on_render_payment_link()
    {
        $this->_amount_string = number_format($this->_amount, 2, '.', '');
        $this->_calculate_stamp();
        $this->_calculate_reference();
        $this->_mask_text_strings();
        $this->_generate_return_urls();
        
        // Be sure to have everything initialized before calculating the MAC.
        $this->_calculate_mac();
        
        // Generate the payment link now.
        $this->_print();
    }

    /**
     * Processes the payment. Incomplete request arrays always result in a critical 
     * error, aborting execution.
     */
    function _on_process_payment()
    {
        if (! array_key_exists('net_nemein_payment_state', $_REQUEST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payments (nordea handler): The request variable net_nemein_payment_state was missing, this is critical.');
            // This will exit.
        }
        
        switch ((int) $_REQUEST['net_nemein_payment_state'])
        {
            case NET_NEMEIN_PAYMENT_OK:
                $this->_process_ok();
                break;
                
            case NET_NEMEIN_PAYMENT_CANCELLED:
                $this->_process_cancel();
                break;
                
            case NET_NEMEIN_PAYMENT_REJECTED:
                $this->_process_reject();
                break;
                
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "n.n.payments (nordea handler): The state return code {$_REQUEST['net_nemein_payment_state']} is unknown, this is critical.");
                // This will exit.                
        }
    }
    
    /**
     * The payment has been successful.
     */
    function _process_ok()
    {
        $this->_read_response();
        
        $message = "Payment successful:\n";
        $message .= "Internal Payment code: {$this->_reference}\n";
        $message .= "Payment reference: {$this->_payment_reference}\n";
        $message .= "Bank's payment code: {$this->_response_payment_code}";
        
        $this->machine_response['SOLOPMT_RETURN_VERSION'] = $this->_version;
        $this->machine_response['SOLOPMT_RETURN_STAMP'] = $this->_reference;
        $this->machine_response['SOLOPMT_RETURN_REF'] = $this->_payment_reference;
        $this->machine_response['SOLOPMT_RETURN_PAID'] = $this->_response_payment_code;
        $this->machine_response['SOLOPMT_RETURN_MAC'] = $this->_mac;
        
        $this->message = $message;        
        $this->result = NET_NEMEIN_PAYMENT_OK;
        $this->machine_response['result'] = $this->result;
    }
    
    /**
     * Reads and verifies the response received from the Nordea server.
     * If the MAC check fails, execution halts.
     */
    function _read_response()
    {
        if (   ! array_key_exists('SOLOPMT_RETURN_VERSION', $_REQUEST)
            || ! array_key_exists('SOLOPMT_RETURN_STAMP', $_REQUEST)
            || ! array_key_exists('SOLOPMT_RETURN_REF', $_REQUEST)
            || ! array_key_exists('SOLOPMT_RETURN_PAID', $_REQUEST)
            || ! array_key_exists('SOLOPMT_RETURN_MAC', $_REQUEST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "n.n.payments (nordea handler): The response received was incomplete, this is critical.");
            // This will exit.
        }
        
        if ($this->_version != $_REQUEST['SOLOPMT_RETURN_VERSION'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "n.n.payments (nordea handler): The response version number is not supported, this is critical.");
            // This will exit.
        }
        $this->_stamp = $_REQUEST['SOLOPMT_RETURN_STAMP'];
        $this->_payment_reference = $_REQUEST['SOLOPMT_RETURN_REF'];
        $this->_response_payment_code = $_REQUEST['SOLOPMT_RETURN_PAID'];
        $this->_mac = $_REQUEST['SOLOPMT_RETURN_MAC'];
        $this->_reference = substr($this->_payment_reference, 0, -1 * (strlen($this->__payment_reference_suffix) + 1));
        
        $this->_check_mac();
        
        // Payment authentication succeeded.
    }
    
    /**
     * The payment has been cancelled by the user.
     */
    function _process_cancel()
    {
        $this->message = "The user has cancelled the payment.";
        $this->result = NET_NEMEIN_PAYMENT_CANCELLED;
        $this->machine_response['result'] = $this->result;
    }
   
    /**
     * The payment has been rejected by the bank.
     */
    function _process_reject()
    {
        $this->message = "The Payment has been rejected by the service provider.";
        $this->result = NET_NEMEIN_PAYMENT_REJECTED;
        $this->machine_response['result'] = $this->result;
    }
    
    /**
     * This helper prints the actual form to stdout.
     * 
     * @access private
     */
    function _print()
    {
        echo "<form method='post' action='https://solo3.nordea.fi/cgi-bin/SOLOPM01'>\n";
        echo "<input type='hidden' name='SOLOPMT_VERSION' value='{$this->_version}' />\n";
        echo "<input type='hidden' name='SOLOPMT_STAMP' value='{$this->_stamp}' />\n";
        echo "<input type='hidden' name='SOLOPMT_RCV_ID' value='{$this->_handler_config['SOLOPMT_RCV_ID']}' />\n";
        if ($this->_rcv_account != '')
        {
            $string = substr($this->_rcv_account, 0, 15);
            echo "<input type='hidden' name='SOLOPMT_RCV_ACCOUNT' value='{$string}' />\n";
        }
        if ($this->_rcv_name != '')
        {
            $string = substr($this->_rcv_name, 0, 30);
            echo "<input type='hidden' name='SOLOPMT_RCV_NAME' value='{$string}' />\n";
        }
        echo "<input type='hidden' name='SOLOPMT_LANGUAGE' value='{$this->_language}' />\n";
        echo "<input type='hidden' name='SOLOPMT_AMOUNT' value='{$this->_amount_string}' />\n";
        echo "<input type='hidden' name='SOLOPMT_REF' value='{$this->_payment_reference}' />\n";
        echo "<input type='hidden' name='SOLOPMT_DATE' value='{$this->_date}' />\n";
        if ($this->_message != '')
        {
            $string = substr($this->_message, 0, 234);
            echo "<input type='hidden' name='SOLOPMT_MSG' value='{$string}' />\n";
        }
        echo "<input type='hidden' name='SOLOPMT_RETURN' value='{$this->_ok_url}' />\n";
        echo "<input type='hidden' name='SOLOPMT_CANCEL' value='{$this->_cancel_url}' />\n";
        echo "<input type='hidden' name='SOLOPMT_REJECT' value='{$this->_rejected_url}' />\n";
        echo "<input type='hidden' name='SOLOPMT_MAC' value='{$this->_mac}' />\n";
        echo "<input type='hidden' name='SOLOPMT_CONFIRM' value='{$this->_payment_confirmation}' />\n";
        echo "<input type='hidden' name='SOLOPMT_KEYVERS' value='{$this->_key_version}' />\n";
        echo "<input type='hidden' name='SOLOPMT_CUR' value='{$this->_currency}' />\n";
        
        $logo = MIDCOM_STATIC_URL . '/net.nemein.payment/handler/nordea/nordea.png';
        echo "<input align='middle' type='image' src='{$logo}' value='Nordea' name='Nordea' />\n";
        
        echo "</form>\n";
    }
    
    /**
     * This helper generates the three (ok, cancel, reject) return urls for the
     * current payment request. They are generated using the supplied return
     * URL and pass a parameter by HTTP GET so that the result can be processed
     * accordingly.
     * 
     * It can deal with URLs that already have GET parameters without problems.
     * 
     * @access private
     */
    function _generate_return_urls()
    {
     	$this->_ok_url = "{$this->_return_url}&net_nemein_payment_state=" . NET_NEMEIN_PAYMENT_OK;
        $this->_cancel_url = "{$this->_return_url}&net_nemein_payment_state=" . NET_NEMEIN_PAYMENT_CANCELLED;
        $this->_rejected_url = "{$this->_return_url}&net_nemein_payment_state=" . NET_NEMEIN_PAYMENT_REJECTED;
    }
    
    /**
     * This helper masks text strings (currently only the user message
     * is affected) so that it does not interfere with the surrounding HTML.
     * 
     * @access private
     */
    function _mask_text_strings()
    {
        $this->_message = htmlspecialchars($this->_message);
    }
    
    /**
     * Sync the Remote UI language with the language set by MidCOM.
     * 
     * @access private
     */
    function _detect_language()
    {
        switch ($this->_i18n->get_current_language())
        {
            case 'fi':
            case 'fi_tampere':
                $this->_language = 1;
                break;
                
            case 'sv':
                $this->_language = 2;
                break;
                
            default:
                // Unsupported, fall back to English.
                $this->_language = 3;
                break;
        }
    }
    
    /**
     * Calculates the stamp for this payment, which has to be unique.
     * It is constructed using the Order ID, current timestamp and the reference
     * suffix.
     * 
     * @access private
     */
    function _calculate_stamp()
    {
        $stamp = (string) time();
        $stamp .= $this->_reference;
        $stamp .= $this->_payment_reference_suffix;
        $this->_stamp = substr($stamp, 0, 20);
    }
    
    /**
     * Calculates the message authentication code for the current request data.
     * 
     * @access private
     */
    function _calculate_mac()
    {
        $mac_string =  "{$this->_version}&";
        $mac_string .= "{$this->_stamp}&";
        $mac_string .= "{$this->_handler_config['SOLOPMT_RCV_ID']}&";
        $mac_string .= "{$this->_amount_string}&";
        $mac_string .= "{$this->_payment_reference}&";
        $mac_string .= "{$this->_date}&";
        $mac_string .= "{$this->_currency}&";
        $mac_string .= "{$this->_handler_config['MAC_SEED']}&";
        $this->_mac = strtoupper(md5($mac_string));
    }
        
    /**
     * Calculates the message authentication code from the return information
     * and triggers an error, if it does not match the one supplied in the
     * response (as stored in $_mac).
     * 
     * @access private
     */
    function _check_mac()
    {
        $mac_string =  "{$this->_version}&";
        $mac_string .= "{$this->_stamp}&";
        $mac_string .= "{$this->_payment_reference}&";
        $mac_string .= "{$this->_response_payment_code}&";
        $mac_string .= "{$this->_handler_config['MAC_SEED']}&";
        $mac = md5($mac_string);
        if (strtoupper($mac) != strtoupper($this->_mac))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "n.n.payment (nordea handler): The returned Message Authentication Code was invalid, assuming tampreing with the request, aborting.");
            // This will exit.
        }
    }
       
    /**
     * Calculates the payment reference number by adding the check
     * digit as described in Chapter 3.2, Field number 8 in the Payment
     * specification.
     * 
     * If set, the reference suffix is appended before check digit calculation.
     * 
     * @see $_payment_reference
     * @access private
     */
    function _calculate_reference()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Calcualting the reference with check digit from '{$this->_reference}'");
        
        $multipliers = Array(7, 3, 1);
        $result = Array();
        $sum = 0;
        $currmult = 0;
        $reference_string = (string) $this->_reference;
        $reference_string .= $this->_payment_reference_suffix;
        for ($i = strlen($reference_string) - 1; $i >= 0; $i--)
        {
            $digit = $reference_string{$i};
            if (! is_numeric($digit))
            {
                debug_add("The character {$i}='{$digit}' was not numeric, skipping it.", MIDCOM_LOG_WARN);
                continue;
            }
            $digit = (int) $digit;
            $sum += $digit * $multipliers[$currmult];
            $currmult = ($currmult == 2) ? 0 : $currmult + 1;
        }
        // Get the difference to the next highest ten
        $nextten = (((int) ($sum / 10)) + 1) * 10;
        $checkdigit = $nextten - $sum;
        if ($checkdigit == 10)
        {
            $checkdigit = 0;
        }
        debug_add("Computed check digit is {$checkdigit}, nextten was {$nextten}, sum was {$sum}");
        
        $this->_payment_reference = "{$this->_reference}{$this->_payment_reference_suffix}{$checkdigit}";
        
        debug_pop();
    }
} 

?>