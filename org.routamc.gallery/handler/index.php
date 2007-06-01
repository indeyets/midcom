<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Gallery photo display
 *
 * @package org.routamc.gallery
 */
class org_routamc_gallery_handler_index  extends midcom_baseclasses_components_handler
{
    /*
     * The midcom_baseclasses_components_handler class defines a bunch of helper vars
     * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
     */

    /**
     * Simple default constructor.
     */
    function org_routamc_gallery_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _prepare_ajax_controllers()
    {
        // Initiate AJAX controllers for all photos
        $this->_request_data['controllers'] = array();
        foreach ($this->_request_data['photos'] as $photo)
        {
            $this->_request_data['controllers'][$photo->id] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controllers'][$photo->id]->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controllers'][$photo->id]->set_storage($photo);
            $this->_request_data['controllers'][$photo->id]->process_ajax();
        }
    }

    /**
     * The handler displaying photos
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_index($handler_id, $args, &$data)
    {
        if (!$this->_config->get('gallery_type'))
        {
            // No type yet set, relocate to config screen
            $_MIDCOM->relocate('config.html');
            // This will exit
        }
        debug_push_class(__CLASS__, __FUNCTION__);

        $data['node'] =& $this->_topic;
        
        //mgd_debug_start();
        $qb = new org_openpsa_qbpager('org_routamc_gallery_photolink_dba', 'gallery_index');
        $qb->results_per_page = $this->_config->get('photos_per_page');
        $qb->add_constraint('node', '=', $this->_topic->id);

        // FIXME: This property should be rethought
        $qb->add_constraint('censored', '=', 0);
        
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
                $qb->add_order($ordering, 'DESC');
            }
            else
            {
                $qb->add_order($ordering);
            }
        }        

        $this->_request_data['qb'] =& $qb;

        $photolinks = $qb->execute();
        //mgd_debug_stop();
        $data['photos'] = array();
        debug_add('found ' . count($photolinks) . ' links');
        foreach ($photolinks as $photolink)
        {
            $photo = new org_routamc_photostream_photo_dba($photolink->photo);
            if (!$photo)
            {
                debug_add("Could not read photo #{$photolink->photo}, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
                continue;
            }
            $data['photos'][] = $photo;
        }
        
        // Get sub galleries
        $data['galleries'] = array();
        $nap = new midcom_helper_nav();
        $nodes = $nap->list_nodes($this->_topic->id);
        foreach ($nodes as $node_id)
        {
            $node = $nap->get_node($node_id);
            if ($node[MIDCOM_NAV_COMPONENT] == 'org.routamc.gallery')
            {
                $data['galleries'][] = $node;
            }
        }
        debug_pop();
        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        return true;
    }

    /**
     * This function does the output.
     *
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('show_index_header');

        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);

        foreach ($data['galleries'] as $gallery)
        {
            $data['gallery'] =& $gallery;
            $qb = org_routamc_gallery_photolink_dba::new_query_builder();
            $qb->set_limit(1);
            $qb->add_constraint('node', '=', $gallery[MIDCOM_NAV_ID]);

            // FIXME: This property should be rethought
            $qb->add_constraint('censored', '=', 0);

            $photolinks = $qb->execute();
            if (count($photolinks) == 0)
            {
                // Skip this gallery, it has no images
                continue;
            }

            $data['photo'] = new org_routamc_photostream_photo_dba($photolinks[0]->photo);
            if (!$data['photo'])
            {
                // Something wrong with this gallery
                continue;
            }

            if (! $data['datamanager']->autoset_storage($data['photo']))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The datamanager for photo {$data['photo']->id} could not be initialized, skipping it.");
                debug_print_r('Object was:', $data['photo']);
                debug_pop();
                continue;
            }
            $data['photo_view'] = $data['datamanager']->get_content_html();

            midcom_show_style('show_index_gallery');
        }

        foreach ($data['photos'] as $photo)
        {
            $data['photo'] = $photo;

            $data['photo_view'] = $data['controllers'][$photo->id]->get_content_html();
            $data['datamanager'] =& $data['controllers'][$photo->id]->datamanager;

            midcom_show_style('show_index_item');
        }

        midcom_show_style('show_index_footer');
    }
}
?>
