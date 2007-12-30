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
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
 * @package org.routamc.gallery
 */
class org_routamc_gallery_handler_index  extends midcom_baseclasses_components_handler
{
    /**
     * Navigation access point
     *
     * @var midcom_helper_nav $_nap
     */
    var $_nap;

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
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return bool Indicating success.
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

        // Get sub galleries
        $data['galleries'] = array();
        $this->_nap = new midcom_helper_nav();
        $nodes = $this->_nap->list_nodes($this->_topic->id);

        foreach ($nodes as $node_id)
        {
            $node = $this->_nap->get_node($node_id);
            if ($node[MIDCOM_NAV_COMPONENT] === 'org.routamc.gallery')
            {
                $data['galleries'][] = $node;
            }
        }

        // Initialize QB Pager
        $qb = new org_openpsa_qbpager('org_routamc_gallery_photolink_dba', 'gallery_index');

        $qb->listen_parameter('org_routamc_gallery_order', array('reversed'));
        $qb->listen_parameter('org_routamc_gallery_order_by', '*');

        // TODO: Something like this offset should be possible to be done in org.openpsa.qbpager,
        // but has to be done there first. This will push the result set backwards to include the
        // subgalleries in the result set, but not to overflow because of them!
        /*
        // Check the offset
        if (   !isset($_GET['org_openpsa_qbpager_gallery_index_page'])
            || $_GET['org_openpsa_qbpager_gallery_index_page'] == 1)
        {
            $offset = count($data['galleries']);
        }
        else
        {
            $offset = -1 * (count($data['galleries']));
        }

        $qb->results_per_page = $this->_config->get('photos_per_page') - $offset;
        */

        $qb->results_per_page = $this->_config->get('photos_per_page');
        $qb->add_constraint('node', '=', $this->_topic->id);

        // FIXME: This property should be rethought
        $qb->add_constraint('censored', '=', 0);

        // Prevent errors
        if (is_array($this->_config->get('index_order')))
        {
            $order = $this->_config->get('index_order');
        }
        else
        {
            $order = array
            (
                $this->_config->get('index_order'),
            );
        }

        if (isset($_REQUEST['org_routamc_gallery_order_by']))
        {
            $order_str = $_REQUEST['org_routamc_gallery_order_by'];

            if (   isset($_REQUEST['org_routamc_gallery_order'])
                && $_REQUEST['org_routamc_gallery_order'] == 'DESC')
            {
                $order_str += " reversed";
            }

            $order = array( $order_str );
        }

        // Set the orders
        foreach ($order as $ordering)
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
            if (   !$photo
                || !$photo->guid)
            {
                debug_add("Could not read photo #{$photolink->photo}, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
                continue;
            }
            $data['photos'][] = $photo;
        }


        debug_pop();
        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        return true;
    }

    /**
     * Scan the subgalleries for photo links
     *
     * @access private
     * @param integer $node    ID of the photo gallery
     * @return org_routamc_photostream_photo_dba or false on failure
     */
    function _scan_subgalleries($node)
    {
        $mc = org_routamc_gallery_photolink_dba::new_collector('node', $node);
        $mc->add_value_property('photo');
        $mc->add_constraint('censored', '=', 0);
        $mc->add_order('photo.taken', 'DESC');
        $mc->set_limit(1);
        $mc->execute();
        $photolinks = $mc->list_keys();

        foreach ($photolinks as $guid => $array)
        {
            $id = $mc->get_subkey($guid, 'photo');
            $photo = new org_routamc_photostream_photo_dba($id);
            return $photo;
        }

        $mc = midcom_db_topic::new_collector('up', $node);
        $mc->add_value_property('id');
        $mc->add_constraint('up', '=', $node);
        $mc->add_constraint('component', '=', 'org.routamc.gallery');
        $mc->add_constraint('metadata.navnoentry', '=', 0);
        $mc->add_order('score');

        $mc->execute();

        $nodes = $mc->list_keys();

        foreach ($nodes as $guid => $array)
        {
            $id = $mc->get_subkey($guid, 'id');

            $link = $this->_scan_subgalleries($id);

            if ($link)
            {
                return $link;
            }
        }

        return false;
    }

    /**
     * This function does the output.
     *
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('show_index_header');

        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);

        // Show the subgalleries only on the frontpage of the gallery
        if (   !isset($_GET['org_openpsa_qbpager_gallery_index_page'])
            || $_GET['org_openpsa_qbpager_gallery_index_page'] == 1)
        {
            foreach ($data['galleries'] as $gallery)
            {
                $data['gallery'] =& $gallery;

                // Get the subgallery photo
                $data['photo'] = $this->_scan_subgalleries($gallery[MIDCOM_NAV_ID]);

                if (   !$data['photo']
                    || !is_a($data['photo'], 'org_routamc_photostream_photo_dba')
                    || !$data['photo']->guid)
                {
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
