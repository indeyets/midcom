<?
/**
 * This is a factory class that creates the emailclass
 * 
 * handles the sending of the emails
 *
 * It uses net_nemein_email
 */
class no_odindata_quickform2_email 
{
    var $_factory;
    var $_config;
    /**
     * @var $config midcom_helper_config handler configuration
     * @var $factory no_odindata_quickform2_factory
     */
    function no_odindata_quickform2_email ( $config, $factory )
    {
        $this->_config = $config;
        $this->_factory = $factory;
    }

    function execute ( ) 
    {
        debug_push( __CLASS__, __FUNCTION__ );
        $GLOBALS['midcom_debugger']->setLogLevel( 5 );       
        
        $email_gen_class = $this->_config->get( 'mail_class' );

        $email = new $email_gen_class( new org_openpsa_mail, new org_openpsa_mail  );

        $email->set_charset( $this->_config->get('mail_encoding'));
        $email->set_subject($this->_config->get('mail_subject'),
                $this->_config->get( 'mail_subject_reciept' )
                );

        $email->set_to( $this->_config->get('mail_address_to'));
        $email->set_values( $this->_factory->values(  ) );
        $email->set_schema( $this->_factory->get_schema( ) );
        $email->set_from(  $this->_config->get('mail_address_from'));
        $email->set_reply_to( $this->_config->get('mail_reply_to')) ;
        $email->set_reciept_message(  $this->_config->get('mail_reciept_message') );
        $email->set_add_reciept_data(  $this->_config->get('mail_reciept_data'));
        $email->set_send_reciept( $this->_config->get( 'mail_reciept' ) );



        debug_add( "Sending Email-------------" );
        $email->execute(  );
        $email->send(  );

        debug_pop(  );
        return;

    }

}
