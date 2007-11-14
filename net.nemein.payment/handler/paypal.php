<?php
/**
 * @package net.nemein.payment
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PayPal Frontend for FORM based payments (does not use Web Services).
 *
 * Supports both encrypted and unencrypted payments, although the latter is only
 * recommended for testing purposes. Each transaction is using a payment authentication
 * code in addition to the encryption which does its best to secure a payment against
 * fraud. Note, however, that this is no replacement for the security provided by
 * fully encrypted payments.
 *
 * The handler supports only "Buy Now" buttons at this time, nothing else. Special
 * features like questions, undefined quantities etc. are not supported. This handler
 * is just there for basic payment.
 *
 * Important note: This handler does <i>not</i> provide any PayPal certificates within
 * its distribution. You could never be sure if this certifciate would be entirely
 * valid or if you would be suspectible to a man-in-the-middle attack. Always get
 * the current certificates from the PayPal Website and ensure their validity.
 *
 * To provide limited security even in unencrypted mode, the payment reference and the
 * amount are both secured using a MD5 Hash with a seed either auto-created based
 * on the current install or set by the user in the configuration.
 *
 * <b>Required configuration directives:</b>
 *
 * - <i>account:</i> The login mail address of the paypal account to use.
 * - <i>pdt_id_token:</i> This is the authentication token required for PDT access.
 *   It is obtained from the PayPal Website. See the Order Management Integration
 *   Guide.
 *
 * <b>Optional configuration directives:</b>
 *
 * - <i>additional_options:</i> You can set any additional option into the
 *   request at this point. This is useful f.x. for adding logos to the paypal
 *   pages etc. See the "Website Payments Standard Checkout Integration Guide"
 *   found on the PayPal Developer Website. Note, that not all options can
 *   be set freely, several options are fixed by the system (see below).
 *   This is a list of key/value pairs. Defaults to an empty list.
 * - <i>currency:</i> The currency to use. This must be a valid PayPal Currency code.
 *   It defaults to "EUR".
 * - <i>paypal_cert:</i> Path to the Paypal X.509 certificate key file to use for
 *   encryption. If unset, no encrypted communication can be done.
 * - <i>local_key:</i> Path to the user X.509 key file to use for signing. If unset,
 *   no encrypted communication can be done.
 * - <i>local_cert:</i> Path to the user X.509 certificate file to use for signing.
 *   If unset, it defaults to local_key.
 * - <i>local_key_password:</i> If your private key requires a passphrase, put it into
 *   this configuration option.
 * - <i>local_cert_id:</i> The certificate ID assigned by PayPal. Must be set for encryption
 *   to work.
 * - <i>backend:</i> The paypal service backend to use, this can be either "live",
 *   "sandbox" or a fully qualified URL. It defaults to "live".
 * - <i>authentication_seed:</i> This is a custom seed used in securing the amount
 *   and payment reference fields from alteration. If this is not set, the GUID
 *   of the current host is used. Note, that this is not recommended, you should always
 *   add a random phrase here, with at least 15 to 20 characters in length.
 *
 * <b>Optional paypal fields which cannot be used</b>
 *
 * - amount, item_*, quantity, undefined_quantity, currency_code
 * - return, cancel_return, rm
 * - charset
 * - custom, invoice
 *
 * <b>System Requirements:</b>
 *
 * PHP must be compiled with OpenSSL support for the payment encryption to work correctly.
 * PHP 4.3 or higher is required as well to support the ssl:// target for fsockopen.
 *
 * <b>PayPal configuration:</b>
 *
 * You have to enable the Auto Return feature of the Paypal Account. The URL you are setting
 * there is irrelevant, as the generated request to paypal overrides it. It is important
 * that you activate the Payment Data Transfer option and put the identity code to into
 * the pdt_id_token config option.
 *
 *
 * @see developer.paypal.com
 * @package net.nemein.payment
 */
class net_nemein_payment_handler_paypal extends net_nemein_payment_handler
{
    /**
     * The PayPal account to use (this is an E-Mail address).
     *
     * @var string
     * @access private
     */
    var $_account = null;

