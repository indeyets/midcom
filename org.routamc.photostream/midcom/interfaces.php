<?php

/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

class org_routamc_photostream_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_routamc_photostream_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.routamc.photostream';
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php',
            'photo.php',
            'importer.php',
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.qbpager',
            'net.nemein.tag',
        );
    }

    function _on_initialize()
    {
        // Load needed data classes
        $_MIDCOM->componentloader->load_graceful('net.nehmer.buddylist');

        return true;
    }
    
    function _on_resolve_permalink($topic, $config, $guid)
    {
        $photo = new org_routamc_photostream_photo_dba($guid);
        if (   ! $photo
            || $photo->node != $topic->id)
        {
            return null;
        }

        return "photo/{$photo->guid}/";
    }
}
?>