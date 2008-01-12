<?php
/**
 * @package cc.kaktus.pearserver
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php 4368 2006-10-20 07:47:46Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server handler class for viewing the welcome screen
 *
 * @package cc.kaktus.pearserver
 */
class cc_kaktus_pearserver_handler_welcome extends midcom_baseclasses_components_handler
{
    function cc_kaktus_pearserver_handler_welcome()
    {
        parent::midcom_baseclasses_components_handler();
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_welcome($handler_id, $args, &$data)
    {
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_welcome($handler_id, &$data)
    {

    }
}
?>