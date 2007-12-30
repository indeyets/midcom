<?php
/**
 * @package net.nehmer.comments
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comments welcome page handler
 *
 * @package net.nehmer.comments
 */

class net_nehmer_comments_handler_admin extends midcom_baseclasses_components_handler
{
    function net_nehmer_comments_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * TODO
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    /**
     * TODO
     */
    function _show_welcome($handler_id, &$data)
    {
        echo "<p>Comments Admin missing yet.</p>";
        // midcom_show_style('welcome');
    }

}

?>
