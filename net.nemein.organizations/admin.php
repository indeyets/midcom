<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Group viewer AIS interface class.
 * 
 * @package net.nemein.organizations
 */
class net_nemein_organizations_admin {

    var $_debug_prefix;

    var $_config;
    var $_topic;
    var $_view;

    var $_group;
    var $_attachment;
    var $_layout;
    var $_organization;
    var $_user;
    var $_preferred_group;

    var $errcode;
    var $errstr;
    
    var $_l10n;
    var $_l10n_midcom;
    
    var $_local_toolbar;
    var $_topic_toolbar;

    function net_nemein_organizations_admin($topic, $config) {
        global $midgard;

        $this->_debug_prefix = "net.nemein.organizations admin::";

        $this->_config = $config;
        $this->_topic = $topic;
        $this->_view = "";

        $this->_organization = false;
        $this->_user = $midgard->user;
        $this->_attachment = false;
        $this->_layout = false;
        $this->_group = new midcom_baseclasses_database_group($this->_config->get("group"));
        $this->_preferred_group = $this->_config->get("preferred_group");

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $GLOBALS['midcom_component_data']['net.nemein.organizations']['active_leaf'] = false;

        $i18n =& $_MIDCOM->i18n;
        $this->_l10n = $_MIDCOM->i18n->get_l10n('net.nemein.organizations');
        $this->_l10n_midcom = $_MIDCOM->i18n->get_l10n('midcom');
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;
    }


    function can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "prepare");
        
        if ($argc > 2)
            return false;
        
        if ($argc == 0)
            return true;
        
        switch ($argv[0]) {
            case "view":
            case "edit":
                return true;
            default:
                return false;
        }
        
    }


    function handle($argc, $argv) {

        debug_push($this->_debug_prefix . "handle");

        /* Add the topic configuration item */
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
	        MIDCOM_TOOLBAR_HIDDEN => 
	        (
	               ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
	            || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
	        )
        ));
        
        if ($argc == 0) {
            if (isset($_REQUEST["net_nemein_organizations_submit"])) 
            {
              $this->_init_config();
            }
            $this->_view = "welcome";
            debug_add ("viewport = welcome");
            debug_pop ();
            return true;
        }

        switch ($argv[0]) {

            case "edit":
                $result = $this->_init_edit($argv[1]);
                break;

            case "view":
                $result = $this->_init_view($argv[1]);
                break;

            default:
                $result = false;
                break;
        }

        debug_pop();
        return $result;
    }

    function _init_config() {
      // Verify permissions
      $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
      $_MIDCOM->auth->require_do('midcom:component_config', $this->_topic);
      
      global $_REQUEST;
      if (is_array($_REQUEST["net_nemein_organizations_config"])) {
        $new_config = $_REQUEST["net_nemein_organizations_config"];
        foreach ($new_config as $key => $value) {
          $this->_topic->parameter("net.nemein.organizations",$key,$value);
        }
        reset($new_config);
        $this->_config->store($new_config);
        $this->_group = new midcom_baseclasses_database_group($this->_config->get("group"));
        $this->_preferred_group = $this->_config->get("preferred_group");
      }
    }

    function _init_view ($id) {    
      $organization = new midcom_baseclasses_database_group($id);
      if (!$organization) {    
        debug_add("group $id could not be loaded: " . mgd_errstr(),    
          MIDCOM_LOG_INFO);    
        $this->errstr = "group $id could not be loaded: " . mgd_errstr();    
        $this->errcode = MIDCOM_ERRNOTFOUND;    
        return false;    
      }    
      $this->_organization = $organization;    
      $GLOBALS['midcom_component_data']['net.nemein.organizations']['active_leaf'] = $id;    
   
      /* Add the toolbar items */
      $this->_local_toolbar->add_item(Array(
          MIDCOM_TOOLBAR_URL => "edit/{$this->_organization->id}.html",
          MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
          MIDCOM_TOOLBAR_HELPTEXT => null,
          MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
          MIDCOM_TOOLBAR_ENABLED => true,
		  MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:update', $this->_organization) == false)
      ));
      
      $this->_layout = new midcom_helper_datamanager($this->_config->get('schemadb'));    
   
      if (! $this->_layout) {    
        $this->errstr = "Could not create layout, see Debug Log";    
        $this->errcode = MIDCOM_ERRCRIT;    
        debug_add($this->errstr, MIDCOM_LOG_CRIT);    
        return false;    
      }    
   
      if (! $this->_layout->init($this->_organization)) {    
        $this->errstr = "Could not initialize layout, see Debug Log";    
        $this->errcode = MIDCOM_ERRCRIT;    
        debug_add($this->errstr, MIDCOM_LOG_CRIT);    
        return false;    
      }    
   
      $this->_view = "view";    
   
      return true;    
    }  


    function _init_edit($id) {

        if (!$this->_init_view($id))
            return false;
        
        $_MIDCOM->auth->require_do('midgard:update', $this->_organization);
        
        $this->_topic_toolbar->disable_item('');
        $this->_local_toolbar->disable_item("edit/{$id}.html");
        $this->_local_toolbar->disable_view_page();
            
        switch ($this->_layout->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                $this->_view = "edit";
                $GLOBALS['midcom_component_data']['net.nemein.organizations']['active_leaf'] = $id;
                return true;

            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                $GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "view/{$id}.html");
                // This will exit()
                
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
    }

    function show() {

        // get l10n libraries
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.organizations");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

        global $view_topic;
        $GLOBALS["view_topic"] = $this->_topic;
  
        eval("\$result = \$this->_show_$this->_view();");
        return $result;
    }


    function _show_welcome() {

        global $midcom;
        global $title;
        global $view_current_group;
        global $view_organizations;
        global $view_preferred_group;

        $view_members = array();
        $view_preferred_group= $this->_preferred_group;

        if (is_object($this->_group)) {
          $view_current_group = $this->_group->guid();
          
          $organizations = mgd_list_groups($this->_group->id);
          if ($organizations) {
            while ($organizations->fetch()) {
              $organization = mgd_get_group($organizations->id);
              if ($organization->official) {
                $view_organizations[$organization->guid()] = $organization->official;
              } else {
                $view_organizations[$organization->guid()] = $organization->name;
              }
            }
          }
        }

        midcom_show_style("admin-welcome");
    }


    function _show_view() {

        global $midcom;
        global $view;
        global $view_id;
        global $view_title;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view = $this->_layout;
        $view_id = $this->_organization->id;

        // Show only if we have permissions
        if (mgd_is_group_owner($view_id)) {
            midcom_show_style("admin-view");
        } else {
            midcom_show_style("admin-noperms");
        }
    }


    function _show_edit (){

        global $midcom;
        global $view;
        global $view_id;
        global $view_title;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view_title = "group";
        $view = $this->_layout;
        $view_id = $this->_organization->id;

        // Show only if we have permissions
        if (mgd_is_group_owner($view_id)) {
            midcom_show_style("admin-edit");
        } else {
            midcom_show_style("admin-noperms");
        }
    }

    function get_metadata() {

        if ($this->_organization) {
            return array (
                MIDCOM_META_CREATOR => 0,
                MIDCOM_META_EDITOR  => 0,
                MIDCOM_META_CREATED => 0,
                MIDCOM_META_EDITED  => 0
            );
        }
        else
            return false;
    }

} // admin

?>