<?php

class net_nemein_favourites_favourite_dba extends __net_nemein_favourites_favourite_dba
{
    function net_nemein_favourites_favourite_dba($id = null)
    {
        $this->_use_rcs = false;
        parent::__net_nemein_favourites_favourite_dba($id);
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
        return $this->metadata->creator;
    }

    /**
     * Returns the number of favs associated with a given object. This is intended for
     * outside usage to render stuff like "15 favs". The count is executed unchecked.
     * 
     * May be called statically.
     *
     * @return int Number of favs matching a given result. 
     */
    function count_by_objectguid($guid)
    {
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', '=', $guid);        
        $qb->add_constraint('bury', '=', false);
        return $qb->count_unchecked();
    }
    
    /**
     * Returns the number of buries associated with a given object. This is intended for
     * outside usage to render stuff like "15 buries". The count is executed unchecked.
     * 
     * May be called statically.
     *
     * @return int Number of buries matching a given result. 
     */
    function count_buries_by_objectguid($guid)
    {
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', '=', $guid);    
        $qb->add_constraint('bury', '=', true);    
        return $qb->count_unchecked();
    }
}

?>
