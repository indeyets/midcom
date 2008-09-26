<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package org.routamc.gallery
 */
class org_routamc_gallery_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => array
            (
                'org_routamc_gallery_handler_configuration',
                'config'
            ),
            'fixed_args' => array
            (
                'config'
            ),
        );
        
        // Sort photos
        // Match /sort/
        $this->_request_switch['sort'] = array
        (
            'handler' => array ('org_routamc_gallery_handler_sort', 'sort'),
            'fixed_args' => array ('sort'),
        );
        
        // Show a single photo
        // Match /photo/<guid>
        $this->_request_switch['photo'] = Array
        (
            'handler' => Array('org_routamc_gallery_handler_view', 'view'),
            'fixed_args' => Array('photo'),
            'variable_args' => 1,
        );
        
        // Show gallery index
        // Match /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('org_routamc_gallery_handler_index', 'index'),
        );
    }
    
    /**
     * Try to find a photostream node for uploading pictures
     * 
     * @access private
     */
    function _seek_photostream()
    {
        if ($this->_config->get('photostream'))
        {
            // We have a specified photostream here
            $photostream = new midcom_db_topic($this->_config->get('photostream'));
            if (!$photostream)
            {
                return false;
            }

            // We got a topic. Make it a NAP node
            $nap = new midcom_helper_nav();
            $photostream_node = $nap->get_node($photostream->id);
            
            return $photostream_node;
        }
        
        // No photostream specified, autoprobe
        $photostream_node = midcom_helper_find_node_by_component('org.routamc.photostream');

        // Cache the data
        if ($_MIDCOM->auth->request_sudo('org.routamc.gallery'))
        {
            $this->_topic->parameter('org.routamc.gallery', 'photostream', $photostream_node[MIDCOM_NAV_GUID]);
            $_MIDCOM->auth->drop_sudo();
        }

        return $photostream_node;
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if (   $this->_topic->get_parameter('org.routamc.gallery', 'gallery_type') == ORG_ROUTAMC_GALLERY_TYPE_HANDPICKED
            && $this->_request_data['photostream'])
        {
            // Upload photos to this gallery
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "{$this->_request_data['photostream'][MIDCOM_NAV_FULLURL]}upload/?to_gallery={$this->_topic->id}",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('upload photos', 'org.routamc.photostream'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/images.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_request_data['photostream'][MIDCOM_NAV_OBJECT]->can_do('midgard:create'),
                    MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                )
            );
        }

        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'sort/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('sort photos'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:update'),
            )
        );
        
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('gallery settings'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n->get('gallery settings helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['photostream'] = $this->_seek_photostream();

        if (   $handler === 'index'
            && !is_array($this->_request_data['photostream'])
            && $_MIDCOM->auth->user)
        {
            $_MIDCOM->uimessages->add($this->_l10n->get('org.routamc.gallery'), $this->_l10n->get('photostream node not found, please make sure it is accessible'));
            // Bad practise, gallery should be accessible e.g. if the photostream page is hidden from navigation.
            // $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'No photostream found for this gallery. Please create a new org.routamc.photostream folder for uploading photos.');
            // This will exit.
        }

        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.routamc.photostream/photos.css",
            )
        );

        return true;
    }
}
?>