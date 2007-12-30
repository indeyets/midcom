<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: delete.php 4125 2006-09-19 17:02:52Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * calendar event delete handler
 *
 * @package net.nemein.calendar
 */
class net_nemein_calendar_handler_delete extends midcom_baseclasses_components_handler
{
    /**
     * The calendar event we're deleting
     *
     * @var net_nemein_calendar_event_dba
     * @access private
     */
    var $_event = null;

    /**
     * The Datamanager of the article to display
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    function net_nemein_calendar_handler_delete()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the datamanager for the current calendar event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_event))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_event->id}.");
            // This will exit.
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data, $delete_mode = true)
    {
        $this->_event = new net_nemein_calendar_event_dba($args[0]);
        if (!$this->_event)
        {
            return false;
        }

        $this->_event->require_do('midgard:delete');

        if (array_key_exists('net_nemein_calendar_deleteok', $_POST))
        {
            $calendarword = $this->_event->title;
            if ($this->_event->delete())
            {
                $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.calendar'), sprintf($this->_request_data['l10n']->get('page %s deleted'), $calendarword), 'ok');

                // Update the index
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($this->_event->guid);

                $_MIDCOM->relocate('');
            }
            else
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to delete calendar event, reason " . mgd_errstr());
                // This will exit.
            }
        }
        elseif (array_key_exists('net_nemein_calendar_deletecancel', $_POST))
        {
            $_MIDCOM->relocate("{$this->_event->name}/");
        }

        $this->_load_datamanager();

        $_MIDCOM->bind_view_to_object($this->_event);

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$this->_event->name}/",
            MIDCOM_NAV_NAME => $this->_event->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "delete/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => $this->_request_data['l10n_midcom']->get('delete'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $_MIDCOM->set_pagetitle($this->_event->title);

        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "{$this->_event->name}/",
            MIDCOM_NAV_NAME => $this->_event->title,
        );
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "delete/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('delete')),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        return true;
    }

    function _show_delete($handler_id, &$data)
    {
        $this->_request_data['datamanager'] = $this->_datamanager;

        midcom_show_style('admin_deletecheck');
    }
}
?>
