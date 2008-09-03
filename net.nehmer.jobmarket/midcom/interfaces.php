<?php

/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job market component.
 *
 * This is a component geared for communities offering a job market service. It allows
 * several types/classes of jobs, as well as categorization. Basic searching is available.
 *
 * This component is not designed to serve as your companies personal job ticker. Usually
 * you will fare better with a simple blog or static there. Use this component only
 * if you want to have a large number of jobs to manage.
 *
 * The component is built to work on the data delivered by the account component, as their
 * values are used as a basis for the new market entry system.
 *
 * The topics serve only as containers for hooking up the component, its actual data is stored
 * in its own table, for more flexible management / querying. If you need to have different
 * job markets on the same site(group), you need to distinguish them by different job type
 * configurations.
 *
 * Be aware that the basic creation / reading rules are computed inside the component for
 * entire type trees, they do not use MidCOM ACL. The main reason for this is that there
 * is no common parent object which could be used to hold the necessary privileges. Instead
 * creation is always done using sudo restricted by the rules set in the type_config
 * configuration option.
 *
 * @package net.nehmer.jobmarket
 */
class net_nehmer_jobmarket_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_jobmarket_interface()
    {
        parent::__construct();

        define ('NET_NEHMER_JOBMARKET_LEAFID_SUBMIT', 1);
        define ('NET_NEHMER_JOBMARKET_LEAFID_SELF_OFFERS', 2);
        define ('NET_NEHMER_JOBMARKET_LEAFID_SELF_APPLICATIONS', 3);
        define ('NET_NEHMER_JOBMARKET_LEAFID_TICKER_OFFERS', 4);
        define ('NET_NEHMER_JOBMARKET_LEAFID_TICKER_APPLICATIONS', 5);
        define ('NET_NEHMER_JOBMARKET_LEAFID_SEARCH_OFFERS', 6);
        define ('NET_NEHMER_JOBMARKET_LEAFID_SEARCH_APPLICATIONS', 7);
        define ('NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_OFFER', 8);
        define ('NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_APPLICATION', 9);
        define ('NET_NEHMER_JOBMARKET_LEAFID_OTHER', 999);

        $this->_component = 'net.nehmer.jobmarket';
        $this->_autoload_files = Array('viewer.php', 'navigation.php', 'entry.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager2');
    }

    /**
     * The delete handler will drop all entries associated with any person record that has been
     * deleted. We don't need to check for watched classes at this time, since we have no other
     * watches defined.
     */
    function _on_watched_dba_delete($object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = net_nehmer_jobmarket_entry::new_query_builder();
        $qb->add_constraint('account', '=', $object->guid);
        $result = $qb->execute();
        if ($result)
        {
            foreach ($result as $entry)
            {
                debug_add("Deleting Jobmarket entry {$entry->title} ID {$entry->id} for user {$object->username}");
                $entry->delete();
            }
        }
        debug_pop();
    }

    /**
     * Iterate over all entries and create an index record using the datamanager2 indexer
     * method. We reuse the same DM2 instance. We use the set branchen to limit the number
     * of objects we look at simultaneously.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        if ($config->get('index_to'))
        {
            return;
        }

        debug_push_class(__CLASS__, __FUNCTION__);

        // Initialize everything. We add the request data custom context key here,
        // as the schemas depend on that for returning configuration.
        $type_config = $config->get('type_config');
        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        $dm = new midcom_helper_datamanager2_datamanager($schemadb);

        $data = Array('config' => $config);
        $_MIDCOM->set_custom_context_data('request_data', $data);

        foreach ($type_config as $type => $config)
        {
            // Offers
            $dm->set_schema($config['offer_schema']);
            $qb = net_nehmer_jobmarket_entry::new_query_builder();
            $qb->add_constraint('type', '=', $type);
            $qb->add_constraint('offer', '=', true);
            $entries = $qb->execute();

            foreach ($entries as $entry)
            {
                $dm->set_storage($entry);
                net_nehmer_jobmarket_entry::index($dm, $indexer, $topic, $config['offer_anonymous_read']);
            }

            // Applications
            $dm->set_schema($config['application_schema']);
            $qb = net_nehmer_jobmarket_entry::new_query_builder();
            $qb->add_constraint('type', '=', $type);
            $qb->add_constraint('offer', '=', false);
            $entries = $qb->execute();

            foreach ($entries as $entry)
            {
                $dm->set_storage($entry);
                net_nehmer_jobmarket_entry::index($dm, $indexer, $topic, $config['application_anonymous_read']);
            }
        }

        debug_pop();
    }

    /**
     * Checks the index documents' permission using the unindexed _anonymous_read field
     * associated with the document.
     */
    function _on_check_document_permissions (&$document, $config, $topic)
    {
        if (   ! $_MIDCOM->auth->user
            && ! $document->get_field('_anonymous_read'))
        {
            return false;
        }
        return true;
    }

    /**
     * Resolves entry guids into view URLs.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        if ($config->get('index_to'))
        {
            // We only resolve permalinks on indexed topics, as non-indexed are normally
            // "second-level views" of other topics.
            return null;
        }
        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
        if (is_a($object, 'net_nehmer_jobmarket_entry'))
        {
            return "entry/view/{$object->guid}.html";
        }
        return null;
    }

}
?>