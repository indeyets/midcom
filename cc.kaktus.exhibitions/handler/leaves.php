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
class cc_kaktus_exhibitions_handler_leaves extends midcom_baseclasses_components_handler
{
    /**
     * Datamanager 2 instance
     * 
     * @access private
     * @var midcom_helper_datamanager2_datamanager $_datamanager
     */
    private $_datamanager = null;
    
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
     * AJAX HTML mode storage
     * 
     * @access private
     * @var Array $_view_html
     */
    private $_view_html = array ();
    
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
     * Connect to the parent class constructor
     *
     * @access public
     */
    public function cc_kaktus_exhibitions_handler_leaves()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Load the required JavaScript libraries
     * 
     * @access public
     */
    public function _on_initialize()
    {
        // Add JavaScript headers
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/cc.kaktus.exhibitions/sorter.js');
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/cc.kaktus.exhibitions/sorter.css',
            )
        );
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
            
            foreach ($this->_leaves as $leaf)
            {
                $this->_controller->set_storage($leaf);
                $this->_controller->process_ajax();
                $this->_view_html[$leaf->guid] = $this->_controller->get_content_html();
            }
        }
        
        if (! $this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }
    
    /**
     * Set the view toolbar items for special attachments and sub pages
     * 
     * @access private
     */
    private function _populate_toolbar()
    {
        /*
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
        /* */
    }
    
    /**
     * Process the sent form
     * 
     * @access private
     */
    private function _process_form()
    {
        // Set the location for successful form processing to redirect to
        $redirect = date('Y', $this->_event->start) . "/{$this->_event->extra}/";
        
        // Redirect back to the event page on cancel
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate($redirect);
            // This will exit
        }
        
        // Return if the form hasn't been submitted
        if (!isset($_POST['f_submit']))
        {
            return;
        }
        
        $delete = false;
        $count = count($_POST['sortable']);
        
        // Check all the form data
        foreach ($_POST['sortable'] as $i => $guid)
        {
            if ($guid === 'delete')
            {
                $delete = true;
                continue;
            }
            
            // Get the event object
            $event = new midcom_db_event($guid);
            
            if (   !$event
                || !$event->guid)
            {
                continue;
            }
            
            // Set the score and update regardless of possible deletion
            $event->metadata->score = $count - $i;
            $event->update();
            
            if ($delete)
            {
                $event->delete();
            }
        }
        
        $_MIDCOM->relocate($redirect);
        // This will exit
    }
    
    /**
     * Check if the requested event is showable
     * 
     * @access public
     * @return boolean Indicating success
     */
    public function _handler_list($handler_id, $args, &$data)
    {
        $this->_event = new midcom_db_event($args[1]);
        
        if (   !$this->_event
            || !$this->_event->guid)
        {
            return false;
        }
        
        // Process the sent form
        $this->_process_form();
        
        // Get the leaf data
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', $this->_event->id);
        
        switch ($args[0])
        {
            case 'attachments':
                $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_ATTACHMENT);
                break;
            case 'subpages':
                $qb->add_constraint('type', '=', CC_KAKTUS_EXHIBITIONS_SUBPAGE);
                break;
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Unknown type {$args[0]}");
                // This will exit
        }
        
        $qb->add_order('metadata.score', 'DESC');
        
        $this->_leaves = $qb->execute();
        
        // Set the URL of the event
        $data['event_url'] = "{$args[0]}/{$this->_event->extra}/";
        
        // Set the page type
        $data['leaf_type'] = $args[0];
        
        // Load the DM2 instance
        $this->_load_datamanager();
        
        // Add the toolbar item for attachments and subpages
        $this->_populate_toolbar();
        
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => date('Y/', $this->_event->start),
            MIDCOM_NAV_NAME => date('Y', $this->_event->start),
        );
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => cc_kaktus_exhibitions_viewer::determine_return_page($this->_event->guid),
            MIDCOM_NAV_NAME => $this->_event->title,
        );
        $breadcrumb[] = array
        (
            MIDCOM_NAV_URL => "list/{$args[0]}/{$args[1]}/",
            MIDCOM_NAV_NAME => $this->_l10n->get($args[0]),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        $_MIDCOM->set_pagetitle(sprintf($this->_l10n->get('%s for event %s'), $this->_l10n->get($data['leaf_type']), $this->_event->title));
        
        /* */
        return true;
    }
    
    /**
     * Show the page of an artist
     * 
     * @access public
     */
    public function _show_list($handler_id, &$data)
    {
        $data['datamanager'] =& $this->_datamanager;
        $data['event'] =& $this->_event;
        
        midcom_show_style('exhibition-leaves-list-header');
        
        if (count($this->_leaves) > 0)
        {
            foreach ($this->_leaves as $leaf)
            {
                $data['leaf'] =& $leaf;
                $this->_datamanager->autoset_storage($leaf);
                
                if ($this->_config->get('enable_ajax_editing'))
                {
                    $data['view'] = $this->_view_html[$leaf->guid];
                }
                else
                {
                    $data['view'] = $this->_datamanager->get_content_html();
                }
                
                $data['attachment'] =& $attachment;
                
                midcom_show_style('exhibition-leaves-list-item');
            }
        }
        midcom_show_style('exhibition-leaves-list-footer');
    }
}
?>