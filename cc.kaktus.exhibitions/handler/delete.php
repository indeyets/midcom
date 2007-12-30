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
class cc_kaktus_exhibitions_handler_delete extends midcom_baseclasses_components_handler
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
    public function cc_kaktus_exhibitions_handler_delete()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Load the DM2 instance
     *
     * @access private
     */
    private function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (! $this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }

    /**
     * Process the form and redirect according to the action
     *
     * @access private
     */
    private function _process_form()
    {
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate(cc_kaktus_exhibitions_viewer::determine_return_page($this->_event->guid));
            // This will exit
        }

        if (!isset($_POST['f_submit']))
        {
            return;
        }

        // Try to delete the event
        if (!$this->_event->delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r("Failed to delete the event ({$this->_event->guid}), last mgd_errstr() said: " . mgd_errstr(), $this->_event, MIDCOM_LOG_ERROR);
            debug_pop();

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to delete the event, see error level log for details.');
            // This will exit
        }

        // All went well, redirect to the parent page
        $_MIDCOM->relocate(cc_kaktus_exhibitions_viewer::determine_return_page($this->_parent->guid));
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
    public function _handler_delete($handler_id, $args, &$data)
    {
        $this->_event = new midcom_db_event($args[0]);
        $this->_parent = new midcom_db_event($this->_event->up);

        if (   !$this->_event
            || !isset($this->_event->guid))
        {
            return false;
        }

        // Require the correct ACL
        $this->_event->require_do('midgard:delete');

        $this->_process_form();
        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($this->_event);

        $this->_layout = $this->_event->get_parameter('midcom.helper.datamanager2', 'schema_name');

        // Set the page title
        $_MIDCOM->set_pagetitle(sprintf($this->_l10n->get('delete %s'), $this->_layout));

        // Bind to the context data
        $this->_view_toolbar->bind_to($this->_event);

        switch ($this->_layout)
        {
            case 'exhibition':
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_event->start) . '/',
                    MIDCOM_NAV_NAME => date('Y', $this->_event->start),
                );
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
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . '/',
                    MIDCOM_NAV_NAME => date('Y', $this->_up->start),
                );
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
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . '/',
                    MIDCOM_NAV_NAME => date('Y', $this->_up->start),
                );
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => date('Y', $this->_up->start) . "/{$this->_up->extra}/",
                    MIDCOM_NAV_NAME => $this->_up->title,
                );
                break;
        }

        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "delete/{$this->_event->guid}/",
            MIDCOM_NAV_NAME => $this->_l10n->get('delete'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return true;
    }

    /**
     * Show the editing form
     *
     * @access public
     */
    public function _show_delete($handler_id, &$data)
    {
        $data['event'] =& $this->_event;
        $data['layout'] = $this->_layout;
        $data['datamanager'] =& $this->_datamanager;

        midcom_show_style('delete-event');
    }
}
?>