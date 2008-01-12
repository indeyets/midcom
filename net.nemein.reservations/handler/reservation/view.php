<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: index.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum index
 *
 * @package net.nemein.reservations
 */
class net_nemein_reservations_handler_reservation_view extends midcom_baseclasses_components_handler
{
    /**
     * The event which has been created
     *
     * @var org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resource = null;

    /**
     * The event which has been created
     *
     * @var org_openpsa_calendar_event
     * @access private
     */
    var $_event = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_reservations_handler_reservation_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the datamanager for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_reservation']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for events.");
            // This will exit.
        }
    }

    /**
     * Looks up an event to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_event = new org_openpsa_calendar_event($args[0]);
        if (   !$this->_event
            || count($this->_event->resources) < 1)
        {
            return false;
            // This will 404
        }

        foreach ($this->_event->resources as $resource => $included)
        {
            $this->_resource = new org_openpsa_calendar_resource_dba($resource);
            break;
        }

        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($this->_event);

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "view/{$this->_resource->name}/",
            MIDCOM_NAV_NAME => $this->_resource->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "reservation/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => "{$this->_event->title} " . strftime('%x', $this->_event->start),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['datamanager'] =& $this->_datamanager;

        // Populate the toolbar
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reservation/edit/{$this->_event->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:update')
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reservation/delete/{$this->_event->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:delete')
            )
        );

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reservation/repeat/{$this->_event->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('repeating'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-master-document.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_event->can_do('midgard:update'),
            )
        );

        $_MIDCOM->bind_view_to_object($this->_event, $this->_datamanager->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_event->metadata->revised, $this->_event->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$this->_event->title} " . strftime('%x', $this->_event->start));

        return true;
    }

    /**
     * Shows the loaded event.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view ($handler_id, &$data)
    {
        $this->_request_data['view_reservation'] = $this->_datamanager->get_content_html();

        midcom_show_style('view-reservation');
    }
}

?>