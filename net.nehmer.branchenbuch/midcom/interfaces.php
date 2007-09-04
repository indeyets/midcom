<?php

/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch (Yellow Pages) MidCOM interface class.
 *
 * This YP component needs a net.nehmer.account installation to interface with. It takes all
 * account types from there and also uses its schema database as a basis. The configuration settings
 * stored in this component only modify the visibility settings and define foreign key fields where
 * appropriate.
 *
 * This component will manage all category lists in the form of its own category database, which
 * is not bound to the MidCOM topic hierarchy. The reason for this is better ability to query
 * the information using the QB. The root level cateogires, which are bound to the account types
 * specified in the n.n.account manager, will be created automatically by AIS in case that they
 * are missing.
 *
 * AIS category hierarchy management is kept to a minimum at this time.
 *
 * The site interface code does not support hierarchies nested deeper then three levels, including
 * the root type selection category. This has been done to simplify the implementation of the
 * viewer classes, but of course, the system may be exended at a later time.
 *
 * Sorry for the German naming in a few places, the component was not originally planned for open
 * release. A quick primer: "Branchenbuch" => "Yellow Pages", "Branche" is a YP category.
 *
 * @package net.nehmer.branchenbuch
 */
class net_nehmer_branchenbuch_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_branchenbuch_interface()
    {
        parent::midcom_baseclasses_components_interface();

        // We avoid anything here that could be treated as a object identifier.
        define ('NET_NEHMER_BRANCHENBUCH_LEAFID_ADDENTRY', 'leaf_addentry');
        define ('NET_NEHMER_BRANCHENBUCH_LEAFID_LISTSELF', 'leaf_listself');

        $this->_component = 'net.nehmer.branchenbuch';
        $this->_autoload_files = Array('viewer.php', 'navigation.php', 'branche.php',
            'entry.php', 'schemamgr.php', 'callbacks/categorylister.php');
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
        $qb = net_nehmer_branchenbuch_entry::new_query_builder();
        $qb->add_constraint('account', '=', $object->guid);
        $result = $qb->execute();
        if ($result)
        {
            foreach ($result as $entry)
            {
                debug_add("Deleting YP entry ID {$entry->id} for user {$object->username}");
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

        $schemamgr = new net_nehmer_branchenbuch_schemamgr($topic);
        $types = array_keys($config->get('type_config'));
        $schemadb = Array();
        foreach ($types as $type)
        {
            $schemadb[$type] = $schemamgr->get_account_schema($type);
        }
        $dm = new midcom_helper_datamanager2_datamanager($schemadb);

        foreach ($types as $type)
        {
            $dm->set_schema($type);

            $qb = net_nehmer_branchenbuch_branche::new_query_builder();
            $qb->add_constraint('type', '=', $type);
            $branchen = $qb->execute();
            foreach ($branchen as $branche)
            {
                $qb = net_nehmer_branchenbuch_entry::new_query_builder();
                $qb->add_constraint('branche', '=', $branche->guid);
                $entries = $qb->execute();
                foreach ($entries as $entry)
                {
                    // Don't index directly, that would loose a reference due to limitations
                    // of the index() method. Needs fixex there.
                    $dm->set_storage($entry);
                    net_nehmer_branchenbuch_entry::index($dm, $indexer, $topic);
                }
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
     * Resolves the permalink if the 'indexed' option is set. Supports categories/types,
     * branchen and entries.
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
        if (is_a($object, 'net_nehmer_branchenbuch_branche'))
        {
            if (! $object->parent)
            {
                // Category, check for default listing mode
                $type_config = $config->get('type_config');
                if ($type_config[$object->type]['default_view'] == 'alpha')
                {
                    return "category/list/alpha/{$object->guid}/a.html";
                }
                else
                {
                    return "category/list/{$object->guid}.html";
                }
            }
            else
            {
                // Branche
                return "entry/list/{$object->guid}.html";
            }
        }
        if (is_a($object, 'net_nehmer_branchenbuch_entry'))
        {
            return "entry/view/{$object->guid}.html";
        }
        return null;
    }
}
?>
