<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Gallery photo sorting
 *
 * @package org.routamc.gallery
 */
class org_routamc_gallery_handler_sort extends midcom_baseclasses_components_handler
{
    /**
     * Index of photos
     *
     * @access private
     * @var Array
     */
    var $_photos = array ();
    
    /**
     * 
     * 
     * 
     */
    
    /**
     * Constructor, connect to the parent class
     * 
     * @access public
     */
    function org_routamc_gallery_handler_sort()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Load midcom_helper_datamanager2_datamanager for content
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);
        
        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
        
        return true;
    }
    
    /**
     * Handler method for sorting
     * 
     * @access public
     * @return boolean Indicating success
     */
    function _handler_sort($handler_id, $args, &$data)
    {
        // ACL handling
        $this->_topic->require_do('midgard:update');
        $this->_topic->require_do('midgard:create');
        
        // Add JavaScript headers
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL.'/org.routamc.gallery/sorter.js');
        
        // Add style sheet
        $_MIDCOM->add_link_head
        (
            array
            (
                'type' => 'text/css',
                'rel' => 'stylesheet',
                'href' => MIDCOM_STATIC_URL . '/org.routamc.gallery/sorter.css',
            )
        );
        
        // Initialize DM2 instance
        $this->_load_datamanager();
        
        return $this->_process_form();
    }
    
    /**
     * Process the form. If the form has been submitted this method will not return anything, but relocate straight
     * to the correct page.
     * 
     * @access private
     * @return boolean indicating success
     */
    function _process_form()
    {
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->relocate('');
            // This will exit
        }
        
        // Form processing ends if there is no form submitted for processing
        if (!isset($_POST['f_submit']))
        {
            return true;
        }
        
        // Initialize debugging only for changes
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $count = count($_POST['sortable']);
        
        $n = 1;
        
        foreach ($_POST['sortable'] as $i => $value)
        {
            $score = $count - $i;
            
            if (!preg_match('/^([a-z]+)_(.+?)_(.+)$/', $value, $regs))
            {
                continue;
            }
            
            switch ($regs[1])
            {
                case 'gallery':
                case 'group':
                    if (ereg('^new:', $regs[2]))
                    {
                        debug_add('Create a new topic');
                        $create = true;
                        $topic = new midcom_db_topic();
                        
                        // MidCOM 2.8 compatibility
                        if (isset($topic->component))
                        {
                            $topic->component = $this->_topic->component;
                        }
                        
                        $topic->extra = $regs[3];
                        $topic->up = $this->_topic->id;
                        $topic->name = midcom_generate_urlname_from_string($regs[3]);
                    }
                    else
                    {
                        $create = false;
                        $topic = new midcom_db_topic((int) $regs[2]);
                        
                        if (   !$topic
                            || !$topic->id)
                        {
                            debug_add("Failed to get topic with id {$regs[2]}. Last mgd_errstr() was " . mgd_errstr(), MIDCOM_LOG_ERROR);
                            debug_pop();
                            
                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                            // This will exit
                        }
                        
                        if ($topic->extra !== $regs[3])
                        {
                            $topic->extra = $regs[3];
                        }
                    }
                    
                    if ($topic->id !== $this->_topic->id)
                    {
                        $topic->score = $i;
                    }
                    
                    if ($create)
                    {
                        if (!$topic->create())
                        {
                            debug_print_r("Failed to create a topic, last error was " . mgd_errstr(), $topic, MIDCOM_LOG_ERROR);
                            debug_pop();
                            
                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                            // This will exit
                        }
                        
                        // Set the component information
                        $topic->component = $this->_topic->component;
                        foreach ($this->_topic->list_parameters('org.routamc.gallery') as $name => $value)
                        {
                            $topic->set_parameter('org.routamc.gallery', $name, $value);
                        }
                    }
                    else
                    {
                        if (!$topic->update())
                        {
                            debug_print_r("Failed to update the topic {$topic->id}, last error was " . mgd_errstr(), $topic, MIDCOM_LOG_ERROR);
                            debug_pop();
                            
                            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                            // This will exit
                        }
                    }
                    
                    if (   isset($_POST['approvals'])
                        && $_POST['approvals'])
                    {
                        // Get the original approval status
                        $metadata =& midcom_helper_metadata::retrieve($topic);
                        $metadata->approve();
                    }
                    
                    break;
                
                case 'link':
                    $link = new org_routamc_gallery_photolink_dba((int) $regs[2]);
                    
                    $qb = org_routamc_gallery_photolink_dba::new_query_builder();
                    $qb->add_constraint('id', '<>', (int) $regs[2]);
                    $qb->add_constraint('node', '=', $topic->id);
                    $qb->add_constraint('photo', '=', (int) $regs[3]);
                    
                    if (   !$link
                        || !$link->id)
                    {
                        debug_add("Failed to get a link with id '{$regs[2]}'", MIDCOM_LOG_ERROR);
                        debug_pop();
                        
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                        // This will exit
                    }
                    
                    if ($qb->count() !== 0)
                    {
                        $link->delete();
                        continue;
                    }
                    
                    $link->node = $topic->id;
                    $link->score = $score;
                    
                    if (   isset($link->metadata)
                        && isset($link->metadata->score))
                    {
                        $link->metadata->score = $score;
                    }
                    
                    if (!$link->update())
                    {
                        debug_print_r("Failed to update the photo link {$link->id}, last error was " . mgd_errstr(), $link, MIDCOM_LOG_ERROR);
                        debug_pop();
                        
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to process the form data, see error level log for details');
                        // This will exit
                    }
                    
                    if (   isset($_POST['approvals'])
                        && $_POST['approvals'])
                    {
                        // Get the original approval status
                        $metadata =& midcom_helper_metadata::retrieve($link);
                        $metadata->approve();
                    }
            }
        }
        
        $_MIDCOM->relocate('');
    }
    
    /**
     * Show the sorting interface
     * 
     * @access public
     */
    function _show_sort($handler_id, &$data)
    {
        midcom_show_style('gallery-sort-header');
        
        $data['gallery'] =& $this->_topic;
        $data['class'] = 'master';
        $data['datamanager'] =& $this->_datamanager;
        
        midcom_show_style('gallery-sort-subset-header');
        
        $organizer = new org_routamc_gallery_organizer('metadata.score');
        $organizer->node = $this->_topic->id;
        
        foreach ($organizer->get_sorted() as $link_id => $photo)
        {
            $this->_datamanager->autoset_storage($photo);
            $data['photo'] =& $photo;
            $data['link_id'] = $link_id;
            
            midcom_show_style('gallery-sort-subset-item');
        }
        
        midcom_show_style('gallery-sort-subset-footer');
        
        // Get the other galleries
        $nap = new midcom_helper_nav();
        $data['class'] = 'group';
        
        // Print subset for each gallery
        foreach ($nap->list_nodes($this->_topic->id) as $node_id)
        {
            $node = $nap->get_node($node_id);
            
            // Skip all the non-gallery components
            if ($node[MIDCOM_NAV_COMPONENT] !== 'org.routamc.gallery')
            {
                continue;
            }
            
            $data['gallery'] =& $node[MIDCOM_NAV_OBJECT];
            
            midcom_show_style('gallery-sort-subset-header');
            
            $organizer->node = $node_id;
            
            foreach ($organizer->get_sorted() as $link_id => $photo)
            {
                $this->_datamanager->autoset_storage($photo);
                $data['link_id'] = $link_id;
                $data['photo'] =& $photo;
                
                midcom_show_style('gallery-sort-subset-item');
            }
            
            midcom_show_style('gallery-sort-subset-footer');
        }
        
        midcom_show_style('gallery-sort-footer');
    }
}
?>