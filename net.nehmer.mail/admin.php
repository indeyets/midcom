<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mail System AIS interface class
 *
 * @package net.nehmer.mail
 */

class net_nehmer_mail_admin extends midcom_baseclasses_components_request_admin
{
    function net_nehmer_mail_admin($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    /**
     * @access private
     */
    function _on_initialize()
    {
        $this->_request_switch[] = Array
        (
            'handler' => 'welcome',
        );

        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'fixed_args' => Array('config'),
            'schemadb' => 'file:/net/nehmer/mail/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );

        $this->_request_switch[] = Array
        (
            'handler' => 'mailbox_create',
            'fixed_args' => Array('mailbox', 'create'),
        );

        $this->_request_switch[] = Array
        (
            'handler' => 'mailbox_edit',
            'fixed_args' => Array('mailbox', 'edit'),
            'variable_args' => 1,
        );

        $this->_request_switch[] = Array
        (
            'handler' => 'mailbox_delete',
            'fixed_args' => Array('mailbox', 'delete'),
            'variable_args' => 1,
        );

    }

    /**
     * General request initialization, which populates the topic toolbar.
     */
    function _on_handle($handler_id, $args)
    {
        $this->_prepare_topic_toolbar();
        return true;
    }

    /**
     * This function adds all of the standard items (configuration and create links)
     * to the topic toolbar.
     *
     * @access private
     */
    function _prepare_topic_toolbar()
    {
        /*
        $this->_topic_toolbar->add_item(Array
        (
            MIDCOM_TOOLBAR_URL => "search.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('search mailboxes'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
            MIDCOM_TOOLBAR_ENABLED => true,
			MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_user_do('browse_other_mailboxes'),
        ), 0);
        */

        $this->_topic_toolbar->add_item(Array
        (
            MIDCOM_TOOLBAR_URL => "mailbox/create.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create mailbox'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_user_do('midgard:create', null, 'net_nehmer_mail_mailbox'),
        ), 0);

        $this->_topic_toolbar->add_item(Array
        (
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
	        MIDCOM_TOOLBAR_HIDDEN =>
	        (
	               ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
	            || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
	        )
        ));
    }

    /**
     * Welcome page handler.
     *
     * It shows a list of available schemas with create links unless there is no
     * index article and autoindex mode is disabled, in which case it redirects
     * into the create mode.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_welcome ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_pop();
        return true;
    }

    /**
     * Renders the welcome page.
     *
     * @access private
     */
    function _show_welcome ($handler_id, &$data)
    {
    	echo '<h2>' . $this->_l10n->get('your mailboxes') . '</h2>';

        $mailboxes = net_nehmer_mail_mailbox::list_mailboxes();

        echo "<ul>\n";

        foreach ($mailboxes as $mailbox)
        {
            $anchor_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $msgcount = $mailbox->get_message_count();
            if ($mailbox->quota > 0)
            {
                $quota_string = "{$msgcount}/{$mailbox->quota}";
            }
            else
            {
                $quota_string = $msgcount;
            }

            echo '<li>';
            if ($_MIDCOM->auth->can_do('midgard:update', $mailbox))
            {
                echo "<a href='{$anchor_prefix}mailbox/edit/{$mailbox->guid}'>{$mailbox->name}</a>";
            }
            else
            {
                echo $mailbox->name;
            }

            echo " ({$quota_string})";

            if ($_MIDCOM->auth->can_do('midgard:delete', $mailbox))
            {
                echo " <a href='{$anchor_prefix}mailbox/delete/{$mailbox->guid}'>" . $this->_l10n_midcom->get('delete') . '</a>';
            }

            echo "</li>\n";
        }

        echo "</ul>\n";
    }

    /**
     * Prepares an edit form using the dm2, which is frozen unless we have update privileges.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_mailbox_edit($handler_id, $args, &$data)
    {
        $data['mailbox'] = new net_nehmer_mail_mailbox($args[0]);

        if (! $data['mailbox'])
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The mailbox {$args[0]} was not found or you do not have read permission for it.";
            return false;
        }

        $this->_component_data['active_leaf'] = $this->_request_data['mailbox']->guid;

        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true,
        ));

        $data['controller'] = midcom_helper_datamanager2_controller::create('simple');
        $data['controller']->load_schemadb('file:/net/nehmer/mail/config/schemadb.inc');
        $data['controller']->set_storage($data['mailbox'], 'mailbox');
        $data['controller']->initialize();

        if ($_MIDCOM->auth->can_do('midgard:update', $data['mailbox']))
        {
	        // If the save is successful, we adjust the privileges.
	        $oldowner = $data['mailbox']->owner;

            // Process the form and update the owner if necessary
            switch ($data['controller']->process_form())
            {
                case 'save':
	                if ($oldowner != $data['mailbox']->owner)
	                {
	                    $data['mailbox']->set_privilege('midgard:owner', "user:{$data['mailbox']->owner}");

	                    // Revert old privileges.
	                    $data['mailbox']->unset_privilege('midgard:owner', "user:{$oldowner}");
	                }

                    // *** FALL THROUGH ***

                case 'cancel':
                    $_MIDCOM->relocate('');
                	// This will exit.
            }
        }
        else
        {
            // no read permission, no processing, freeze the form instead.
            $data['controller']->formmanager->form->freeze();
        }

        return true;
    }

    /**
     * Deletes a mailbox (currently without safety checks).
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_mailbox_delete($handler_id, $args, &$data)
    {
        $data['mailbox'] = new net_nehmer_mail_mailbox($args[0]);

        if (! $data['mailbox'])
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "The mailbox {$args[0]} was not found or you do not have read permission for it.";
            return false;
        }

        $_MIDCOM->auth->can_do('midgard:delete', $data['mailbox']);

        // This calls generate_error on failure.
        $data['mailbox']->delete();

        $_MIDCOM->relocate('');
    }

    /**
     * This is small creation form, driven by DM2 without any data backend.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_mailbox_create($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard:create', 'net_nehmer_mail_mailbox');

        $data['controller'] =& midcom_helper_datamanager2_controller::create('nullstorage');
        $data['controller']->load_schemadb('file:/net/nehmer/mail/config/schemadb.inc');
        $data['controller']->schemaname = 'mailbox';
        $data['controller']->defaults = Array('name' => 'INBOX', 'quota' => $this->_config->get('default_quota'));
        $data['controller']->initialize();

        $data['formmanager'] =& $data['controller']->formmanager;
        $data['datamanager'] =& $data['controller']->datamanager;

        switch ($data['controller']->process_form())
        {
            case 'save':
                $this->_create_mailbox();
	            $_MIDCOM->relocate('');
	            // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        // Editing...
        return true;
    }

    /**
     * This function handles the actual mailbox creation from the 'create' handler.
     * It depends on the request data to gain access to form/datamanager and will
     * relocate to the mailbox view on success, or continue editing otherwise.
     *
     * @access private
     */
    function _create_mailbox()
    {
        $mailbox = new net_nehmer_mail_mailbox();
        $mailbox->name = $this->_request_data['datamanager']->types['name']->value;
        $mailbox->quota = $this->_request_data['datamanager']->types['quota']->value;
        $mailbox->owner = $this->_request_data['datamanager']->types['owner']->selection[0];

        if (! $mailbox->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Object was:', $mailbox);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new mailbox. See the debug log for more information.');
            // This will exit.
        }

        // Resign ownership to the Mailbox's owner, but don't allow deletions for the owner
        // if it is an INBOX.
        $mailbox->set_privilege('midgard:owner', "user:{$mailbox->owner}");
        $mailbox->unset_privilege('midgard:owner');

        $_MIDCOM->relocate("mailbox/edit/{$mailbox->guid}.html");
        // This will exit.
    }

    /**
     * Simple Mailbox creation form handler.
     *
     * @access private
     */
    function _show_mailbox_create($handler_id, &$data)
    {
        echo '<h2>' . $this->_l10n->get('create mailbox') . '</h2>';
        $data['controller']->display_form();
    }

    /**
     * Simple Mailbox creation form handler.
     *
     * @access private
     */
    function _show_mailbox_edit($handler_id, &$data)
    {
        echo '<h2>' . $this->_l10n->get('edit mailbox') . '</h2>';
        $data['controller']->display_form();
    }

    /**
     * Mailbox deletion stub. Not used yet.
     *
     * @access private
     */
    function _show_mailbox_delete($handler_id, &$data)
    {
        // Not called yet
    }

}

?>
