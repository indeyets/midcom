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
     */
    function _handler_welcome($handler_id, $args, &$data)
    {

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
