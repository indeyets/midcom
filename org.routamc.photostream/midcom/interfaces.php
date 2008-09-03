<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.routamc.photostream
 */
class org_routamc_photostream_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        $this->_component = 'org.routamc.photostream';
        $this->_autoload_files = array();
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
        );
    }

    function _on_initialize()
    {
        // Define constants
        define('ORG_ROUTAMC_PHOTOSTREAM_STATUS_UNMODERATED', 0);
        define('ORG_ROUTAMC_PHOTOSTREAM_STATUS_ACCEPTED', 1);
        define('ORG_ROUTAMC_PHOTOSTREAM_STATUS_REJECTED', 2);

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