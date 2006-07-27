<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mail System Mailbox class
 *
 * To update the cached item counts in case of an inconsistency, use the update_itemcounts.php
 * script in the components exec directory.
 * (Call http://$host/midcom-exec-net.nehmer.branchenbuch/update_itemcounts.php)
 *
 *
 * @package net.nehmer.branchenbuch
 */
class net_nehmer_branchenbuch_branche extends __net_nehmer_branchenbuch_branche
{
    function net_nehmer_branchenbuch_branche($id = null)
    {
        parent::__net_nehmer_branchenbuch_branche($id);
    }

    /**
     * Links to the uplink parent, which can be null.
     */
    function get_parent_guid_uncached()
    {
        if (mgd_is_guid($this->parent))
        {
            return $this->parent;
        }
        else
        {
            return null;
        }
    }

    /**
     * Returns the parent branche, if applicable. A new object is created.
     *
     * @return net_nehmer_branchenbuch_branche Parent category or null on failure.
     */
    function get_parent_branche()
    {
        if (mgd_is_guid($this->parent))
        {
            return new net_nehmer_branchenbuch_branche($this->parent);
        }
        else
        {
            return null;
        }
    }

    /**
     * Helper function which lists the root categories.
     *
     * This function can be called staically
     *
     * @return Array A listing of root category class instances. This is a regular QB resultset.
     */
    function list_root_categories()
    {
        $qb = net_nehmer_branchenbuch_branche::new_query_builder();
        $qb->add_constraint('parent', '=', '');
        $qb->add_order('name');
        return $qb->execute();
    }

    /**
     * This helper function returns the root category accociated with this instance. This
     * is a copy of ourselves in case that we already look at a root cateogry. The lookup
     * is done using the type field.
     *
     * DB inconsistencies will trigger generate_error.
     *
     * @return net_nehmer_branchenbuch_branche The found root category.
     */
    function get_root_category()
    {
        if ($this->parent == '')
        {
            return $this;
        }

        $qb = net_nehmer_branchenbuch_branche::new_query_builder();
        $qb->add_constraint('parent', '=', '');
        $qb->add_constraint('type', '=', $this->type);
        $result = $qb->execute();

        if (   ! $result
            || count($result) != 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Got this resultset:', $result);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "DB inconsistency while trying to load the root category for type {$type}.");
            // This will exit.
        }

        return $result[0];
    }

    /**
     * This helper function returns the root category identified by a given account
     * type name. DB inconsistencies will trigger generate_error.
     *
     * This function may be called statically.
     *
     * @param string $type The type to look up.
     * @return net_nehmer_branchenbuch_branche The found root category or false on failure.
     */
    function get_root_category_by_type($type)
    {
        $qb = net_nehmer_branchenbuch_branche::new_query_builder();
        $qb->add_constraint('parent', '=', '');
        $qb->add_constraint('type', '=', $type);
        $result = $qb->execute();

        if (   ! $result
            || count($result) != 1)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Got this resultset:', $result);
            debug_add("DB inconsistency while trying to load the root category for type {$type}.", MIDCOM_LOG_WARN);;
            return false;
        }

        return $result[0];
    }


    /**
     * Helper function which lists the childs of the current category.
     *
     * @return Array A listing of subcategory class instances. This is a regular QB resultset.
     */
    function list_childs()
    {
        $qb = $this->get_list_childs_qb();
        return $qb->execute();
    }

    /**
     * Returns a QB which is prepared to list all childs of the object using default sorting.
     *
     * @return midcom_core_querybuilder A prepared query builder.
     */
    function get_list_childs_qb()
    {
        $qb = net_nehmer_branchenbuch_branche::new_query_builder();
        $qb->add_constraint('parent', '=', $this->guid);
        $qb->add_order('name');
        return $qb;
    }

    /**
     * This helper constructs the full name of the component, in case you don't want
     * to worry about climbing the tree yourself. Note, that there is at least one DB
     * interaction required for this operation, so if you already know the name in your
     * app, try to avoid this call.
     *
     * @return string The full name of the category, skipping the root category and concatenating
     *     the various categories with ': '.
     */
    function get_full_name()
    {
        // Shortcut for root names.
        if ($this->parent == '')
        {
            return $this->name;
        }

        // This is for all other types, they are local names. Be aware, that the root
        // group is skipped.
        $name = $this->name;
        $tmp = $this->get_parent();
        while (   $tmp
               && $tmp->parent != '')
        {
            $name = "{$tmp->name}: {$name}";
            $tmp = $tmp->get_parent();
        }
        return $name;
    }

    /**
     * This function returns the list of entries accociated with the current category.
     *
     * This requires the view_entries privilege.
     *
     * This function has (optional) paging capabilities, see the argument list.
     *
     * @param int $page The page number to query. Omit this to get the full list. This is a
     *     one-based index, not a zero based one!
     * @param int $page_size The size of a single page. Omit this to get the full list. This
     *     defaults to 10 in case only the page number is specified.
     * @return Array A QB resultset consisting of net_nehmer_branchenbuch_entry records.
     */
    function list_entries($page = null, $page_size = null)
    {
        $qb = $this->get_list_entries_qb();
        $qb->add_order('lastname');
        $qb->add_order('firstname');
        $qb->add_order('id');
        if ($page !== null)
        {
            if ($page_size === null)
            {
                $page_size = 10;
            }
            if ($page <= 0)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "{$page} is not a valid page number.");
                // This will exit.
            }
            if (   ! is_numeric($page_size)
                || $page_size <= 0)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "{$page_size} is not a valid page size.");
                // This will exit.
            }
            $offset = ($page - 1) * $page_size;
            $qb->set_offset($offset);
            $qb->set_limit($page_size);
        }
        return $qb->execute();
    }

    /**
     * This function detects the real entry count for the current category, not the
     * cached one used in the category listings.
     */
    function get_live_entry_count()
    {
        $qb = $this->get_list_entries_qb();
        return $qb->count_unchecked();
    }

    /**
     * This function will return a QB suitable for listing entries, without any sort order
     * applied.
     *
     * This requires the view_entries privilege.
     *
     * @return midcom_core_qb A prepared QB instance.
     */
    function get_list_entries_qb()
    {
        $_MIDCOM->auth->require_do('net.nehmer.branchenbuch:view_entries', $this);
        $qb = net_nehmer_branchenbuch_entry::new_query_builder();
        $qb->add_constraint('branche', '=', $this->guid);
        return $qb;
    }

    /**
     * Returns the number of elements assigned to the current category, the check lists
     * all elements, regardless of ACL for performance reasons.
     *
     * @return int The number of entries.
     */
    function get_local_element_count()
    {
        $qb = net_nehmer_branchenbuch_entry::new_query_builder();
        $qb->add_constraint('branche', '=', $this->guid);
        return $qb->count_unchecked();
    }

    /**
     * DBA magic defaults which assign listing porivileges to all authenticated users.
     */
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array(),
            'ANONYMOUS' => Array(),
            'USERS' => Array('net.nehmer.branchenbuch:view_entries' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }

    /**
     * This static function returns a full select list for all categories in the selected
     * type. It tries to minimize the number of select calls for efficiency reasons.
     *
     * The lists are not cached in any way at this time.
     *
     * Categories, which have subcategories are <i>not</i> are not shown themselves, only their
     * subcategories will make it into the resultset.
     *
     * The result will be indexed by the GUID of the category and contain its name as value.
     *
     * <i>Implementation details</i>
     *
     * Basically, the system runs two queries, one for the main categories, another for the
     * subcategories. The lists are merged on the PHP level afterwards. This should be faster
     * as having independant queries for the subcategories for each category.
     *
     * The logic should take over the sorting from the SQL resultset, so no explicit sort()
     * is required.
     *
     * This call isn't optimal in case you want to do something with the category objects, as
     * they are not returned by this call.
     *
     * @param string $type The name of the type for which we should list the categories.
     * @return Array List suitable for usage with select-style operations. In case of errors
     *     which are non-fatal, the error is logged and an empty array is returned.
     */
    function get_select_list_for_type($type)
    {
        // Get root category first.
        $root_category = net_nehmer_branchenbuch_branche::get_root_category_by_type($type);
        if (! $root_category)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to load the select list for type {$type}, it does not exist or is not readable.", MIDCOM_LOG_ERROR);
            debug_pop();
            return Array();
        }

        // Get main categories:
        $qb = net_nehmer_branchenbuch_branche::new_query_builder();
        $qb->add_constraint('parent', '=', $root_category->guid);
        $qb->add_constraint('type', '=', $type);
        $qb->add_order('name');
        $main_categories = $qb->execute();

        // Get subcategories
        $qb = net_nehmer_branchenbuch_branche::new_query_builder();
        $qb->add_constraint('parent', '<>', $root_category->guid);
        $qb->add_constraint('parent', '<>', '');
        $qb->add_constraint('type', '=', $type);
        $qb->add_order('parent');
        $qb->add_order('name');
        $sub_categories_db = $qb->execute();

        // Group subcategories and free memory (just in case).
        $sub_categories = Array();
        foreach ($sub_categories_db as $sub_category)
        {
            $sub_categories[$sub_category->parent][] = $sub_category;
        }
        unset($sub_categories_db);

        // Generate the final resultset
        $result = Array();
        foreach ($main_categories as $main_category)
        {
            if (array_key_exists($main_category->guid, $sub_categories))
            {
                foreach ($sub_categories[$main_category->guid] as $sub_category)
                {
                    $result[$sub_category->guid] = "{$main_category->name}: {$sub_category->name}";
                }
            }
            else
            {
                $result[$main_category->guid] = "{$main_category->name}";
            }
        }
        return $result;
    }

    /**
     * Updates the count entry of this record. This will be done by a sudo'ed SQL count() statement.
     */
    function update_item_count()
    {
        // Use sudo rights to update the item count.
        if (! $_MIDCOM->auth->request_sudo('net.nehmer.branchenbuch'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to request sudo privileges to update the branchen item count cache.');
            // This will exit.
        }

        $this->itemcount = $this->get_live_entry_count();
        $this->update();

        $_MIDCOM->auth->drop_sudo();
    }



}