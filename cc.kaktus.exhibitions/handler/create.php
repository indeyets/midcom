<?php
/**
 * @package cc.kaktus.exhibitions
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 5500 2007-03-08 13:22:25Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Handler class for creating an event
 *
 * @package cc.kaktus.exhibitions
 */
class cc_kaktus_exhibitions_handler_create extends midcom_baseclasses_components_handler
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
     * Connect to the parent class constructor
     *
     * @access public
     */
    public function cc_kaktus_exhibitions_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    private function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->schemaname = $this->_layout;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_event = new midcom_db_event();
        $this->_event->up = $this->_up->id;

        // Create the event
        if (!$this->_event->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_event);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new event, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_event;
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
    public function _handler_create($handler_id, $args, &$data)
    {
        // Initialize the new event
        $this->_event = new midcom_db_event();

        // Get the schema layout name
        $this->_layout = $args[0];

        // Load the controller interface
        $this->_load_controller();

        // Handle creation of subpages
        if (isset($args[1]))
        {
            $this->_up = new midcom_db_event($args[1]);

            if (   !$this->_up
                || !isset($this->_up->guid))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to get the master event for creation');
                // This will exit
            }
        }
        else
        {
            $this->_up = new midcom_db_event($this->_config->get('master_event'));
        }

        $this->_up->require_do('midgard:create');

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Update the name
                $name = cc_kaktus_exhibitions_viewer::generate_name($this->_event->title);

                // Check for unique name
                $extra = '';

                // Do until a unique name in the root event has been found
                do
                {
                    $qb = midcom_db_event::new_query_builder();
                    $qb->add_constraint('up', '=', $this->_up->id);
                    $qb->add_constraint('extra', '=', $name . $extra);
                    $qb->add_constraint('type', '=', $name . $extra);

                    if (!$extra)
                    {
                        $extra = 0;
                    }

                    $extra++;
                }
                while ($qb->count() !== 0);

                switch ($this->_event->get_parameter('midcom.helper.datamanager2', 'schema_name'))
                {
                    case 'attachment':
                        $this->_event->type = CC_KAKTUS_EXHIBITIONS_ATTACHMENT;
                        break;
                    case 'subpage':
                        $this->_event->type = CC_KAKTUS_EXHIBITIONS_SUBPAGE;
                        break;
                    default:
                        $this->_event->type = 0;
                        break;
                }

                $this->_event->extra = $name;
                $this->_event->update();

                $_MIDCOM->relocate(cc_kaktus_exhibitions_viewer::determine_return_page($this->_event->guid, $this->_layout));
                break;
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
                    MIDCOM_NAV_URL => "create/{$this->_event->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('create an exhibition'),
                );
                $data['page_title'] = $this->_l10n->get('create an exhibition');
                break;

            case 'subpage':
                $up = new midcom_db_event($this->_event->up);
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . "/{$this->_up->extra}/",
                    MIDCOM_NAV_NAME => $this->_up->title,
                );
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "create/{$this->_event->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('create an exhibition subpage'),
                );
                $data['page_title'] = $this->_l10n->get('create an exhibition subpage');
                break;

            case 'attachment':
                $up = new midcom_db_event($this->_event->up);
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . "/{$this->_up->extra}/",
                    MIDCOM_NAV_NAME => $this->_up->title,
                );
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "create/{$this->_event->guid}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('create an exhibition attachment'),
                );
                $data['page_title'] = $this->_l10n->get('create an exhibition attachment');
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return true;
    }

    /**
     * Show the editing form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access public
     */
    public function _show_create($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;
        $data['event'] =& $this->_event;
        $data['layout'] = $this->_layout;

        midcom_show_style('create-event');
    }
}
?>