    /**
     * The PayPal identity token required for PDT access.
     *
     * @var string
     * @access private
     */
    var $_pdt_id_token = null;

    /**
     * The currency to use.
     *
     * @var string
     * @access private
     */
    var $_currency = 'EUR';

    /**
     * Additional options to pass to PayPal, see class introduction for
     * details.
     *
     * @var Array
     * @access private
     */
    var $_additional_options = Array();

    /**
     * The path to the X.509 certificate to use to encrypt the requests to
     * PayPal. This must be a valid certificate parameter for the openssl
     * PHP module.
     *
     * Usually you'll want file://... URL here.
     *
     * @var string
     * @access private
     */
    var $_paypal_cert = null;

    /**
     * The path to the X.509 certificate used to sign the requests to PayPal.
     * This must be a valid certificate parameter for the openssl
     * PHP module.
     *
     * It defaults to the resource given in _local_key if omitted, thus allowing
     * you to have both the public certificate and private key in the same file.
     *
     * Usually you'll want file://... URL here.
     *
     * @var string
     * @access private
     */
    var $_local_cert = null;

    /**
     * The path to the X.509 private key used to sign the requests to PayPal.
     * This must be a valid key parameter for the openssl
     * PHP module.
     *
     * Usually you'll want file://... URL here.
     *
     * @var string
     * @access private
     */
    var $_local_key = null;

    /**
     * The Certificate ID assigend by PayPal when uploading the certificate.
     *
     * @var string
     * @access private
     */
    var $_local_cert_id = null;

    /**
     * Contains the password required to access the local key, if set.
     *
     * @var string
     * @access private
     */
    var $_local_key_password = null;

    /**
     * The PayPal Backend to use, this is either 'live', 'sandbox' or a full
     * URL.
     *
     * @var string
     * @access private
     */
    var $_backend = 'live';

    /**
     * This variable holds the arguments that should go into the request. It is
     * build during the payment rendering and is rendered encrypted if possible.
     *
     * It holds valid key/value pairs used for the hidden HTML form elements.
     *
     * @var Array
     * @access private
     */
    var $_request_args = Array();

    /**
     * The request URL derived from the "backend" variable. This is always a full
     * URL.
     *
     * @var string
     * @access private
     */
    var $_request_url = null;

    /**
     * The seed used to secure the MD5 sum of the payment reference and amount.
     *
     * @var string
     * @access private
     */
    var $_authentication_seed = null;

    /**
     * Constructor, relays to base class.
     *
     * @param Array $config Configuration data.
     */
    function net_nemein_payment_handler_paypal($config)
    {
        parent::net_nemein_payment_handler($config);

        if (! array_key_exists('account', $config))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must specify the PayPal account to use the PayPal payment backend.');
            // This will exit.
        }
        $this->_account = $config['account'];

