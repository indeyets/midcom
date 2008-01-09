<?php
/**
 * @package org.openpsa.documents
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * @package org.openpsa.documents
 * @todo This is a hotfix.
 */
class midcom_org_openpsa_document extends __midcom_org_openpsa_document
{
    function midcom_org_openpsa_document($id = null)
    {
        return parent::__midcom_org_openpsa_document($id);
    }
}

/**
 * Wrapper for org_openpsa_document
 *
 * Implements parameter and attachment methods for DM compatibility
 *
 * @package org.openpsa.documents
 *
 */
class org_openpsa_documents_document extends midcom_org_openpsa_document
{
    function org_openpsa_documents_document($identifier=NULL)
    {
        return parent::midcom_org_openpsa_document($identifier);
    }

    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if (   isset($this->nextversion)
            && $this->nextversion != 0)
        {
            $parent = new org_openpsa_documents_document($this->nextversion);
        }
        else
        {
            $parent = new org_openpsa_documents_directory($this->topic);
        }
        return $parent;
    }

    function _on_loaded()
    {
        if ($this->title == "")
        {
            $this->title = "Document #{$this->id}";
        }

        if (!$this->docStatus)
        {
            $this->docStatus = ORG_OPENPSA_DOCUMENT_STATUS_DRAFT;
        }

        return true;
    }

    function _on_creating()
    {
        $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_DOCUMENT;
        $user = $_MIDCOM->auth->user->get_storage();
        $this->author = $user->id;
        return true;
    }

    function _on_updated()
    {
        // Sync the object's ACL properties into MidCOM ACL system
        $sync = new org_openpsa_core_acl_synchronizer();
        $sync->write_acls($this, $this->orgOpenpsaOwnerWg, $this->orgOpenpsaAccesstype);
        return true;
    }

    function backup_version()
    {
        // Instantiate the backup object
        $backup = new org_openpsa_documents_document();

        // Copy current properties
        while (list($key, $value) = each($this))
        {
            if ($key != 'guid' && $key != 'id')
            {
                $backup->$key = $value;
            }
        }
        $backup->nextVersion = $this->id;
        $stat = $backup->create();

        if ($stat)
        {
            $backup = new org_openpsa_documents_document($backup->id);

            // Find the attachment
            $attachments = $this->listattachments();
            if ($attachments)
            {
                while ($attachments->fetch())
                {
                    $original_attachment = new midcom_baseclasses_database_attachment($attachments->id);
                    $stat = $backup->createattachment($original_attachment->name, $original_attachment->title, $original_attachment->mimetype);
                    if ($stat)
                    {
                        $backup_attachment = new midcom_baseclasses_database_attachment($stat);

                        // Copy the contents
                        $original_handle = mgd_open_attachment($original_attachment->id, 'r');
                        $backup_handle = mgd_open_attachment($backup_attachment->id, 'w');
                        while (!feof($original_handle))
                        {
                            fwrite($backup_handle, fread($original_handle, 4096), 4096);
                        }
                        fclose($original_handle);

                        // Copy attachment parameters
                        $param_domains = $original_attachment->listparameters();
                        if ($param_domains)
                        {
                            while ($param_domains->fetch())
                            {
                                $params = $original_attachment->listparameters($param_domains->domain);
                                if ($params)
                                {
                                    while ($params->fetch())
                                    {
                                        $backup_attachment->parameter($params->domain, $params->name, $params->value);
                                    }
                                }
                            }
                        }
                        return true;
                    }
                    else
                    {
                        // Failed to copy the attachment, abort
                        return $backup->delete();
                    }
                }
            }

        }
        else
        {
            return $stat;
        }
    }

    function _pid_to_obj($pid)
    {
        return new midcom_baseclasses_database_person($pid);
    }

}

?>