<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_profile_view extends midcom_baseclasses_components_handler
{
    /**
     * The person to register for
     *
     * @var array
     * @access private
     */
    var $_person = null;

    /**
     * The schema database (taken from the config)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;
    
    /**
     * The schema (taken from the config)
     *
     * @var Array
     * @access private
     */
    var $_schema = null;    

    /**
     * The Datamanager of the person to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_controller = null;

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_profile_view()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    function _prepare_request_data()
    {
        $this->_request_data['person'] =& $this->_person;
        $this->_request_data['controller'] =& $this->_controller;
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database( $this->_config->get('profile_schemadb') );
        $this->_schema = $this->_config->get('profile_schema');
    }

    /**
     * Internal helper
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();

        $this->_controller =& new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_controller
            || ! $this->_controller->set_schema($this->_schema) )
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }

        $this->_controller->set_storage($this->_person);
    }

    function _handler_view($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ($handler_id == 'ajax-profile-view')
        {
            $_MIDCOM->skip_page_style = true;
        }
        
        $this->_person = new midcom_db_person($args[0]);
        if (!$this->_person)
        {
            return false;
        }

        $this->_load_controller();
        
        $this->_prepare_request_data();
        
        debug_pop();
        return true;
    }
    
    function _show_view($handler_id, &$data)
    {
        if ($handler_id == 'ajax-profile-view')
        {
            midcom_show_style('profile-view-ajax');
        }
        else
        {
            midcom_show_style('profile-view');            
        }        
    }    
        
}

?>