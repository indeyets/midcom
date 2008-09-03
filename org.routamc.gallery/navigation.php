<?php
/**
 * @package org.routamc.gallery 
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * NAP interface class
 *
 * This class has been rewritten for MidCOM 2.6 utilizing all of the currently
 * available state-of-the-art technology.
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package org.routamc.gallery
 */
class org_routamc_gallery_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * Simple constructor, calls base class.
     */
    function org_routamc_gallery_navigation()
    {
        parent::__construct();
    }

    /**
     * Returns all leaves for the current content topic.
     *
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    function get_leaves()
    {
        // Prepare everything
        $leaves = array ();
        
        // Show pictures in navigation according to configuration settings
        if (   !$this->_config->get('navigation_items')
            || !is_numeric($this->_config->get('navigation_items')))
        {
            return $leaves;
        }
        
        $organizer = new org_routamc_gallery_organizer($this->_config->get('index_order'));
        $organizer->node = $this->_topic->id;
        $organizer->limit = $this->_config->get('navigation_items');
        
        foreach ($organizer->get_sorted() as $link_id => $photo)
        {
            $leaves[$photo->guid] = array
            (
                MIDCOM_NAV_URL => "photo/{$photo->guid}.html",
                MIDCOM_NAV_NAME => $photo->title,
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => $photo->guid,
                MIDCOM_NAV_OBJECT => $photo,
            );

        }
        
        return $leaves;
    }
}

?>