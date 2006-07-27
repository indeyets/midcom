<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market entry class
 *
 * Entries have no uplink at this time.
 *
 * @package net.nehmer.jobmarket
 */
class net_nehmer_jobmarket_entry extends __net_nehmer_jobmarket_entry
{
    function net_nehmer_jobmarket_entry($id = null)
    {
        parent::__net_nehmer_jobmarket_entry($id);
    }

    /**
     * Lists all entries belonging to a given user.
     *
     * @param midcom_core_user $user The user of whom the entries should be listed,
     *    this defaults to the currenlty active user.
     * @return Array A QB resultset or false on failure.
     */
    function list_by_user($user = null)
    {
        if ($user === null)
        {
            $_MIDCOM->auth->require_valid_user();
            $user =& $_MIDCOM->auth->user;
        }

        $qb = net_nehmer_jobmarket_entry::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        return $qb->execute();
    }

    /**
     * Indexes an entry. The generated document use component security and have the
     * anonymous_read information from the type config present. This is used from
     * the entry and reindex handlers.
     *
     * This function is usually called statically.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encaspulating the entry.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     * @param bool $anonymous True if anonymous access is allowed, false otherwise.
     */
    function index(&$dm, &$indexer, $topic, $anonymous)
    {
        if (is_object($topic))
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
        $author = $_MIDCOM->auth->get_user($dm->storage->object->account);

        $document = $indexer->new_document($dm);
        $document->security = 'component';
        $document->component = 'net.nehmer.jobmarket';
        $document->add_unindexed('_anonymous_read', (int) $anonymous);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->author = $author->name;
        $document->created = $dm->storage->object->published;
        $document->edited = time();
        $type = $dm->storage->object->offer ? 'offer' : 'application';
        $document->add_keyword('_type', $type);
        $indexer->index($document);
    }



}