        if (! array_key_exists('pdt_id_token', $config))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must specify the PayPal PDT ID Token to use the PayPal payment backend.');
            // This will exit.
        }
        $this->_pdt_id_token = $config['pdt_id_token'];

        if (   array_key_exists('paypal_cert', $config)
            && array_key_exists('local_key', $config)
            && array_key_exists('local_cert_id', $config))
        {
            $this->_paypal_cert = $config['paypal_cert'];
            $this->_local_key = $config['local_key'];
            $this->_local_cert = (array_key_exists('local_cert', $config))
                ? $config['local_cert'] : $this->_local_key;
            $this->_local_key_password = (array_key_exists('_local_key_password', $config))
                ? $config['_local_key_password'] : null;
            $this->_local_cert_id = $config['local_cert_id'];
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('You have not set at any X.509 certificates/keys to use for encrypted communication. This is strongly discouraged.',
                MIDCOM_LOG_WARN);
            debug_pop();
        }

        if (array_key_exists('additional_options', $config))
        {
            if (is_array($config['additional_options']))
            {
                $this->_additional_options = $config['additional_options'];
            }
            else
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('The additional_options configuration directive is no array, skipping it.',
                    MIDCOM_LOG_WARN);
                debug_pop();
            }
        }
        if (array_key_exists('backend', $config))
        {
            $this->_backend = $config['backend'];
        }
        if (array_key_exists('currency', $config))
        {
            $this->_currency = $config['currency'];
        }
        if (array_key_exists('authentication_seed', $config))
        {
            $this->_authentication_seed = $config['authentication_seed'];
        }
        else
        {
            $host = new midcom_db_host($_MIDGARD['host']);
            $this->_authentication_seed = $host->guid;
        }

        // Validate dependencies:
        if (   version_compare(phpversion(), '4.3.0', '<')
            || ! function_exists('openssl_pkcs7_sign'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payment (paypal handler): PHP 4.3 with openssl support is required to use this handler.');
            // This will exit.
        }
    }

    /**
     * Renders a plain unencrypted form for payment.
     */
    function _render_plain()
    {
        echo "<form action='{$this->_request_url}' method='post'>\n";

        foreach ($this->_request_args as $name => $value)
        {
            $name= htmlspecialchars($name);
            $value = htmlspecialchars($value);
            echo "<input type='hidden' name='{$name}' value='{$value}' />\n";
        }

        echo "<input type='image' src='http://images.paypal.com/images/x-click-but01.gif' " .
            "name='submit' />\n";
        echo "</form>\n";
    }

    /**
     * Determines the actual URL to send the request to.
     */
    function _prepare_request_url()
    {
        if ($this->_backend == 'live')
        {
            $this->_request_url = 'https://www.paypal.com/cgi-bin/webscr';
        }
        else if ($this->_backend == 'sandbox')
        {
            $this->_request_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Custom backends are not yet implemented.');
            // This will exit
        }
    }

    /**
     * Determines the server name to send PDT requests to.
     */
    function _get_pdt_server()
    {
        if ($this->_backend == 'live')
        {
            return 'ssl://www.paypal.com';
        }
        else if ($this->_backend == 'sandbox')
        {
            return 'ssl://www.sandbox.paypal.com';
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Custom backends are not yet implemented.');
            // This will exit
        }
    }

    /**
     * This function computes the MD5 hash which secures the payment reference and
     * the amount.
     *
     * @return string MD5 digest
     */
    function _get_message_digest()
    {
        $mac_string = $this->_reference
            . number_format($this->_amount, 2, '.', '')
            . $this->_authentication_seed;
        return md5($mac_string);
    }


    /**
     * Prepares the request_args array to add the actual data. The array is initialized
     * with the additional options field (thus any restricted option will be overwritten
     * later) and is completed with the actual payment information.
     *
     * If available, the current users data is added to the request so that the paypal
     * account signup is as short as possible.
     */
    function _prepare_request_args()
    {
        $this->_request_args = $this->_additional_options;
        $this->_request_args['cmd'] = '_xclick';
        $this->_request_args['business'] = $this->_account;
        $this->_request_args['return'] = "{$this->_return_url}&net_nemein_payment_state=" . NET_NEMEIN_PAYMENT_OK;
        $this->_request_args['cancel_return'] = "{$this->_return_url}&net_nemein_payment_state=" . NET_NEMEIN_PAYMENT_CANCELLED;
        $this->_request_args['undefined_quantity'] = 0;
        $this->_request_args['invoice'] = $this->_reference;
        $this->_request_args['item_name'] = $this->_message;
        $this->_request_args['quantity'] = 1;
        $this->_request_args['no_shipping'] = 1;
        $this->_request_args['rm'] = 2;
        $this->_request_args['currency_code'] = $this->_currency;
        $this->_request_args['amount'] = number_format($this->_amount, 2, '.', '');
        $this->_request_args['charset'] = $_MIDCOM->i18n->get_current_charset();
        $this->_request_args['custom'] = $this->_get_message_digest();

        if ($_MIDCOM->auth->user)
        {
            $person = $_MIDCOM->auth->user->get_storage();
            if ($person)
            {
                // Add data for account signup prepopulation. This adapts the cmd targets.
                $this->_request_args['redirect_cmd'] = $this->_request_args['cmd'];
                $this->_request_args['cmd'] = '_ext-enter';

                $this->_request_args['email'] = $person->email;
                $this->_request_args['first_name'] = $person->firstname;
                $this->_request_args['last_name'] = $person->lastname;

                $address = explode("\n", $person->street);
                $this->_request_args['address1'] = $address[0];
                $this->_request_args['address2'] = (count($address) > 1) ? $address[1] : '';

                $this->_request_args['city'] = $person->city;
                $this->_request_args['zip'] = $person->postcode;

                $this->_request_args['lc'] = $_MIDCOM->i18n->get_current_language();

                // homephone handphone workphone homepage
            }
        }
    }


    /**
     * Process the payment and populate the payment status variables.
     *
     * Unless we have a cancelled request, we just verify the basics and go on in _process_ok.
     */
    function _on_process_payment()
    {
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('$_REQUEST:', $_REQUEST);
        debug_print_r('$_GET:', $_GET);
        debug_print_r('$_POST:', $_POST);
        debug_pop();
        */

        if (! array_key_exists('net_nemein_payment_state', $_REQUEST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payments (paypal handler): The request variable net_nemein_payment_state was missing, this is critical.');
            // This will exit.
        }

        switch ((int) $_REQUEST['net_nemein_payment_state'])
        {
            case NET_NEMEIN_PAYMENT_OK:
                $this->_process_ok();
                break;

            case NET_NEMEIN_PAYMENT_CANCELLED:
                $this->message = "The user has cancelled the payment.";
                $this->result = NET_NEMEIN_PAYMENT_CANCELLED;
                $this->machine_response['result'] = $this->result;
                break;

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "n.n.payments (paypal handler): The state return code {$_REQUEST['net_nemein_payment_state']} is unknown, this is critical.");
                // This will exit.
        }
    }


    /**
     * Verifies the response by using PayPals Payment Data Transfer (PDT) service.
     * Unfortunalety, I could so far not find any instructuions how the actual response
     * could be verified directly, despite of the fact that there is a verify_sign
     * digital signature in the actual response. If anybody finds some docs about this, please
     * contact me.
     *
     * This function also validates the message digest passed in the custom variable of
     * the PayPal response to protect against alterations of the price or the reference.
     */
    function _process_ok()
    {
        $this->_read_response();
        $this->_validate_against_pdt();
    }

    /**
     * This function validates the current response data using the PDT service of PayPal.
     * Appearantly, there is no way to really authenticate an incoming payment request
     * short of this full blown solution.
     *
     * Requires _read_response to be called before it.
     *
     * If validation fails, generate_error is called. On success, the response code is set
     * accordingly to the PDT response.
     */
    function _validate_against_pdt()
    {
        $pdt_request = 'cmd=_notify-synch';
        $pdt_request .= "&tx={$_REQUEST['tx']}";
        $pdt_request .= "&at={$this->_pdt_id_token}";

        $pdt_result = $this->_execute_pdt_request($pdt_request);

        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('PDT request result:', $pdt_result);
        debug_pop();

        if ($pdt_result['response'] != 'SUCCESS')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "n.n.payments (paypal handler): The Payment Data Tranfer request failed, supplied transaction is invalid.");
            // This will exit.
        }

        $pdt_data = $pdt_result['data'];
        $this->machine_response = $pdt_data;
        $this->_reference = $pdt_data['invoice'];

        // Validate current request against PDT data and check our message digest
        if ($this->_amount > ((double) $pdt_data['mc_gross']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "n.n.payments (paypal handler): The Payment Data Tranfer validation failed, amount is not large enough.");
            // This will exit.
        }
        if ($pdt_data['custom'] != $this->_get_message_digest())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payments (paypal handler): The message digest was not valid, aborting transaction.');
            // This will exit.
        }

        // Check the payment status.
        switch (strtolower($pdt_data['payment_status']))
        {
            case 'completed':
            case 'processed':
                $this->result = NET_NEMEIN_PAYMENT_OK;
                break;

            case 'pending':
                $this->result = NET_NEMEIN_PAYMENT_OK_BUT_PENDING;
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r("Payment reported pending because of '{$pdt_data['pending_reason']}'", MIDCOM_LOG_WARN);
                debug_pop();
                break;

            case 'in-process':
                $this->result = NET_NEMEIN_PAYMENT_OK_BUT_PENDING;
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('Payment processing not yet complete.', MIDCOM_LOG_WARN);
                debug_pop();
                break;

            default:
                $this->result = NET_NEMEIN_PAYMENT_REJECTED;
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r("Payment was rejected: '{$pdt_data['payment_status']}'", MIDCOM_LOG_ERROR);
                debug_pop();
                break;

        }


    }

    /**
     * Executes a PDT request to the server determined by the _get_pdt_server helper.
     * The request is always made to /cgi-bin/webscr.
     *
     * The response array contains these fields:
     *
     * - 'response' is the response code set by PayPal. This should normally be 'SUCCESS'.
     * - 'data' is the key/value list holding the data about the requested transaction. It
     *   is already URL decoded.
     *
     * Any HTTP Error at this Point will trigger generate_error. It usually means that Paypal
     * is offline, in which case it would be a huge coincidence that paypal goes down between
     * the user completing the transaction and paypal sending him back to us.
     *
     * @var string $request The Request to send to the Server, it must be x-www-form-urlencoded.
     * @return Array The processed Array.
     */
    function _execute_pdt_request($request)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= 'Content-Length: ' . strlen($request) . "\r\n\r\n";

        $full_request = $header . $request;

        debug_print_r('Sending this request to ' . $this->_get_pdt_server() . ':443', $full_request);

        $errno = 0;
        $errstr = null;
        $handle = fsockopen ($this->_get_pdt_server(), 443, $errno, $errstr, 30);

        if (! $handle)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to connect to the PayPal Server to validate the payment: {$errstr} ({$errno}).");
            // This will exit.
        }

        // Send the Request
        fputs ($handle, $full_request);

        // Read the response, skip the header.
        $result = '';
        $headerdone = false;
        while (!feof($handle))
        {
            $line = fgets ($handle, 1024);
            if (strcmp($line, "\r\n") == 0)
            {
                // we got the header now.
                $headerdone = true;
            }
            else if ($headerdone)
            {
                $result .= $line;
            }
        }

        fclose($handle);

        // Parse the data.
        $data_lines = explode("\n", $result);
        $response = array_shift($data_lines);

        $data = Array();
        foreach ($data_lines as $line_number => $line)
        {
            if ($line)
            {
                $tmp = explode('=', $line);
                if (count($tmp) > 1)
                {
                    $key = $tmp[0];
                    $value = $tmp[1];
                    $data[urldecode($key)] = urldecode($value);
                }
                else
                {
                    $data[$line_number] = urldecode($line);
                }
            }
        }

        // Convert the data into the site charset. Only done if a charset
        // entry was found. Naturally, only values are converted.
        if (array_key_exists('charset', $data))
        {
            $src_charset = $data['charset'];
            foreach ($data as $key => $value)
            {
                $data[$key] = $_MIDCOM->i18n->convert_to_current_charset($value, $src_charset);
            }
            $data['charset'] = $_MIDCOM->i18n->get_current_charset();
        }

        debug_pop();

        return Array('response' => $response, 'data' => $data);
    }


    /**
     * Reads all applicable information from the HTTP Request into the class. The
     * message digest is not yet checked the PDT data is required to do that.
     *
     * generate_error will be called if anything suspcious is found.
     */
    function _read_response()
    {
        if (   ! array_key_exists('amt', $_REQUEST)
            || ! array_key_exists('tx', $_REQUEST)
            || ! array_key_exists('cm', $_REQUEST)
            || ! array_key_exists('sig', $_REQUEST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payments (paypal handler): The response received was incomplete, this is critical, ' .
                'the request might have been tampered with. We do not continue for security reasons.');
            // This will exit.
        }

        $this->_amount = (double) $_REQUEST['amt'];
    }

    /**
     * Renders the payment link.
     *
     * The actual form rendering is delegated to helper functions which handle the
     * actual rendering, depending on the encryption settings.
     */
    function _on_render_payment_link()
    {
        $this->_prepare_request_args();
        $this->_prepare_request_url();

        if ($this->_paypal_cert)
        {
            $this->_render_encrypted();
        }
        else
        {
            $this->_render_plain();
        }
    }

    /**
     * Renders an encrypted request for paypal. Called if the corresponding certificates
     * are set.
     *
     * The general code is taken from the PayPal SDK, although it has been revised a bit to
     * match MidCOM coding standards.
     *
     * Errors will trigger generate_error.
     */
    function _render_encrypted()
    {
        // Implementation note: All errors from file_put_contents are hidden.
        // It seems that it cannot handle UTF-8 correctly, as it throws error messages like this:
        // | Warning: file_put_contents() Only 2384 of 2174 bytes written, possibly out of free disk space.

        debug_push_class(__CLASS__, __FUNCTION__);

        // Prepare temporary variables
        $local_key = ($this->_local_key_password)
            ? Array($this->_local_key, $this->_local_key_password)
            : $this->_local_key;

        $tmpfile_in = tempnam($GLOBALS['midcom_config']['midcom_tempdir'], 'net.nemein.payments.handler.paypal');
        $tmpfile_out = tempnam($GLOBALS['midcom_config']['midcom_tempdir'], 'net.nemein.payments.handler.paypal');

        // Write raw data
        $data = "cert_id={$this->_local_cert_id}\n";
        foreach ($this->_request_args as $name => $value)
        {
            $data .= "{$name}={$value}\n";
        }
        @file_put_contents($tmpfile_in, $data);

        debug_print_r('Data written:', $data);

        // Sign raw data
        $result = @openssl_pkcs7_sign($tmpfile_in, $tmpfile_out, $this->_local_cert, $local_key,
            Array(), PKCS7_BINARY);
        if (! $result)
        {
            @unlink($tmpfile_in);
            @unlink($tmpfile_out);
            debug_print_r('Local cert:', $this->_local_cert);
            debug_print_r('Local key:', $this->_local_key);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payment (PayPal handler): Failed to sign raw data: ' . openssl_error_string());
            // This will exit.
        }

        // Read signed data, prepare it, and write it out again to be encrypted.
        $data = file_get_contents($tmpfile_out);

        debug_print_r('Raw signed data', $data);

        if (! $data)
        {
            @unlink($tmpfile_in);
            @unlink($tmpfile_out);
            debug_print_r('Local cert:', $this->_local_cert);
            debug_print_r('Local key:', $this->_local_key);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payment (PayPal handler): Failed to sign raw data: Result file was empty.');
            // This will exit.
        }
        $data = explode("\n\n", $data);
        $data = base64_decode($data[1]);
        @file_put_contents($tmpfile_in, $data);

        debug_print_r('Signed data to encrypt:', $data);

        // Encrypt signed data
        $result = @openssl_pkcs7_encrypt($tmpfile_in, $tmpfile_out, $this->_paypal_cert,
            Array(), PKCS7_BINARY);
        if (! $result)
        {
            @unlink($tmpfile_in);
            @unlink($tmpfile_out);
            debug_print_r('PayPal cert:', $this->_paypal_cert);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payment (PayPal handler): Failed to encrypt signed data: ' . openssl_error_string());
            // This will exit.
        }

        // Read the encrypted data and prepare it to be sent to the browser.
        $data = file_get_contents($tmpfile_out);

        debug_print_r('Raw encrypted data', $data);

        if (! $data)
        {
            @unlink($tmpfile_in);
            @unlink($tmpfile_out);
            debug_print_r('PayPal cert:', $this->_paypal_cert);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'n.n.payment (PayPal handler): Failed to encrypt signed data: Result file was empty.');
            // This will exit.
        }

        $data = explode("\n\n", $data);
        $data = trim(str_replace("\n", '', $data[1]));
        $data = "-----BEGIN PKCS7-----{$data}-----END PKCS7-----";

        @unlink($tmpfile_in);
        @unlink($tmpfile_out);
        debug_pop();

        echo <<<EOF
<form action="{$this->_request_url}" method="post">
  <input type="hidden" name="cmd" value="_s-xclick" />
  <input type="hidden" name="encrypted" value="{$data}" />
  <input type='image' src='http://images.paypal.com/images/x-click-but01.gif' "name='submit' />
</form>
EOF;
    }

}

?>