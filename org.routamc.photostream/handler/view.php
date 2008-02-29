<?php
/**
 * @package org.routamc.photostream
 * @author tarjei huse
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Created on 2006-Oct-Thu
 *
 * @package org.routamc.photostream
 *
 */
class org_routamc_photostream_handler_view extends midcom_baseclasses_components_handler
{
    /**
     * Datamanager2 instance for AJAX editing of a photo
     *
     * @access private
     * @var midcom_helper_datamanager2_controller $_controller
     */
    var $_controller;

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

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
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

        // Enable AJAX editing
        if ($this->_config->get('enable_ajax_editing'))
        {
            $this->_controller = midcom_helper_datamanager2_controller::create('ajax');
            $this->_controller->schemadb =& $data['schemadb'];
            $this->_controller->set_storage($data['photo']);
            $this->_controller->process_ajax();
        }

        return true;
    }


    /**
     * The handler for displaying a single photo
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        $data =& $this->_request_data;

        // Prepare object and DM2
        if (!$this->_load_photo($args[0]))
        {
            return false;
        }

        // Show only the moderated photos for those who aren't supposed to see it
        if (   $this->_config->get('moderate_uploaded_photos')
            && $data['photo']->photographer !== $_MIDGARD['user']
            && $data['photo']->status !== ORG_ROUTAMC_PHOTOSTREAM_STATUS_ACCEPTED
            && !$this->_content_topic->can_do('org.routamc.photostream:moderate'))
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

        if (   $this->_config->get('moderate_uploaded_photos')
            && $this->_content_topic->can_do('org.routamc.photostream:moderate'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "moderate/{$data['photo']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('moderate'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
        
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

        $limiters = array();
        if (isset($args[1]))
        {
            $limiters['type'] = $args[1];

            switch ($limiters['type'])
            {
                case 'tag':
                    $limiters['tag'] = $args[3];
                    break;
                case 'user':
                    $limiters['user'] = $args[2];
                    break;
                case 'between':
                    if (isset($args[4]))
                    {
                        $limiters['user'] = $args[2];
                        $limiters['start'] = $args[3];
                        $limiters['end'] = $args[4];
                    }
                    else
                    {
                        $limiters['start'] = $args[2];
                        $limiters['end'] = $args[3];
                    }
            }
        }

        // Get the next and previous
        $data['previous_guid'] = false;
        $data['next_guid'] = false;
        if ($this->_config->get('load_next_prev'))
        {
            $data['previous_guid'] = org_routamc_photostream_photo_dba::get_previous($data['photo'], $limiters, $this->_tags_shared);
            $data['next_guid'] = org_routamc_photostream_photo_dba::get_next($data['photo'], $limiters, $this->_tags_shared);
        }

        // Create the link suffix
        $data['suffix'] = '';
        if (   $this->_config->get('navigate_with_context')
            && isset($args[1])
            && isset($args[2]))
        {
            foreach ($args as $key => $arg)
            {
                if ($key === 0)
                {
                    continue;
                }

                $data['suffix'] .= "{$arg}/";
            }
        }

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_update_breadcrumb_line($handler_id, $args);

        return true;
    }

    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        // Enable AJAX editing
        if ($this->_config->get('enable_ajax_editing'))
        {
            $data['photo_view'] = $this->_controller->get_content_html();
        }
        else
        {
            $data['photo_view'] = $data['datamanager']->get_content_html();
        }

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
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
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

        // Add special limits
        if (preg_match('/photo_args_/', $handler_id))
        {
            if (isset($args[1]))
            {
                switch ($args[1])
                {
                    case 'tag':
                        $tmp[] = array
                        (
                            MIDCOM_NAV_URL => "list/{$args[2]}/",
                            MIDCOM_NAV_NAME => ($args[2] === 'all') ? $this->_l10n->get('all') : $this->_request_data['photographer']->name,
                        );
                        $tmp[] = array
                        (
                            MIDCOM_NAV_URL => "tag/{$args[2]}/{$args[3]}",
                            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('tagged %s'), $args[3]),
                        );
                        break;

                    case 'between':
                        if (isset($args[4]))
                        {
                            $user = "{$args[2]}/";
                            $start = @strtotime($args[3]);
                            $end = @strtotime($args[4]);
                            $raw_start = $args[3];
                            $raw_end = $args[4];
                        }
                        else
                        {
                            $user = '';
                            $start = @strtotime($args[2]);
                            $end = @strtotime($args[3]);
                            $raw_start = $args[2];
                            $raw_end = $args[3];
                        }

                        $tmp[] = array
                        (
                            MIDCOM_NAV_URL => "between/{$user}{$raw_start}/{$raw_end}/",
                            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('between %s-%s'), strftime('%x', $start), strftime('%x', $end)),
                        );
                }
            }
        }
        else
        {
            $tmp[] = array
            (
                MIDCOM_NAV_URL => "list/{$this->_request_data['user_url']}/",
                MIDCOM_NAV_NAME => sprintf($this->_l10n->get('photos of %s'), $this->_request_data['photographer']->name),
            );
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