<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
class midcom_org_openpsa_campaign_message_receipt extends __midcom_org_openpsa_campaign_message_receipt
{
    function midcom_org_openpsa_campaign_message_receipt($id = null)
    {
        return parent::__midcom_org_openpsa_campaign_message_receipt($id);
    }

    function _on_creating()
    {
        if (!$this->timestamp)
        {
            $this->timestamp = time();
        }
        return true;
    }

    /**
     * Check whether given token has already been used in the database
     * @param string $token
     * @ret bool indicating whether token is free or not (true for free == not present)
     */
    function token_is_free($token, $type = ORG_OPENPSA_MESSAGERECEIPT_SENT)
    {
        $qb = new MidgardQueryBuilder('org_openpsa_campaign_message_receipt');
        $qb->add_constraint('token', '=', $token);
        if ($type)
        {
            $qb->add_constraint('orgOpenpsaObtype', '=', $type);
        }
        $ret = @$qb->execute();
        if (empty($ret))
        {
            return true;
        }
        return false;
    }

}

/**
 * Another wrap level
 */
class org_openpsa_directmarketing_campaign_message_receipt extends midcom_org_openpsa_campaign_message_receipt
{
    function org_openpsa_directmarketing_campaign_message_receipt($id = null)
    {
        return parent::midcom_org_openpsa_campaign_message_receipt($id);
    }
}


?>