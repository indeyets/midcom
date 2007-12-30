<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** This class requires the categorylister */
require_once(MIDCOM_ROOT . '/net/nehmer/marketplace/callbacks/categorylister.php');

/**
 * Marketplace entry submission handler.
 *
 * This handler class requireds sodo privileges to create new objects, as restrictions
 * are done arbitarily based on the existing type configuration.
 *
 * Registration is done in the following steps
 *
 * 1. (optional) Display a selection dialoge between ask and bid submission.
 *
 * 2. Display the data entry form. It will be pre-filled with the data from the
 *    current account, using the mapping currently present in the system.
 *
 * 3. If validation succeeds, create the new record and display a we're happy page
 *    along with a link to the entry.
 *
 * @package net.nehmer.marketplace
 */

class net_nehmer_marketplace_handler_submit extends midcom_baseclasses_components_handler
{
    /**
     * The category lister class instance.
     *
     * @var net_nehmer_marketplace_callbacks_categorylister
     * @access private
     */
    var $_category_lister = null;

    /**
     * The category key for which we create an entry.
     *
     * @var string
     * @access private
     */
    var $_category = null;

    /**
     * The field mpaaing list applicable to the current user account.
     *
     * @var Array
     * @access private
     */
    var $_mapping = null;

    /**
     * One of 'ask' or 'bid'.
     *
     * @var string
     * @access private
     */
    var $_mode = null;

    /**
     * The DM2 controller instance we use to operate the forms.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    /**
     * Account remoting interface, will be loaded by _load_account_remote, which might fail
     * to do so, in which case this variable stays null. The caller has to take care of this.
     *
     * @var net_nehmer_account_remote
     * @access private
     */
    var $_account_remote = null;

    /**
     * Used only on the thankyou page, this variable contains the newly created entry.
     *
     * @var net_nehmer_marketplace_entry
     * @access private
     */
    var $_entry = null;

    function net_nehmer_marketplace_handler_submit()
    {
        parent::midcom_baseclasses_components_handler();

        $this->_category_lister = new net_nehmer_marketplace_callbacks_categorylister();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['mode'] =& $this->_mode;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['entry'] =& $this->_entry;
        $this->_request_data['category_lister'] =& $this->_category_lister;
    }

    /**
     * Internal helper, populates the account remoting interface accordingly.
     * If the account_topic configuration variable is not set, the system tries
     * to auto-detect the account management topic using
     */
    function _load_account_remote()
    {
        $guid = $this->_config->get('account_topic');

		if (! $guid)
        {
            $tmp = midcom_helper_find_node_by_component('net.nehmer.account');
            if (! $tmp)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to locate a net.nehmer.account topic automatically. Continuing without account support.', MIDCOM_LOG_INFO);
                debug_pop();
                return;
            }
            $guid = $tmp[MIDCOM_NAV_GUID];
        }
        if (! $_MIDCOM->componentloader->load_graceful('net.nehmer.account'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to load net.nehmer.account. Continuing without account support.', MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }
        $interface =& $_MIDCOM->componentloader->get_interface_class('net.nehmer.account');
        $this->_account_remote = $interface->create_remote_controller($guid);
    }

    /**
     * Simple submission welcome page handler, computes the type list to
     * display a welcome page.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'net_nehmer_marketplace_entry');

        /*
        if ($handler_id == 'submit_welcome_mode')
        {
            $this->_mode = $args[0];
        }
        */

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get('submit new entry'));
        $this->_component_data['active_leaf'] = NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT;

