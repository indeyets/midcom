<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * simpledb entry viewer
 *
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function net_nemein_simpledb_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Displays an entry
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $args[0]);
        $entries = $qb->execute();

        if (count($entries) == 0)
        {
            // Try getting with GUID
            $data['entry'] = new midcom_db_article($args[0]);

            if (!$data['entry'])
            {
                return false;
                // This will exit
            }
        }
        else
        {
            $data['entry'] = $entries[0];
        }

        $data['datamanager']->init($data['entry']);
        $data['view'] = $data['datamanager']->get_array();

        $data['view_title'] = $data['entry']->title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$data['entry']->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $data['l10n_midcom']->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $data['entry']->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$data['entry']->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $data['l10n_midcom']->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $data['entry']->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );

        $_MIDCOM->bind_view_to_object($data['entry'], $data['schema_name']);

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$data['entry']->guid}.html",
            MIDCOM_NAV_NAME => $data['view_title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('view-entry');
    }
}

?>