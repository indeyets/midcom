<?php

/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Quickpoll Viewer interface class.
 * 
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_viewer {

  var $_debug_prefix;

  var $_config;
  var $_quickpoll;
  var $_options;
  var $_votes;
  var $_total_votes;
  var $_topic;
  var $_view;
  var $_allow_vote;
  var $_vote_counted;
  var $_temporary_authentication; 

  function net_nemein_quickpoll_viewer($topic, $config) {

    global $midgard;
    
    $this->_debug_prefix = "net.nemein.quickpoll viewer::";

    $this->_config = $config;
    $this->_topic = $topic;
    $this->_quickpoll = false;
    $this->_view = false; 
    $this->_options = array();
    $this->_votes = array();
    $this->_total_votes = 0;
    $this->_vote_counted = false;

    // Whether to allow editing
    if ($GLOBALS["midgard"]->user) {
      $this->_allow_vote = true;
    } else {
      $this->_allow_vote = false;
    }

    $GLOBALS["net_nemein_quickpoll_errstr"] = "";

    $this->_temporary_authentication = false;

    // get l10n libraries
    $i18n =& $GLOBALS["midcom"]->get_service("i18n");
    $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.quickpoll");
    $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

  }
  
  function _allow_vote() {
  
    if ($this->_vote_counted) {
      return false;
    } elseif ($this->_temporary_authentication && (!$this->_config->get("allow_multiple_votes")) && $this->_quickpoll->parameter("net.nemein.quickpoll.voted_ip",$_SERVER['REMOTE_ADDR'])) {
      // This anonymous user's IP has already voted
      return false;
    } elseif ($GLOBALS["midgard"]->user) {
      $user = mgd_get_person($GLOBALS["midgard"]->user);
      if ((!$this->_config->get("allow_multiple_votes")) && $this->_quickpoll->parameter("net.nemein.quickpoll.voted_user",$user->username)) {
        // This user has already voted
        return false;    
      } else {
        return $this->_allow_vote;
      }
    } else {
      return $this->_allow_vote;
    }
  
  }

  function _anon_auth() {
    if (!$this->_allow_vote && $this->_config->get("allow_anonymous")) {
      mgd_auth_midgard($this->_config->get("anonymous_username"),$this->_config->get("anonymous_password"),0);
      $GLOBALS["midgard"] = mgd_get_midgard();
      if ($GLOBALS["midgard"]->user) {
        $this->_temporary_authentication = true;
        $this->_allow_vote = true;
      } else {
        debug_add("Anonymous authentication failed, reason ".mgd_errstr());
      }
    }
  }

  function _anon_unauth() {
    if ($this->_temporary_authentication) {
      mgd_unsetuid();
      $GLOBALS["midgard"] = mgd_get_midgard();
      $this->_temporary_authentication = false;
      $this->_allow_vote = false;
    }
  }

  function can_handle($argc, $argv) {

    debug_push($this->_debug_prefix . "can_handle");

    $this->_argv = $argv;

    // see if we can handle this request.
    if (!$this->_getquickpoll($argc, $argv)) {
      // errstr and errcode are already set by getquickpoll
      debug_add("could not get poll. see above.");
      debug_pop();
      return false;
    }
    $GLOBALS['midcom_component_data']['net.nemein.quickpoll']['active_leaf'] = $this->_quickpoll->id;

    debug_pop();
    return true;
  }
  
    function _handleVote($vote)
    {
        debug_push($this->_debug_prefix . "_handleVote");
        
        debug_add("Processing vote ".$vote." for user #".$GLOBALS["midgard"]->user,MIDCOM_LOG_DEBUG);
        
        // Check if votes match a preset option and there are already votes
        $votes = $this->_quickpoll->parameter("net.nemein.quickpoll.option",$this->_options[$vote]);
        
        $vote_registered = false;
        
        if ($this->_config->get("allow_user_supplied_options")) {
            // This poll allows votes for user-provided options
            
            $existing_votes = $this->_quickpoll->parameter("net.nemein.quickpoll.option",$vote);
            if ($existing_votes != "")
            {
                // Somebody has voted for this option before
                if ($this->_quickpoll->parameter("net.nemein.quickpoll.option",$vote,$existing_votes+1))
                {
                    $vote_registered = true;
                    
                    // Add this vote to the score cache
                    foreach ($this->_options as $id => $name)
                    {
                        if ($name == $vote) {
                            $this->_votes[$id]++;
                        }
                    }
                    
                    debug_add("Vote ".$vote." registered, score ".$existing_votes+1,MIDCOM_LOG_DEBUG);
                }
            } else {
                if ($this->_quickpoll->parameter("net.nemein.quickpoll.option",$vote,1))
                {
                    $vote_registered = true;
                    
                    // Add this vote and option to the score cache
                    $this->_options[count($this->_options)] = $vote;
                    $this->_votes[count($this->_options)] = 1;
                    
                    debug_add("Vote ".$vote." registered, first vote for option!",MIDCOM_LOG_DEBUG);
                }
            }
        }
        elseif ($votes != "")
        {
            // Count vote for existing option
            $this->_quickpoll->parameter("net.nemein.quickpoll.option",$this->_options[$vote],$votes+1);
            debug_add("Vote for existing option ".$vote." registered, score ".$votes+1,MIDCOM_LOG_DEBUG);
            $vote_registered = true;
            $this->_votes[$vote]++;
        }
            
        if ($vote_registered)
        {
            // One of the three voting cases worked
            $this->_total_votes++;
          
            // Invalidate cache
            $GLOBALS["midcom"]->cache->invalidate_all();

            if (!$this->_config->get("allow_multiple_votes"))
            {
                // Disallow user from voting again
                if ($this->_temporary_authentication)
                {
                    // Temporary user, prevention through IP
                    $this->_quickpoll->parameter("net.nemein.quickpoll.voted_ip",$_SERVER['REMOTE_ADDR'],time());
                } 
                else
                {
                    // Registered, normal user. Prevention through username
                    $user = mgd_get_person($GLOBALS["midgard"]->user);
                    $this->_quickpoll->parameter("net.nemein.quickpoll.voted_user",$user->username,time());
                }
            }
            $this->_vote_counted = true;
            debug_pop();
            return true;
        }
        else
        {
            // User tried to vote but failed
            // TODO: provide an UI-level error message
            debug_add("Vote registration failed, last Midgard state is ".mgd_errstr(),MIDCOM_LOG_DEBUG);
            debug_pop();
            return false;
        }
    }

    function _getquickpoll($argc, $argv) {
  
        debug_push($this->_debug_prefix . "_getquickpoll");

        if ($argc == 0)
        {
            // List of polls
            $this->_view = "index";
            debug_pop();
            return true;
        }
        elseif ($argc == 1) 
        {
            if ($argv[0] == "latest")
            {
                debug_add("Getting latest poll",MIDCOM_LOG_DEBUG);
                $polls = mgd_list_topic_articles($this->_topic->id);
                if ($polls)
                {
                    if ($polls->fetch())
                    {
                        $this->_quickpoll = mgd_get_article($polls->id); 
                    }
                }
            }
            else
            {
                // Get named poll
                $this->_quickpoll = mgd_get_article_by_name($this->_topic->id,$argv[0]);
            }
          
            if (!$this->_quickpoll)
            {
                // Oops, no poll found. 404.
                debug_add("quickpoll $argv[0] could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
                $this->errstr = "quickpoll $argv[0] could not be loaded: " . mgd_errstr();
                $this->errcode = MIDCOM_ERRNOTFOUND;
                debug_pop();
                return false;
            } 
            else
            {
                // Ok, we've got specific poll
                $this->_view = "quickpoll";
        
                // Populate existing poll options
                $options = $this->_quickpoll->listparameters("net.nemein.quickpoll.option");
                $options_shown = 0;
                if ($options)
                {
                    while ($options->fetch()) {
                        $options_shown++;
                        $this->_options[$options_shown] = $options->name;
                        $this->_votes[$options_shown] = $this->_quickpoll->parameter("net.nemein.quickpoll.option",$options->name);
                        $this->_total_votes = $this->_total_votes + $this->_votes[$options_shown];
                    }
                }
            
                if (isset($_REQUEST["net_nemein_quickpoll_vote"]))
                {
                    // User is trying to vote
                    // TODO: Switch from _REQUEST to _POST
                    $this->_anon_auth();
                    if ($this->_allow_vote())
                    {
                        $this->_handleVote($_REQUEST["net_nemein_quickpoll_vote"]);
                    }
                    else
                    {
                        // User tried to vote but wasn't allowed
                        // TODO: Give UI-level message                                        
                    }
                }
                debug_pop(); 
                return true;
            }
        }
    }

  function handle() {
    
    debug_push($this->_debug_prefix . "handle");

    // We can't cache requests if anonymous voting is on, as the status checks
    // are based on IP address and MidCOM cache doesn't handle that
    if ($this->_config->get("allow_anonymous") && !$GLOBALS["midgard"]->user)
    {
        $GLOBALS["midcom"]->cache->content->no_cache();
    }

    debug_pop();
    return true;
  }

  function show() {
  
    global $midcom;
    global $view;
  
    debug_push($this->_debug_prefix . "show");

    if ($this->_view == "quickpoll") {

      global $view_options, $view_votes, $view_total_votes;
      $view = $this->_quickpoll;
      $view_options = $this->_options;
      $view_votes = $this->_votes;
      $view_total_votes = $this->_total_votes;

      $this->_anon_auth();
      if ($this->_allow_vote()) {
        midcom_show_style("view-quickpoll");
      } else {
        midcom_show_style("view-quickpoll-results");
      }
      $this->_anon_unauth();

    } elseif ($this->_view == "index") {
    
      global $view_topic;
      $view_topic = $this->_topic;

      midcom_show_style("view-index-header");
      
      $polls = mgd_list_topic_articles($this->_topic->id);
      if ($polls) {
        while ($polls->fetch()) {
          $view = $polls;
          midcom_show_style("view-index-item");          
        }
      }
      
      midcom_show_style("view-index-footer");

    }    

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