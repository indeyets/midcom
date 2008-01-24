<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Category map class, maps publications to categories.
 *
 * Their privileges link to the corresponding publication.
 *
 * The categorymap table merges the category group identifier and the category identifier
 * into one field, seperating them by a dash. The category group identifiers are always
 * positive integers, so this is safe.
 *
 * The Category has to be set before creation, or the create will be cancelled.
 *
 * @package net.nehmer.publications
 */
class net_nehmer_publications_categorymap extends __net_nehmer_publications_categorymap
{
    function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * Returns the publication associated with this object.
     */
    function get_parent_guid_uncached()
    {
        return $this->publication;
    }

    /**
     * Returns the category group identifier for this mapping entry.
     *
     * @return string The category group ID.
     */
    function get_category_group()
    {
        $i = strpos($this->category, '-');
        return substr($this->category, 0, $i - 1);
    }

    /**
     * Returns the category identifier within the selected group for this mapping entry.
     *
     * @return string The category ID.
     */
    function get_category_id()
    {
        $i = strpos($this->category, '-');
        return substr($this->cartegory, $i + 1);
    }

    /**
     * Returns the parent publication. Faster then get_parent as it works on a known type.
     *
     * @return net_nehmer_publications_entry The publication associated to this mapping
     *     entry
     */
    function get_publication()
    {
        return new net_nehmer_publications_entry($this->publication);
    }

    /**
     * Validate and popuplate the $category member.
     */
    function _on_create()
    {
        if (! $this->category)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Category may not be unset when creating an object.', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        return true;
    }

    /**
     * Constructs a category identifier out of the group and ID values given.
     *
     * @param int $group The category group identifier
     * @param string $id The category identifier within the group
     */
    function set_category($group, $id)
    {
        $this->category = "{$group}-{$id}";
    }

}

?>