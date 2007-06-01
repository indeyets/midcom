<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class fi_mik_flight_dba extends __fi_mik_flight_dba
{
    function fi_mik_flight_dba($id = null)
    {
        return parent::__fi_mik_flight_dba($id);
    }
    
    function get_parent_guid_uncached()
    {
        return null;
    }
}
?>