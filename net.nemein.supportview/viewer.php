<?php
/**
 * @package net.nemein.supportview
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * OpenPSA Supporview Site interface class.
 * 
 * @package net.nemein.supportview
 */
class net_nemein_supportview_viewer {

    var $_argv;
    var $_config;
    var $_debug_prefix;
    var $_ticket;
    var $_topic;
    var $_tt_root_topic;
    var $_user;
    var $_view;
    var $_show_closed;
    var $form_prefix;
    var $view;

    function net_nemein_supportview_viewer($topic, $config) {

        $this->_argv = array();
        $this->_config = $config;
        $this->_debug_prefix = "net.nemein.supportview viewer::";
        $this->_ticket = false;
        $this->_topic = $topic;
        $this->_show_closed = false;
        $this->_tt_root_topic = mgd_get_topic_by_name(0, $config->get("tt_root_topic_name"));
        
        if ($_MIDGARD["user"])
        {
            $this->_user = mgd_get_person($_MIDGARD["user"]);
        }
        else
        {
            $this->_user = null;
        }
        
        $this->_view = false; 
        $this->form_prefix = "net_nemein_supportview_viewer_";
        $this->view = false;
        
        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();
        
        $_MIDCOM->add_link_head(array(
            'rel' => 'stylesheet',
            'type' => 'text/css',
            'href' => MIDCOM_STATIC_URL."/net.nemein.supportview/supportview.css",
        ));   
        
    }
    
    function _check_ticket_visibility($ticket) 
    {
        if (   !isset($ticket->contacts['email']) 
            || empty($ticket->contacts['email']))
        {
            return false;
        }
        
        if ($this->_config->get('client_domain'))
        {
            // List all tickets sent from a domain, show to anybody, can have multiple domains pipe separated
            if (preg_match('/('.$this->_config->get('client_domain').')$/i', $ticket->contacts['email']))
            {
                // Ticket is in the domain configured for this component
                return true;
            }
            else
            {
                // Don't show
                return false;
            }
        }
    
        // Otherwise we're in the default behaviour, show to user tickets sent from his domain
        if ($this->_user && $this->_user->email)
        {
            //Get domain part of users email
            if (preg_match('/@([^> ]+)/', $this->_user->email, $matches))
            {
                //Check that ticket "from" address ends with said domain
                if (preg_match('/'.$matches[1].'$/i', $ticket->contacts['email']))
                {
                    return true;
                }
            }
        }

        // No match, no tickets
        return false;        
    }

    function can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "can_handle");

        $this->_argv = $argv;

        error_reporting(E_ALL ^ E_NOTICE);
        if ($argc == 1) {
   
            switch ($argv[0]) {
                case "closed":
                        // Show closed tickets
                        $this->_show_closed = true;
                        $this->_view = "list";
                        return true;
            	case "statistics":
                	$this->_view = "statistics";
            		return true;
            	default:
                	$this->_view = "ticket";
            		break;
            }

            if ($this->_view === "ticket") {
                //$this->_ticket = mgd_get_article_by_name($this->_tt_root_topic->id,$argv[0]);
                error_reporting(E_ALL ^ E_NOTICE);
                $this->_ticket = nemein_get_ticket($argv[0]);
                if (!$this->_ticket) {
                    debug_add("Ticket $argv[0] could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
                    $this->errstr = "Ticket $argv[0] could not be loaded: " . mgd_errstr();
                    $this->errcode = MIDCOM_ERRNOTFOUND;
                    return false;
                } else {
                    if ($this->_check_ticket_visibility($this->_ticket))
                    {
                        return true;
                    }
                    else
                    {
                        debug_add("Ticket $argv[0] is not allowed: " . mgd_errstr(), MIDCOM_LOG_INFO);
                        $this->errstr = "Access forbidden";
                        $this->errcode = MIDCOM_ERRNOTFOUND;
                        return false;
                    }
                }
                error_reporting(E_ALL);
            }
        } else {

            // Show index (search) view
            $this->_view = "list";
            return true;

        }

        $GLOBALS['midcom_component_data']['net.nemein.supportview']['active_leaf'] = $this->_ticket->id;
        
        error_reporting(E_ALL);
        debug_pop();
        return true;

    }

    function handle() {
        global $_REQUEST;
        
        debug_push($this->_debug_prefix . "handle");
        debug_pop();
        return true;
    }

    function show() {
  
        error_reporting(E_ALL ^ E_NOTICE);
        global $midcom, $view, $view_topic_title, $view_tickets_found;
        
        debug_push($this->_debug_prefix . "show");
        
        // get l10n libraries
        $i18n =& $_MIDCOM->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.supportview");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");
        
        $view_form_prefix = $this->form_prefix;
        
        $view_topic_title = $this->_topic->extra;

        $view_tickets_found = 0;

        if ($this->_view === "ticket") {

            $view = $this->_ticket;
            
            midcom_show_style("view-ticket");
            error_reporting(E_ALL ^ E_NOTICE);

            $tt_notes = nemein_list_ticket_notes($view->id);

            if ($tt_notes)
            {
                while ($tt_notes->fetch())
                {
                    $display_note = nemein_can_view_ticket($tt_notes);
                    
                    if ($this->_config->get('require_email')
                        && $tt_notes->sendEmail == 0)
                    {
                        $display_note = false;
                    }
                
                    if ($display_note)
                    {
                        $tt_notes->description=nl2br(htmlspecialchars($tt_notes->description));
                        $view = $tt_notes;
                        midcom_show_style("view-ticket-note");
                    }
                }
            }
            error_reporting(E_ALL);
             
        } elseif (!$this->_view === "statistics") {
            
            //TODO

        } else {

            // Ticket listing
           
            midcom_show_style("view-header");
            error_reporting(E_ALL ^ E_NOTICE);
            $ttlist = nemein_list_tickets("lastaction_stamp", 0, $this->_tt_root_topic->id, "", "", "", "");

            $tickets = array();
            if ($ttlist)
            {
                while ($ttlist->fetch())
                {
                    error_reporting(E_ALL ^ E_NOTICE);
                    if ($this->_show_closed)
                    { 
                        if (   $ttlist->status == 5
                            && $this->_check_ticket_visibility($ttlist))
                        {
                            // Show only closed tickets
                            $tickets[] = $ttlist;
                        }
                    }
                    elseif (   $ttlist->status != 5 
                            && $this->_check_ticket_visibility($ttlist))
                    {
                        $tickets[] = $ttlist;
                    }
                }
            }
            
            if (count($tickets) > 0)
            {
                midcom_show_style("view-list-header");
                $GLOBALS["view_even"] = false;

                foreach ($tickets as $ticket)
                {
                    $view = $ticket;
                    midcom_show_style("view-list-item");
                    $view_tickets_found++;
                    
                    if (!$GLOBALS["view_even"])
                    {
                        $GLOBALS["view_even"] = true;
                    } 
                    else
                    {
                        $GLOBALS["view_even"] = false;
                    }
                }
                
                midcom_show_style("view-list-footer");
            }
            else
            {
                midcom_show_style("view-list-notickets");
            }
            
        }
        error_reporting(E_ALL);
        
        error_reporting(E_ALL);
        debug_pop();
        return true;

    }
        

    function get_metadata() {


        // metadata for the current element
        /*
        return array (
            MIDCOM_META_CREATOR => ...,
            MIDCOM_META_EDITOR => ...,
            MIDCOM_META_CREATED => ...,
            MIDCOM_META_EDITED => ...
        );*/
    }
    
} // viewer

?>
