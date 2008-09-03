<?php

/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications MidCOM interface class.
 *
 * @package net.nehmer.publications
 */
class net_nehmer_publications_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_publications_interface()
    {
        parent::__construct();

        define('NET_NEHMER_PUBLICATIONS_LEAFID_ARCHIVE', 1);
        define('NET_NEHMER_PUBLICATIONS_LEAFID_FEEDS', 2);

        $this->_component = 'net.nehmer.publications';
        $this->_autoload_files = Array
        (
            'viewer.php',
            'navigation.php',
            'entry.php',
            'categorymap.php',
            'query.php',
            'callbacks/categorylister.php'
        );
        $this->_autoload_libraries = Array('midcom.helper.datamanager', 'midcom.helper.datamanager2');
    }

    /**
     * Iterate over all articles and create index record using the datamanager indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        if ($config->get('master_topic'))
        {
            return;
        }

        debug_push_class(__CLASS__, __FUNCTION__);

        // Populate custom context data with configuration, so that everybody is happy.
        $request_data = Array('config' => $config);
        $_MIDCOM->set_custom_context_data('request_data', $request_data);

        $qb = new net_nehmer_publications_query(false);
        $qb->apply_filter_list($config);
        $remaining_objects = $qb->count_unchecked();

        if (! $remaining_objects)
        {
            debug_add("No results found, we are ready.");
            return;
        }

        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);

        if (! $datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to create a DM2 instance for reindexing of topic {$topic->id}.");
            // This will exit.
        }

        $offset = 0;
        $step = 10;

        while ($remaining_objects > 0)
        {
            $qb = new net_nehmer_publications_query();
            $qb->apply_filter_list($config);
            $qb->set_limit($step);
            $qb->set_offset($offset);

            $result = $qb->execute_unchecked();

            foreach ($result as $publication)
            {
                if (! $datamanager->autoset_storage($publication))
                {
                    debug_add("Failed to initialize the DM2 instance to publicaiton {$publication->id}, skipping it.",
                        MIDCOM_LOG_WARN);
                    continue;
                }

                $publication->index($datamanager, $indexer, $topic);
            }

            $offset += $step;
            $remaining_objects -= $step;
        }


        debug_pop();
    }

    /**
     * If we are are a master topic, we bail. Otherwise, we redirect to the view/$guid URL.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        if ($config->get('master_topic'))
        {
            return false;
        }

        $publication = new net_nehmer_publications_entry($guid);
        if (! $publication)
        {
            return null;
        }

        return "view/{$guid}.html";
    }


}
?>