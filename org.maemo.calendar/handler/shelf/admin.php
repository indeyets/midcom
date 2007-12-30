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
class org_maemo_calendar_handler_shelf_admin  extends midcom_baseclasses_components_handler
{

    var $_shelf_items = array();

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_shelf_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
    }

    function _load_shelf_contents()
    {
        $session =& new midcom_service_session('org.maemo.calendarpanel');
        if ($session->exists('shelf_contents'))
        {
            $this->_shelf_items = json_decode($session->get('shelf_contents'));
        }
        else
        {
            $session->set('shelf_contents',json_encode($this->_shelf_items));
        }
        unset($session);
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_load($handler_id, $args, &$data)
    {
        $this->_load_shelf_contents();

        $_MIDCOM->skip_page_style = true;



        return true;
    }

    function _show_load()
    {

    }

}

?>