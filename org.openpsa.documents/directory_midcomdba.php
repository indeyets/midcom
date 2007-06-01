<?php
/**
 * Wrapper for midcom_baseclasses_database_topic
 */
class org_openpsa_documents_directory extends midcom_baseclasses_database_topic
{
    function org_openpsa_documents_directory($identifier=NULL)
    {
        return parent::midcom_baseclasses_database_topic($identifier);
    }

    function _on_created()
    {
        $this->parameter('midcom', 'component', 'org.openpsa.documents');
    }

    function _on_updated()
    {
        $ownerwg = $this->parameter('org.openpsa.core', 'orgOpenpsaOwnerWg');
        $accesstype = $this->parameter('org.openpsa.core', 'orgOpenpsaAccesstype');

        if (   $ownerwg
            && $accesstype)
        {
            // Sync the object's ACL properties into MidCOM ACL system
            $sync = new org_openpsa_core_acl_synchronizer();
            $sync->write_acls($this, $ownerwg, $accesstype);
            return true;
        }
    }

}
?>