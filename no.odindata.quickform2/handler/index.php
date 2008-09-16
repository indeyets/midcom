<?php
/**
 * @package no.odindata.quickform2
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for no.odindata.quickform2
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package no.odindata.quickform2
 */
class no_odindata_quickform2_handler_index  extends midcom_baseclasses_components_handler
{

    /**
     * the schema to use
     * @var midcom_helper_datamanager2_schema
     */
    var $_schemadb = null;
    
    /**
     * Switch to determine if the message should be already sent.
     * 
     * @access private
     * @var boolean
     */
    var $_send_message = false;
    
    
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {

        $this->_request_data['name']  = 'no.odindata.quickform2';
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
    }
    
    /**
     * Generate the form by using a style element rather than an automatically
     * generated message
     * 
     * @access private
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_style($handler_id, $args, &$data)
    {
        // Generate the controller
        $this->_controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->schemadb =& $this->_schemadb;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance ");
            // This will exit.
        }
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Skip the styles and proceed to generating the message
                $_MIDCOM->skip_page_style = true;
                $this->_send_message = true;
                break;
            
            case 'cancel':
                // Relocate to self to clear the message data
                $_MIDCOM->relocate('');
        }
        
        // Since the form hasn't yet been submitted, use the original controller
        $this->_request_data['form']  = new no_odindata_quickform2_factory($this->_schemadb, $this->_config);
        
        return true;
    }
    
    /**
     * Generate the message body with style element
     * 
     * @access private
     */
    function _styled_message($handler_id, &$data)
    {
        // Populate request data
        $data['schemadb'] =& $this->_schemadb;
        $data['controller'] =& $this->_controller;
        
        // Get the message with output buffering
        ob_start();
        midcom_show_style('quickform-message');
        $body = ob_get_contents();
        ob_end_clean();
        
        // Use the configured mail class generator
        $email_gen_class = $this->_config->get('mail_class');
        
        $email = new $email_gen_class(new org_openpsa_mail, new org_openpsa_mail);
        $email->config =& $this->_config;

        $email->set_charset($this->_config->get('mail_encoding'));
        $email->set_subject($this->_config->get('mail_subject'), $this->_config->get('mail_subject_reciept'));

        $email->set_to($this->_config->get('mail_address_to'));
        $email->set_from($this->_config->get('mail_address_from'));
        $email->set_reply_to($this->_config->get('mail_reply_to')) ;
        $email->set_recipient_message($this->_config->get('mail_reciept_message'));
        $email->set_add_recipient_data($this->_config->get('mail_reciept_data'));
        $email->set_send_recipient($this->_config->get('mail_reciept'));
        
        $email->mail->body = $body;
        $email->recipient_msg = $body;
        
        if (!$email->send())
        {
            $_MIDCOM->relocate('submitnotok/');
        }
        
        $this->_send_callback();
        $_MIDCOM->relocate('submitok/');
    }
    
    /**
     * Run the callback script if the component has been configured to do so
     * 
     * @access private
     */
    function _send_callback()
    {
        // Creation callback function
        if ($this->_config->get('callback_function'))
        {
            if ($this->_config->get('callback_snippet'))
            {
                $eval = midcom_get_snippet_content($this->_config->get('callback_snippet'));

                if ($eval)
                {
                    eval("?>{$eval}<?php");
                }
            }

            $callback = $this->_config->get('callback_function');
            $callback($this->_request_data['controller'], $this->_config);
        }
    }

    /**
     * The handler for the index article.
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get( $this->_config->get( 'breadcrumb' ) );
        $_MIDCOM->set_pagetitle("{$title}");
        
        // Use style based message formatting instead of the class based message formatting
        if ($this->_config->get('style_based_message'))
        {
            return $this->_handler_style($handler_id, $args, &$data);
        }

        if ( $this->_config->get( 'breadcrumb' ) != '' )
        {
            $this->_update_breadcrumb_line( $this->_config->get( 'breadcrumb' ) );
        }

        $this->_request_data['form']  = new no_odindata_quickform2_factory($this->_schemadb, $this->_config);
        
        switch ($this->_request_data['form']->process_form())
        {
            case 'save':
                // Check for the callback function
                $this->_send_callback();
                $_MIDCOM->relocate('submitok.html');
                break;
            
            case 'cancel':
                $_MIDCOM->relocate('');
                break;
        }

       return true;
    }


    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
        if ($this->_send_message)
        {
            $this->_styled_message($handler_id, &$data);
            return;
        }
        
        midcom_show_style('show-form');
    }
    /**
     * Form submit worked ok.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_submitok()
    {
        $this->_request_data['end_message'] = $this->_config->get('end_message');
        return true;
    }

    function _show_submitok()
    {
        midcom_show_style('show-form-finished');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_submitnotok()
    {
        $this->_request_data['end_message'] = $this->_l10n->get('error sending the message');
        return true;
    }

    function _show_submitnotok()
    {
        midcom_show_style('show-form-failed');
    }



    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($txt)
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $txt,
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>