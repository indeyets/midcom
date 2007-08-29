<?php
/**
 * @package net.nehmer.comments
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comments main comment class
 *
 * Comments link up to the object they refer to.
 *
 * @package net.nehmer.comments
 */
class net_nehmer_comments_comment extends __net_nehmer_comments_comment
{
    function net_nehmer_comments_comment($id = null)
    {
        parent::__net_nehmer_comments_comment($id);
    }

    /**
     * DBA magic defaults which assign write privileges for all USERS, so that they can
     * add new comments at will.
     */
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array(),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }

    /**
     * Link to the parent object specified in the objectguid field.
     */
    function get_parent_guid_uncached()
    {
        return $this->objectguid;
    }

    /**
     * Returns a list of comments applicable to a given object, ordered by creation
     * date.
     * 
     * May be called statically.
     *
     * @param guid $guid The GUID of the object to bind to.
     * @return Array List of applicable comments.
     */
    function list_by_objectguid($guid)
    {
        $qb = net_nehmer_comments_comment::new_query_builder();
        $qb->add_constraint('objectguid', '=', $guid);

        if (version_compare(mgd_version(), '1.8', '>='))
        {        
            $qb->add_order('metadata.created');
        }
        else
        {
            $qb->add_order('created', 'ASC');
        }

        return $qb->execute();
    }

    /**
     * Returns the number of comments associated with a given object. This is intended for
     * outside usage to render stuff like "15 comments". The count is executed unchecked.
     * 
     * May be called statically.
     *
     * @return int Number of comments matching a given result. 
     */
    function count_by_objectguid($guid)
    {
        $qb = net_nehmer_comments_comment::new_query_builder();
        $qb->add_constraint('objectguid', '=', $guid);        
        return $qb->count_unchecked();
    }
}

?>