<?php

/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class org_routamc_gallery_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_routamc_gallery_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.routamc.gallery';
        $this->_autoload_files = Array
        (
            'viewer.php',
            'navigation.php',
            'photolink.php',
            'gallery_helper.php',
            'organizer.php',
        );
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2',
            'net.nemein.tag',
            'org.openpsa.qbpager',
        );
    }

    function _on_initialize()
    {
        // Define some constants
        define('ORG_ROUTAMC_GALLERY_TYPE_HANDPICKED', 10);
        define('ORG_ROUTAMC_GALLERY_TYPE_TAGS', 20);
        define('ORG_ROUTAMC_GALLERY_TYPE_TIME', 30);
        define('ORG_ROUTAMC_GALLERY_TYPE_EVENT', 40);
        define('ORG_ROUTAMC_GALLERY_TYPE_WINDOW', 50);

        // Photostream is not a library so we need to load it here
        $_MIDCOM->componentloader->load('org.routamc.photostream');

        return true;
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $photo = new org_routamc_gallery_photolink_dba($guid);
        if (   ! $photo
            || $photo->node != $topic->id)
        {
            return null;
        }

        return "photo/{$photo->guid}/";
    }
}
?>