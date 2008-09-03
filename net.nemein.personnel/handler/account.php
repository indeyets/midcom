<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 5395 2007-02-21 13:24:16Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * View page handler, renders index and detail views.
 *
 * @package net.nemein.personnel
 */
class net_nemein_personnel_handler_account extends midcom_baseclasses_components_handler
{
    /**
     * Person object
     *
     * @var midcom_db_person
     * @access private
     */
    var $_person;

    /**
     * Error messages
     *
     * @access private
     */
    var $_errors = array();

    /**
     * Simple constructor
     */
    function net_nemein_personnel_handler_account()
    {
        parent::__construct();
    }

    /**
     * Process the user account form and handle the possible errors
     *
     * @access private
     */
    function _process_form()
    {
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate("{$this->_person->guid}/");
            // This will exit
        }

        if (!isset($_POST['f_submit']))
        {
            return;
        }

        // Check for the username
        if (   !isset($_POST['f_username'])
            || !$_POST['f_username'])
        {
            $this->_errors[] = 'username cannot be left blank';
        }
        elseif (preg_match('/[^a-zA-Z0-9\.\-_]/', $_POST['f_username']))
        {
            $this->_errors[] = 'username contains illegal characters';
        }

        // Check for the password validity
        if (   !isset($_POST['f_password'])
            || !isset($_POST['f_password'][0])
            || !isset($_POST['f_password'][1])
            || strlen(trim($_POST['f_password'][0])) === 0)
        {
            $this->_errors[] = 'enter the same password twice';
            return false;
        }
        elseif ($_POST['f_password'][0] !== $_POST['f_password'][1])
        {
            $this->_errors[] = 'passwords did not match';
        }

        // Stop if any errors were encountered
        if (count($this->_errors) > 0)
        {
            return;
        }

        // Set the credentials
        $this->_person->username = $_POST['f_username'];
        $this->_person->password = "**{$_POST['f_password'][0]}";

        // Finally update the person object
        if (!$this->_person->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to update the person due to '.mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('We operated on object', $this->_person);
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to update the account details, see error level log for details');
            // This will exit
        }

        if (   isset($_POST['send_email'])
            && $_POST['send_email'] == 1)
        {
            $this->_process_email();
        }

        // Show confirmation for the user
        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.personnel'), sprintf($this->_l10n->get('user account updated successfully'), $this->_person->name));

        // Relocate back to page
        $_MIDCOM->relocate(net_nemein_personnel_viewer::get_url($this->_person));
        // This will exit.
    }

    /**
     * Process the email interface
     *
     * @access private
     */
    function _process_email()
    {
        $email = trim(@$_POST['email']);

        if (!preg_match('/^[a-z0-9][a-z0-9\-_\.]*@[a-z0-9][a-z0-9\-_\.]+\.[a-z]{2,4}$/i', $email))
        {
            $this->_errors[] = 'invalid email address';
            return false;
        }

        // Load the mailer component
        $_MIDCOM->componentloader->load('org.openpsa.mail');

        // Initialize the mailer
        $mail = new org_openpsa_mail();
        $mail->to = $email;
        $mail->from = $this->_config->get('email_from');
        $mail->subject = $this->_config->get('email_subject');
        $mail->body = $this->_config->get('email_content');

        $timestamp = strftime('%c');
        $username = $_POST['f_username'];
        $password = $_POST['f_password'][0];

        $mail->body = str_replace('[TIMESTAMP]', $timestamp, $mail->body);
        $mail->body = str_replace('[USERNAME]', $username, $mail->body);
        $mail->body = str_replace('[PASSWORD]', $password, $mail->body);

        if (!$mail->send())
        {
            $this->_errors[] = 'sending of the email failed';
            return false;
        }

        // Show confirmation for the user
        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.personnel'), sprintf($this->_l10n->get('password sent by email to the user'), $this->_person->name));

        return true;
    }

    /**
     * Handle the person account creation
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_account($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:admin');

        // Get the user account requested for modification
        $qb = midcom_db_person::new_query_builder();
        $qb->begin_group('OR');
            $qb->add_constraint('guid', '=', $args[0]);
            $qb->add_constraint('username', '=', $args[0]);
        $qb->end_group();

        if ($qb->count() === 0)
        {
            return false;
        }

        $results = $qb->execute_unchecked();
        $this->_person =& $results[0];

        $this->_process_form();

        // Bind to context data
        $_MIDCOM->set_pagetitle($this->_l10n->get('edit user account of %s'), $this->_person->name);

        // Set the breadcrumb data
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "{$this->_person->guid}.html",
            MIDCOM_NAV_NAME => $this->_person->name,
        );
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "account/{$this->_person->guid}",
            MIDCOM_NAV_NAME => $this->_l10n->get('user account'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        // Add stylesheet
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/net.nemein.personnel/passwd.css',
                'media' => 'all',
            )
        );

        return true;
    }

    /**
     * Show the user account editing form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access public
     */
    function _show_account($handler_id, &$data)
    {
        if (isset($_POST['f_username']))
        {
            $this->_person->username = $_POST['f_username'];
        }

        if (   !$this->_person->username
            && preg_match('/^(.+?)@/', $this->_person->email, $regs))
        {
            $this->_person->username = $regs[1];
        }

        $data['person'] =& $this->_person;
        $data['errors'] = $this->_errors;

        midcom_show_style('admin-account');
    }

    /**
     * Generate automatically passwords
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_passwords($handler_id, $args, &$data)
    {
        $_MIDCOM->skip_page_style = true;
        return true;
    }

    /**
     * Show randomly generated passwords
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access public
     */
    function _show_passwords($handler_id, &$data)
    {
        midcom_show_style('admin-random-passwords');
    }
}
?>