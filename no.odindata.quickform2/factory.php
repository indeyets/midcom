<?php
/**
 * @package no.odindata.quickform2
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package no.odindata.quickform2
 */
class no_odindata_quickform2_factory
{

    /**
     * The schema to use. Public until the controller is loaded.
     * @var array
     * @access public
     */
    var $schema = null;

    /**
     * @var midcom_helper_configuration
     */
    var $config = null;

    /**
     * @var no_odindata_quickform2_email
     * @access public
     */
    var $email;

    /**
     * Datamanager controller
     * @var midcom_helper_datamanager2controller
     */
    var $_controller;

    /**
     * Schema name. for now hardcoded to 'default'
     * @var string
     * @access public
     */
    var $schema_name = 'default';

    function no_odindata_quickform2_factory( $schema, $config )
    {
        $this->schema = $schema;
        $this->config = $config;
        $this->_load_controller();
        $this->email  = new no_odindata_quickform2_email( $config, $this );
    }

    /**
     * Display the form
     */
    function display_form()
    {
        $this->_controller->display_form();
    }
    /**
     * Set the value of a formelement.
     * @param string $key the form field name
     * @param string $value the new value to set
     */
    function set_value( $key, $value )
    {
       $element =&   $this->_controller->formmanager->form->getElement( $key);
       $element->setValue( $value );
    }
    /**
     * Returns the values from a submitted form
     */
    function values()
    {
        return $this->_controller->datamanager->types;
    }

    /**
     * Returns the form schema
     */
    function get_schema()
    {
        return $this->schema[$this->schema_name];
    }
    /**
     * Not strictly formfactory related.
     * @return string the form description
     */
    function description()
    {
        return $this->config->get( 'form_description');
    }

    function error ()
    {
        return "";
    }

    function process_form()
    {
        debug_push( __CLASS__, __FUNCTION__ );
        //$this->_load_controller();

        $res = $this->_controller->process_form();
        debug_add( "Process_form: $res", MIDCOM_LOG_INFO );
        switch ($res)
        {
            case 'save':
                $this->_save();
                return 'save';
                
            case 'cancel':
                $this->_cancel();
                return 'cancel';
        }

        debug_pop();
    }
    /**
     * function to run the emailhandler
     * @access private
     *
     */
    function _save()
    {
        $this->email->execute();
    }
    /**
     * Cancelling function
     */
    function _cancel()
    {
        $_MIDCOM->relocate('');
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $this->_controller->schemadb =& $this->schema;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance ");
            // This will exit.
        }
    }
}
?>