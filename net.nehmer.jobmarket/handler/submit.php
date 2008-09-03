<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market entry submission handler.
 *
 * This handler class requireds sodo privileges to create new objects, as restrictions
 * are done arbitarily based on the existing type configuration.
 *
 * Registration is done in the following steps
 *
 * 1. (optional) Display a list of available types with links to the appropriate
 *    registration URLs
 *
 * 2. Display the data entry form. It will be pre-filled with the data from the
 *    current account, using the mapping currently present in the system.
 *
 * 3. If validation succeeds, create the new record and display a we're happy page
 *    along with a link to the entry.
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_handler_submit extends midcom_baseclasses_components_handler
{
    /**
     * This is an array holding the computed type list.
     *
     * The elements are indexed by type name and contain the following keys:
     *
     * - all keys from the configuration array
     * - string offer_create_url
     * - string application_create_url
     *
     * The two URLs may be null if creation is prohibitied.
     *
     * @var Array
     * @access private
     */
    var $_type_list = null;

    /**
     * A shortcut to $_type_list[$_type].
     *
     * @var Array
     * @access private
     */
    var $_type_config = null;

    /**
     * The name of the type we have to create.
     *
     * @var string
     * @access private
     */
    var $_type = null;

    /**
     * One of 'offer' or 'application'.
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
     * @var net_nehmer_jobmarket_entry
     * @access private
     */
    var $_entry = null;

    function net_nehmer_jobmarket_handler_submit()
    {
        parent::__construct();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['type_list'] =& $this->_type_list;
        $this->_request_data['type_config'] =& $this->_type_config;
        $this->_request_data['type'] =& $this->_type;
        $this->_request_data['mode'] =& $this->_mode;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['entry'] =& $this->_entry;
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
     * Little internal helper, populates the _type_list member and computes
     * a few URLs for it.
     *
     * If there is no user authenticated, the write rules specified in the
     * configuration are observed accordingly.
     */
    function _compute_type_list()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_type_list = Array();
        foreach ($this->_config->get('type_config') as $name => $config)
        {
            $this->_type_list[$name] = $config;
            if ($config['offer_schema'])
            {
                if (   $_MIDCOM->auth->user !== null
                    || $config['offer_anonymous_read'])
                {
                    $this->_type_list[$name]['offer_create_url'] = "{$prefix}submit/offer/{$name}.html";
                }
                else
                {
                    $this->_type_list[$name]['offer_create_url'] = null;
                }
            }
            if ($config['application_schema'])
            {
                if (   $_MIDCOM->auth->user !== null
                    || $config['application_anonymous_read'])
                {
                    $this->_type_list[$name]['application_create_url'] = "{$prefix}submit/application/{$name}.html";
                }
                else
                {
                    $this->_type_list[$name]['application_create_url'] = null;
                }
            }
        }
    }

    /**
     * Simple submission welcome page handler, computes the type list to
     * display a welcome page.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $this->_compute_type_list();

        if ($handler_id == 'submit_welcome_mode')
        {
            $this->_mode = $args[0];
            $this->_component_data['active_leaf'] = ($this->_mode == 'offer') ?
                NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_OFFER : NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_APPLICATION;
        }
        else
        {
            $this->_component_data['active_leaf'] = NET_NEHMER_JOBMARKET_LEAFID_SUBMIT;
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get('submit new entry'));

        return true;
    }

    /**
     * Displays a welcome page with all types available for submission linked
     * accordingly.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_welcome($handler_id, &$data)
    {
        if ($handler_id == 'submit_welcome_mode')
        {
            midcom_show_style('submit-welcome-mode');
        }
        else
        {
            midcom_show_style('submit-welcome');
        }
    }

    /**
     * Validates the step1 handle arguments.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean True if the request can be handled, false otherwise.
     */
    function _can_handle_welcome($handler_id, $args, &$data)
    {
        if ($handler_id != 'submit_welcome_mode')
        {
            return true;
        }

        if (   $args[0] != 'offer'
            && $args[0] != 'application')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'offfer' or 'application' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Validates the step1 handle arguments.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean True if the request can be handled, false otherwise.
     */
    function _can_handle_step1($handler_id, $args, &$data)
    {
        $this->_compute_type_list();

        if (   $args[0] != 'offer'
            && $args[0] != 'application')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'offfer' or 'application' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        if (! array_key_exists($args[1], $this->_type_list))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need a valid type as second argument, got '{$args[1]}'", MIDCOM_LOG_INFO);
            debug_print_r('Type Listing:', $this->_type_list);
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
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_step1($handler_id, $args, &$data)
    {
        // Type list is already computed at this point, this is done in can-handle.
        // Argumenst have been validated for their syntactical correctness as well,
        // so we can immediately start with the permission checks.
        $this->_mode = $args[0];
        $this->_type = $args[1];
        $this->_type_config = $this->_type_list[$this->_type];
        if (   $_MIDCOM->auth->user === null
            && ! $this->_type_config["{$this->_mode}_anonymous_write"])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN,
                $this->_l10n->get('you need to be authenticated to create entries here'));
            // This will exit.
        }

        // We need to set this first, as NAP could get started up when trying to
        // auto-detect an account topic (thus loading the active leaf before it
        // gets set).
        $this->_component_data['active_leaf'] = ($this->_mode == 'offer') ?
            NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_OFFER : NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_APPLICATION;


        $this->_prepare_datamanager();
        $this->_process_datamanager();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get("submit {$data['mode']}"));
        $_MIDCOM->substyle_append($this->_type_config["{$this->_mode}_schema"]);

        return true;
    }

    /**
     * Internal helper function: prepares a nullstorage controller instance and populates
     * its defaults from the current account using the _get_defaults_from_account helper.
     */
    function _prepare_datamanager()
    {
        $this->_controller = midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->load_schemadb($this->_config->get('schemadb'));
        $this->_controller->schemaname = $this->_type_config["{$this->_mode}_schema"];
        $this->_controller->defaults = $this->_get_defaults_from_account();
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
                $this->_create_entry();
                // This will exit.
        }
    }

    /**
     * This function will create a new job entry based on the data currently read in by
     * the DM instance. Errors will trigger generate_error, success will redirect, putting
     * the newly created record into a PHP session (URL args are not used here for security
     * reasons, as sensitive information could be included in the thank you page).
     *
     * The actual creation is handled under sudo privileges, consecutive operations only
     * if we are working as a not authenticated user.
     */
    function _create_entry()
    {
        // Create a fresh storage object. We need sudo for this.
        $entry = new net_nehmer_jobmarket_entry();
        $entry->offer = ($this->_mode == 'offer');
        $entry->type = $this->_type;
        if ($_MIDCOM->auth->user !== null)
        {
            $entry->account = $_MIDCOM->auth->user->guid;
        }
        $entry->published = time();

        if (! $_MIDCOM->auth->request_sudo())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to obtain sudo privileges, cannot continue.');
            // This will exit.
        }

        if (! $entry->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $entry);
            debug_pop();
            die(mgd_errstr());
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new job entry, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        // Create a new controller and synchronize the form information to the new entry.
        $controller = midcom_helper_datamanager2_controller::create('simple');
        $controller->set_schemadb($this->_controller->schemadb);
        $controller->set_storage($entry, $this->_type_config["{$this->_mode}_schema"]);
        $controller->initialize();
        if ($controller->process_form() != 'save')
        {
            $entry->delete();
            $_MIDCOM->auth->drop_sudo();
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $entry);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create the entry, could not save form data, last Midgard Error was:' . mgd_errstr());
            // This will exit.
        }

        $_MIDCOM->auth->drop_sudo();

        $topic = $this->_config->get('index_to');
        if (! $topic)
        {
            $topic = $this->_topic;
        }
        $indexer =& $_MIDCOM->get_service('indexer');
        net_nehmer_jobmarket_entry::index($controller->datamanager, $indexer, $topic, $this->_type_config["{$this->_mode}_anonymous_read"]);

        $session = new midcom_service_session();
        $session->set('entry', $entry);
        $_MIDCOM->relocate("submit/thankyou.html");
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
        foreach ($this->_type_config["{$this->_mode}_mapping"] as $name => $sources)
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

        $content = $this->_account_remote->get_content_from_account();
        foreach ($this->_type_config["{$this->_mode}_mapping_content"] as $name => $sources)
        {
            if (is_array($sources))
            {
                $tmp = '';
                foreach ($sources as $source)
                {
                    if (array_key_exists($source, $content))
                    {
                        $tmp .= "{$content[$source]} ";
                    }
                }
                $defaults[$name] = trim($tmp);
            }
            else
            {
                if (! array_key_exists($sources, $content))
                {
                    $defaults[$name] = '';
                }
                else
                {
                    $defaults[$name] = $content[$sources];
                }
            }
        }
        return $defaults;
    }

    /**
     * Calls a simple style element which shows the form.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
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
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_thankyou($handler_id, $args, &$data)
    {
        $this->_compute_type_list();

        $session = new midcom_service_session();
        $this->_entry = $session->remove('entry');
        if (! $this->_entry)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid session data, cannot continue.');
            // This will exit.
        }

        $this->_mode = ($this->_entry->offer ? 'offer' : 'application');
        $this->_type = $this->_entry->type;
        $this->_type_config = $this->_type_list[$this->_type];

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_prepare_request_data();
        $this->_request_data['entry_url'] = "{$prefix}entry/view/{$this->_entry->guid}.html";
        $this->_request_data['return_url'] = $prefix;

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);
        $this->_component_data['active_leaf'] = ($this->_mode == 'offer') ?
            NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_OFFER : NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_APPLICATION;
        $_MIDCOM->substyle_append($this->_type_config["{$this->_mode}_schema"]);

        return true;
    }

    /**
     * Displays the everything's happy message.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_thankyou($handler_id, &$data)
    {
        midcom_show_style('submit-thankyou');
    }



}

?>