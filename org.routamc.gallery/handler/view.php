<?php
/**
 * Created on 2006-Oct-Thu
 * @author tarjei huse
 * @package org.routamc.photostream
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */
class org_routamc_gallery_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * Datamanager2 instance for AJAX editing of a photo
     * 
     * @access private
     * @var midcom_helper_datamanager2_controller $_controller
     */
    var $_controller;
    
    /**
     * Datamanager2 instance for showing the photo
     * 
     * @access private
     * @var midcom_helper_datamanager2_datamanager $_datamanager
     */
    var $_datamanager;
    
    /**
     * Photo shown on the page
     * 
     * @var org_routamc_photostream_photo $_photo
     */
    var $_photo;
    
    /**
     * Simple default constructor.
     */
    function org_routamc_photostream_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Load photo by ID
     * 
     * 
     */
    function _load_photo($id)
    {
        $this->_photo = new org_routamc_photostream_photo_dba($id);
        if (   !is_object($this->_photo)
            || !$this->_photo->guid)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not load photo {$id}");
            // This will exit
        }
        
        // Initialize the DM2 instance
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);
        
        if (!$this->_datamanager->set_schema('photo'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "DM2 could not set schema");
            // This will exit
        }
        if (!$this->_datamanager->set_storage($this->_photo))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "DM2 could not set storage");
            // This will exit
        }
        
        // Get the AJAX controller if required
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_controller = midcom_helper_datamanager2_controller::create('ajax');
            $this->_controller->schemadb =& $this->_request_data['schemadb'];
            $this->_controller->set_storage($this->_photo);
            $this->_controller->process_ajax();
        }
        
        return true;
    }


    /**
     * The handler for displaying a single photo
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_view($handler_id, $args, &$data)
    {
        // Prepare object and DM2
        if (!$this->_load_photo($args[0]))
        {
            return false;
        }
        
        $data['photo'] =& $this->_photo;
        
        //get the two neighboring photos
        $mc = new midgard_collector('org_routamc_gallery_photolink', 'node', $this->_topic->id);
        $mc->set_key_property('photo');
        // FIXME: This property should be rethought
        $mc->add_constraint('censored', '=', 0);

        foreach ($this->_config->get('index_order') as $ordering)
        {
            if (preg_match('/\s*reversed?\s*/', $ordering))
            {
                $reversed = true;
                $ordering = preg_replace('/\s*reversed?\s*/', '', $ordering);
            }
            else
            {
                $reversed = false;
            }

            if ($ordering === 'metadata.score')
            {
                if (version_compare(mgd_version(), '1.8.2', '<'))
                {
                    $ordering = 'score';
                    $reversed = false;
                }
            }

            if (   strpos($ordering, '.')
                && !class_exists('midgard_query_builder'))
            {
                debug_add("Ordering by linked properties requires 1.8 series Midgard", MIDCOM_LOG_WARN);
                continue;
            }

            if ($reversed)
            {
                $mc->add_order($ordering, 'DESC');
            }
            else
            {
                $mc->add_order($ordering);
            }
        }
        
        $mc->execute();
        $photolinks = $mc->list_keys();
        $photolinks = array_keys($photolinks);
        
        debug_add('found ' . count($photolinks) . ' links');
        
        $i = 0;
        $data['next'] = null;
        $data['previous'] = null;
        
        foreach ($photolinks as $photolink)
        {
            if ($photolink == $this->_photo->id)
            {
                if (isset($photolinks[$i - 1]))
                {
                    $previous = new org_routamc_photostream_photo_dba($photolinks[$i - 1]);
                    $data['previous'] = '<a href="' . $previous->guid . '.html">&laquo;&nbsp;' . $this->_l10n->get('previous') . '</a>&nbsp;';
                }
                if (isset($photolinks[$i + 1]))
                {
                    $next = new org_routamc_photostream_photo_dba($photolinks[$i + 1]);
                    $data['next'] = '&nbsp;<a href="' . $next->guid . '.html">' . $this->_l10n->get('next') . '&nbsp;&raquo;</a>';
                }
                break;
            }
            $i++;
        }
        
        // Add the toolbar items
        $nap = new midcom_helper_nav();
        $data['photostream_node'] = $nap->get_node($data['photo']->node);
        $data['gallery_node'] = $nap->get_node($this->_topic->id);
        if (!empty($data['photostream_node']))
        {
            // Add toolbar items
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => $data['photostream_node'][MIDCOM_NAV_FULLURL] . "edit/{$data['photo']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => $data['photo']->can_do('midgard:update'),
                    MIDCOM_TOOLBAR_ACCESSKEY => 'e',
                )
            );
            /*
            // PONDER: should  this be just delete from this gallery (ie, use the censored property) ??
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => $data['photostream_node'][MIDCOM_NAV_FULLURL] . "delete/{$data['photo']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => $data['photo']->can_do('midgard:delete'),
                    MIDCOM_TOOLBAR_ACCESSKEY => 'd',
                )
            );
            */
        }

        $_MIDCOM->bind_view_to_object($data['photo'], $this->_datamanager->schema->name);

        $data['view_title'] = $data['photo']->title;

        // Figure out how URLs to photo lists should be constructed
        $data['photographer'] = new midcom_db_person($data['photo']->photographer);
        if ($data['photographer']->username)
        {
            $data['user_url'] = $data['photographer']->username;
        }
        else
        {
            $data['user_url'] = $data['photographer']->guid;
        }

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     * This function does the output.
     */
    function _show_view($handler_id, &$data)
    {
        // Get the view HTML depending on the configuration
        if ($this->_config->get('enable_ajax_editing'))
        {
            $data['photo_view'] = $this->_controller->get_content_html();
        }
        else
        {
            $data['photo_view'] = $this->_datamanager->get_content_html();
        }

        midcom_show_style('show_photo');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     * @param string $handler_id
     * @access private
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = array();

        $tmp[] = array
        (
            MIDCOM_NAV_URL => $this->_request_data['gallery_node'][MIDCOM_NAV_FULLURL] . "{$this->_request_data['photo']->guid}.html",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );
        // TODO: How can we present the correct gallery page in breacrumb ?

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>