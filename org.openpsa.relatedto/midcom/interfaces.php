<?php
/**
 * OpenPSA relatedto library, handled saving and retvieving "related to" information
 *
 * Startup loads main class, which is used for all operations.
 *
 * @package org.openpsa.relatedto
 */
class org_openpsa_relatedto_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_relatedto_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.relatedto';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'relatedto.php',
            'main.php',
            'handler_prototype.php',
            'suspects.php',
        );
    }

    function _on_initialize()
    {
        define('ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED', 100);
        define('ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED', 120);
        define('ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED', 130);

        // This component uses AHAH, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Prototype/prototype.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/Pearified/JavaScript/Scriptaculous/scriptaculous.js?effects");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.relatedto/related_to.js");

        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.relatedto/related_to.css",
            )
        );
        return true;
    }

    /**
     * Support for contacts person merge
     */
    function org_openpsa_contacts_duplicates_merge_person(&$person1, &$person2, $mode)
    {
        switch($mode)
        {
            case 'all':
                break;
            case 'future':
                // Relatedto does not have future references so we have nothing to transfer...
                return true;
                break;
            default:
                // Mode not implemented
                debug_add("mode {$mode} not implemented", MIDCOM_LOG_ERROR);
                return false;
                break;
        }

        $qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $qb->begin_group('OR');
            $qb->add_constraint('fromGuid', '=', $person2->guid);
            $qb->add_constraint('toGuid', '=', $person2->guid);
        $qb->end_group();
        $links = $qb->execute();
        if ($links === false)
        {
            // QB Error
            return false;
        }
        foreach ($links as $link)
        {
            if ($link->fromGuid == $person2->guid)
            {
                debug_add("Transferred link->fromGuid #{$link->id} to person #{$person1->id} (from {$link->fromGuid})");
                $link->fromGuid = $person1->guid;
            }
            if ($link->toGuid == $person2->guid)
            {
                debug_add("Transferred link->toGuid #{$link->id} to person #{$person1->id} (from {$link->toGuid})");
                $link->toGuid = $person1->guid;
            }
        }

        // TODO: Check for duplicates and remove those (also from the links array...)

        // Save updates to remaining links
        foreach($links as $link)
        {
            if (!$link->update())
            {
                // Failure updating
                return false;
            }
        }

        // TODO: Check version for real and act accordingly
        if ($version_not_18 = true)
        {
            $qb = org_openpsa_relatedto_relatedto::new_query_builder();
            $qb->begin_group('OR');
                $qb->add_constraint('creator', '=', $person2->id);
                $qb->add_constraint('revisor', '=', $person2->id);
            $qb->end_group();
            $links = $qb->execute();
            foreach ($links as $link)
            {
                if ($link->revisor == $person2->id)
                {
                    $link->revisor = $person1->id;
                }
                if ($link->creator == $person2->id)
                {
                    $link->creator = $person1->id;
                }
                if (!$link->update())
                {
                    // Update failure
                    return false;
                }
            }
        }
        else
        {
            // TODO: 1.8 metadata format support
        }
        // All done
        return true;
    }
}


?>