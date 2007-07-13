<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * 
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_admin  extends midcom_baseclasses_components_handler
{
     * The root event (taken from the request data area)
     *
     * @var net_nemein_registrations_event
     * @access private
     */
    var $_root_event = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The processing message to show.
     *
     * @var string
     * @access private
     */
    var $_processing_msg = '';

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['processing_msg'] =& $this->_processing_msg;

    }

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the root event and schemadb from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_root_event =& $this->_request_data['root_event'];
        $this->_schemadb =& $this->_request_data['schemadb'];
    }
    
}

?>