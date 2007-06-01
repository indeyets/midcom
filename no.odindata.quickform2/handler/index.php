<?php
/**
 * @package no.odindata.quickform2
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for no.odindata.quickform2
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
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
     * Simple default constructor.
     */
    function no_odindata_quickform2_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {

        $this->_request_data['name']  = "no.odindata.quickform2";
        $this->_schemadb =
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        

    }

    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $GLOBALS['midcom_debugger']->setLogLevel( 5 );

        /**
         * change the pagetitle. (must be supported in the style)
         */
        $title = $this->_l10n_midcom->get( $this->_config->get( 'breadcrumb' ) );
        $_MIDCOM->set_pagetitle(":: {$title}");

        if ( $this->_config->get( 'breadcrumb' ) != '' ) 
        {
            $this->_update_breadcrumb_line( $this->_config->get( 'breadcrumb' ) );
        }

        $this->_request_data['form']  = new no_odindata_quickform2_factory( $this->_schemadb, $this->_config );
        $this->_request_data['form']->process_form(  );

       return true;
    }
    
    
    /**
     * This function does the output.
     *  
     */
    function _show_index($handler_id, &$data)
    {
         
        // hint: look in the style/index.php file to see what happens here.
        midcom_show_style('show-form');
    }
    /**
     * Form submitt worked ok.
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
     * Helper, updates the context so that we get a complete breadcrum line towards the current
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
