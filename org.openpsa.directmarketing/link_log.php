<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
class midcom_org_openpsa_link_log extends __midcom_org_openpsa_link_log
{
    function midcom_org_openpsa_campaign_link_log($id = null)
    {
        return parent::__midcom_org_openpsa_campaign_link_log($id);
    }

    function _on_creating()
    {
        if (!$this->timestamp)
        {
            $this->timestamp = time();
        }
        if (   !$this->referrer
            && array_key_exists('HTTP_REFERER', $_SERVER)
            && !empty($_SERVER['HTTP_REFERER']))
        {
            $this->referrer = $_SERVER['HTTP_REFERER'];
        }
        return true;
    }

}

/**
 * Another wrap level
 */
class org_openpsa_directmarketing_link_log extends midcom_org_openpsa_link_log
{
    function org_openpsa_directmarketing_campaign_link_log($id = null)
    {
        return parent::midcom_org_openpsa_campaign_link_log($id);
    }
}


?>