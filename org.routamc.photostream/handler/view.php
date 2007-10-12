<?php
/**
 * Created on 2006-Oct-Thu
 * @author tarjei huse
 * @package org.routamc.photostream
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */
class org_routamc_photostream_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * GUIDs of the photos that share the requested tag
     * 
     * @access private
     */
    var $_tags_shared = null;
    
    /**
     * Simple default constructor.
     */
    function org_routamc_photostream_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _load_photo($id)
    {
        $data =& $this->_request_data;
        $photo = new org_routamc_photostream_photo_dba($id);
        if (!is_object($photo))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not load photo {$id}");
            // This will exit
        }
        $data['photo'] = $photo;
        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);
        if (!$data['datamanager']->set_schema('photo'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "DM2 could not set schema");
            // This will exit
        }
        if (!$data['datamanager']->set_storage($data['photo']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "DM2 could not set storage");
            // This will exit
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
        $data =& $this->_request_data;

        // Prepare object and DM2
        if (!$this->_load_photo($args[0]))
        {
            return false;
        }

        if ($handler_id == 'photo_gallery')
        {
            $gallery = new midcom_db_topic($args[1]);
            if (!$gallery)
            {
                return false;
            }
            $nap = new midcom_helper_nav();
            $data['gallery_node'] = $nap->get_node($gallery->id);
            if (!$data['gallery_node'])
            {
                return false;
            }
        }

        // Add toolbar items
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "edit/{$data['photo']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => $data['photo']->can_do('midgard:update'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$data['photo']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $data['photo']->can_do('midgard:delete'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'd',
            )
        );

        $_MIDCOM->bind_view_to_object($data['photo'], $data['datamanager']->schema->name);

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

        if ($handler_id == 'photo_raw')
        {
            $_MIDCOM->skip_page_style = true;
        }
        
        // Get the next and previous
        $data['previous_guid'] = $this->_get_surrounding_photo('<', $args, $data['photo']);
        $data['next_guid'] = $this->_get_surrounding_photo('>', $args, $data['photo']);
        
        // Create the link suffix
        if (   isset($args[1])
            && isset($args[2]))
        {
            $data['suffix'] = "{$args[1]}/{$args[2]}/";
        }
        else
        {
            $data['suffix'] = '';
        }

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_update_breadcrumb_line($handler_id, $args);

        return true;
    }

    /**
     * This function does the output.
     */
    function _show_view($handler_id, &$data)
    {
        $data['photo_view'] = $data['datamanager']->get_content_html();

        if ($handler_id == 'photo_raw')
        {
            midcom_show_style('show_photo_raw');
        }
        else
        {
            midcom_show_style('show_photo');
        }
    }
    
    /**
     * Get the next and previous photo guids
     * 
     * @access private
     */
    function _get_surrounding_photo($direction, $args, $photo)
    {
        $data['suffix'] = '';
        $guids = array();
        
        $constraint = array
        (
            'key' => 'sitegroup',
            'value' => $_MIDGARD['sitegroup'],
        );
        
        if (isset($args[1]))
        {
            switch ($args[1])
            {
                case 'user';
                    $mc = midcom_db_person::new_collector('username', $args[2]);
                    $mc->add_value_property('id');
                    $mc->add_constraint('username', '=', $args[2]);
                    $mc->set_limit(1);
                    $mc->execute();
                    
                    $persons = $mc->list_keys();
                    
                    foreach ($persons as $guid => $array)
                    {
                        $id = $mc->get_subkey($guid, 'id');
                        break;
                    }
                    
                    $constraint['key'] = 'id';
                    $constraint['value'] = $id;
                    break;
                
                case 'tag':
                    // Get the list of tags only once
                    if ($this->_tags_shared)
                    {
                        break;
                    }
                    
                    // Get a list of guids that share the requested tag
                    $mc = net_nemein_tag_link_dba::new_collector('fromClass', 'org_routamc_photostream_photo_dba');
                    $mc->add_value_property('fromGuid');
                    $mc->add_constraint('tag.tag', '=', $args[2]);
                    $mc->add_constraint('fromGuid', '<>', $photo->guid);
                    $mc->execute();
                    
                    $tags = $mc->list_keys();
                    
                    // Initialize the array
                    $this->_tags_shared = array();
                    
                    // Store the object guids for later use
                    foreach ($tags as $guid => $array)
                    {
                        $this->_tags_shared[] = $mc->get_subkey($guid, 'fromGuid');
                    }
                    
                    break;
                
                case 'all':
                default:
                    // TODO - anything needed?
            }
        }
        
        // Initialize the collector
        $mc = org_routamc_photostream_photo_dba::new_collector($constraint['key'], $constraint['value']);
        
        // Add first the common constraints
        $mc->add_value_property('title');
        
        if ($direction === '<')
        {
            $mc->add_constraint('taken', '<', $photo->taken);
            $mc->add_order('taken', 'DESC');
        }
        else
        {
            $mc->add_constraint('taken', '>', $photo->taken);
            $mc->add_order('taken');
        }
        
        $mc->set_limit(1);
        
        // Include the tag constraints
        if ($this->_tags_shared)
        {
            if (count($this->_tags_shared) > 0)
            {
                $mc->begin_group('OR');
                foreach ($this->_tags_shared as $guid)
                {
                    $mc->add_constraint('guid', '=', $guid);
                }
                $mc->end_group();
                $link = $mc->list_keys();
            }
            else
            {
                $link = array();
            }
        }
        else
        {
            $mc->execute();
            $link = $mc->list_keys();
        }
        
        foreach ($link as $guid => $array)
        {
            return $guid;
        }
        
        return false;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id, $args)
    {
        $tmp = array();

        // TODO: How can we present the correct gallery/stream page in breacrumb ?
        if ($handler_id === 'photo_gallery')
        {
            // Point user back to gallery
            $tmp[] = array
            (
                MIDCOM_NAV_URL => $this->_request_data['gallery_node'][MIDCOM_NAV_FULLURL],
                MIDCOM_NAV_NAME => $this->_request_data['gallery_node'][MIDCOM_NAV_NAME],
            );
        }
        else
        {
            $tmp[] = array
            (
                MIDCOM_NAV_URL => "list/{$this->_request_data['user_url']}/",
                MIDCOM_NAV_NAME => sprintf($this->_l10n->get('photos of %s'), $this->_request_data['photographer']->name),
            );
        }
        
        // Add special limits
        if ($handler_id === 'photo_limited')
        {
            if (isset($args[1]))
            {
                switch ($args[1])
                {
                    case 'tag':
                        $tmp[] = array
                        (
                            MIDCOM_NAV_URL => "tag/{$this->_request_data['user_url']}/{$args[2]}",
                            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('tagged %s'), $args[2]),
                        );
                        break;
                }
            }
        }

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "photo/{$this->_request_data['photo']->guid}/",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>