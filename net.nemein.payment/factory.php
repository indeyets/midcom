<?php

/**
 * @package net.nemein.payment
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Payment handler factory class.
 *
 * ...
 *
 * @package net.nemein.payment
 */

class net_nemein_payment_factory extends midcom_baseclasses_components_purecode
{

    /**
     * The list of configured and constructed handlers. They are
     * indexed automatically.
     *
     * @var Array
     * @see net_nemein_payment_handler
     */
    var $handlers;

    /**
     * Constructor, initialize base class.
     *
     * Do not call this directly, this class is a singelton, use get_instance()
     * instead.
     *
     * @see get_instance()
     * @access private
     */
    function net_nemein_payment_factory ()
    {
        $this->_component = 'net.nemein.payment';
        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Parses the configuration and initializes all concrete handler classes.
     *
     * This is called during instance creation and should not be called form the
     * outside.
     *
     * @access private
     */
    function initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->handlers = Array();
        $definitions = $this->_config->get('handlers');
        foreach ($definitions as $name => $config)
        {
            debug_print_r("Processing handler {$name} using this configuration:", $config);

            $config['_name'] = $name;
            $config['_id'] = count($this->handlers);

            $filename = MIDCOM_ROOT . "/net/nemein/payment/handler/{$config['_handler']}.php";
            $classname = "net_nemein_payment_handler_{$config['_handler']}";

            if (! file_exists($filename))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "n.n.payments: The payment handler {$classname} was not found, we searched for {$filename}. This is critical.");
                // This will exit.
            }
            require_once($filename);

            $this->handlers[] = new $classname($config);
        }

        debug_print_r("Handler database after initialization:", $this->handlers);

        debug_pop();
    }

    /**
     * Renders a list of payment links for all configured handlers. The Return URL must
     * lead to a piece of code which calls process_payment.
     *
     * Any invalid parameter will call generate_error.
     *
     * ...
     *
     * @param double $amount The amount that needs to be deducted.
     * @param string $return_url The fully qualified URL to return to.
     * @param string $reference The Payment reference code.
     * @param string $message An optional message shown during payment processing.
     * @see net_nemein_payment_handler::render_payment_link()
     */
    function render_payment_links($amount, $return_url, $reference, $message = '')
    {
        echo "<ul class='net_nemein_payments_linklist'>\n";

        for ($i = 0; $i < count($this->handlers); $i++)
        {
            echo "<li>";
            $this->handlers[$i]->render_payment_link($amount, $return_url, $reference, $message);
            echo "</li>\n";
        }

        echo "</ul>\n";
    }

    /**
     * Processes the payment that has been invoked by a previous instance of this call.
     * The request parameter net_nemein_payment_id is used to identify the required
     * handler.
     *
     * It will return a reference to the actual handler which has processed the payment.
     *
     * @return net_nemein_payment_handler Handler subclass, which has processed the payment.
     * @see net_nemein_payment_handler::process_payment()
     */
    function & process_payment()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_print_r('Processing payment for this request:', $_REQUEST);

        if (! array_key_exists('net_nemein_payment_id', $_REQUEST))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                'n.n.payment: The request does not contain the key net_nemein_payment_id, this is critical, aborting.');
            // This will exit
        }

        $id = (int) $_REQUEST['net_nemein_payment_id'];
        if (! array_key_exists($id, $this->handlers))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                'n.n.payment: Handler #{$id} does not exist, this is critical, aborting.');
            // This will exit
        }

        $this->handlers[$id]->process_payment();

        return $this->handlers[$id];

        debug_pop();
    }

    /**
     * Singelton interface, returns the factory instance.
     *
     * @return net_nemein_payment_factory Factory instance
     */
    function & get_instance()
    {
        static $instance = null;
        if (is_null($instance))
        {
            $instance = new net_nemein_payment_factory();
            $instance->initialize();
        }
        return $instance;
    }

}

?>