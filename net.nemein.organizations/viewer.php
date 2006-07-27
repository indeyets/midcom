<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Group viewer Site interface class.
 * 
 * @package net.nemein.organizations
 */
class net_nemein_organizations_viewer {

    var $_debug_prefix;

    var $_config;
    var $_topic;
    var $_layout;
    var $_view;

    var $_preferred_group;
    var $_shown_groups;
    var $_group;
    var $_organization;
    var $_enable_random;    // Boolean, whether to allow random listing or not
    var $_random;          // Amount of randomly generated groups
    var $_random_field;    // Field to check if the group can be shown as random
    var $_random_groups;   // Array of randomly generated groups

    var $errcode;
    var $errstr;

    function net_nemein_organizations_viewer($topic, $config) {

        $this->_debug_prefix = "net.nemein.organizations viewer::";

        $this->_config = $config;
        $this->_topic = $topic;

        $this->_organization = false;
        $this->_shown_groups = array();

        $this->_layout = new midcom_helper_datamanager($this->_config->get('schemadb'));
        if (!$this->_layout) {
          $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,"Layout class could not be instantinated.");
        }  

        $this->_view = false;

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        //$GLOBALS['midcom_component_data']['net.nemein.organizations']['active_leaf'] = false;

        $this->_group = new midcom_baseclasses_database_group($this->_config->get("group"));
        
        $qb = midcom_baseclasses_database_group::new_query_builder();
        $qb->add_constraint('owner', '=', $this->_group->id);
        $this->_total_groups = $qb->count();

        $this->_preferred_group = $this->_config->get("preferred_group");

        $this->_go = false;

