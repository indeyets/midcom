<?php
/**
 * @package org.openpsa.relatedto
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: handler_prototype.php,v 1.6 2006/06/01 12:25:32 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * relatedto ajax/ahah handler, extended by each component using relatedtos
 *
 * @package org.openpsa.relatedto
 */
class org_openpsa_relatedto_handler_relatedto extends midcom_baseclasses_components_handler
{
    var $realcomponent = false;

    function org_openpsa_relatedto_handler_relatedto()
    {
        parent::midcom_baseclasses_components_handler();
    }

/*
    function _handler_xxx($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    function _show_xxx($handler_id, &$data)
    {
    }
*/

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_render($handler_id, $args, &$data)
    {
        $this->_request_data['object'] = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (   !is_object($this->_request_data['object'])
            || !$this->_request_data['object']->guid)
        {
            return false;
        }
        $this->_request_data['mode'] = $args[1];
        $this->_request_data['sort'] = 'default';
        if (isset($args[2]))
        {
            $this->_request_data['sort'] = $args[2];
        }

        $this->_request_data['links'] = array(
            'in' => array(),
            'out' => array(),
        );

        switch ($this->_request_data['mode'])
        {
            case 'in-paged':
                //Fall-trough intentional
            case 'in':
                $this->_get_object_links_in($this->_request_data['links']['in'], $this->_request_data['object']);
                break;
            case 'out-paged':
                //Fall-trough intentional
            case 'out':
                $this->_get_object_links_out($this->_request_data['links']['out'], $this->_request_data['object']);
                break;
            case 'both-paged':
                //Fall-trough intentional
            case 'both':
                $this->_get_object_links_in($this->_request_data['links']['in'], $this->_request_data['object']);
                $this->_get_object_links_out($this->_request_data['links']['out'], $this->_request_data['object']);
                break;
            default:
                //Mode not supported
                return false;
        }

        //TODO: Add custom handler ID for skipping style
        //$_MIDCOM->skip_page_style = true;
        return true;
    }

    /**
     * Renders the selected view
     *
     * Due to this being a purecode component we can't use the MidCOM style engine
     * but operations are divided into overrideable methods as much as possible so
     * components then can override them and then use the style engine within their
     * own context.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_render($handler_id, &$data)
    {
        echo "<div id=\"org_openpsa_relatedto_{$this->_request_data['object']->guid}\" class=\"org_openpsa_relatedto_container\">\n";
        //TODO: better word?, localization
        //echo "    <h2>related to</h2>\n";
        $this->_show_render_modeswitch();
        echo "</div>\n";
    }

    function _show_render_modeswitch()
    {
        switch ($this->_request_data['mode'])
        {
            case 'in-paged':
                //Fall-trough intentional
            case 'in':
                $this->_show_render_in();
                break;
            case 'out-paged':
                //Fall-trough intentional
            case 'out':
                $this->_show_render_out();
                break;
            case 'both-paged':
                //Fall-trough intentional
            case 'both':
                $this->_show_render_in(true);
                $this->_show_render_out(true);
                break;
        }
    }

    /**
     * Renders inbound links
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_in($show_title = false)
    {
        if (count($this->_request_data['links']['in']) < 1)
        {
            return;
        }
        echo "    <div class=\"org_openpsa_relatedto_list_in_container\">\n";
        if ($show_title)
        {
            echo "        <h2>".$_MIDCOM->i18n->get_string('related to this', 'org.openpsa.relatedto')."</h2>\n";
        }
        echo "        <ol class=\"org_openpsa_relatedto_list\">\n";
        //Sort the array of links
        $this->_sort_link_array($this->_request_data['links']['in']);

        //echo "        <p>DEBUG: links['in']<pre>\n" . sprint_r($this->_request_data['links']['in']) . "</pre></p>\n";

        foreach($this->_request_data['links']['in'] as $linkdata)
        {
            $this->_request_data['link_obj'] =& $linkdata['link'];
            $this->_request_data['link_other_obj'] =& $linkdata['other_obj'];
            $this->_request_data['link_other_guid'] =& $linkdata['other_obj']->guid;
            echo "            <a name=\"{$this->_request_data['link_other_guid']}\"></a>\n";
            $this->_show_render_line();
        }

        echo "        </ol>\n";
        echo "    </div>\n";
    }

    /**
     * Renders outbound links
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_out($show_title = false)
    {
        if (count($this->_request_data['links']['out']) < 1)
        {
            return;
        }
        echo "    <div class=\"org_openpsa_relatedto_list_out_container\">\n";
        if ($show_title)
        {
            echo "        <h2>".$_MIDCOM->i18n->get_string('this is related to', 'org.openpsa.relatedto')."</h2>\n";
        }
        echo "        <ol class=\"org_openpsa_relatedto_list\">\n";
        //Sort the array of links
        $this->_sort_link_array($this->_request_data['links']['out']);

        //echo "        <p>DEBUG: links['out']<pre>\n" . sprint_r($this->_request_data['links']['out']) . "</pre></p>\n";

        foreach($this->_request_data['links']['out'] as $linkdata)
        {
            $this->_request_data['link_obj'] =& $linkdata['link'];
            $this->_request_data['link_other_obj'] =& $linkdata['other_obj'];
            $this->_request_data['link_other_guid'] =& $linkdata['other_obj']->guid;
            echo "            <a name=\"{$this->_request_data['link_other_guid']}\"></a>\n";
            $this->_show_render_line();
        }
        echo "        </ol>\n";
        echo "    </div>\n";
    }

    /**
     * Sorts the given link array based on $this->_request_data['sort']
     */
    function _sort_link_array(&$arr)
    {
        switch ($this->_request_data['sort'])
        {
            case 'reverse':
                /* HACK: usort can't use even static methods so we create an "anonymous" function from code received via method */
                usort($arr, create_function('$a,$b', $this->_code_for_sort_by_time_reverse()));
                break;
            case 'normal':
            case 'default':
            default:
                /* HACK: usort can't use even static methods so we create an "anonymous" function from code received via method */
                usort($arr, create_function('$a,$b', $this->_code_for_sort_by_time()));
                break;
        }
    }

