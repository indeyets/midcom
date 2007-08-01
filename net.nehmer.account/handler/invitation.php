<?php

class net_nehmer_account_handler_invitation extends midcom_baseclasses_components_handler
{
    var $_mail = null;
    var $_invite = null;
    var $_sent_invites = null;

    function net_nehmer_account_handler_invitation()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $_MIDCOM->componentloader->load('org.openpsa.mail');
    }


    /**
     * Checks if user is already registered
     */
    function _is_person_registered($email)
    {
        $qb = midcom_db_person::new_query_builder();
	$qb->add_constraint('sitegroup', '=', $this->_topic->sitegroup);
	$qb->add_constraint('email', '=', $email);

	$persons = $qb->execute();

	if (count($persons) > 0)
	{
	    return true;
	}
	else
	{
            return false;
	}
    }

    /**
     * Adds a buddy
     */
    function _add_as_buddy()
    {

    }

    function _handler_invite($handler_id, $args, &$data)
    {
echo "<pre>";
//print_r($_POST);
echo "</pre>";

        if (isset($_POST['net_nehmer_accounts_invitation_submit']))
	{
            for ($i = 0; $i < $_POST['net_nehmer_accounts_invitation_total_contacts']; $i++)
	    {
                if ($i >= $this->_config->get('email_fields') && !isset($_POST["net_nehmer_accounts_invitation_invitee_selected_{$i}"]))
	        {
	            echo "Continuing";
                    continue;
	        }

                if (isset($_POST["net_nehmer_accounts_invitation_invitee_name_{$i}"])
	            && isset($_POST["net_nehmer_accounts_invitation_invitee_email_{$i}"])
	            && !empty($_POST["net_nehmer_accounts_invitation_invitee_name_{$i}"])
	            && !empty($_POST["net_nehmer_accounts_invitation_invitee_email_{$i}"])
	        )
	        {
	            /**
		     * Saving the invite object
		     */
                    $this->_invite = new net_nehmer_accounts_invites_invite_dba();
                    $this->_invite->hash = md5($_POST["net_nehmer_accounts_invitation_invitee_email_{$i}_{$_MIDCOM->auth->user->guid}"]);
		    $this->_invite->email = $_POST["net_nehmer_accounts_invitation_invitee_email_{$i}"];
		    $this->_invite->buddy = $_MIDCOM->auth->user->guid;

                    if ($this->_is_person_registered($_POST["net_nehmer_accounts_invitation_invitee_email_{$i}"]))
		    {
                        $this->_add_as_buddy();

		        $_MIDCOM->relocate('sent_invites');
		    }
		    else
		    {
		        if (!$this->_invite->create())
                        {
                            debug_add("Could not create invite object ID " . $this->_invite->id);
		        }
		    }

                    $this->_request_data['hash'] = $this->_invite->hash;

                    /**
                     * Sending invitations
		     */
	            $this->_mail = new org_openpsa_mail();
	            $this->_mail->to = 'juhana@nemein.com'; //$_POST["net_nehmer_invitation_invitee_name{$i}"];
	            $this->_mail->from = $_MIDCOM->auth->user->_storage->email;
	            $this->_mail->subject = $this->_l10n->get($this->_config->get('email_subject'));
	            // This may be a hack, but it allows us tons more control in rendering the email
	            $_MIDCOM->style->enter_context(0);
	            ob_start();
	            midcom_show_style('invitation-email-body');
	            $this->_mail->body = ob_get_contents();
	            ob_end_clean();
	            $_MIDCOM->style->leave_context();
	            debug_pop();
	        
                    if (!$this->_mail->send())
		    {
                        debug_add("Sending invitation email failed!");
		    }
	
                }
	   
            }
            $_MIDCOM->relocate('sent_invites');
	}
        return true;
    }

    function _show_invite($handler_id, &$data)
    {
        midcom_show_style('show-invite-emails');
    }

    function _handler_sent_invites($handler_id, $args, &$data)
    {
        $qb = net_nehmer_accounts_invites_invite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);

	$this->_sent_invites = $qb->execute();

        return true;
    }

    function _show_sent_invites($handler_id, &$data)
    {
        midcom_show_style('invites-list-header');

        foreach ($this->_sent_invites as $invite)
	{
	    $this->_request_data['invite'] = $invite;
            midcom_show_style('invites-list-item');
	}

        midcom_show_style('invites-list-footer');
    }
}

?>
