<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Account Management handler class: Register new Account
 *
 * This class implements the account registration process. It uses several handlers
 * (documented below) and a hidden form value to operate during the registration.
 *
 * URLs in use:
 *
 * register.html: The main registration page, it shows the list of available account
 *     types, or redirects to the approriate page if only one account type is set
 *     in the schema.
 *
 * register/$type.html: The actual registration code, using DM2 to input all neccessary
 *     information.
 *
 * If you want the system to relocate to a specific page after account activation, you
 * have to set the HTTP Request parameter net_nehmer_account_register_returnto when
 * calling the registration page.
 *
 * If you want to limit the account types open for registration, set them into the
 * configuration option register_allow_types. If this is non-null, only the types
 * listed in there are open for registration.
 *
 * @package net.nehmer.account
 */

class net_nehmer_account_handler_register extends midcom_baseclasses_components_handler
{
    function net_nehmer_account_handler_register()
    {
        parent::midcom_baseclasses_components_handler();
    }

    var $_sent_invites = null;

    /**
     * The datamanager controller instance used to create the new record.
     *
     * @var midcom_helper_datamanager2_controller
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database to use when creating new accounts.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The type of account which is being registered. This is a schema name.
     *
     * @var string
     * @access private
     */
    var $_account_type = '';

    /**
     * This member indicates the processing stage we are in, this is used
     * in the sessioning code to connect the various requests. The value is
     * transited by a hidden form element.
     *
     * @var string
     * @access private
     */
    var $_stage = '';

    /**
     * The account which has been activated, not valid otherwise.
     *
     * @var midcom_core_user
     * @access private
     */
    var $_account = null;

    /**
     * The person record of the account which has been activated. Not valid otherwise.
     *
     * @var midcom_db_person
     * @access private
     */
    var $_person = null;

    /**
     * The account activation processing message. Used for already-activated style
     * messages.
     *
     * @var string
     * @access private
     */
    var $_processing_msg = '';

    /**
     * The raw, untranslated processing message. Use this if you want to have your own translation
     * beside the defaults given by the component. The variable contains the l10n string IDs.
     *
     * @var string
     * @access private
     */
    var $_processing_msg_raw = '';

    /**
     * This is the request data preparation code used during the actual registration sequence.
     * The account type selection is not covered by this call.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['account'] =& $this->_account;
        $this->_request_data['person'] =& $this->_person;
        $this->_request_data['processing_msg'] =& $this->_processing_msg;
        $this->_request_data['processing_msg_raw'] =& $this->_processing_msg_raw;
    }

    /**
     * This is the main request handler, which shows a list of account types based on the
     * schema database. If only one schema is available, it automatically relocates to the
     * corresponding form.
     */
    function _handler_select_type($handler_id, $args, &$data)
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_account_creation'));

        // Prepare the request information, filter down to allowed types if neccessary
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_request_data['types'] = Array();
        $open_types = $this->_config->get('register_allow_types');

        foreach ($this->_schemadb as $name => $schema)
        {
            if (   ! $open_types
                || in_array($name, $open_types))
            {
                $this->_request_data['types']["{$prefix}register/{$name}.html"] = $schema->description;
            }
        }

        // If there is only one type, relocate to there immediately.
        if (! $this->_request_data['types'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'No account types are currently open for registration.');
            // This will exit.
        }

        // Save any return URL
        $session = new midcom_service_session();
        if (array_key_exists('net_nehmer_account_register_returnto', $_REQUEST))
        {
            $session->set('register_returnto', $_REQUEST['net_nehmer_account_register_returnto']);
        }

