<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** We need the PEAR Date class. See http://pear.php.net/package/Date/docs/latest/ */
require_once('Date.php');

/**
 * YP entry class
 *
 * @package net.nehmer.branchenbuch
 */
class net_nehmer_branchenbuch_entry extends __net_nehmer_branchenbuch_entry
{
    function net_nehmer_branchenbuch_entry($id = null)
    {
        parent::__net_nehmer_branchenbuch_entry($id);
    }

    /**
     * Links to the uplink parent, which can be null.
     */
    function get_parent_guid_uncached()
    {
        return $this->branche;
    }

    /**
     * Returns the category of this entry. A new object is created. If the object
     * fails to create, false/null is returned.
     *
     * @return net_nehmer_branchenbuch_branche The category of this entry.
     */
    function get_branche()
    {
        return new net_nehmer_branchenbuch_branche($this->branche);
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
            $user =& $_MIDCOM->auth->user;
        }

        $qb = net_nehmer_branchenbuch_entry::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        return $qb->execute();
    }

    /**
     * Gets the next logical entry in the current category within the
     * alphabetical sort order.
     *
     * @return net_nehmer_branchenbuch_entry The next entry in the (sorted) category or null
     *     in case there is none.
     */
    function get_next()
    {
        $qb = net_nehmer_branchenbuch_entry::new_query_builder();
        $qb->add_constraint('branche', '=', $this->branche);
        $qb->begin_group('OR');
        $qb->add_constraint('lastname', '>', $this->lastname);
        $qb->begin_group('AND');
        $qb->add_constraint('lastname', '=', $this->lastname);
        $qb->add_constraint('firstname', '>', $this->firstname);
        $qb->end_group();
        $qb->begin_group('AND');
        $qb->add_constraint('lastname', '=', $this->lastname);
        $qb->add_constraint('firstname', '=', $this->firstname);
        $qb->add_constraint('id', '>', $this->id);
        $qb->end_group();
        $qb->end_group();
        $qb->add_constraint('id', '<>', $this->id);
        $qb->add_order('lastname');
        $qb->add_order('firstname');
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
     * Gets the previous logical entry in the current category within the
     * alphabetical sort order.
     *
     * @return net_nehmer_branchenbuch_entry The previous entry in the (sorted) category or null
     *     in case there is none.
     */
    function get_previous()
    {
        $qb = net_nehmer_branchenbuch_entry::new_query_builder();
        $qb->add_constraint('branche', '=', $this->branche);
        $qb->begin_group('OR');
        $qb->add_constraint('lastname', '<', $this->lastname);
        $qb->begin_group('AND');
        $qb->add_constraint('lastname', '=', $this->lastname);
        $qb->add_constraint('firstname', '<', $this->firstname);
        $qb->end_group();
        $qb->begin_group('AND');
        $qb->add_constraint('lastname', '=', $this->lastname);
        $qb->add_constraint('firstname', '=', $this->firstname);
        $qb->add_constraint('id', '<', $this->id);
        $qb->end_group();
        $qb->end_group();
        $qb->add_constraint('id', '<>', $this->id);
        $qb->add_order('lastname', 'DESC');
        $qb->add_order('firstname', 'DESC');
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
     * Temporary helper to set a last-modified timestamp.
     */
    function touch()
    {
        $this->set_parameter('net.nehmer.branchenbuch', 'revised', time());
    }

    /**
     * Temporary helper to create a last-modified timestamp and the cached branchen
     * entry counts.
     */
    function _on_created()
    {
        $this->_update_branchen_count();
        $this->touch();
    }

    /**
     * Temporary helper to create a last-modified timestamp and the cached branchen
     * entry counts.
     */
    function _on_updated()
    {
        $this->_update_branchen_count();
        $this->touch();
    }

    /**
     * Helper to update the cached branchen entry counts.
     */
    function _on_deleted()
    {
        $this->_update_branchen_count();
    }

    /**
     * Triggers the item count update of the branchen entry.
     */
    function _update_branchen_count()
    {
        $branche = $this->get_branche();
        $branche->update_item_count();
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
        $entry = $dm->storage->object;

        $document = $indexer->new_document($dm);
        $document->security = 'component';
        $document->component = 'net.nehmer.branchenbuch';
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $author = $_MIDCOM->auth->get_user($entry->account);
        $document->author = $author->name;
        $document->created = time();
        $document->edited = time();
        $document->add_keyword('_type', $entry->type);
        $newtitle = trim("{$entry->firstname} {$entry->lastname}");
        if ($newtitle)
        {
            $document->title = $newtitle;
        }
        $indexer->index($document);
    }

    /**
     * This is a helper function targeted for usage from the account component's
     * account activation callback. It allows you to create a YP entry for a given
     * person in the system. The entry will have the default_entry flag set (see
     * below), which will in turn be invalidated after during successful formmanager
     * save operations in the regular edit cycle.
     *
     * The flag is stored in the parameter 'net.nehmer.branchenbuch'/'default_entry',
     * which is set to 1 in case it is a default entry or to 0/unset otherwise.
     *
     * The code is based on the _saved_confirmed_entry call found in the addentry.php
     * handler class. It uses the Datamanager to actually save the data, so that the
     * storage operation stays abstracted. The defaults are obtained using the
     * schemamgr class.
     *
     * This call assumes sudo privileges, as it will force the ownership of the newly
     * created record to the user it is created for.
     *
     * On any error, generate_error will be called.
     *
     * This function can only be called statically.
     *
     * Example:
     *
     * <code>
     * $category_guid = $person->get_parameter('midcom.helper.datamanager2', 'category');
     * $topic = new midcom_db_topic('baf8210a488f3b8d4dbcabaafba8ba8e');
     * $entry = net_nehmer_branchenbuch_entry::create_default_entry($category_guid, $person, $topic);
     * </code>
     *
     * This assumes that the YP topic is known and that the category to use is queried using the
     * account creation schema, perhaps with a field declaration like this (assuming an account
     * type 'freelancer'):
     *
     * <code>
     * 'category' => Array
     * (
     *     'title' => 'Beruf',
     *     'description' => '',
     *     'helptext' => '',
     *
     *     'storage' => 'parameter',
     *     'type' => 'select',
     *     'type_config' => Array
     *     (
     *         'option_callback' => 'net_nehmer_branchenbuch_callbacks_categorylister',
     *         'option_callback_arg' => 'freelancer',
     *     ),
     *     'widget' => 'select',
     * ),
     * </code>
     *
     * @param string $guid The GUID of the category the new record should be created in.
     * @param midcom_db_person $person The record for which the default entry should be created.
     * @param midcom_db_topic $topic The YP topic in which we operate (used for configuration).
     * @static
     */
    function create_default_entry($category_guid, $person, $topic)
    {
        // Preparation work.
        $user =& $_MIDCOM->auth->get_user($person);
        $branche = new net_nehmer_branchenbuch_branche($category_guid);
        if (! $branche)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The category guid {$category_guid} specified is invalid, aborting.");
            // This will exit.
        }
        $schemamgr = new net_nehmer_branchenbuch_schemamgr($topic);

        // Create a fresh storage object.
        $entry = new net_nehmer_branchenbuch_entry();
        $entry->branche = $branche->guid;
        $entry->type = $branche->type;
        $entry->account = $user->guid;

        if (! $entry->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We tried to create this object:', $entry);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create the defaultentry, see the debug level log for more information, last Midgard Error was:' . mgd_errstr());
            // This will exit.
        }

        $schemadb = Array($branche->type => $schemamgr->get_account_schema($branche->type));
        $dm = new midcom_helper_datamanager2_datamanager($schemadb);
        $dm->set_schema($branche->type);
        $dm->set_storage($entry);

        // Write all defaults into the DM, we need to use the storage loader interface
        // here as the defaults come in raw form.
        $defaults = $schemamgr->remote->get_defaults_from_account($user);
        foreach ($defaults as $field => $value)
        {
            $dm->types[$field]->convert_from_storage($value);
        }

        // Save
        if (! $dm->save())
        {
            // Delete the original object again so that no incomplete records remain.
            $entry->delete();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create the default entry, see the debug level log for more information, Dm2 save operation failed');
            // This will exit.
        }

        // Set the default entry flag.
        $entry->set_parameter('net.nehmer.branchenbuch', 'autocreated_entry', 1);

        // Adjust permissions
        $entry->unset_privilege('midgard:owner');
        $entry->set_privilege('midgard:owner', $user);

        // Update the Index
        $indexer =& $_MIDCOM->get_service('indexer');
        $entry->index($dm, $indexer, $topic);

        return $entry;
    }

}