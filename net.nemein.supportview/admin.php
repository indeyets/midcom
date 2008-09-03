<?php
/**
 * @package net.nemein.supportview
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * OpenPSA Supporview AIS interface class.
 * 
 * @package net.nemein.supportview
 */
class net_nemein_supportview_admin {

    var $_debug_prefix;

    var $_config;
    var $_config_dm;
    var $_topic;
    var $errcode;
    var $errstr;
    var $form_prefix;
    // ...
    var $_l10n;
    var $_l10n_midcom;
    var $_local_toolbar;
    var $_topic_toolbar;
    
    function __construct($topic, $config) {

        $this->_debug_prefix = "net.nemein.supportview admin::";
        
        $this->_config = $config;
        $this->_config_dm = null;
        $this->_topic = $topic;
        
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        
        $this->form_prefix = "net_nemein_supportview_";
        
        $i18n =& $_MIDCOM->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.supportview");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;
    }
    

    function can_handle($argc, $argv) {

        debug_push($this->_debug_prefix . "can_handle");

        // see if we can handle this request
        if ($argc == 0) {
            return true;
        }
        switch ($argv[0]) {
        case "config":
            return ($argc == 1);
        default:
            return false;
        }
    }
    
    
    function handle($argc, $argv) {
        
        global $_REQUEST;
        
        debug_push($this->_debug_prefix . "handle");
        
        /* Add the topic configuration item */
        $this->_topic_toolbar->add_item(Array(
                                              MIDCOM_TOOLBAR_URL => 'config/',
                                              MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                                              MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                                              MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                                              MIDCOM_TOOLBAR_ENABLED => true
                                              ));
        
        // handle args, parse the url, save data from forms, prepare output
        
        if ($argc == 0) {
            $this->_view = "welcome";
            debug_add ("viewport = welcome");
            debug_pop ();
            return true;
        }
        
        switch ($argv[0]) {
            
        case "config":  return $this->_init_config();
        
        default:
            $result = false;
        break;
        
        }
 
        debug_pop();
        return $result;
        
    }


    function show() {

        global $midcom;
        global $title;
        global $view_config;
        global $view_topic;
        $view_topic = $this->_topic;
        
        debug_push($this->_debug_prefix . "show");
        
        // get l10n libraries
        $i18n =& $_MIDCOM->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.nemein.supportview");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

        if ($this->_view == "config") {

            $view_config = $this->_config_dm;
            $view_topic = $this->_topic;

            midcom_show_style("admin-config");
        
        } else {

            $view_config = $this->_config;
            $view_topic = $this->_topic;
    
            midcom_show_style("admin-welcome");
            
        }
        
        debug_pop();
        return true;
    }

    function _init_config() {
        debug_push($this->_debug_prefix . "_init_config");
        
        $this->_prepare_config_dm();
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        switch ($this->_config_dm->process_form()) {
            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_CANCELLED:
                // Do nothing here, the datamanager will invalidate the cache.
                // Apart from that, let the user edit the configuration as long
                // as he likes.
                break;

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }
        
        $this->_view = "config";
        debug_pop();
        return true;
    }

    function _show_config() {
        global $view_config;
        global $view_topic;
        $view_config = $this->_config_dm;
        $view_topic = $this->_topic;
        midcom_show_style("admin_config");
    }

    function _prepare_config_dm () {
        /* Set a global so that the schema gets internationalized */
        $GLOBALS["view_l10n"] = $this->_l10n;
        $GLOBALS["view_l10n_midcom"] = $this->_l10n_midcom;
        $schemadbs = $this->_config->get("schemadbs");
        $GLOBALS["view_schemadbs"] = array_merge(
            Array("" => $this->_l10n_midcom->get("default setting")), 
            $schemadbs);
        $this->_config_dm = new midcom_helper_datamanager("file:/net/nemein/supportview/config/schemadb_config.inc");
        
        if ($this->_config_dm == false) {
            debug_add("Failed to instantiate configuration datamanager.", MIDCOM_LOG_CRIT);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 
                                               "Failed to instantiate configuration datamanager.");
        }
        
        $midgard = mgd_get_midgard();
        $person = mgd_get_person($midgard->user);
        
        if (! $this->_config_dm->init($this->_topic)) {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
            debug_print_r("Topic object we tried was:", $this->_config_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 
                                               "Failed to initialize configuration datamanager.");
        }
    }
    
    function get_metadata() {
        /*        
        if ($this->_release) {
            return array (
                          MIDCOM_META_CREATOR => $this->_release->creator,
                          MIDCOM_META_EDITOR  => $this->_release->revisor,
                          MIDCOM_META_CREATED => $this->_release->created,
                          MIDCOM_META_EDITED  => $this->_release->revised
                          );
        } else {
            return false;
        }
        */
    }
    
} // admin

?>