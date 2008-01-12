<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_transporter extends midcom_baseclasses_components_purecode
{
    /**
     * The subscription object the transporter has been instantiated for
     *
     * @var midcom_helper_replicator_subscription_dba
     * @access protected
     */
    var $subscription;
    
    /**
     * Possible processing error.
     *
     * @var string
     * @access protected
     */
    var $error = '';

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     *
     * @param midcom_helper_replicator_subscription_dba $subscription Subscription
     */
    function midcom_helper_replicator_transporter($subscription)
    {
         $this->_component = 'midcom.helper.replicator';
         
         $this->subscription = $subscription;
         
         parent::midcom_baseclasses_components_purecode();
    }
    
    /**
     * This is a static factory method which lets you dynamically create transporter instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * On any error (class not found etc.) the factory method will call generate_error.
     *
     * <b>This function must be called statically.</b>
     *
     * @param midcom_helper_replicator_subscription_dba $subscription Subscription
     * @return midcom_helper_replicator_transporter A reference to the newly created transporter instance.
     * @static
     */
    function & create($subscription)
    {
        $type = $subscription->transporter;
        $filename = MIDCOM_ROOT . "/midcom/helper/replicator/transporter/{$type}.php";
        
        if (!file_exists($filename))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested transporter file {$type} is not installed.");
            // This will exit.
        }
        require_once($filename);

        $classname = "midcom_helper_replicator_transporter_{$type}";        
        if (!class_exists($classname))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested transporter class {$type} is not installed.");
            // This will exit.
        }
        
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname($subscription);
        return $class;
    }
    
    function list_transports()
    {
        return array
        (
            'email' => $_MIDCOM->i18n->get_string('email transport', 'midcom.helper.replicator'),
            'archive' => $_MIDCOM->i18n->get_string('file archive transport', 'midcom.helper.replicator'),
            'http' => $_MIDCOM->i18n->get_string('HTTP POST transport', 'midcom.helper.replicator'),
        );
    }

    /**
     * Processes incoming items (subclasses must override this method)
     *
     * The items array is in the order given by the exporter and transport must preserve
     * the order in whatever way is best for it (but importer needs the entries in order as well)
     *
     * The method must return false on critical error preventing the processing of the items
     *
     * When the transport has successfully processed an item it must unset it from the list it receives,
     * this way the queue manager knows to remove said item from the queue, any items left in the list
     * will be retried on next run.
     *
     * @todo How to let transport specify retry interval
     *
     */
    function process(&$items)
    {
        // Subclasses must override this method
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('This method MUST be overridden by the subclass', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }
    
    /**
     * This method allows transporters to tell what they're doing in the subscription list.
     */
    function get_information()
    {
        return '';
    }

    /**
     * This method allows transporters to muck the UI DM2 schema to add their own options there
     */
    function add_ui_options(&$schema)
    {
        return;
    }

}
?>
