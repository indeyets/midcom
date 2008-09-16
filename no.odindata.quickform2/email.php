<?php
/**
 * @package no.odindata.quickform2
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a factory class that creates the emailclass
 *
 * handles the sending of the emails
 *
 * It uses net_nemein_email
 *
 * @package no.odindata.quickform2
 *
 */
class no_odindata_quickform2_email
{
    var $_factory;
    var $_config;
    /**
     * @param midcom_helper_config handler $config configuration
     * @param no_odindata_quickform2_factory $factory
     */
    function __construct($config, $factory)
    {
        $this->_config = $config;
        $this->_factory = $factory;
    }

    /**
     * Set the email data and send the mail
     * 
     * @access public
     */
    function execute ()
    {
        debug_push(__CLASS__, __FUNCTION__);

        $email_gen_class = $this->_config->get('mail_class');

        $email = new $email_gen_class(new org_openpsa_mail, new org_openpsa_mail);
        $email->config =& $this->_config;

        $email->set_charset($this->_config->get('mail_encoding'));
        $email->set_subject($this->_config->get('mail_subject'),
                $this->_config->get('mail_subject_reciept')
                );

        $email->set_to($this->_config->get('mail_address_to'));
        $email->set_values($this->_factory->values());
        $email->set_schema($this->_factory->get_schema());
        $email->set_from($this->_config->get('mail_address_from'));
        $email->set_reply_to($this->_config->get('mail_reply_to')) ;
        $email->set_recipient_message($this->_config->get('mail_reciept_message'));
        $email->set_add_recipient_data($this->_config->get('mail_reciept_data'));
        $email->set_send_recipient($this->_config->get('mail_reciept'));

        $email->execute();
        $email->send();
        return;

    }

}
