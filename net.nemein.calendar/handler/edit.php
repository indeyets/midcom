<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar event editer
 *
 * @package net.nemein.calendar
 */
class net_nemein_calendar_handler_edit extends midcom_baseclasses_components_handler
{

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_calendar_handler_edit()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->set_storage($this->_request_data['event']);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
        $this->_request_data['controller'] =& $this->_controller;
    }

    /**
     * Can-Handle check against the current event GUID. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool True if the request can be handled, false otherwise.
     */
    function _can_handle_edit($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_request_data['event'] = new net_nemein_calendar_event_dba($args[0]);

        if ($this->_request_data['event'])
        {
            if (   !$this->_config->get('show_events_locally')
                && $this->_request_data['event']->node != $this->_request_data['content_topic']->id)
            {
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, 'Event not in the event tree');
                // This will exit
            }

            debug_pop();
            return true;
        }
        else
        {
            debug_add("Event {$args[0]} not found, ".mgd_errstr());
            debug_pop();
            return false;
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_edit($handler_id, $args, &$data)
    {

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Reindex the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_calendar_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("{$this->_request_data['event']->name}/");
                // This will exit.
        }

        $_MIDCOM->set_pagetitle($this->_request_data['event']->title);

        $this->_view_toolbar->bind_to($this->_request_data['event']);

        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "{$this->_request_data['event']->name}/",
            MIDCOM_NAV_NAME => $this->_request_data['event']->title,
        );
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "edit/{$this->_request_data['event']->guid}/",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('edit')),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return true;
    }

    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['view_event'] = $this->_controller->datamanager->get_content_html();
        midcom_show_style('admin_edit');
    }
}

?>
