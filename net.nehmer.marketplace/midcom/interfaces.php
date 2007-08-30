<?php

/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace component.
 *
 * This is a component geared for communities offering a marketplace service. It allows
 * categorization.
 *
 * The component is built to work on the data delivered by the account component, as their
 * values are used as a basis for the new market entry system.
 *
 * The topics serve only as containers for hooking up the component, its actual data is stored
 * in its own table, for more flexible management / querying. If you need to have different
 * market places on the same site(group), you need to distinguish them by different category
 * configurations.
 *
 * @package net.nehmer.marketplace
 */
class net_nehmer_marketplace_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_marketplace_interface()
    {
        parent::midcom_baseclasses_components_interface();

        define ('NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT', 1);
        define ('NET_NEHMER_MARKETPLACE_LEAFID_SELF_ASKS', 2);
        define ('NET_NEHMER_MARKETPLACE_LEAFID_SELF_BIDS', 3);
        define ('NET_NEHMER_MARKETPLACE_LEAFID_ASKS', 4);
        define ('NET_NEHMER_MARKETPLACE_LEAFID_BIDS', 5);
        define ('NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_ASK', 6);
        define ('NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_BID', 7);
        define ('NET_NEHMER_MARKETPLACE_LEAFID_OTHER', 999);

        $this->_component = 'net.nehmer.marketplace';
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
        $qb = net_nehmer_marketplace_entry::new_query_builder();
        $qb->add_constraint('account', '=', $object->guid);
        $result = $qb->execute();
        if ($result)
        {
            foreach ($result as $entry)
            {
                debug_add("Deleting Marketplace entry {$entry->title} ID {$entry->id} for user {$object->username}");
                $entry->delete();
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
        return ($_MIDCOM->auth->user !== null);
    }

    /**
     * Reindex everything, try to conserve as much memory as possible.
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
        $categories = $config->get('categories');
        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        $dm = new midcom_helper_datamanager2_datamanager($schemadb);

        $data = Array('config' => $config);
        $_MIDCOM->set_custom_context_data('request_data', $data);

        foreach ($categories as $identifier => $name)
        {
            // Asks
            $dm->set_schema($config->get('ask_schema'));
            $qb = net_nehmer_marketplace_entry::new_query_builder();
            $qb->add_constraint('category', '=', $identifier);
            $qb->add_constraint('ask', '=', true);
            $entries = $qb->execute();

            foreach ($entries as $entry)
            {
                $dm->set_storage($entry);
                net_nehmer_marketplace_entry::index($dm, $indexer, $topic);
            }

            // Bids
            $dm->set_schema($config->get('bid_schema'));
            $qb = net_nehmer_marketplace_entry::new_query_builder();
            $qb->add_constraint('category', '=', $identifier);
            $qb->add_constraint('ask', '=', false);
            $entries = $qb->execute();

            foreach ($entries as $entry)
            {
                $dm->set_storage($entry);
                net_nehmer_marketplace_entry::index($dm, $indexer, $topic);
            }
        }

        debug_pop();
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
        if (is_a($object, 'net_nehmer_marketplace_entry'))
        {
            return "entry/view/{$object->guid}.html";
        }
        return null;
    }



}
?>
