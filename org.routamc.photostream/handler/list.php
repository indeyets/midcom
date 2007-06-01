<?php
/**
 * Created on 2006-Oct-Thu
 * @package org.routamc.photostream
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */
class org_routamc_photostream_handler_list extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function org_routamc_photostream_handler_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Resolve username or person GUID to a midcom_db_person object
     *
     * @param string $username Username or GUID
     * @return midcom_db_person Matching person or null
     */
    function _resolve_user($username)
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '=', $username);
        $users = $qb->execute();
        if (count($users) > 0)
        {
            return $users[0];
        }

        if (mgd_is_guid($username))
        {
            // Try resolving as GUID as well
            $user = new midcom_db_person($username);
            return $user;
        }

        return null;
    }

    /**
     * Prepare a paged query builder for listing photos
     */
    function &_prepare_photo_qb()
    {
        $qb = new org_openpsa_qbpager('org_routamc_photostream_photo_dba', 'org_routamc_photostream_photo');
        $qb->results_per_page = $this->_config->get('photos_per_page');
        $qb->add_constraint('node', '=', $this->_topic->id);
        $this->_request_data['qb'] =& $qb;
        return $qb;
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
     * The handler for displaying a photographer's photostream
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_photostream_list($handler_id, $args, &$data)
    {
        if ($handler_id == 'photostream_list')
        {
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('photos of %s'), $data['user']->name);
            $data['user_url'] = $args[0];
        }
        else
        {
            $data['view_title'] = $this->_l10n->get('all photos');
            $data['user_url'] = 'all';
        }

        // List photos
        $qb =& $this->_prepare_photo_qb();

        if ($handler_id == 'photostream_list')
        {
            // Limit list of photos to the user
            $qb->add_constraint('photographer', '=', $data['user']->id);
        }

        $qb->add_order('taken', 'DESC');
        $data['photos'] = $qb->execute();

        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _show_photostream_list($handler_id, &$data)
    {
        $this->_show_photostream($handler_id, &$data);
    }
    
    /**
     * The handler for displaying a photographer's photostream
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_photostream_latest($handler_id, $args, &$data)
    {
        if ($handler_id == 'photostream_latest')
        {
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('latest photos of %s'), $data['user']->name);
            $data['user_url'] = $args[0];
            $data['limit'] = $args[1];
        }
        else
        {
            $data['view_title'] = $this->_l10n->get('latest photos');
            $data['user_url'] = 'all';
            $data['limit'] = $args[0];
        }

        // List photos
        $qb = org_routamc_photostream_photo_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->_topic->id);

        if ($handler_id == 'photostream_latest')
        {
            // Limit list of photos to the user
            $qb->add_constraint('photographer', '=', $data['user']->id);
        }

        $qb->add_order('taken', 'DESC');
        $qb->set_limit($data['limit']);
        
        $data['photos'] = $qb->execute();

        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _show_photostream_latest($handler_id, &$data)
    {
        $this->_show_photostream($handler_id, &$data);
    }

    /**
     * The handler for displaying photos in time window
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * @todo 1.7 support
     */
    function _handler_photostream_between($handler_id, $args, &$data)
    {
        // TODO: Check format as YYYY-MM-DD via regexp    
        $data['from_time'] = @strtotime($args[0]);
        $data['to_time'] = @strtotime($args[1]);
        if (   !$data['from_time']
            || !$data['to_time'])
        {
            return false;
        }
    
        $data['view_title'] = sprintf($this->_l10n->get('photos from %s - %s'), strftime('%x', $data['from_time']), strftime('%x', $data['to_time']));
        $qb =& $this->_prepare_photo_qb();
        $qb->add_constraint('taken', '>=', $data['from_time']);
        $qb->add_constraint('taken', '<=', $data['to_time']);
        $data['photos'] = $qb->execute();

        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _show_photostream_between($handler_id, &$data)
    {
        $this->_show_photostream($handler_id, &$data);
    }

    /**
     * The handler for displaying photos by upload batch
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * @todo 1.7 support
     */
    function _handler_photostream_batch($handler_id, $args, &$data)
    {
        $data['view_title'] = sprintf($this->_l10n->get('photos in batch %s'), $args[0]);
        $qb =& $this->_prepare_photo_qb();
        if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
        {
            $qb->add_constraint('parameter.domain', '=', 'org.routamc.photostream');
            $qb->add_constraint('parameter.name', '=', 'batch_number');
            $qb->add_constraint('parameter.value', '=', $args[0]);
            $data['photos'] = $qb->execute();
        }
        else
        {
            // FIXME: This is Midgard 1.7 compatibility patch
            $photos = $qb->execute();
            $data['photos'] = array();
            foreach ($photos as $photo)
            {
                if ($photo->parameter('org.routamc.photostream', 'batch_number') == $args[0])
                {
                    $data['photos'][] = $photo;
                }
            }
        }

        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _show_photostream_batch($handler_id, &$data)
    {
        $this->_show_photostream($handler_id, &$data);
    }

    /**
     * The handler for displaying photos by tag
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_photostream_tags($handler_id, $args, &$data)
    {
        if ($handler_id == 'photostream_tag')
        {
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('photo tags of %s'), $data['user']->name);
            $data['user_url'] = $args[0];
            $data['tags'] = net_nemein_tag_handler::get_tags_by_class('org_routamc_photostream_photo_dba', $data['user']);
        }
        else
        {
            $data['view_title'] = $this->_l10n->get('photo tags');
            $data['user_url'] = 'all';
            $data['tags'] = net_nemein_tag_handler::get_tags_by_class('org_routamc_photostream_photo_dba');
        }

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);
        
        return true;
    }
    
    function _show_photostream_tags($handler_id, &$data)
    {
        midcom_show_style('show_photostream_tags');
    }

    /**
     * The handler for displaying photos by tag
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_photostream_tag($handler_id, $args, &$data)
    {
        if ($handler_id == 'photostream_tag')
        {
            $data['tag'] = $args[1];
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('photos of %s tagged with %s'), $data['user']->name, $data['tag']);
            $data['user_url'] = $args[0];
        }
        else
        {
            $data['tag'] = $args[0];
            $data['view_title'] = sprintf($this->_l10n->get('photos tagged with %s'), $data['tag']);
            $data['user_url'] = 'all';
        }
        $data['photos'] = array();

        // Get photo GUIDs from tags
        // TODO: Use MidgardCollector for this
        $qb = net_nemein_tag_link_dba::new_query_builder();
        $qb->add_constraint('tag.tag', '=', $data['tag']);
        $qb->begin_group('OR');
            $qb->add_constraint('fromClass', '=', 'org_routamc_photostream_photo_dba');
            $qb->add_constraint('fromClass', '=', 'org_routamc_photostream_photo');
        $qb->end_group();
        //mgd_debug_start();
        $tags = $qb->execute();
        //mgd_debug_stop();

        if (count($tags) > 0)
        {
            // List photos
            $qb =& $this->_prepare_photo_qb();

            $qb->begin_group('OR');
            foreach ($tags as $tag)
            {
                if (class_exists('midgard_query_builder'))
                {
                    // 1.8 allows us to do this sanely
                    $qb->add_constraint('guid', '=', $tag->fromGuid);
                }
                else
                {
                    // 1.7 much less so...
                    $photo = new org_routamc_photostream_photo_dba($tag->fromGuid);
                    if (!$photo)
                    {
                        continue;
                    }
                    $qb->add_constraint('id', '=', $photo->id);
                }
            }
            $qb->end_group();

            if ($handler_id == 'photostream_tag')
            {
                // Limit list of photos to the user
                $qb->add_constraint('photographer', '=', $data['user']->id);
            }

            $qb->add_order('taken', 'DESC');
            $data['photos'] = $qb->execute();
        }

        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _show_photostream_tag($handler_id, &$data)
    {
        $this->_show_photostream($handler_id, &$data);
    }

    /**
     * The handler for displaying photos rated with specific rating
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     */
    function _handler_photostream_rated($handler_id, $args, &$data)
    {
        if ($handler_id == 'photostream_rated')
        {
            $data['rating'] = $args[1];
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('photos of %s rated as %s'), $data['user']->name, $data['rating']);
            $data['user_url'] = $args[0];
        }
        else
        {
            $data['rating'] = $args[0];
            $data['view_title'] = sprintf($this->_l10n->get('all photos rated as %s'), $data['rating']);
            $data['user_url'] = 'all';
        }

        if (!is_numeric($data['rating']))
        {
            return false;
        }

        // List photos
        $qb =& $this->_prepare_photo_qb();

        // TODO: We should support "this or better" here too
        $qb->add_constraint('rating', '=', $data['rating']);

        if ($handler_id == 'photostream_rated')
        {
            // Limit list of photos to the user
            $qb->add_constraint('photographer', '=', $data['user']->id);
        }

        $qb->add_order('taken', 'DESC');
        $data['photos'] = $qb->execute();

        // Make photos AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    function _show_photostream_rated($handler_id, &$data)
    {
        $this->_show_photostream($handler_id, &$data);
    }

    /**
     * Display a list of photos. This method is used by several of the request
     * switches.
     */
    function _show_photostream($handler_id, &$data)
    {
        midcom_show_style('show_photostream_header');

        foreach ($data['photos'] as $photo)
        {
            $data['photo'] = $photo;

            $data['photo_view'] = $data['controllers'][$photo->id]->get_content_html();
            $data['datamanager'] =& $data['controllers'][$photo->id]->datamanager;

            midcom_show_style('show_photostream_item');
        }

        midcom_show_style('show_photostream_footer');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();

        switch ($handler_id)
        {
            case 'photostream_list_all':
            case 'photostream_list':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "list/{$this->_request_data['user_url']}/",
                    MIDCOM_NAV_NAME => $this->_request_data['view_title'],
                );
                break;
            case 'photostream_tags_all':
            case 'photostream_tags':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "tag/{$this->_request_data['user_url']}/",
                    MIDCOM_NAV_NAME => $this->_request_data['view_title'],
                );
                break;
            case 'photostream_tag_all':
            case 'photostream_tag':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "tag/{$this->_request_data['user_url']}/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('photo tags'),
                );
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "tag/{$this->_request_data['user_url']}/{$this->_request_data['tag']}",
                    MIDCOM_NAV_NAME => $this->_request_data['view_title'],
                );
                break;
            case 'photostream_rated_all':
            case 'photostream_rated':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "rated/{$this->_request_data['user_url']}/{$this->_request_data['rating']}",
                    MIDCOM_NAV_NAME => $this->_request_data['view_title'],
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>