        if (count($this->_request_data['types']) == 1)
        {
            reset($this->_request_data['types']);
            $dest = key($this->_request_data['types']);
            $_MIDCOM->relocate($dest);
            // This will exit.
        }


        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_l10n->get('account registration') . ': ' . $this->_l10n->get('select account type'));


        return true;
    }

    /**
     * Lists the available account types.
     */
    function _show_select_type($handler_id, &$data)
    {
        midcom_show_style('registration-account-type-list');
    }

    function _handler_register_invitation($handler_id, $args, &$data)
    {
	$hash = $args[0];
	$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $keep_sent_invites = $this->_config->get('keep_sent_invites');
	$current_time = time();

        $qb = net_nehmer_accounts_invites_invite_dba::new_query_builder();
	$qb->add_constraint('hash', '=', $hash);

	$invites = $qb->execute();

        /**
	 * Removing expired invites
	 */
	foreach($invites as $invite)
	{
            if ($current_time > ($invite->metadata->created + $keep_sent_invites * 86400))
	    {
                $invite->delete();
	    }
	    
	}

	$this->_sent_invites = $qb->execute();


	if (isset($_POST['net_nehmer_account_register_invitation']))
        {
	    $session = new midcom_service_session();
	    $session->set('invite_hash', $hash);

            $schema_name = $this->_config->get('invreg_schema');
            $dest = "{$prefix}register/{$schema_name}.html";

	    $_MIDCOM->relocate($dest);
	    
	}

	if (isset($_POST['net_nehmer_account_cancel_invitation']))
	{
	    $qb = net_nehmer_accounts_invites_invite_dba::new_query_builder();
	    $qb->add_constraint('hash', '=', $hash);

	    $invites = $qb->execute();

	    foreach($invites as $invite)
	    {
                $invite->delete();
	    }

            $_MIDCOM->relocate($prefix);
	}

        return true;
    }

    function _show_register_invitation($handler_id, &$data)
    {
        if (count($this->_sent_invites) > 0)
        {
            midcom_show_style('show-register-invitation');
	}
	else
	{
            midcom_show_style('show-expired-invitation');
	}
    }

    /**
     * This handler manages the actual input of the required information, using the DM2.
     */
    function _handler_register($handler_id, $args, &$data)
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_account_creation'));
        if (! array_key_exists($args[0], $this->_schemadb))
        {
            $this->errstr = "The account type {$args[0]} is unknown.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            return false;
        }
        $this->_account_type = $args[0];

        // Validate account type against open type list.
        $open_types = $this->_config->get('register_allow_types');
        if (   $open_types
            && ! in_array($this->_account_type, $open_types))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND,
                "The account type '{$this->_account_type} is currently not open for registration.");
            // This will exit.
        }

        // Determine current stage
        if (array_key_exists('_form_stage', $_REQUEST))
        {
            $this->_stage = $_REQUEST['_form_stage'];
        }
        else
        {
            // No request data yet, this must be a first-time-call
            $this->_stage = 'input';

            // Save any return URL
            $session = new midcom_service_session();
            if (array_key_exists('net_nehmer_account_register_returnto', $_REQUEST))
            {
                $session->set('register_returnto', $_REQUEST['net_nehmer_account_register_returnto']);
            }
        }

        // Patch the current schema to match the stage we have.
        $this->_patch_schema();

        // Do the Data I/O
        switch($this->_stage)
        {
            case 'input':
                $this->_handle_input_stage();
                break;

            case 'confirm':
                $this->_handle_confirm_stage();
                break;

            case 'success':
                // We do nothing here, in case we get some submission in success stage.
                // This can only happen if the style code renders the form (which it shoudln't,
                // but you never know).
                break;

            default:
                // Tried form-spoofing?
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The stage {$this->_stage} is invalid, cannot continue. Please restart registration.");
                // This will exit.
        }

        // Prepare output
        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $this->_component_data['active_leaf'] = NET_NEHMER_ACCOUNT_LEAFID_REGISTER;

        switch($this->_stage)
        {
            // We have to switch again as the current stage can change during
            // processing.
            case 'input':
                $_MIDCOM->set_pagetitle($this->_l10n->get('account registration') . ': ' . $this->_l10n->get('enter account details'));
                break;

            case 'confirm':
                $_MIDCOM->set_pagetitle($this->_l10n->get('account registration') . ': ' . $this->_l10n->get('confirm account details'));
                break;

            case 'success':
                $_MIDCOM->set_pagetitle($this->_l10n->get('account registration') . ': ' . $this->_l10n->get('registration successful'));
                break;

        }

        return true;
    }

    /**
     * This is an internal helper function which handles the input stage, e.g. the
     * form which is open for input.
     *
     * In case the form processing indicates a next key, it will freeze the form and alter
     * the processing stage to 'confirm' *if* the form-validtion succeeded and the user
     * should confirm the settings.
     *
     * Processing will only be done after basic validation of the environment (to protect
     * against F5-hitters).
     */
    function _handle_input_stage()
    {
        // This will shortcut without creating any datamanager to avoid the possibly
        // expensive creation process.
        switch (midcom_helper_datamanager2_formmanager::get_clicked_button())
        {
            case 'previous':
                // *** FALL THROUGH ***
            case 'cancel':
                // Return to the account type selection
                $_MIDCOM->relocate('register.html');
                // This will exit.
        }

        $this->_create_null_controller();
        $form_stage_element =& $this->_controller->formmanager->form->addElement('hidden', '_form_stage', 'unset');

        if ($this->_controller->process_form() == 'next')
        {
            // The form has validated at this point, both regarding
            // HTML_QuickForm and type-based validation. We freeze
            // and prepare for saveing through this (in the next request)
            $this->_stage = 'confirm';
            $form_stage_element->setValue('confirm');
            $this->_controller->formmanager->form->freeze();
        }
        else
        {
            // Initialize the current stage value.
            $form_stage_element->setValue('input');
        }

        // edit result remains unhandled, so that we stay in the edit-loop.
    }

    function _add_inviter_as_buddy($inviter_guid)
    {
        if (!$_MIDCOM->componentloader->is_loaded('net.nehmer.buddylist'))
        {
            if ($_MIDCOM->componentloader->load_graceful('net.nehmer.buddylist'))
	    {
                $_MIDCOM->auth->require_valid_user();

	        // Setup.
		$buddy_user = $_MIDCOM->auth->get_user($inviter_guid);
		if (!$buddy_user)
		{
		    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The user guid {$buddy_user} is unknown.");
		}

                if (net_nehmer_buddylist_entry::is_on_buddy_list($buddy_user))
		{
		    $this->_processing_msg_raw = 'user already on your buddylist.';
		}
		else
		{
		    $entry = new net_nehmer_buddylist_entry();
		    $entry->account = $_MIDCOM->auth->user->guid;
		    $entry->buddy = $buddy_user->guid;
		    $entry->isapproved = true;
		    $entry->create();
		    $this->_processing_msg_raw = 'buddy request sent.';
		}
	    }
        }
    }

    /**
     * This function handles the confirm stage. Cancel relocates back to the account type
     * selection screen, next will save previous will go back to allow the user to edit the
     * data.
     */
    function _handle_confirm_stage()
    {
        // This will shortcut without creating any datamanager to avoid the possibly
        // expensive creation process.
        if (midcom_helper_datamanager2_formmanager::get_clicked_button() == 'cancel')
        {
            $_MIDCOM->relocate('register.html');
            // This will exit.
        }

        $this->_create_null_controller();
        $form_stage_element =& $this->_controller->formmanager->form->addElement('hidden', '_form_stage', 'unset');

        switch ($this->_controller->process_form())
        {
            case 'next':
            case 'save':
                $this->_create_account();
                $this->_stage = 'success';
                $form_stage_element->setValue('success');

                /**
		 * Ok, if the user is registering an account from invitation
		 * we need to delete the corresponding invitation from db
		 */
                 $session = new midcom_service_session();
                 $hash = $session->get('invite_hash');
                 
                 if (isset($hash))
                 {
		     $qb = net_nehmer_accounts_invites_invite_dba::new_query_builder();
                     $qb->add_constraint('hash', '=', $hash);
		     $invites = $qb->execute();

		     foreach ($invites as $invite)
		     {
                         $invite->delete();

                         $this->_add_inviter_as_buddy($invite->buddy);
		     }                 
                 }

                break;

            case 'edit':
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('The confirm stage form returned edit, this indicates somehow screwed validation and should not happen. Treating as previous click.',
                    MIDCOM_LOG_WARN);
                debug_pop();

                // *** FALL THROUGH ***

            case 'previous':
                $this->_stage = 'input';
                $form_stage_element->setValue('input');
                break;
        }
    }

    /**
     * Lists the available account types.
     */
    function _show_register($handler_id, &$data)
    {
        switch ($this->_stage)
        {
            case 'input':
                midcom_show_style('registration-stage-input');
                break;

            case 'confirm':
                midcom_show_style('registration-stage-confirm');
                break;

            case 'success':
                midcom_show_style('registration-success');
                break;
        }
    }

    /**
     * This function patches the active schema to have the correct navigation buttons
     * present.
     */
    function _patch_schema()
    {
        switch ($this->_stage)
        {
            case 'input':
            case 'confirm':
                $operations = Array();
                $operations['previous'] = '';
                $operations['next'] = '';
                $operations['cancel'] = '';
                $this->_schemadb[$this->_account_type]->operations = $operations;
                break;
        }
    }

    /**
     * This starts up a datamanager suitable for the first stage of processing. It
     * will render the form without any storage object.
     */
    function _create_null_controller()
    {
        $this->_controller = midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->schemadb = $this->_schemadb;
        $this->_controller->schemaname = $this->_account_type;
        $this->_controller->defaults = Array();
        $this->_controller->initialize();
        $this->_register_username_validation_rule($this->_controller);
    }

    /**
     * Add further validation rules, this is done with the form directly,
     * as we have to register the callback first. We have to load the callback
     * file manually, as we don't add it to the standard loader code.
     * This will prohibit duplicate user names.
     *
     * @param midcom_helper_datamanager2_controller $controller A reference to the controller class to which
     *     the validation rules should be added.
     * @access private
     */
    function _register_username_validation_rule(&$controller)
    {
        require_once(MIDCOM_ROOT . '/net/nehmer/account/callbacks/validation.php');
        $controller->formmanager->form->registerRule
        (
            'check_user_name',
            'callback',
            'check_user_name',
            'net_nehmer_account_callbacks_validation'
        );
        $controller->formmanager->form->addRule
        (
            'username',
            $this->_l10n->get('the username is already in use.'),
            'check_user_name',
            ''
        );
        $controller->formmanager->form->addRule
        (
            'username',
            $this->_l10n->get('invalid username'),
            'regex',
            '/^[^\+\*!]+$/'
        );
        if ($this->_config->get('username_is_email'))
        {
            $this->_controller->formmanager->form->addRule
            (
                'username',
                $this->_l10n->get('invalid username email'),
                'email'
            );
        }
    }

    /**
     * This function will create a new controller based on the simple storage backend to store
     * the entered information into a newly created storage object.
     *
     * This function operates with SUDO privileges unless it detects that the currently active
     * user has write permissions to the person tree.
     */
    function _create_account()
    {
        if (! $_MIDCOM->auth->request_sudo('net.nehmer.account'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to request sudo privileges for account creation.');
            // This will exit.
        }

        // Create the object.
        $person = new midcom_db_person();
        $person->lastname = 'Temporary net.nehmer.account record; ' . time();
        if (! $person->create())
        {
            $_MIDCOM->auth->drop_sudo();

            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to create a person record, last error was: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('Tried to create this record:', $person);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a person record, last error was: ' . mgd_errstr());
            // This will exit.
        }

        // Set ownership and datamanager parameters
        $person->unset_all_privileges();
        $person->set_privilege('midgard:owner', "user:{$person->guid}");
        $person->set_parameter('midcom.helper.datamanager2', 'schema_name', $this->_account_type);

        $controller = midcom_helper_datamanager2_controller::create('simple');
        $controller->schemadb = $this->_schemadb;
        $controller->set_storage($person, $this->_account_type);
        $controller->initialize();
        $this->_register_username_validation_rule($controller);

        $result = $controller->process_form();

        if ($result == 'next')
        {
            // Save data, next does not save implicitly to keep flexibility.
            $controller->datamanager->save();

            if ($this->_config->get('username_is_email'))
            {
                $person->username = $person->email;
                $person->update();
            }
            
			if ($this->_config->get('assign_to_group') != null)
            {
                $group_id = (int)$this->_config->get('assign_to_group');
				$group = mgd_get_group($group_id);

				if ($group)
				{
					$person->add_to_group($group->name);
				}
            }
        }
        else if ($result != 'save')
        {
            // Ups. Something went really wrong here. We shouldn't end up in the edit mode here,
            // unless something was tampered with. We bail out therefore and throw a critical
            // error.
            $person->delete();
            $_MIDCOM->auth->drop_sudo();

            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Original person record we tried to update:', $person);
            debug_print_r('Request data passed to us:', $controller->formmanager->form->getSubmitValues());
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to store the data into the newly created record, this indicates tampering with the request data");
            // This will exit.
        }

        // Generate a random password and activation Hash
        $password = '**';
        $length = max(8, $this->_config->get('password_minlength'));
        for ($i = 0; $i < $length ; $i++)
        {
            $password .= chr(rand(97,122));
        }
        $activation_hash = md5
        (
              serialize(microtime())
            . $person->username
            . serialize($_MIDGARD)
            . $password
            . serialize($_SERVER)
        );
        $activation_link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "register/activate/{$person->guid}/{$activation_hash}.html";

        $person->set_parameter('net.nehmer.account', 'password', $password);
        $person->set_parameter('net.nehmer.account', 'activation_hash', $activation_hash);
        $person->set_parameter('net.nehmer.account', 'activation_hash_created', strftime('%Y-%m-%d', time()));

        $session = new midcom_service_session();
        if ($session->exists('register_returnto'))
        {
            $person->set_parameter('net.nehmer.account', 'activation_returnto',
                $session->remove('register_returnto'));
        }

        $this->_send_registration_mail($person, substr($password, 2), $activation_link);

        $_MIDCOM->auth->drop_sudo();
    }

    /**
     * This is a simple function which generates and sends an account registration confirmation
     * including the randomly-generated password and the corresponding activation link.
     *
     * @param midcom_db_person $person The newly created person account.
     * @todo Make this configurable.
     */
    function _send_registration_mail($person, $password, $activation_link)
    {
        $from = $this->_config->get('activation_mail_sender');
        if (! $from)
        {
            $from = $person->email;
        }
        $template = Array(
            'from' => $from,
            'reply-to' => '',
            'cc' => '',
            'bcc' => '',
            'x-mailer' => '',
            'subject' => $this->_l10n->get($this->_config->get('activation_mail_subject')),
            'body' => $this->_l10n->get($this->_config->get('activation_mail_body')),
            'body_mime_type' => 'text/plain',
            'charset' => 'UTF-8',
        );

        $mail = new midcom_helper_mailtemplate($template);
        $parameters = Array
        (
            'PERSON' => $person,
            'PASSWORD' => $password,
            'ACTIVATIONLINK' => $activation_link,
        );
        $mail->set_parameters($parameters);
        $mail->parse();
        $mail->send($person->email);
    }

    /**
     * This is a simple handler which verifies if the argument pair given in the
     * URL is a correct Person GUID / Account Activation Hash pair. If yes, the
     * account will be activated, and the user will be relocated to a success page.
     *
     * In case that the account is already activated, the success page will display
     * an corresponding message.
     */
    function _handler_activate($handler_id, $args, &$data)
    {
        $guid = $args[0];
        $hash = $args[1];

        $this->_person = new midcom_db_person($guid);
        if (! $this->_person)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'Invalid activation link, the person record was not found.');
            // This will exit.
        }
        $this->_account = $_MIDCOM->auth->get_user($this->_person);

        // Set the default return URL, this might get overridden by activate_account
        $data['return_url'] = $_MIDCOM->get_page_prefix() . 'midcom-login-';

        $activation_hash = $this->_person->get_parameter('net.nehmer.account', 'activation_hash');
        if ($activation_hash != $hash)
        {
            if ($activation_hash)
            {
                // wrong activation hash has been passed.
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'Invalid activation link.');
                // This will exit
            }
            $this->_processing_msg = $this->_l10n->get('your account has already been activated.');
            $this->_processing_msg_raw = 'your account has already been activated.';
        }
        else
        {
            $this->_activate_account();
        }

        // Prepare output
        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $this->_component_data['active_leaf'] = NET_NEHMER_ACCOUNT_LEAFID_REGISTER;
        $_MIDCOM->set_pagetitle($this->_l10n->get('account registration') . ': ' . $this->_l10n->get('activation successful'));

        return true;
    }

    /**
     * Lists the available account types.
     */
    function _show_activate($handler_id, &$data)
    {
        midcom_show_style('registration-account-activated');
    }


    /**
     * This call will actually activate the account, gaining privileges using the
     * sudo service.
     */
    function _activate_account()
    {
        if (! $_MIDCOM->auth->request_sudo('net.nehmer.account'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to request sudo privileges for account activation.');
            // This will exit.
        }

        // Update the password
        $this->_person->password = $this->_person->get_parameter('net.nehmer.account', 'password');
        if (! $this->_person->update())
        {
            $_MIDCOM->auth->drop_sudo();

            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to update a person record, last error was: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('Tried to update this record:', $this->_person);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to update a person record, last error was: ' . mgd_errstr());
            // This will exit.
        }

        // Add corresponding group memberships, but ignore errors, as this component doesn't
        // depend on the membership.
        $this->_account_type = $this->_person->get_parameter('midcom.helper.datamanager2', 'schema_name');
        if (! $this->_person->add_to_group($this->_account_type))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to create group membership for current record, most probably the group {$this->_account_type} does not exist. Continuing silently.",
                MIDCOM_LOG_ERROR);
            debug_pop();
        }

        // Clean up
        $this->_person->delete_parameter('net.nehmer.account', 'password');
        $this->_person->delete_parameter('net.nehmer.account', 'activation_hash');
        $this->_person->delete_parameter('net.nehmer.account', 'activation_hash_created');

        // Trigger post-activation hooks
        $this->_auto_publish_account_details();
        $this->_invoke_account_activation_callback();
        $this->_send_welcome_mail();

        // Check for a custom return_url
        $return_to = $this->_person->get_parameter('net.nehmer.account', 'activation_returnto');
        if ($return_to)
        {
            $this->_request_data['return_url'] = $return_to;
            $this->_person->delete_parameter('net.nehmer.account', 'activation_returnto');
        }

        $_MIDCOM->auth->drop_sudo();
    }

    /**
     * This call automatically publishes the account details if the component is configured
     * to do so. This encompasses all user-publishable schema fields regardless of their
     * content.
     *
     * @access private
     */
    function _auto_publish_account_details()
    {
        if (! $this->_config->get('publish_all_on_activation'))
        {
            return;
        }

        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_account'));
        $published_fields = Array();
        foreach ($this->_schemadb[$this->_account_type]->fields as $name => $config)
        {
            if (   ! array_key_exists('visible_mode', $config['customdata'])
                || $config['customdata']['visible_mode'] == 'user')
            {
                $published_fields[] = $name;
            }
        }

        $this->_person->set_privilege('midcom:isonline', 'USERS', MIDCOM_PRIVILEGE_ALLOW);
        $this->_person->set_parameter('net.nehmer.account', 'visible_field_list', implode(',', $published_fields));
        $this->_person->set_parameter('net.nehmer.account', 'published', time());
        $this->_person->set_parameter('net.nehmer.account', 'auto_published', '1');
    }

    /**
     * This function invokes the callback set in the component configuration upon
     * activation of an account. It will be executed at the end of the activation
     * with sudo privilgeges.
     *
     * Configuration syntax:
     * <pre>
     * 'on_activate_account' => Array
     * (
     *     'callback' => 'callback_function_name',
     *     'autoload_snippet' => 'snippet_name', // optional
     *     'autoload_file' => 'filename', // optional
     * ),
     * </pre>
     *
     * The callback function will receive the midcom_db_person object instance as an argument.
     *
     * @access private
     */
    function _invoke_account_activation_callback()
    {
        $callback = $this->_config->get('on_activate_account');
        if ($callback)
        {
            // Try autoload:
            if (array_key_exists('autoload_snippet', $callback))
            {
                mgd_include_snippet_php($callback['autoload_snippet']);
            }
            if (array_key_exists('autoload_file', $callback))
            {
                require_once($callback['autoload_file']);
            }

            if (! function_exists($callback['callback']))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the callback {$callback['callback']} for account activation, the function is not defined.", MIDCOM_ERRCRIT);
                debug_pop();
                return;
            }
            $callback['callback']($this->_person);
        }
    }


    /**
     * Sends a welcome mail to the user given in the _account member. It will exit directly
     * if the net.nehmer.mail integration is disabled.
     */
    function _send_welcome_mail()
    {
        if (! $this->_config->get('have_net_nehmer_mail'))
        {
            return;
        }
        if (! $_MIDCOM->componentloader->load_graceful('net.nehmer.mail'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to load net.nehmer.mail, either install the component or disable the integration.', MIDCOM_ERRCRIT);
            debug_pop();
            return;
        }

        if ($this->_config->get('welcome_mail_sender'))
        {
            $sender_guid = $this->_config->get('welcome_mail_sender');
            $sender =& $_MIDCOM->auth->get_user($sender_guid);
            if (! $sender)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the user {$sender_guid} from the database, used as sender for the welcome mails. Not sending mail.",
                    MIDCOM_ERRCRIT);
                debug_pop();
                return;
            }
        }
        else
        {
            $sender =& $this->_account;
        }

        $subject = $this->_l10n->get($this->_config->get('welcome_mail_subject'));
        $subject = str_replace('__USERNAME__', $this->_account->username, $subject);
        $body = $this->_l10n->get($this->_config->get('welcome_mail_body'));
        $body = str_replace('__USERNAME__', $this->_account->username, $body);

        $inbox = net_nehmer_mail_mailbox::get_inbox($this->_account);
        $result = $inbox->deliver_mail($sender, $subject, $body);

        if ($this->isError($result))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to send welcome mail: ' . $result->getMessage(), MIDCOM_ERRCRIT);
            debug_pop();
        }
    }
}

?>
