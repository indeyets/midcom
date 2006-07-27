<?php

/**
 * @package net.nehmer.buddylist
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
 * @package net.nehmer.buddylist
 */
class net_nehmer_buddylist_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_buddylist_interface()
    {
        parent::midcom_baseclasses_components_interface();

        define ('NET_NEHMER_BUDDYLIST_LEAFID_PENDING', 1);

        $this->_component = 'net.nehmer.buddylist';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php', 'entry.php');
        // $this->_autoload_libraries = Array('midcom.helper.datamanager2');
    }

    /**
     * The delete handler will drop all entries accociated with any person record that has been
     * deleted. We don't need to check for watched classes at this time, since we have no other
     * watches defined.
     */
    function _on_watched_dba_delete($object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->begin_group('OR');
        $qb->add_constraint('account', '=', $object->guid);
        $qb->add_constraint('buddy', '=', $object->guid);
        $qb->end_group();
        $result = $qb->execute();
        if ($result)
        {
            foreach ($result as $entry)
            {
                debug_add("Deleting Buddylist Entry entry {$entry->guid} ID {$entry->id}.");
                $entry->delete();
            }
        }
        debug_pop();
    }

    /**
     * Checks the index documents' permission using the unindexed _anonymous_read field
     * accociated with the document.
     */
    function _on_check_document_permissions (&$document, $config, $topic)
    {
        return ($_MIDCOM->auth->user !== null);
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
        if (is_a($object, 'net_nehmer_buddylist_entry'))
        {
            return "entry/view/{$object->guid}.html";
        }
        return null;
    }



}
?>
