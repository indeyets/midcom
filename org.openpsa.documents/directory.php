<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Wrapper for midcom_baseclasses_database_topic
 *
 * @package org.openpsa.documents
 *
 */
class org_openpsa_documents_directory extends midcom_baseclasses_database_topic
{
    function __construct($identifier = NULL)
    {
        return parent::__construct($identifier);
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