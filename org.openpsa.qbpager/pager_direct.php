<?php
/**
 * Pages QB resultsets (uses midgard QB directly)
 */
class org_openpsa_qbpager_direct extends org_openpsa_qbpager
{
    function org_openpsa_qbpager_direct($classname, $pager_id)
    {
        parent::midcom_baseclasses_components_purecode();
        $this->_component = 'org.openpsa.qbpager';
        
        $this->_limit =& $this->results_per_page;
        $this->_pager_id = $pager_id;
        $this->_midcom_qb = new MidgardQueryBuilder($classname);
        if (!$this->_sanity_check())
        {
            return false;
        }
        $this->_prefix = 'org_openpsa_qbpager_' . $this->_pager_id . '_';
        
        return true;
    }

    function execute()
    {
        if (!$this->_sanity_check())
        {
            return false;
        }
        $qb_copy = $this->_midcom_qb;
        $this->_qb_limits($qb_copy);
        return @$qb_copy->execute();
    }
    
    /**
     * Wraps to count since midgard QB does not support said method yet
     */
    function execute_unchecked()
    {
        return $this->execute();
    }

    /**
     * Wraps to count since midgard QB does not support said method yet
     */
    function count_unchecked()
    {
        return $this->count();
    }
}
?>