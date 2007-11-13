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

    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = org_routamc_photostream_photo_dba::new_query_builder();
        $qb->add_constraint('node', '=', $topic->id);
        $photos = $qb->execute();
        unset($qb);
        if (empty($photos))
        {
            debug_add("No photos to index in topic #{$topic->id}");
            debug_pop();
            return true;
        }
        debug_add('Reindexing ' . count($photos) . ' photos');
        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        if (!$datamanager)
        {
            debug_add('Failed to create a datamanager instance with this schemapath:' . $config->get('schemadb'), MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }
        foreach ($photos as $k => $photo)
        {
            if (!$datamanager->autoset_storage($photo))
            {
                unset($photos[$k]);
                debug_add("Failed to initialize datamanager for photo {$photo->id}. Skipping it.", MIDCOM_LOG_WARN);
                continue;
            }
            unset($photos[$k]);
            org_routamc_photostream_viewer::index($datamanager, $indexer, $topic);
        }
        unset($photos);
        debug_add('Done');
        debug_pop();
        return true;
    }
}
?>