    /**
     * Renders single link line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line()
    {
        /* Load renderer based on to which class tree the object belongs to
           REMEMBER: to keep more complex rules above simpler ones, ESPECIALLY
           if the simple one can match part of the complex one */
        switch(true)
        {
            case (   is_a($this->_request_data['link_other_obj'], 'midcom_baseclasses_database_article')
                  && $this->_request_data['link_obj']->fromComponent == 'net.nemein.wiki'
                  && $this->_request_data['link_other_obj']->parameter('net.nemein.wiki:emailimport', 'is_email')):
                $this->_show_render_line_wikipage_email();
                break;
            case (   is_a($this->_request_data['link_other_obj'], 'midcom_baseclasses_database_article')
                  && $this->_request_data['link_obj']->fromComponent == 'net.nemein.wiki'):
                $this->_show_render_line_wikipage();
                break;
            case is_a($this->_request_data['link_other_obj'], 'midcom_baseclasses_database_event'):
                //Fall-trough intentional
            case is_a($this->_request_data['link_other_obj'], 'midcom_org_openpsa_event'):
                $this->_show_render_line_event();
                break;
            case is_a($this->_request_data['link_other_obj'], 'midcom_org_openpsa_task'):
                if ($this->_request_data['link_other_obj']->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_TASK)
                {
                    $this->_show_render_line_task();
                }
                // TODO: Show related projects too
                break;
            case is_a($this->_request_data['link_other_obj'], 'midcom_org_openpsa_document'):
                $this->_show_render_line_document();
                break;
            case is_a($this->_request_data['link_other_obj'], 'midcom_org_openpsa_salesproject'):
                $this->_show_render_line_salesproject();
                break;
            case is_a($this->_request_data['link_other_obj'], 'midcom_org_openpsa_hour_report'):
                $this->_show_render_line_hour_report();
                break;
            case is_a($this->_request_data['link_other_obj'], 'org_openpsa_invoices_invoice'):
                $this->_show_render_line_invoice();
                break;
            default:
                $this->_show_render_line_default();
                break;
        }

    }

    /**
     * Renders (if necessary) controls for confirming/deleting link object
     */
    function _show_render_line_controls()
    {
        //TODO
        echo "<ul class=\"relatedto_toolbar\" id=\"org_openpsa_relatedto_toolbar_{$this->_request_data['link_obj']->guid}\">\n";

        switch ($this->_request_data['link_obj']->fromComponent)
        {
            case 'net.nemein.wiki':
            case 'org.openpsa.calendar':
                echo "<li><input type=\"button\" class=\"button\" id=\"org_openpsa_relatedto_details_button_{$this->_request_data['link_other_obj']->guid}\" onclick=\"ooToggleRelatedInfoDisplay('{$this->_request_data['link_other_obj']->guid}');\" class=\"info\" value=\"" . $_MIDCOM->i18n->get_string('details', 'org.openpsa.relatedto') . "\" /></li>\n";
                break;
        }

        if ($this->_request_data['link_obj']->status == ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED)
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            echo "    <span id=\"org_openpsa_relatedto_toolbar_confirmdeny_{$this->_request_data['link_obj']->guid}\">\n";
            echo "        <li id=\"org_openpsa_relatedto_toolbar_confirm_{$this->_request_data['link_obj']->guid}\"><input type=\"button\" class=\"button\" value=\"" . $_MIDCOM->i18n->get_string('confirm relation', 'org.openpsa.relatedto') . "\" onclick=\"ooRelatedDenyConfirm('{$prefix}', 'confirm', '{$this->_request_data['link_obj']->guid}');\" /></li>\n";
            echo "        <li id=\"org_openpsa_relatedto_toolbar_deny_{$this->_request_data['link_obj']->guid}\"><input type=\"button\" class=\"button\" value=\"" . $_MIDCOM->i18n->get_string('deny relation', 'org.openpsa.relatedto') . "\" onclick=\"ooRelatedDenyConfirm('{$prefix}', 'deny', '{$this->_request_data['link_obj']->guid}');\" /><li>\n";
            echo "    </span>\n";
        }
        echo "</ul>\n";
    }

    /**
     * If a component wishes to show hour_report lines it must override this method
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_hour_report()
    {
        return;
    }

    /**
     * Renders a document line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_document()
    {
        //Make sure we have the calendar classes available
        $_MIDCOM->componentloader->load_graceful('org.openpsa.documents');
        //Fallback to default renderer if not
        if (!class_exists('net_nemein_wiki_wikipage'))
        {
            return $this->_show_render_line_default();
        }
        $document = new org_openpsa_documents_document($this->_request_data['link_other_obj']->guid);
        if (!is_object($document))
        {
            //probably ACL prevents us from seeing anything about it
            return;
        }
        echo "            <li class=\"document\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";
        $document_url = $_MIDCOM->permalinks->create_permalink($document->guid);
        //PONDER: Slower but browser would like it better?
        //$document_url = $_MIDCOM->permalinks->resolve_permalink($document->guid);
        echo "                <span class=\"title\"><a href=\"{$document_url}\" target=\"document_{$document->guid}\">{$document->title}</a></span>\n";

        echo "                <ul class=\"metadata\">\n";

        // Time
        echo '                    <li class="time">' . strftime('%x', $document->created) . "</li>\n";

        // Show shortcut to file download
        echo '                    <li class="file">';

        $atts = $document->list_attachments();
        if (count($atts) == 0)
        {
            echo $_MIDCOM->i18n->get_string('no files', 'org.openpsa.documents');
        }
        else
        {
            foreach ($atts as $file)
            {
                // FIXME: This is a messy way of linking into DM-managed files
                if ($file->parameter('midcom.helper.datamanager.datatype.blob', 'fieldname') == 'document')
                {
                    echo "<a target=\"document_{$document->guid}\" href=\"{$_MIDGARD['self']}midcom-serveattachmentguid-{$file->guid}/{$file->name}\">{$file->name}</a> (".sprintf($_MIDCOM->i18n->get_string('%s document', 'org.openpsa.documents'), $_MIDCOM->i18n->get_string($file->mimetype, 'org.openpsa.documents')).")";
                }
            }
        }

        echo '                   </li>';

        echo "                </ul>\n";

        echo "                <div id=\"org_openpsa_relatedto_details_{$document->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
        echo "                TEST</div>\n";
        //TODO: get correct node and via it then handle details trough AHAH (and when we have node we can use proper link in document_url as well

        $this->_show_render_line_controls();
        echo "            </li>\n";
    }

    /**
     * Renders a wikipage line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_wikipage_email()
    {
        //Make sure we have the calendar classes available
        $_MIDCOM->componentloader->load_graceful('net.nemein.wiki');
        //Fallback to default renderer if not
        if (!class_exists('net_nemein_wiki_wikipage'))
        {
            return $this->_show_render_line_default();
        }
        $page = new net_nemein_wiki_wikipage($this->_request_data['link_other_obj']->guid);
        if (!is_object($page))
        {
            //probably ACL prevents us from seeing anything about it
            return;
        }
        echo "            <li class=\"note email\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";

        $nap = new midcom_helper_nav();
        $node = $nap->get_node($page->topic);
        if (!$node)
        {
            // The page isn't from this site
            return;
        }
        $page_url = "{$node[MIDCOM_NAV_FULLURL]}{$page->name}";

        echo "                <span class=\"title\"><a href=\"{$page_url}\" target=\"wiki_{$page->guid}\">{$page->title}</a></span>\n";

        // Start metadata UL
        echo "                <ul class=\"metadata\">\n";
        // Time
        echo '                    <li class="time">' . strftime('%x', $page->created) . "</li>\n";
        // Author
        echo "                    <li class=\"members\">".$_MIDCOM->i18n->get_string('sender', 'net.nemein.wiki').": ";
        $author = new midcom_db_person($page->author);
        $author_card = new org_openpsa_contactwidget($author);
        echo $author_card->show_inline()." ";
        echo "                    </li>\n";
        // Recipients
        $this->_show_render_line_wikipage_email_recipients($page);
        // End metadata UL
        echo "                </ul>\n";

        echo "                <div id=\"org_openpsa_relatedto_details_url_{$page->guid}\" style=\"display: none;\" title=\"{$node[MIDCOM_NAV_FULLURL]}raw/{$page->name}.html\"></div>\n";
        echo "                <div id=\"org_openpsa_relatedto_details_{$page->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
        echo "                </div>\n";
        //TODO: get correct node and via it then handle details trough AHAH (and when we have node we can use proper link in page_url as well

        $this->_show_render_line_controls();
        echo "            </li>\n";
    }

    function _show_render_line_wikipage_email_recipients($page)
    {
        $seen_emails = array();
        $qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $qb->add_constraint('fromGuid', '=', $page->guid);
        $qb->add_constraint('fromComponent', '=', 'net.nemein.wiki');
        $qb->add_constraint('toComponent', '=', 'org.openpsa.contacts');
        $qb->begin_group('OR');
            $qb->add_constraint('toClass', '=', 'midcom_db_person');
            $qb->add_constraint('toClass', '=', 'midcom_org_openpsa_person');
            $qb->add_constraint('toClass', '=', 'org_openpsa_contacts_person');
        $qb->end_group();
        $qb->add_constraint('status', '<>', ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED);
        $recipients = $qb->execute();
        echo "                    <li class=\"members\">".$_MIDCOM->i18n->get_string('recipients', 'net.nemein.wiki').": ";
        foreach ($recipients as $recipient_link)
        {
            $recipient = new midcom_db_person($recipient_link->toGuid);
            $seen_emails[$recipient->email] = true;
            if (!is_a($recipient, 'midcom_db_person'))
            {
                continue;
            }
            $recipient_card = new org_openpsa_contactwidget($recipient);
            echo $recipient_card->show_inline() . " ";
        }
        $other_emails = $page->listparameters('net.nemein.wiki:emailimport_recipients');
        if ($other_emails)
        {
            while ($other_emails->fetch())
            {
                $email = $other_emails->name;
                if (isset($seen_emails[$email]))
                {
                    continue;
                }
                echo $email . ' ';
                $seen_emails[$email] = true;
            }
        }
        echo "                    </li>\n";
    }

    /**
     * Renders a wikipage line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_wikipage()
    {
        //Make sure we have the calendar classes available
        $_MIDCOM->componentloader->load_graceful('net.nemein.wiki');
        //Fallback to default renderer if not
        if (!class_exists('net_nemein_wiki_wikipage'))
        {
            return $this->_show_render_line_default();
        }
        $page = new net_nemein_wiki_wikipage($this->_request_data['link_other_obj']->guid);
        if (!is_object($page))
        {
            //probably ACL prevents us from seeing anything about it
            return;
        }
        echo "            <li class=\"note\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";

        $nap = new midcom_helper_nav();
        $node = $nap->get_node($page->topic);
        if (!$node)
        {
            // The page isn't from this site
            return;
        }
        $page_url = "{$node[MIDCOM_NAV_FULLURL]}{$page->name}";

        echo "                <span class=\"title\"><a href=\"{$page_url}\" target=\"wiki_{$page->guid}\">{$page->title}</a></span>\n";

        echo "                <ul class=\"metadata\">\n";

        // Time
        echo '                    <li class="time">' . strftime('%x', $page->created) . "</li>\n";

        // Author
        echo "                    <li class=\"members\">".$_MIDCOM->i18n->get_string('author', 'net.nemein.wiki').": ";
        $author = new midcom_db_person($page->author);
        $author_card = new org_openpsa_contactwidget($author);
        echo $author_card->show_inline()." ";
        echo "                    </li>\n";
        echo "                </ul>\n";

        echo "                <div id=\"org_openpsa_relatedto_details_url_{$page->guid}\" style=\"display: none;\" title=\"{$node[MIDCOM_NAV_FULLURL]}raw/{$page->name}.html\"></div>\n";
        echo "                <div id=\"org_openpsa_relatedto_details_{$page->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
        echo "                </div>\n";
        //TODO: get correct node and via it then handle details trough AHAH (and when we have node we can use proper link in page_url as well

        $this->_show_render_line_controls();
        echo "            </li>\n";
    }


    /**
     * Renders an event line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_event()
    {
        //Make sure we have the calendar classes available
        $_MIDCOM->componentloader->load_graceful('org.openpsa.calendar');
        //Fallback to default renderer if not
        if (!class_exists('org_openpsa_calendar_event'))
        {
            return $this->_show_render_line_default();
        }
        $event = new org_openpsa_calendar_event($this->_request_data['link_other_obj']->guid);
        if (!is_object($event))
        {
            //probably ACL prevents us from seeing anything about it
            return;
        }
        echo "            <li class=\"event\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";
        $cal_node = false;
        if (!array_key_exists('org_openpsa_relatedto_render_line_event_calnode', $GLOBALS))
        {
            $GLOBALS['org_openpsa_relatedto_render_line_event_calnode'] = midcom_helper_find_node_by_component('org.openpsa.calendar');
        }
        $cal_node =& $GLOBALS['org_openpsa_relatedto_render_line_event_calnode'];
        if (!empty($cal_node))
        {
            //Calendar node found, render a better view
            $event_url = "{$cal_node[MIDCOM_NAV_FULLURL]}event/{$event->guid}";
            $event_js = org_openpsa_calendar_interface::calendar_editevent_js($event->guid, $cal_node);
            echo "                <span class=\"title\"><a href=\"{$event_url}\" onclick=\"{$event_js}\" target=\"event_{$event->guid}\">{$event->title}</a></span>\n";

            echo "                <ul class=\"metadata\">\n";

            // Time
            echo '                    <li class="time location">' . $event->format_timeframe() . ", {$event->location}</li>\n";

            // Participants
            echo "                    <li class=\"members\">".$_MIDCOM->i18n->get_string('participants', 'org.openpsa.calendar').": ";
            foreach ($event->participants as $person_id => $confirmed)
            {
                $participant = new midcom_db_person($person_id);
                $participant_card = new org_openpsa_contactwidget($participant);
                echo $participant_card->show_inline()." ";
            }
            echo "                    </li>\n";
            echo "                </ul>\n";

            echo "                <div id=\"org_openpsa_relatedto_details_url_{$event->guid}\" style=\"display: none;\" title=\"{$cal_node[MIDCOM_NAV_FULLURL]}event/raw/{$event->guid}/\"></div>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$event->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            echo "                </div>\n";
            //TODO: necessary JS stuff to load details (which should in turn include the events own relatedtos) via AHAH
        }
        else
        {
            //We cannot find calendar node, render the plaintext view trough the calendar class
            echo "                <span class=\"title\">{$event->title}</span>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$event->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            echo $event->details_text(false, false, "<br>\n");
            echo "                </div>\n";
            //TODO: necessary JS stuff to display/hide the div here
        }

        $this->_show_render_line_controls();
        echo "            </li>\n";
    }

    /**
     * Renders a task line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_task()
    {
        //Make sure we have the calendar classes available
        $_MIDCOM->componentloader->load_graceful('org.openpsa.projects');
        //Fallback to default renderer if not
        if (!class_exists('org_openpsa_projects_task'))
        {
            return $this->_show_render_line_default();
        }
        $task = new org_openpsa_projects_task($this->_request_data['link_other_obj']->guid);
        if (!is_object($task))
        {
            //probably ACL prevents us from seeing anything about it
            return;
        }
        echo "            <li class=\"task\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";

        $proj_node = false;
        if (!array_key_exists('org_openpsa_relatedto_render_line_event_projnode', $GLOBALS))
        {
            $GLOBALS['org_openpsa_relatedto_render_line_event_projnode'] = midcom_helper_find_node_by_component('org.openpsa.projects');
        }
        $proj_node =& $GLOBALS['org_openpsa_relatedto_render_line_event_projnode'];
        if (!empty($proj_node))
        {
            $task_url = "{$proj_node[MIDCOM_NAV_FULLURL]}task/{$task->guid}";
            echo "                <span class=\"title\"><a href=\"{$task_url}\" target=\"task_{$task->guid}\">{$task->title}</a></span>\n";
            echo "                <ul class=\"metadata\">\n";

            // Deadline
            echo "                    <li>".$_MIDCOM->i18n->get_string('deadline', 'org.openpsa.projects').": ".strftime('%x', $task->end)."</li>";

            // Resources
            echo "                    <li>".$_MIDCOM->i18n->get_string('resources', 'org.openpsa.projects').": ";
            foreach ($task->resources as $resource_id => $confirmed)
            {
                $resource = new midcom_db_person($resource_id);
                $resource_card = new org_openpsa_contactwidget($resource);
                echo $resource_card->show_inline()." ";
            }
            echo "                    </li>\n";
            echo "                </ul>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$task->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            echo "                </div>\n";
            //TODO: necessary JS stuff to load details (which should in turn include the tasks own relatedtos) via AHAH
        }
        else
        {
            echo "                <span class=\"title\">{$task->title}</span>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$task->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            //TODO: Output some details ?
            echo "                </div>\n";
            //TODO: necessary JS stuff to display/hide the div here
        }

        $this->_show_render_line_controls();
        echo "            </li>\n";
    }

    /**
     * Renders a sales project line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_salesproject()
    {
        //Make sure we have the calendar classes available
        $_MIDCOM->componentloader->load_graceful('org.openpsa.sales');
        //Fallback to default renderer if not
        if (!class_exists('org_openpsa_projects_task'))
        {
            return $this->_show_render_line_default();
        }
        $salesproject = new org_openpsa_sales_salesproject($this->_request_data['link_other_obj']->guid);
        if (!is_object($salesproject))
        {
            //probably ACL prevents us from seeing anything about it
            return;
        }
        echo "            <li class=\"salesproject\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";

        $sales_node = false;
        if (!array_key_exists('org_openpsa_relatedto_render_line_event_salesnode', $GLOBALS))
        {
            $GLOBALS['org_openpsa_relatedto_render_line_event_salesnode'] = midcom_helper_find_node_by_component('org.openpsa.sales');
        }
        $sales_node =& $GLOBALS['org_openpsa_relatedto_render_line_event_salesnode'];
        if (!empty($sales_node))
        {
            $sales_url = "{$sales_node[MIDCOM_NAV_FULLURL]}salesproject/{$salesproject->guid}";
            echo "                <span class=\"title\"><a href=\"{$sales_url}\" target=\"task_{$salesproject->guid}\">{$salesproject->title}</a></span>\n";
            echo "                <ul class=\"metadata\">\n";

            // Owner
            echo "                    <li>".$_MIDCOM->i18n->get_string('owner', 'midcom').": ";
            $owner = new midcom_db_person($salesproject->owner);
            $owner_card = new org_openpsa_contactwidget($owner);
            echo $owner_card->show_inline()." ";
            echo "</li>";

            // Customer
            if ($salesproject->customer)
            {
                echo "                    <li>".$_MIDCOM->i18n->get_string('customer', 'org.openpsa.sales').": ";
                $customer = new midcom_db_group($salesproject->customer);
                echo $customer->official;
                echo "</li>";
            }

            echo "                </ul>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$salesproject->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            echo "                </div>\n";
            //TODO: necessary JS stuff to load details (which should in turn include the tasks own relatedtos) via AHAH
        }
        else
        {
            echo "                <span class=\"title\">{$salesproject->title}</span>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$salesproject->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            //TODO: Output some details ?
            echo "                </div>\n";
            //TODO: necessary JS stuff to display/hide the div here
        }

        $this->_show_render_line_controls();
        echo "            </li>\n";
    }

    /**
     * Renders an invoice line
     *
     * See the _show_render documentation for details about styling
     */
    function _show_render_line_invoice()
    {
        //Make sure we have the calendar classes available
        $_MIDCOM->componentloader->load_graceful('org.openpsa.invoices');
        //Fallback to default renderer if not
        if (!class_exists('org_openpsa_invoices_invoice'))
        {
            return $this->_show_render_line_default();
        }
        $invoice = new org_openpsa_invoices_invoice($this->_request_data['link_other_obj']->guid);
        if (!is_object($invoice))
        {
            //probably ACL prevents us from seeing anything about it
            return;
        }
        echo "            <li class=\"invoice\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";

        $invoices_node = false;
        if (!array_key_exists('org_openpsa_relatedto_render_line_event_invoicesnode', $GLOBALS))
        {
            $GLOBALS['org_openpsa_relatedto_render_line_event_invoicesnode'] = midcom_helper_find_node_by_component('org.openpsa.invoices');
        }
        $invoices_node =& $GLOBALS['org_openpsa_relatedto_render_line_event_invoicesnode'];
        if (!empty($invoices_node))
        {
            $invoice_url = "{$invoices_node[MIDCOM_NAV_FULLURL]}invoice/{$invoice->guid}";
            echo "                <span class=\"title\"><a href=\"{$invoice_url}\" target=\"task_{$invoice->guid}\">".$_MIDCOM->i18n->get_string('invoice', 'org.openpsa.invoices')." {$invoice->invoiceNumber}</a></span>\n";
            echo "                <ul class=\"metadata\">\n";

            // Customer
            if ($invoice->customer)
            {
                $customer = new midcom_db_group($invoice->customer);
                echo "                    <li>".$_MIDCOM->i18n->get_string('customer', 'org.openpsa.invoices').": {$customer->official}</li>";
            }

            // Sum and due date
            if ($invoice->due < time())
            {
                if ($invoice->paid > 0)
                {
                    $paid = ", " . $_MIDCOM->i18n->get_string('paid', 'org.openpsa.invoices') . ": " . strftime('%x', $invoice->paid);
                }
                else
                {
                    $paid = ", " . $_MIDCOM->i18n->get_string('not paid', 'org.openpsa.invoices');
                }
            }
            else
            {
                $paid = '';
            }

            echo "                    <li>".$_MIDCOM->i18n->get_string('sum', 'org.openpsa.invoices').": {$invoice->sum} (".$_MIDCOM->i18n->get_string('due', 'org.openpsa.invoices').": ".strftime('%x', $invoice->due)."{$paid})</li>";

            echo "                    </li>\n";
            echo "                </ul>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$invoice->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            echo "                </div>\n";
            //TODO: necessary JS stuff to load details (which should in turn include the tasks own relatedtos) via AHAH
        }
        else
        {
            echo "                <span class=\"title\">".$_MIDCOM->i18n->get_string('invoice', 'org.openpsa.invoices')." {$invoice->invoiceNumber}</span>\n";
            echo "                <div id=\"org_openpsa_relatedto_details_{$invoice->guid}\" class=\"details hidden\" style=\"display: none;\">\n";
            //TODO: Output some details ?
            echo "                </div>\n";
            //TODO: necessary JS stuff to display/hide the div here
        }

        $this->_show_render_line_controls();
        echo "            </li>\n";
    }

    /**
     * Default line rendering, used if a specific renderer cannot be found
     *
     * Tries to find certain properties likely to hold semi-useful information about
     * the object, failing that outputs class and guid.
     */
    function _show_render_line_default()
    {
        $class = get_class($this->_request_data['link_other_obj']);
        echo "            <li class=\"unknown {$class}\" id=\"org_openpsa_relatedto_line_{$this->_request_data['link_obj']->guid}\">\n";
        $this->_show_render_line_controls();
        echo '                <span class="title">';
        switch(true)
        {
            case (   isset($this->_request_data['link_other_obj']->title)
                  && !empty($this->_request_data['link_other_obj']->title)):
                echo $this->_request_data['link_other_obj']->title;
                break;
            case (   isset($this->_request_data['link_other_obj']->official)
                  && !empty($this->_request_data['link_other_obj']->official)):
                echo $this->_request_data['link_other_obj']->official;
                break;
            case (   isset($this->_request_data['link_other_obj']->rname)
                  && !empty($this->_request_data['link_other_obj']->rname)):
                echo $this->_request_data['link_other_obj']->rname;
                break;
            case (   isset($this->_request_data['link_other_obj']->name)
                  && !empty($this->_request_data['link_other_obj']->name)):
                echo $this->_request_data['link_other_obj']->rname;
                break;
            default:
                echo "{$class} #{$this->_request_data['link_other_obj']->guid}";
        }
        echo "</span>\n";

        $this->_show_render_line_controls();
        echo "</li>\n";
    }

    /**
     * Code to sort array by key 'sort_time', from greatest to smallest
     *
     * Used by $this->_sort_link_array()
     */
    function _code_for_sort_by_time_reverse()
    {
        return <<<EOF
        \$ap = \$a['sort_time'];
        \$bp = \$b['sort_time'];
        if (\$ap > \$bp)
        {
            return -1;
        }
        if (\$ap < \$bp)
        {
            return 1;
        }
        return 0;
EOF;
    }

    /**
     * Code to sort array by key 'sort_time', from smallest to greatest
     *
     * Used by $this->_sort_link_array()
     */
    function _code_for_sort_by_time()
    {
        return <<<EOF
        \$ap = \$a['sort_time'];
        \$bp = \$b['sort_time'];
        if (\$ap > \$bp)
        {
            return 1;
        }
        if (\$ap < \$bp)
        {
            return -1;
        }
        return 0;
EOF;
    }

    /**
     * Default method for getting object's relatedtos (inbound ie toGuid == $obj->guid)
     *
     * Components handlers may need to override this to account
     * for specific object types and possible traversing of their children
     */
    function _get_object_links_in(&$arr, $obj)
    {
        if (   !is_object($obj)
            || !is_array($arr))
        {
            return false;
        }
        $qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $qb->add_constraint('toGuid', '=', $obj->guid);
        $qb->add_constraint('status', '<>', ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED);
        $links = $qb->execute();
        if (!is_array($links))
        {
            return false;
        }
        foreach($links as $link)
        {
            //TODO: check for duplicates ?
            $to_arr = array('link' => false, 'other_obj' => false, 'sort_time' => false);
            $to_arr['link']  = $link;
            $to_arr['other_obj'] = $_MIDCOM->dbfactory->get_object_by_guid($link->fromGuid);
            if (!is_object($to_arr['other_obj']))
            {
                continue;
            }
            $to_arr['sort_time'] = $this->_get_object_links_sort_time($to_arr['other_obj']);
            $arr[] = $to_arr;
        }
        return true;
    }

    /**
     * Default method for getting object's relatedtos (outbound ie fromGuid == $obj->guid)
     *
     * Components handlers may need to override this to account
     * for specific object types and possible traversing of their children
     */
    function _get_object_links_out(&$arr, $obj)
    {
        if (   !is_object($obj)
            || !is_array($arr))
        {
            return false;
        }
        $qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $qb->add_constraint('fromGuid', '=', $obj->guid);
        $qb->add_constraint('status', '<>', ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED);
        $links = $qb->execute();
        if (!is_array($links))
        {
            return false;
        }
        foreach($links as $link)
        {
            //TODO: check for duplicates ?
            $to_arr = array('link' => false, 'other_obj' => false, 'sort_time' => false);
            $to_arr['link']  = $link;
            $to_arr['other_obj'] = $_MIDCOM->dbfactory->get_object_by_guid($link->toGuid);
            if (!is_object($to_arr['other_obj']))
            {
                continue;
            }
            $to_arr['sort_time'] = $this->_get_object_links_sort_time($to_arr['other_obj']);
            $arr[] = $to_arr;
        }
        return true;
    }

    /**
     * returns a unix timestamp for sorting relatedto arrays
     *
     * If components need to return very specific values here they should override
     * this method to add their own handling and if they do not know what to do call this
     * via parent::_get_object_links_sort_time()
     */
    function _get_object_links_sort_time($obj)
    {
        switch(true)
        {
            case is_a($obj, 'midcom_baseclasses_database_event'):
                return $obj->start;
            case is_a($obj, 'midcom_org_openpsa_task'):
                return $obj->start;
            default:
                return $obj->created;
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_ajax($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->skip_page_style = true;
        $ajax = new org_openpsa_helpers_ajax();
        //Request mode switch
        $this->_request_data['mode'] =& $args[0];
        $this->_request_data['object'] = false;
        if (isset($args[1]))
        {
            $this->_request_data['object'] = $_MIDCOM->dbfactory->get_object_by_guid($args[1]);
        }
        switch ($this->_request_data['mode'])
        {
            case 'deny':
                if (   !$this->_request_data['object']
                    || !is_a($this->_request_data['object'], 'midcom_org_openpsa_relatedto'))
                {
                    $ajax->simpleReply(false, "method '{$this->_request_data['mode']}' requires guid of a link object as an argument");
                }
                $this->_request_data['object']->status = ORG_OPENPSA_RELATEDTO_STATUS_NOTRELATED;
                $stat = $this->_request_data['object']->update();
                $ajax->simpleReply($stat, 'error:' . mgd_errstr());
                //this will exit()
            case 'confirm':
                if (   !$this->_request_data['object']
                    || !is_a($this->_request_data['object'], 'midcom_org_openpsa_relatedto'))
                {
                    $ajax->simpleReply(false, "method '{$this->_request_data['mode']}' requires guid of a link object as an argument");
                }
                $this->_request_data['object']->status = ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED;
                $stat = $this->_request_data['object']->update();
                $ajax->simpleReply($stat, 'error:' . mgd_errstr());
                //this will exit()
            default:
                $ajax->simpleReply(false, "method '{$this->_request_data['mode']}' not supported");
                //this will exit()
        }
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_ajax($handler_id, &$data)
    {
    }


}

?>