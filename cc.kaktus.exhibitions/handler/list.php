<?php
/**
 * @package cc.kaktus.exhibitions
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 5500 2007-03-08 13:22:25Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Handler class for listing exhibitions
 *
 * @package cc.kaktus.exhibitions
 */
class cc_kaktus_exhibitions_handler_list extends midcom_baseclasses_components_handler
{
    /**
     * List of the years
     *
     * @access private
     * @var Array $years
     */
    private $_years = array ();

    /**
     * Datamanager 2 instance
     *
     * @access private
     * @var midcom_helper_datamanager2_datamanager $_datamanager
     */
    private $_datamanager = null;

    /**
     * Master event
     *
     * @var midcom_db_event $_master_event
     */
    private $_master_event;

    /**
     * Connect to the parent class constructor
     *
     * @access public
     */
    public function cc_kaktus_exhibitions_handler_list()
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

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }

    /**
     * Handler for showing the years. Always returns true
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    public function _handler_years($handler_id, $args, &$data)
    {
        // No master event GUID set, generate it. Done on the welcome page, since authenticated user SHOULD have
        // created the original topic in the first place
        if (!$this->_config->get('master_event'))
        {
            return false;
        }
        else
        {
            $this->_master_event = new midcom_db_event($this->_config->get('master_event'));
        }

        // Get the years
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_master_event->id);
        $qb->add_order('start', 'DESC');

        // Get the count for each year available
        foreach ($qb->execute() as $event)
        {
            if (!isset($this->_years[date('Y', $event->start)]))
            {
                $this->_years[date('Y', $event->start)] = 0;
            }

            $this->_years[date('Y', $event->start)]++;
        }

        return true;
    }

    /**
     * Show a list of years
     *
     * @access public
     */
    public function _show_years($handler_id, &$data)
    {
        midcom_show_style('exhibition-years-header');

        if (count($this->_years) > 0)
        {
            foreach ($this->_years as $year => $count)
            {
                $data['count'] = $count;
                $data['year'] = $year;

                midcom_show_style('exhibition-years-item');
            }
        }
        else
        {
            midcom_show_style('exhibition-years-none');
        }
        midcom_show_style('exhibition-years-footer');
    }

    /**
     * Check the request for listing. Prevent URL hijacking
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @return boolean Indicating success
     */
    public function _can_handle_list($handler_id, $args)
    {
        // Return always true if no arguments have been set
        if (!isset($args[0]))
        {
            return true;
        }

        // Check for URL-hijacking
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $args[0]);
        if (   $qb->count() === 0
            && (int) $args[0] !== 0)
        {
            return true;
        }

        return false;
    }

    /**
     * Handler for request of seeing artists on a certain year
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return boolean Indicating success
     */
    public function _handler_list($handler_id, $args, &$data)
    {
        // No master event GUID set, generate it. Done on the welcome page, since authenticated user SHOULD have
        // created the original topic in the first place
        if (!$this->_config->get('master_event'))
        {
            return false;
        }
        else
        {
            $this->_master_event = new midcom_db_event($this->_config->get('master_event'));
        }

        $qb = midcom_db_event::new_query_builder();

        $qb->add_constraint('up', '=', $this->_master_event->id);
        switch($handler_id)
        {
            case 'future':
                $qb->add_order('start', 'DESC');
                $qb->add_constraint('start', '>', time());
                $qb->add_order('start', 'DESC');

                $data['page_title'] = sprintf($this->_l10n->get('%s exhibitions'), $this->_l10n->get($handler_id));
                $this->_component_data['active_leaf'] = $handler_id;

                // Get the events
                $this->_events = $qb->execute();
                break;

            case 'past':
                $qb->add_constraint('end', '<', time());
                $qb->add_order('start', 'DESC');

                $data['page_title'] = sprintf($this->_l10n->get('%s exhibitions'), $this->_l10n->get($handler_id));
                $this->_component_data['active_leaf'] = $handler_id;

                // Get the events
                $this->_events = $qb->execute();
                break;

            default:
                $qb->begin_group('AND');
                    $qb->add_constraint('start', '>', mktime(0, 0, 0, 1, 1, (int) $args[0]));
                    $qb->add_constraint('start', '<', mktime(0, 0, 0, 1, 1, (int) $args[0] + 1));
                $qb->end_group();

                $qb->add_order('start', 'DESC');

                // Set the breadcrumb path
                $breadcrumb = array ();
                $breadcrumb[] = array
                (
                    MIDCOM_NAV_URL => "{$args[0]}/",
                    MIDCOM_NAV_NAME => $args[0],
                );
                $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

                // Get the events
                $this->_events = $qb->execute();
                $data['year'] = $args[0];
                $data['page_title'] = sprintf($data['l10n']->get('exhibitions for year %s'), $data['year']);

        }

        // Load the DM2 instance
        $this->_load_datamanager();

        return true;
    }

    /**
     * Show the list of exhibitions on a certain year
     *
     * @access public
     */
    public function _show_list($handler_id, &$data)
    {

        $data['exhibitions'] = $this->_events;
        midcom_show_style('exhibition-list-header');

        foreach ($this->_events as $event)
        {
            $data['event'] =& $event;
            $this->_datamanager->autoset_storage($event);
            $data['datamanager'] =& $this->_datamanager;

            midcom_show_style('exhibition-list-item');
        }

        midcom_show_style('exhibition-list-footer');
    }
}
?>