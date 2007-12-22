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

    function _handler_welcome($handler_id, $args, &$data)
    {
        return true;
    }

    function _show_welcome($handler_id, &$data)
    {

    }
}
?>