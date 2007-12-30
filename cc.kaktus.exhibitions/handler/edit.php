<?php
/**
 * @package cc.kaktus.exhibitions
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 5500 2007-03-08 13:22:25Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Handler class for showing an exhibition
 *
 * @package cc.kaktus.exhibitions
 */
class cc_kaktus_exhibitions_handler_edit extends midcom_baseclasses_components_handler
{
    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    private $_controller = null;

    /**
     * Event
     *
     * @access private
     * @var midcom_db_event $_event
     */
    private $_event = null;

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    private function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->set_storage($this->_event);

        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_event->id}.");
            // This will exit.
        }

        $this->_request_data['controller'] =& $this->_controller;
    }

    /**
     * Connect to the parent class constructor
     *
     * @access public
     */
    public function cc_kaktus_exhibitions_handler_edit()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Handler for editing interface. Process form and relocate if required
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    public function _handler_edit($handler_id, $args, &$data)
    {
        $this->_event = new midcom_db_event($args[0]);

        if (   !$this->_event
            || !isset($this->_event->guid))
        {
            return false;
        }

        // Require the correct ACL
        $this->_event->require_do('midgard:update');

        // Load the controller interface
        $this->_load_controller();

        // Get the schema layout name
        $this->_layout = $this->_event->get_parameter('midcom.helper.datamanager2', 'schema_name');
        $data['layout'] = $this->_layout;

        switch ($this->_controller->process_form())
        {
            case 'save':
            case 'cancel':
                $_MIDCOM->relocate(cc_kaktus_exhibitions_viewer::determine_return_page($this->_event->guid, $this->_layout));
                // This will exit
        }

        // Set the page title
        $_MIDCOM->set_pagetitle(sprintf($this->_l10n->get('edit %s'), $this->_layout));

        // Bind to the context data
        $this->_view_toolbar->bind_to($this->_event);

        switch ($this->_layout)
        {
            case 'exhibition':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_event->start) . "/{$this->_event->extra}/",
                    MIDCOM_NAV_NAME => $this->_event->title,
                );
                break;

            case 'subpage':
                $this->_up = new midcom_db_event($this->_event->up);
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . "/{$this->_up->extra}/",
                    MIDCOM_NAV_NAME => $this->_up->title,
                );
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . "/{$this->_up->extra}/{$this->_event->extra}/",
                    MIDCOM_NAV_NAME => $this->_event->title,
                );
                break;

            case 'attachment':
                $this->_up = new midcom_db_event($this->_event->up);
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . "/{$this->_up->extra}/",
                    MIDCOM_NAV_NAME => $this->_up->title,
                );
                break;
        }

        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "edit/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => $this->_l10n->get('edit'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return true;
    }

    /**
     * Show the editing form
     *
     * @access public
     */
    public function _show_edit($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;
        $data['event'] =& $this->_event;
        $data['layout'] = $this->_layout;

        midcom_show_style('edit-event');
    }
}
?>