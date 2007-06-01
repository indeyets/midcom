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

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $this->_update_breadcrumb_line($handler_id);

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
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = array();

        // TODO: How can we present the correct gallery/stream page in breacrumb ?
        if ($handler_id == 'photo_gallery')
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

        $tmp[] = array
        (
            MIDCOM_NAV_URL => "photo/{$this->_request_data['photo']->guid}/",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>