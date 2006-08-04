<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
class org_openpsa_directmarketing_query extends org_openpsa_queries_query
{
    function org_openpsa_directmarketing_query($id = null)
    {
        $stat = parent::__midcom_org_openpsa_query($id);
        if (!$this->component)
        {
            $this->component = 'org.openpsa.directmarketing';
        }
        return $stat;
    }
}

?>