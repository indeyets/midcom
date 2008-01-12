<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace entry class
 *
 * Entries have no uplink at this time.
 *
 * @package net.nehmer.marketplace
 */
class net_nehmer_marketplace_entry extends __net_nehmer_marketplace_entry
{
    function net_nehmer_marketplace_entry($id = null)
    {
        parent::__net_nehmer_marketplace_entry($id);
    }

    /**
     * Lists all entries belonging to a given user.
     *
     * @param midcom_core_user $user The user of whom the entries should be listed,
     *    this defaults to the currently active user.
     * @return Array A QB resultset or false on failure.
     */
    function list_by_user($user = null)
    {
        if ($user === null)
        {
            $_MIDCOM->auth->require_valid_user();
            $user =& $_MIDCOM->auth->user;
        }

        $qb = net_nehmer_marketplace_entry::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        return $qb->execute();
    }

    /**
     * Returns the next result in the current category/mode combination.
     */
    function get_next()
    {
        $qb = net_nehmer_marketplace_entry::new_query_builder();
        $qb->add_constraint('ask', '=', $this->ask);
        $qb->add_constraint('category', '=', $this->category);
        $qb->begin_group('OR');
        $qb->add_constraint('published', '<', $this->published);
        $qb->begin_group('AND');
        $qb->add_constraint('published', '=', $this->published);
        $qb->add_constraint('id', '>', $this->id);
        $qb->end_group();
        $qb->end_group();
        $qb->add_order('published', 'DESC');
        $qb->add_order('id');
        $qb->set_limit(1);
        $result = $qb->execute_unchecked();
        if (! $result)
        {
            return null;
        }

        return $result[0];
    }

    /**
     * Returns the previous result in the current category/mode combination.
     */
    function get_previous()
    {
        $qb = net_nehmer_marketplace_entry::new_query_builder();
        $qb->add_constraint('ask', '=', $this->ask);
        $qb->add_constraint('category', '=', $this->category);
        $qb->begin_group('OR');
        $qb->add_constraint('published', '>', $this->published);
        $qb->begin_group('AND');
        $qb->add_constraint('published', '=', $this->published);
        $qb->add_constraint('id', '<', $this->id);
        $qb->end_group();
        $qb->end_group();
        $qb->add_order('published');
        $qb->add_order('id', 'DESC');
        $qb->set_limit(1);
        $result = $qb->execute_unchecked();
        if (! $result)
        {
            return null;
        }

        return $result[0];
    }

    /**
     * Allow creation for all authenticated users.
     */
    function get_class_magic_default_privileges()
    {
        return Array
        (
            'EVERYONE' => Array(),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW)
        );
    }

    /**
     * Temporary helper to set a last-modified timestamp.
     */
    function touch()
    {
        $this->set_parameter('net.nehmer.branchenbuch', 'revised', time());
    }

    /**
     * Temporary helper to create a last-modified timestamp.
     */
    function _on_created()
    {
        $this->touch();
    }

    /**
     * Temporary helper to update a last-modified timestamp.
     */
    function _on_updated()
    {
        $this->touch();
    }

    /**
     * Temporary helper to return a last-modified timestamp.
     *
     * @return int Last-Modified Stamp.
     */
    function get_revised()
    {
        return new Date($this->get_parameter('net.nehmer.branchenbuch', 'revised'));
    }

    /**
     * Indexes an entry. The generated document use component security and have the
     * anonymous_read information from the type config present. This is used from
     * the entry and reindex handlers.
     *
     * This function is usually called statically.
     *
     * @param midcom_helper_datamanager2_datamanager &$dm The Datamanager encapsulating the entry.
     * @param midcom_services_indexer &$indexer The indexer instance to use.
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
        $document->security = 'component';
        $document->component = 'net.nehmer.marketplace';
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $author = $_MIDCOM->auth->get_user($dm->storage->object->account);
        $document->author = $author->name;
        $document->edited = time();
        $type = $dm->storage->object->ask ? 'ask' : 'bid';
        $document->add_keyword('_type', $type);
        $indexer->index($document);
    }

}

?>