        $this->_enable_random = $this->_config->get("enable_random");

    }


    function can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "can_handle");

        if ($argc == 0) {
            $this->_view = "index";
            return true;
        } 
        if ($argc == 1) {

            // Try to get group
            if ($this->_getOrganization($argv[0])) {
                debug_pop();
                return true;
            }
        }
        if ($argc == 2 && $argv[0] == "random" && $this->_enable_random == true) {
            // Display N random cases
            $this->_view = "random";
            $this->_random = $argv[1];
            $this->_random_groups = $this->draw_random_groups();
            //if (is_array($this->_random_groups)) {
                return true;
            //}
        }
        debug_pop();
        return false;
    }

    function _getOrganization ($getgroup) {

        debug_push($this->_debug_prefix . "_getgroup");

        debug_add("looking for group '$getgroup'", MIDCOM_LOG_DEBUG);

        if (isset($this->_group)) {

          $organization = new midcom_baseclasses_database_group($getgroup);
          if (! $organization)
          {
              debug_add("Failed to load group {$getgroup}", MIDCOM_LOG_INFO);
              debug_pop();
              return false;
          }
          
          if (   $organization->is_object_visible_onsite()
              && $organization->owner == $this->_group->id) 
          {
            $this->_organization = $organization;
            if ($this->_organization) {
              debug_add("found group", MIDCOM_LOG_DEBUG);
              $this->errcode = MIDCOM_ERROK;
              debug_pop();
              $this->_view = "group";
              $GLOBALS['midcom_component_data']['net.nemein.organizations']['active_leaf'] = $this->_organization->id;
              return true;    
            }
          }
        } 
        return false;
    }

    function handle() {

        global $midcom;
        debug_push($this->_debug_prefix . "handle");

        if ($this->errcode != MIDCOM_ERROK) {
            debug_pop();
            return false;
        }

        if ($this->_organization) {
          $this->_layout = new midcom_helper_datamanager($this->_config->get('schemadb'));
          if (! $this->_layout)
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Datamanager class could not be instantinated.");

          if (! $this->_layout->init($this->_organization))
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Datamanager class could not be initialized.");

          // set nap element
          $GLOBALS['midcom_component_data']['net.nemein.organizations']['active_leaf'] = $this->_organization->id;
          $GLOBALS['midcom']->set_pagetitle($this->_group->name);

          // initialize layout
          $substyle = $this->_layout->get_layout_name();
          if ($substyle != "default") {
            debug_add ("pushing substyle $substyle", MIDCOM_LOG_DEBUG);
            $midcom->substyle_append($substyle);
          }
        } else {
          $GLOBALS['midcom']->set_pagetitle($this->_topic->extra);
        }
        debug_pop();
        return true;
    }

    function _readOpenPSA($group_id) {
       // Method to read the serialized company information as stored by OpenPSA

       $organization = new midcom_baseclasses_database_group($group_id);

       if ($organization && $organization->extra) {
         $openpsa_extra = unserialize($organization->extra);

         if (is_array($openpsa_extra['parse'])) {
           while (list ($k, $arr) = each ($openpsa_extra['parse'])) {
             if (is_array($arr)) {
                while (list ($kk, $v) = each ($arr)) {
                  $valname = $k."_".$kk;
                  $openpsa_extra[$valname] = $v;
                }
             } else {
                $openpsa_extra[$k] = $arr;
             }
           }
         }
         unset ($openpsa_extra['parse']);
         return $openpsa_extra;
       }
    }
    
    function _displayOrganization($group_id) {

      $organization = new midcom_baseclasses_database_group($group_id);
      
      if (   $organization 
          && $organization->owner == $this->_group->id) 
      {              
        
        if ($this->_config->get("filter_default") == true) {
          $display_organization = true;
        } else {
          $display_organization = false;
        }
        $filter_out = false;


        // Alphabetical filtering support      
        // Run before datamanager schema loading for better performance
        if (   $this->_config->get("enable_alphabetical") 
            && isset($_REQUEST["net_nemein_organizations_alphabetical"]) 
            && $_REQUEST["net_nemein_organizations_alphabetical"] !== "") {
          if (strtolower(substr($organization->official,0,1)) != strtolower($_REQUEST["net_nemein_organizations_alphabetical"])) {
            $display_organization = false;
            $filter_out = true;
          } else {
            $display_organization = true;
          }
        }

        if (   $display_organization
            && ! $organization->is_object_visible_onsite())
        {
          $display_organization = false;
        }    
        
        if ($display_organization) {

          // Initialize datamanager schema for the group
          if (!$this->_layout->init($organization)) {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,"Layout class could not be initialized.");
          }

          global $view_link;
          global $view_name;
          global $view_openpsa;
          global $view;
          $view = $this->_layout->get_array();

          // Show group only once
          if (in_array($organization->guid(),$this->_shown_groups)) {
            $display_organization = false;
          }

          // Filtering support
          if ($this->_config->get("enable_filtering") && isset($_REQUEST["net_nemein_organizations_filter"])) {
            if (is_array($_REQUEST["net_nemein_organizations_filter"])) {
              foreach ($_REQUEST["net_nemein_organizations_filter"] as $field => $filter) {
                if ($filter !== '' && !stristr($view[$field],$filter)) {
                  $display_organization = false;
                  $filter_out = true;
                } else {
                  if (!$this->_config->get("filter_default") && !$filter_out) {
                    $display_organization = true;
                  }
                }
              }
            }
          }

          if ($display_organization) {

            $this->_shown_groups[] = $organization->guid();

            $view_link = $organization->guid();
            $view_openpsa = $this->_readOpenPSA($organization->id);

            if ($organization->official) {
              $view_name = $organization->official;
            } else {
              $view_name = $organization->name;
            }

            if ($this->_view === "random") {
              $_MIDCOM->cache->content->expires(0);
              midcom_show_style("show-random-item");

            } else {

              midcom_show_style("show-index-item");

            }

          }
        }
      }
    }

    function show() {

        debug_push($this->_debug_prefix . "show");

        if ($this->_view == "group") {

            global $view;
            global $view_name;
            global $view_openpsa;

            if ($this->_organization->official) {
                $view_name = $this->_organization->official;
            } else {
                $view_name = $this->_organization->name;
            }
            $view_openpsa = $this->_readOpenPSA($this->_organization->id);

            $view = $this->_layout->get_array();
            midcom_show_style("show-organization");

        } else {


            if (isset($this->_organization)) {

                global $view_topic;
                global $view_link;
                $view_topic = $this->_topic;

                if ($this->_view === "random") {

                    midcom_show_style("show-random-header");

                } else {

                    midcom_show_style("show-index-header");

                    if ($this->_config->get("enable_alphabetical")) {
                        global $view_group;
                        $view_group = $this->_group;
                        midcom_show_style("show-index-alphabetical");
                    }
            
                    if ($this->_preferred_group && $this->_preferred_group != "false") {
                        // Show preferred group first
                        $organization = new midcom_baseclasses_database_group($this->_preferred_group);
                        if ($organization) {
                            $this->_displayOrganization($organization->id);
                        }
                    }
                }


                if ($this->_view !== "random" || is_array($this->_random_groups)) {
                    $qb = midcom_baseclasses_database_group::new_query_builder();
                    $qb->add_constraint('owner', '=', $this->_group->id);
                    $organizations = $qb->execute();
                    
                    $total_groups = count($organizations);

                    foreach ($organizations as $organization)
                    {
                        if ($this->_view === "random") {
                            if (in_array($organization->id, $this->_random_groups)) {
                                $this->_displayOrganization($organization->id);
                            }
                        } else {
                            $this->_displayOrganization($organization->id);
                        }
                    }

                } else {
                    midcom_show_style("show-random-empty");
                }

                if ($this->_view === "random") {
                    midcom_show_style("show-random-footer");
                } else {
                    midcom_show_style("show-index-footer");
                }
            }
        }

        debug_pop();
        return true;

    }

    function draw_random_groups() {

        $allowed_random_organizations = array();
        $ret = array();
        $n = 0;

        $qb = midcom_baseclasses_database_group::new_query_builder();
        $qb->add_constraint('owner', '=', $this->_group->id);
        $organizations = $qb->execute();
        
        foreach ($organizations as $org)
        {
            if ($org->parameter("net.nemein.organizations","in_random")) 
            {
                $allowed_random_organizations[] = $org->id;
            }
        }

        // Make sure the amount for returned random groups is no more than there are allowed groups
        $n = count($allowed_random_organizations);
        if ($n < 1) 
        {
            return false;
        }
        
        if ($this->_random > $n) 
        {
            $this->_random = $n;
        }

        $random_organizations = array_rand($allowed_random_organizations,$this->_random);

        if (is_array($random_organizations)) {
            foreach ($random_organizations as $key) {
                $ret[] = $allowed_random_organizations[$key];
            }
        } else {
            $ret[] = $allowed_random_organizations[$random_organizations];
        }
        return $ret;
    }

    function get_metadata() {

        if ($this->_organization) {
            return array (
                MIDCOM_META_CREATOR => false,
                MIDCOM_META_EDITOR  => false,
                MIDCOM_META_CREATED => false,
                MIDCOM_META_EDITED  => time()
            );
        }
        else

            return false;
    }

} // viewer

?>