        return true;
    }

    /**
     * Displays a welcome page with all types available for submission linked
     * accordingly.
     */
    function _show_welcome($handler_id, &$data)
    {
        /*
        if ($handler_id == 'submit_welcome_mode')
        {
            midcom_show_style('submit-welcome-mode');
        }
        else
        {
            midcom_show_style('submit-welcome');
        }
        */
        midcom_show_style('submit-welcome');
    }

    /* *
     * Validates the step1 handle arguments.
     * /
    function _can_handle_welcome($handler_id, $args, &$data)
    {
        if ($handler_id != 'submit_welcome_mode')
        {
            return true;
        }

        if (   $args[0] != 'ask'
            && $args[0] != 'bid')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'ask' or 'bid' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        return true;
    }
    */

    /**
     * Validates the step1 handle arguments.
     */
    function _can_handle_step1($handler_id, $args, &$data)
    {
        if (   $args[0] != 'ask'
            && $args[0] != 'bid')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'offfer' or 'application' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        return true;
    }

    /**
     * The step 1 handler displays and processes the new entry form. It will use a nullstorage
     * controller until the form has been validated and the new object has been created.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_step1($handler_id, $args, &$data)
    {
        // Type list is already computed at this point, this is done in can-handle.
        // Argumenst have been validated for their syntactical correctness as well,
        // so we can immediately start with the permission checks.
        $this->_mode = $args[0];

        $_MIDCOM->auth->require_user_do('midgard:create', null, 'net_nehmer_marketplace_entry');

        // We need to set this first, as NAP could get started up when trying to
        // auto-detect an account topic (thus loading the active leaf before it
        // gets set).
        $this->_component_data['active_leaf'] =
            ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_ASK : NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_BID;

        $this->_prepare_datamanager();
        $this->_process_datamanager();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get("submit {$data['mode']}"));
        $_MIDCOM->substyle_append($this->_config->get("{$this->_mode}_schema"));

        return true;
    }

    /**
     * Internal helper function: prepares a create controller instance and populates
     * its defaults from the current account using the _get_defaults_from_account helper.
     */
    function _prepare_datamanager()
    {
        $this->_controller = midcom_helper_datamanager2_controller::create('create');
        $this->_controller->load_schemadb($this->_config->get('schemadb'));
        $this->_controller->schemaname = $this->_config->get("{$this->_mode}_schema");
        $this->_controller->defaults = $this->_get_defaults_from_account();
        $this->_controller->callback_object =& $this;
        $this->_controller->initialize();
    }

    /**
     * This function processes the form. Cancellation redirect to the new entry page,
     * while save creates the entry and relocates to the thanks-very-much page.
     */
    function _process_datamanager()
    {
        switch ($this->_controller->process_form())
        {
            case 'cancel':
                $_MIDCOM->relocate('submit.html');
                // This will exit.

            case 'save':
                $this->_process_create_entry();
                $_MIDCOM->relocate("submit/thankyou.html");
                // This will exit.
        }
    }

    /**
     * DM2 creation controller callback. Creates a new entry, initializes it. The reference is stored
     * in the class and then returned to DM2.
     */
    function & dm2_create_callback (&$controller)
    {
        // Create a fresh storage object. We need sudo for this.
        $this->_entry = new net_nehmer_marketplace_entry();
        $this->_entry->ask = ($this->_mode == 'ask');
        $this->_entry->account = $_MIDCOM->auth->user->guid;
        $this->_entry->published = time();

        if (! $this->_entry->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_entry);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new job entry, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_entry;
    }

    /**
     * This function will post-process a newly created marketplace entry. It will put
     * the newly created record into a PHP session (URL args are not used here for security
     * reasons, as sensitive information could be included in the thank you page).
     */
    function _process_create_entry()
    {
        $topic = $this->_config->get('index_to');
        if (! $topic)
        {
            $topic = $this->_topic;
        }

        $indexer =& $_MIDCOM->get_service('indexer');
        net_nehmer_marketplace_entry::index($this->_controller->datamanager, $indexer, $topic);

        $session = new midcom_service_session();
        $session->set('entry', $this->_entry);
    }

    /**
     * Internal helper, computes the defaults for new job entries based on the settings
     * in the configuration.
     *
     * @return Array Defaults suitable to use with the DM2 instance.
     */
    function _get_defaults_from_account()
    {
        if (! $_MIDCOM->auth->user)
        {
            return Array();
        }

        // First, we get a prepared data list from the account class, we use the account
        // remoting interface for this.
        $this->_load_account_remote();
        if (! $this->_account_remote)
        {
            return Array();
        }
        $defaults = $this->_account_remote->get_defaults_from_account();

        $mapping_config = $this->_config->get('mapping');
        $type = $this->_account_remote->get_account_type();

        if (!empty($type) && array_key_exists($type, $mapping_config))
        {
            $mapping = $mapping_config[$type];
        }
        else
        {
            $mapping = $mapping_config['default'];
        }

        foreach ($mapping as $name => $sources)
        {
            if (is_array($sources))
            {
                $tmp = '';
                foreach ($sources as $source)
                {
                    if (array_key_exists($source, $defaults))
                    {
                        $tmp .= "{$defaults[$source]} ";
                    }
                }
                $defaults[$name] = trim($tmp);
            }
            else
            {
                if (! array_key_exists($sources, $defaults))
                {
                    $defaults[$name] = '';
                }
                else
                {
                    $defaults[$name] = $defaults[$sources];
                }
            }
        }
        return $defaults;
    }

    /**
     * Calls a simple style element which shows the form.
     */
    function _show_step1($handler_id, &$data)
    {
        midcom_show_style('submit-step1');
    }

    /**
     * Displays the everything's happy message.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_thankyou($handler_id, $args, &$data)
    {
        $session = new midcom_service_session();
        $this->_entry = $session->remove('entry');
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid session data, cannot continue.');
            // This will exit.
        }

        $this->_mode = ($this->_entry->ask ? 'ask' : 'bid');

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_prepare_request_data();
        $this->_request_data['entry_url'] = "{$prefix}entry/view/{$this->_entry->guid}.html";
        $this->_request_data['return_url'] = $prefix;

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);
        $this->_component_data['active_leaf'] = ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_ASK : NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_BID;
        $_MIDCOM->substyle_append($this->_config->get("{$this->_mode}_schema"));

        return true;
    }

    /**
     * Displays the everything's happy message.
     */
    function _show_thankyou($handler_id, &$data)
    {
        midcom_show_style('submit-thankyou');
    }



}

?>