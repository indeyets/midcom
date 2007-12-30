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
class cc_kaktus_exhibitions_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * Datamanager 2 instance
     *
     * @access private
     * @var midcom_helper_datamanager2_datamanager $_datamanager
     */
    private $_datamanager = null;

    /**
     * Datamanager 2 controller instance
     *
     * @access private
     * @var midcom_helper_controller2_controller $_controller
     */
    private $_controller = null;

    /**
     * Exhibition event
     *
     * @access private
     * @var midcom_db_event $_event
     */
    private $_event = null;

    /**
     * Subpage for the exhibition
     *
     * @access private
     * @var midcom_db_event $_subpage
     */
    private $_subpage = null;

    /**
     * Subpages of the currently viewed exhibition
     *
     * @access private
     * @var Array $_subpages
     */
    private $_subpages = array ();

    /**
     * attachments of the currently viewed exhibition
     *
     * @access private
     * @var Array $_attachments
     */
    private $_attachments = array ();

    /**
     * Show backlink to the main page
     *
     * @access private
     * @var boolean $_backlink
     */
    private $_backlink = false;

    /**
     * Variable arguments
     *
     * @access private
     * @var Array $_args;
     */
    private $_args = array ();

    /**
     * Next exhibition
     *
     * @access private
     * @var midcom_db_event $_next_event
     */
    private $_next_event = null;

    /**
     * Connect to the parent class constructor
     *
     * @access public
     */
    public function cc_kaktus_exhibitions_handler_view()
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

        // Load AJAX controller if required
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_controller = midcom_helper_datamanager2_controller::create('ajax');
            $this->_controller->schemadb =& $this->_request_data['schemadb'];
            $this->_controller->set_storage($this->_event);
            $this->_controller->process_ajax();
        }

        if (! $this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }

    /**
     * Check the request for showing an exhibition page. Prevent URL hijacking
     *
     * @access public
     * @param Array $args The argument list.
     * @return boolean Indicating success
     */
    public function _can_handle_view($handler_id, $args)
    {
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
     * Set the view toolbar items for special attachments and sub pages
     *
     * @access private
     */
    private function _populate_toolbar()
    {
        if (isset($this->_args[2]))
        {
            $guid = $this->_subpage->guid;
        }
        else
        {
            $guid = $this->_event->guid;
        }

        // Editing link to each page
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            )
        );

        // Add attachment
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "create/attachment/{$this->_event->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create an attachment'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            )
        );

        // Create subpage link
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "create/subpage/{$this->_event->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create a subpage'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
            )
        );

        if (count($this->_attachments) > 0)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "list/attachments/{$this->_event->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('list attachments'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-master-document.png',
                )
            );
        }

        // List subpages
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_event->id);
        $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_ATTACHMENT);

        if ($qb->count() > 0)
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "list/subpages/{$this->_event->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('list subpages'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-master-document.png',
                )
            );
        }
    }

    /**
     * Check if the requested event is showable
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return boolean Indicating success
     */
    public function _handler_view($handler_id, $args, &$data)
    {
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_request_data['master_event']->id);
        $qb->add_constraint('extra', '=', $args[1]);
        $qb->add_constraint('type', '<>', CC_KAKTUS_EXHIBITIONS_ATTACHMENT);
        $qb->set_limit(1);

        // Exhibition not found
        if ($qb->count() === 0)
        {
            return false;
        }

        // Set the arguments
        $this->_args = $args;

        $results = $qb->execute();

        $this->_event =& $results[0];

        // Load the DM2 instance
        $this->_load_datamanager();

        // Get the event attachments
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_event->id);
        $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_ATTACHMENT);
        $qb->add_order('metadata.score', 'DESC');
        $this->_attachments = $qb->execute_unchecked();

        // Handle the subpage request
        if (isset($args[2]))
        {
            $qb = midcom_db_event::new_query_builder();
            $qb->add_constraint('up', '=', $this->_event->id);
            $qb->add_constraint('extra', '=', $args[2]);
            $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_SUBPAGE);
            $qb->set_limit(1);

            if ($qb->count() === 0)
            {
                return false;
            }

            $results = $qb->execute();
            $this->_subpage =& $results[0];

            // Load AJAX controller if required
            if ($this->_config->get('enable_ajax_editing'))
            {
                $this->_controller->set_storage($this->_subpage);
                $this->_controller->process_ajax();
            }
        }

        // Prevent showing exhibitions under the wrong year
        if (date('Y', $this->_event->start) != $args[0])
        {
            return false;
        }

        // Set the URL of the event
        $data['event_url'] = "{$args[0]}/{$this->_event->extra}/";

        // Add the toolbar item for attachments and subpages
        $this->_populate_toolbar();

        // Set the breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "{$args[0]}/",
            MIDCOM_NAV_NAME => $args[0],
        );
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => $data['event_url'],
            MIDCOM_NAV_NAME => $this->_event->title,
        );

        // Bind to context data
        if ($this->_subpage)
        {
            $_MIDCOM->set_pagetitle($this->_subpage->title);
            $_MIDCOM->bind_view_to_object($this->_subpage, $this->_datamanager->schema);

            // Subpage breadcrumb
            $breadcrumb[] = array
            (
                MIDCOM_NAV_URL => "{$data['event_url']}{$this->_subpage->extra}/",
                MIDCOM_NAV_NAME => $this->_subpage->title,
            );

            // Bind the DM2 object
            $this->_datamanager->set_storage($this->_subpage);

            $data['event'] =& $this->_subpage;

            // Back to the exhibition page link
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => $data['event_url'],
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to the exhibition main page'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/back.png',
                )
            );
        }
        else
        {
            $_MIDCOM->set_pagetitle($this->_event->title);
            $_MIDCOM->bind_view_to_object($this->_event, $this->_datamanager->schema);

            // Bind the DM2 object
            $this->_datamanager->set_storage($this->_event);

            $data['event'] =& $this->_event;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        if (!isset($args[2]))
        {
            // Get the possible subpages
            $qb = midcom_db_event::new_query_builder();
            $qb->add_constraint('up', '=', $this->_event->id);
            $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_SUBPAGE);
            $qb->add_order('metadata.score', 'DESC');

            $this->_subpages = $qb->execute();
        }
        else
        {
            $this->_backlink = true;
        }

        // Add JavaScript headers
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/cc.kaktus.exhibitions/thumbnail-handler.js');

        // Proceed to show the page
        return true;
    }

    /**
     * Show the page of an artist
     *
     * @access public
     */
    public function _show_view($handler_id, &$data)
    {
        $data['datamanager'] =& $this->_datamanager;
        $data['handler'] =& $this;

        // Show the main page
        $this->_datamanager->autoset_storage($data['event']);

        if ($this->_config->get('enable_ajax_editing'))
        {
            $data['view'] = $this->_controller->get_content_html();
        }
        else
        {
            $data['view'] = $this->_datamanager->get_content_html();
        }

        midcom_show_style('exhibition-header');

        midcom_show_style('exhibition-details');

        if (count($this->_subpages) > 0)
        {
            midcom_show_style('exhibition-subpage-list-header');
            foreach ($this->_subpages as $subpage)
            {
                $data['subpage'] =& $subpage;
                $this->_datamanager->autoset_storage($subpage);

                midcom_show_style('exhibition-subpage-list-item');
            }
            midcom_show_style('exhibition-subpage-list-footer');
        }

        if ($this->_backlink)
        {
            midcom_show_style('exhibition-backlink');
        }

        midcom_show_style('exhibition-footer');
    }

    /**
     * Show the thumbnails
     *
     * @access public
     */
    public function show_thumbnails()
    {
        // List the attachments as thumbnails
        if (count($this->_attachments) > 0)
        {
            $this->_datamanager->set_schema('attachment');
            midcom_show_style('exhibition-attachments-list-header');
            foreach ($this->_attachments as $attachment)
            {
                $this->_datamanager->autoset_storage($attachment);
                $data['attachment'] =& $attachment;

                midcom_show_style('exhibition-attachments-list-item');

                if (!isset($this->_request_data['first_thumbnail']))
                {
                    $this->_request_data['first_thumbnail'] = $this->_datamanager->get_content_html();
                }
            }
            midcom_show_style('exhibition-attachments-list-footer');
            $this->_datamanager->set_schema('exhibition');
        }
    }

    /**
     * Get the currently ongoing exhibition or handle the error page
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return boolean Indicating success
     */
    public function _handler_current($handler_id, $args, &$data)
    {
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_request_data['master_event']->id);
        $qb->add_constraint('start', '<', time());
        $qb->add_constraint('end', '>', time());
        $qb->add_order('start');
        $qb->set_limit(1);

        if ($qb->count() > 0)
        {
            // Load the DM2 instance
            $this->_load_datamanager();

            $results = $qb->execute();
            $this->_event =& $results[0];

            // Get the event attachments
            $qb = midcom_db_event::new_query_builder();
            $qb->add_constraint('up', '=', $this->_event->id);
            $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_ATTACHMENT);
            $qb->add_order('metadata.score', 'DESC');
            $this->_attachments = $qb->execute_unchecked();

            // Populate the toolbar
            $this->_populate_toolbar();

            // Bind to the view toolbar
            $_MIDCOM->set_pagetitle($this->_event->title);
            $_MIDCOM->bind_view_to_object($this->_event, $this->_datamanager->schema);

        }
        else
        {
            $this->_event = null;
            $qb = midcom_db_event::new_query_builder();
            $qb->add_constraint('up', '=', $this->_request_data['master_event']->id);
            $qb->add_constraint('start', '>', time());
            $qb->add_order('start');
            $qb->set_limit(1);

            if ($qb->count() > 0)
            {
                $results = $qb->execute();
                $this->_next_event =& $results[0];
                $this->_datamanager->autoset_storage($this->_next_event);
                $data['event'] =& $this->_next_event;
                $data['datamanager'] =& $this->_datamanager;
                $data['event_url'] = cc_kaktus_exhibitions_viewer::determine_return_page($this->_next_event);

                // Get the event attachments
                $qb = midcom_db_event::new_query_builder();
                $qb->add_constraint('up', '=', $this->_next_event->id);
                $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_ATTACHMENT);
                $qb->add_order('metadata.score', 'DESC');
                $this->_attachments = $qb->execute_unchecked();
            }
            else
            {
                $data['event'] = null;
            }
        }

        // Subpage breadcrumb
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "current/",
            MIDCOM_NAV_NAME => $this->_l10n->get('current exhibition'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        return true;
    }

    /**
     * Show the current exhibition or an error page
     *
     * @access public
     */
    public function _show_current($handler_id, &$data)
    {
        if ($this->_event)
        {
            $data['handler'] =& $this;
            $this->_datamanager->autoset_storage($this->_event);

            if ($this->_config->get('enable_ajax_editing'))
            {
                $data['view'] = $this->_controller->get_content_html();
            }
            else
            {
                $data['view'] = $this->_datamanager->get_content_html();
            }

            $data['event'] =& $this->_event;
            $data['datamanager'] =& $this->_datamanager;
            midcom_show_style('current-exhibition');
        }
        else
        {
            midcom_show_style('no-current-exhibition');
        }
    }
}
?>