<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar event viewer
 *
 * @package net.nemein.calendar
 */
class net_nemein_calendar_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * The Datamanager of the article to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_calendar_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_request_data['event']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for event {$this->_request_data['event']->id}.");
            // This will exit.
        }
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    /**
     * Can-Handle check against the current event GUID. We have to do this explicitly
     * in can_handle already, otherwise we would hide all subtopics as the request switch
     * accepts all argument count matches unconditionally.
     * 
     * @access private
     * @return boolean
     */
    function _can_handle_view($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Prevent URL hijacking
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('name', '=', (string) $args[0]);
        $qb->add_constraint('up', '=', $this->_topic->id);
        
        if ($qb->count() !== 0)
        {
            return false;
        }
        
        $qb = net_nemein_calendar_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_request_data['root_event']->id);
        $qb->add_constraint('extra', '=', $args[0]);
        
        if ($qb->count() === 0)
        {
            return false;
        }
        
        $events = $qb->execute();
        $this->_request_data['event'] = $events[0];

        return true;        
    }


    function _handler_view($handler_id, $args, &$data)
    {
        if ($handler_id == 'archive-view')
        {
            if (!$this->_config->get('archive_enable'))
            {
                return false;
            }        
            $this->_request_data['archive_mode'] = true;
            $this->_component_data['active_leaf'] = "{$this->_topic->id}_ARCHIVE";
        }
        else
        {
            $this->_request_data['archive_mode'] = false;
        }
        if ($this->_request_data['event']->up == $this->_request_data['root_event']->id)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "edit/{$this->_request_data['event']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('edit'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_request_data['event']->can_do('midgard:update'),
                    MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                )
            );
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "delete/{$this->_request_data['event']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('delete'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_request_data['event']->can_do('midgard:delete'),
                    MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                )
            );
        }
        
        $this->_load_datamanager();

        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_request_data['controller'] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controller']->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controller']->set_storage($this->_request_data['event']);
            $this->_request_data['controller']->process_ajax();
        }

        $_MIDCOM->set_pagetitle($this->_request_data['event']->title);
        
        $_MIDCOM->bind_view_to_object($this->_request_data['event'], $this->_datamanager->schema->name);

        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "{$this->_request_data['event']->extra}/",
            MIDCOM_NAV_NAME => $this->_request_data['event']->title,
        );
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        
        return true;
    }

    function _show_view($handler_id, &$data)
    {
        if ($this->_config->get('enable_ajax_editing'))
        {
            // For AJAX handling it is the controller that renders everything
            $this->_request_data['view_event'] = $this->_request_data['controller']->get_content_html();
        }
        else
        {
            $this->_request_data['view_event'] = $data['datamanager']->get_content_html();
        }    
        midcom_show_style('show_event');
    }
}

?>
