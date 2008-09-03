<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * AIS Stub
 *
 * @package net.nemein.personnel
 */
class net_nemein_personnel_admin extends midcom_baseclasses_components_request_admin
{
}

    /*
    var $_debug_prefix;

    var $_config;
    var $_topic;
    var $_view;

    var $_person;
    var $_attachment;
    var $_layout;
    var $_group;
    var $_user;
    var $_preferred_person;
    var $_owner;

    var $errcode;
    var $errstr;

    var $_l10n;
    var $_l10n_midcom;

    var $_local_toolbar;
    var $_topic_toolbar;

    function __construct($topic, $config) {
        global $midgard;

        $this->_debug_prefix = "net.nemein.personnel admin::";

        $this->_config = $config;
        $this->_topic = $topic;
        $this->_view = "";

        $this->_person = false;
        $this->_user = $midgard->user;
        $this->_owner = false;
        $this->_attachment = false;
        $this->_layout = false;
        $this->_group = new midcom_baseclasses_database_group($this->_config->get("group"));
        $this->_preferred_person = $this->_config->get("preferred_person");

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $GLOBALS['midcom_component_data']['net.nemein.personnel']['active_leaf'] = false;

        $i18n =& $_MIDCOM->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.personnel");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");

        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;
    }


    function can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "can_handle");

        // Schema Database
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $path = $this->_config->get("schemadb");
        $data = midcom_get_snippet_content($path);
        eval("\$GLOBALS[\"net_nemein_personnel_layouts\"] = Array ( {$data} );");

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

        // Add the topic configuration item
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
            if (isset($_REQUEST["net_nemein_personnel_submit"])) {
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
      if (is_array($_REQUEST["net_nemein_personnel_config"])) {
        $new_config = $_REQUEST["net_nemein_personnel_config"];
        foreach ($new_config as $key => $value) {
          $this->_topic->parameter("net.nemein.personnel",$key,$value);
        }
        reset($new_config);
        $this->_config->store($new_config);
        $this->_group = mgd_get_object_by_guid($this->_config->get("group"));
        $this->_preferred_person = $this->_config->get("preferred_person");

        $_MIDCOM->cache->invalidate($this->_topic->guid());
      }
    }

    function _init_view ($id) {

      global $net_nemein_personnel_layouts;

      $person = new midcom_baseclasses_database_person($id);
      if (!$person) {
        debug_add("Person $id could not be loaded: " . mgd_errstr(),
          MIDCOM_LOG_INFO);
        $this->errstr = "Person $id could not be loaded: " . mgd_errstr();
        $this->errcode = MIDCOM_ERRNOTFOUND;
        return false;
      }
      $this->_person = $person;

      // Add the toolbar items
      $this->_local_toolbar->add_item(Array(
          MIDCOM_TOOLBAR_URL => "edit/{$id}.html",
          MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
          MIDCOM_TOOLBAR_HELPTEXT => null,
          MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
          MIDCOM_TOOLBAR_ENABLED => false,
          MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:update', $this->_person) == false)
      ));

      $GLOBALS['midcom_component_data']['net.nemein.personnel']['active_leaf'] = $id;

      $this->_layout = new midcom_helper_datamanager($net_nemein_personnel_layouts);

      if (! $this->_layout) {
        $this->errstr = "Could not create layout, see Debug Log";
        $this->errcode = MIDCOM_ERRCRIT;
        debug_add($this->errstr, MIDCOM_LOG_CRIT);
        return false;
      }

      if (! $this->_layout->init($this->_person)) {
        $this->errstr = "Could not initialize layout, see Debug Log";
        $this->errcode = MIDCOM_ERRCRIT;
        debug_add($this->errstr, MIDCOM_LOG_CRIT);
        return false;
      }

      // Determine whether user can edit the person record
      if (mgd_is_person_owner($this->_person->id))
      {
          $this->_owner = true;
          $this->_local_toolbar->enable_item("edit/{$id}.html");
      }

      $this->_view = "view";

      return true;
    }


    function _init_edit($id) {

        if (!$this->_init_view($id)) {
            return false;
        }

        $this->_topic_toolbar->disable_item('');
        $this->_local_toolbar->disable_item("edit/{$id}.html");
        $this->_local_toolbar->disable_view_page();

        // Go to datamanager's form processing loop only if permissions are correct
        // This is to prevent unauthorized locking
        if ($this->_owner) {
          switch ($this->_layout->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
                $this->_view = "edit";
                $GLOBALS['midcom_component_data']['net.nemein.personnel']['active_leaf'] = $id;
                return true;

            case MIDCOM_DATAMGR_SAVED:
                mgd_update_public($id,true,true,true,true,true);
                // Fall-Through

            case MIDCOM_DATAMGR_CANCELLED:
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "view/{$id}.html");
                // This will exit()

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
          }
        }
    }

    function show() {

        // get l10n libraries
        $i18n =& $_MIDCOM->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.personnel");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");
        global $view_topic;
        $view_topic = $this->_topic;

        eval("\$result = \$this->_show_$this->_view();");
        return $result;
    }


    function _show_welcome() {

        global $midcom;
        global $title;
        global $view_current_group;
        global $view_members;
        global $view_preferred_person;

        $view_members = array();
        $view_preferred_person= $this->_preferred_person;

        if (is_object($this->_group)) {
          $view_current_group = $this->_group->guid();

          $members = mgd_list_members($this->_group->id);
          if ($members) {
            while ($members->fetch()) {
              $person = mgd_get_person($members->uid);
              $view_members[$person->guid()] = $person->rname;
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
        $view_id = $this->_person->id;

        // Show only if we have permissions
        if ($this->_owner) {
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
        $view_title = "Person";
        $view = $this->_layout;
        $view_id = $this->_person->id;

        // Show only if we have permissions
        if ($this->_owner) {
            midcom_show_style("admin-edit");
        } else {
            midcom_show_style("admin-noperms");
        }
    }

    function get_metadata() {

        if ($this->_person) {
            return array (
                MIDCOM_META_CREATOR => $this->_person->creator,
                MIDCOM_META_EDITOR  => $this->_person->revisor,
                MIDCOM_META_CREATED => $this->_person->created,
                MIDCOM_META_EDITED  => $this->_person->revised
            );
        }
        else
            return false;
    }
}
*/

?>