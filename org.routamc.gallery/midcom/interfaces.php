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

    function _on_watched_operation($operation, &$object)
    {
        // In the future we might want to trap creates etc as well, thus we do this flexibly from the start
        switch(true)
        {
            case (   $operation === MIDCOM_OPERATION_DBA_DELETE
                  && is_a($object, 'org_routamc_photostream_photo_dba')):
                $this->remove_photo_links($object->id);
                break;
            default:
                return;
        }
    }

    /**
     * Removes photolink objects that link to given photo id
     *
     * @param int $photoid local id of photo object
     */
    function remove_photo_links($photoid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Clearing photo links for photo #{$photoid}", MIDCOM_LOG_INFO);
        $qb = org_routamc_gallery_photolink_dba::new_query_builder();
        $qb->add_constraint('photo', '=', $photoid);
        $links = $qb->execute();
        if (empty($links))
        {
            debug_add('No links found');
            debug_pop();
            return;
        }
        foreach($links as $link)
        {
            debug_add("Removing link #{$link->id} (node #{$link->node})");
            $link->delete();
        }
        $cnt = count($links);
        debug_add("Removed {$cnt} links for photo #{$photoid}", MIDCOM_LOG_INFO);
        debug_pop();
    }
}
?>