<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications entry class
 *
 * Entries have no uplink at this time.
 *
 * @package net.nehmer.publications
 */
class net_nehmer_publications_entry extends __net_nehmer_publications_entry
{
    function net_nehmer_publications_entry($id = null)
    {
        parent::__net_nehmer_publications_entry($id);
    }

    /**
     * This will delete the category mapping records associated with this
     * publication.
     */
    function _on_deleting()
    {
        $qb = net_nehmer_publications_categorymap::new_query_builder();
        $qb->add_constraint('publication', '=', $this->guid);
        $entries = $qb->execute();

        if ($entries)
        {
            foreach ($entries as $entry)
            {
                $entry->delete();
            }
        }

        return true;
    }

    /**
     * Adds the publication to the specified category. If it is already assigned to it,
     * the request is silently ignored.
     *
     * The call assumes enough privileges to commence the operation.
     */
    function add_to_category($category)
    {
        $qb = net_nehmer_publications_categorymap::new_query_builder();
        $qb->add_constraint('publication', '=', $this->guid);
        $qb->add_constraint('category', '=', $category);
        $result = $qb->execute();

        if (! $result)
        {
            $object = new net_nehmer_publications_categorymap();
            $object->publication = $this->guid;
            $object->category = $category;
            $object->create();
        }
    }

    /**
     * Indexes an entry. This is used from the entry and reindex handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encapsulating the entry.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (!is_object($topic))
        {
            $tmp = new midcom_db_topic($topic);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
                // This will exit.
            }
            $topic = $tmp;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->component = 'net.nehmer.publications';
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $indexer->index($document);
    }

}

?>