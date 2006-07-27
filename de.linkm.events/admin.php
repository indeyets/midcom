<?php 
/**
 * @package de.linkm.events
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Events AIS interface class
 * 
 * @todo document
 * 
 * @package de.linkm.events
 */
class de_linkm_events_admin {

    var $errcode;
    var $errstr;

    var $_topic;
    var $_config;
    var $_view;
    var $_article;
    var $_datamanager;

    var $_l10n;
    var $_l10n_midcom;
    var $_local_toolbar;
    var $_topic_toolbar;
    
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     * 
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;    

    function de_linkm_events_admin($topic, $config) {
        $this->_config = $config;
        $this->_topic = $topic;
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $this->_view = "";
        $this->_article = null;
        $this->_datamanager = null;
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("de.linkm.events");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_content_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;
        
        if ($this->_config->get('schemadb') == 'file:/de/linkm/events/config/schemadb_default.dat')
        {
            // Hotfix for a bad config interface
            $this->_config->store(Array('schemadb' => 'file:/de/linkm/events/config/schemadb_default.inc'), false);
            
            // Try to overwrite the original config key (the call fails silently on missing permissons
            $this->_content_topic->parameter('de.linkm.events', 'schemadb', 'file:/de/linkm/events/config/schemadb_default.inc');
        }
        $this->_determine_content_topic();
    }

    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     * 
     * @access protected
     */
    function _determine_content_topic() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid)) 
        {
            // No symlink topic
            // Workaround, we should talk to an DBA object automatically here in fact. 
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }
        
        $this->_content_topic = new midcom_db_topic($guid);

        // Validate topic.
                
        if (! $this->_content_topic) 
        {
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: ' 
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->generate_error('Failed to open symlink content topic.');
            // This will exit.
        }
        
        if ($this->_content_topic->get_parameter('midcom', 'component') != 'de.linkm.events')
        {
            debug_print_r('Retrieved topic was:', $this->_content_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Symlink content topic is invalid, see the debug level log for details.');
            // This will exit.
        }
        
        debug_pop();
    }

    function get_metadata() {
        if ($this->_article) 
        {
            return array (
                MIDCOM_META_CREATOR => $this->_article->creator,
                MIDCOM_META_EDITOR  => $this->_article->revisor,
                MIDCOM_META_CREATED => $this->_article->created,
                MIDCOM_META_EDITED  => $this->_article->revised
            );
        }
        return false;
    }


    function can_handle($argc, $argv) {
        if ($argc == 0)
        {
            return true;
        }
        
        switch($argv[0]) {
            case "create":
	        case "update_prefs":
	            return ($argc == 1);
                
            case "view":
            case "edit":
            case "delete":
                return ($argc == 2);
        }
        
        return false;
    }


    function handle($argc, $argv) {
        debug_push("de.linkm.events::handle");
        
        /* Add the topic configuration item */
        $this->_content_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        /* Add the new article link at the beginning*/
        $this->_content_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'create.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create event'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ), 0);
        
        if ($argc == 0) 
        {
            $this->_view = "welcome";
            debug_add("viewport = welcome");
            debug_pop();
            return true;
        }
        
        switch ($argv[0]) {
            case "view":
                $result = $this->_init_view($argv[1]);
                break;

            case "edit":
                $result = $this->_init_edit($argv[1]);
                break;

            case "delete":
                $result = $this->_init_delete($argv[1]);
                break;

            case "create":
                $result = $this->_init_create(($argc==2) ? $argv[1] : null);
                break;

            case "update_prefs":
                $result = $this->_update_prefs();
                break;

            default:
                $result = false;
                break;
        }

        debug_pop();
        return $result;
    }


    function _generate_urlname ($article = null)
    {
        if (!$article) 
        {
            $article = $this->_article;
        }

        $updated = false;
        $tries = 0;
        $maxtries = 99;
        while (!$updated && $tries < $maxtries) 
        {
            if ($this->_config->get("create_name_from_title"))
            {
                if ($article->name === midcom_generate_urlname_from_string($article->name))
                {
                    // Don't modify articles that already have URL-safe names
                    return $article;
                } else
                {
                    $article->name = midcom_generate_urlname_from_string($article->title);
                }
            }
            else
            {
                $article->name = date("Y-m-d");
            }
            if ($tries > 0) 
            {
                // Append an integer if articles with same name exist
                $article->name .= sprintf("-%02d", $tries);
            }
            $updated = $article->update();
            $tries++;
        }
        if (! $updated) 
        {
            debug_print_r("Failed to update the Article with a new URL, last article state:", $article);
            $GLOBALS['midcom']->generate_error("Could not update the article's URL Name: " . mgd_errstr());
            // This will exit()
        }
        return $article;
    }


    function _update_prefs() {
        debug_push("de.linkm.events::_update_prefs");
        if (!isset ($_REQUEST)) {
            $this->errstr = "No request data found.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        if (array_key_exists ("fpref_submit", $_REQUEST)) {            
            if (! $this->_content_topic->parameter("de.linkm.events","title",$_REQUEST["fpref_title"]) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter Title: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
            if (! $this->_content_topic->parameter("de.linkm.events","enable_details",$_REQUEST["fpref_enable_details"] ) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter enable_details: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
            if (! $this->_content_topic->parameter("de.linkm.events","index_list_old",$_REQUEST["fpref_index_list_old"] ) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter index_list_old: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
            if (! $this->_content_topic->parameter("de.linkm.events","schemadb",$_REQUEST["fpref_schemadb"]) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter schemadb: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
            if (! $this->_content_topic->parameter("de.linkm.events","default_schema",$_REQUEST["fpref_default_schema"]) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter default_schema: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
            if (! $this->_content_topic->parameter("de.linkm.events","sort_order",$_REQUEST["fpref_sort_order"]) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter sort_order: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
            if (! $this->_content_topic->parameter("de.linkm.events","index_add_seconds_after_endtime",$_REQUEST["fpref_index_add_seconds_after_endtime"]) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter index_add_seconds_after_endtime: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
            if (! $this->_content_topic->parameter("de.linkm.events","create_name_from_title",$_REQUEST["fpref_create_name_from_title"] ) ) {
                $error = mgd_errstr();
                if ($error != "Object does not exist") {
                    $this->errstr = "Could not save parameter create_name_from_title: " . $error;
                    $this->errcode = MIDCOM_ERRAUTH;
                    debug_add($this->errstr, MIDCOM_LOG_INFO);
                    debug_pop();
                    return false;
                }
            }
        }
        
        $GLOBALS["midcom"]->relocate('');
        // This will exit
    }

    function _init_view($id) {
        $article = mgd_get_article($id);
        if (!$article) {
            debug_add("Article $id could not be loaded: " . mgd_errstr(), MIDCOM_LOG_INFO);
            $this->errstr = "Article $id could not be loaded: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            return false;
        }

        if ($article->extra2 != "") {
          // Support both the old YYYY-MM-DD format and the new date widget
          if (!is_numeric($article->extra2)) {
            $article->extra2 = @strtotime($article->extra2);
          }
        }

        $GLOBALS['midcom_component_data']['de.linkm.events']['active_leaf'] = $article->id;
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "edit/{$id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "delete/{$id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        $this->_datamanager = new midcom_helper_datamanager ($this->_config->get("schemadb"));
        if (! ($this->_datamanager && $this->_datamanager->init($article))) {
            $this->errstr = "Could not create layout, see Debug Log";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOGCRIT);
            return false;
        }
        $GLOBALS["view_layout"] = $this->_datamanager;
        
        $this->_article = $article;
        $this->_view = "view";
        return true;
    }


    function _init_edit ($id) {
        if (!$this->_init_view($id))
        {
            return false;
        }
        
        $this->_content_topic_toolbar->disable_item('create.html');
        //$this->_content_topic_toolbar->disable_item('');
        $this->_local_toolbar->disable_item("edit/{$id}.html");
        $this->_local_toolbar->disable_item("delete/{$id}.html");
        $this->_local_toolbar->disable_view_page();
        
        switch ($this->_datamanager->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
                $this->_view = "edit";
                return true;

            case MIDCOM_DATAMGR_SAVED:
                if ($this->_article->name === "")
                {
                    // Empty URL name, regenerate it and refresh the DM
                    $this->_article = $this->_generate_urlname($this->_article);
                    $this->_datamanager->init($this->_article);
                }
                // Reindex the article 
                $this->_index();

            case MIDCOM_DATAMGR_CANCELLED:
	            // Redirect to view page.
	            $GLOBALS['midcom']->relocate("view/{$id}.html");
	            // This will exit()
                
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
    }

    function _init_create($id = null) {
        debug_push("de.linkm.events::_init_create");
        
        $schema = $this->_config->get("default_schema");
        debug_add("Entering creation mode with schema {$schema}.");
        
        /* Disable the View Page Link */
        $this->_local_toolbar->disable_view_page();

        // Initialize sessioning and the datamanager        
        $session = new midcom_service_session();
        $this->_datamanager = new midcom_helper_datamanager($this->_config->get('schemadb'));
        if (! $this->_datamanager)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a datamanager instance, see debug log for details.');
            // This will exit.
        }
                
        // Check wether we already have a content object, or not.
        // Depending on this we'll set up a datamanaer in either create
        // or standard-editing mode. 
        // The NAP active leaf will only be set if we already have a
        // content object.
        if (! $session->exists('admin_create_id'))
        {
            debug_add('We do not currently have a content object, entering creation-start mode.');
            $this->_article = null;
            if (! $this->_datamanager->init_creation_mode($schema, $this))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "Failied to initialize the datamanger in creation mode for schema '{$schema}'.");
                // This will exit
            }
            $create = true;
        }
        else
        {
            $id = $session->get('admin_create_id');
            debug_add("We have id {$id}, loading object.");
            $this->_article = mgd_get_article($id);
            if (! $this->_article)
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    'Could not load created article, error was: ' . mgd_errstr());
                // This will exit
            }
            if (! $this->_datamanager->init($this->_article))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "Failied to initialize the datamanger to article ID '{$id}'.");
                // This will exit
            }
            $GLOBALS['midcom_component_data']['de.linkm.events']['active_leaf'] = $this->_article->id;
            $create = false;
        }
        
        switch ($this->_datamanager->process_form()) 
        {
            case MIDCOM_DATAMGR_CREATING:
                if (! $create) 
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMANAGER_CREATING unknown for non-creation mode.';
                    debug_pop();
                    return false;
                } 
                else 
                {
                    debug_add('First call within creation mode');
                    $this->_view = 'create';
                    break;
                }
            
            case MIDCOM_DATAMGR_EDITING:
                if ($create) 
                {
                    $id = $this->_article->id;
                    debug_add("First time submit, the DM has created an object, adding ID {$id} to session data");
                    $session->set('admin_create_id', $id);
                } 
                else 
                {
                    debug_add('Subsequent submit, we already have an id in the session space.');
                }
                $this->_view = 'create';
                break;
            
            case MIDCOM_DATAMGR_SAVED:
                debug_add('Datamanger has saved, relocating to view.');
                $session->remove('admin_create_id');
                if ($this->_article->name === "")
                {
                    // Empty URL name, regenerate it and refresh the DM
                    $this->_article = $this->_generate_urlname($this->_article);
                    $this->_datamanager->init($this->_article);
                }
                
                $this->_index();
                                
                $GLOBALS['midcom']->relocate("view/{$this->_article->id}.html");
                // This will exit
            
            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                if (! $create) 
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.';
                    debug_pop();
                    return false;
                } 
                else 
                {
                    debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                    $GLOBALS['midcom']->relocate("");
                    // This will exit

                }
            
            case MIDCOM_DATAMGR_CANCELLED:
                if ($create) 
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                    debug_pop();
                    return false;
                } 
                else 
                {
                    debug_add('Cancel with a temporary object, deleting it and redirecting to the welcome screen.');
                    if (! mgd_delete_extensions($this->_article) || ! $this->_article->delete())
                    {
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                            'Failed to remove temporary article or its dependants.');
                        // This will exit
                    }
                    $session->remove('admin_create_id');
                    $GLOBALS['midcom']->relocate('');
                    // This will exit
                }
            
            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanger failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;
            
        }
        
        debug_pop();
        return true;
    }

    function _dm_create_callback(&$datamanager) {
        $result = Array (
            "success" => true,
            "storage" => null,
        );
        
        $midgard = $GLOBALS["midcom"]->get_midgard();
        $this->_article = mgd_get_article();
        $this->_article->topic = $this->_content_topic->id;
        $this->_article->author = $midgard->user;
        $id = $this->_article->create();
        if (! $id) 
        {
            debug_add ("de.linkm.events::admin::_dm_create_callback Could not create article: " . mgd_errstr());
            return null;
        }
        $this->_article = mgd_get_article($id);
        $result["storage"] =& $this->_article;
        return $result;
    }

    function _init_delete ($id) {
        if (! $this->_init_view($id))
        {
            return false;
        }
        
        if (array_key_exists("de_linkm_events_deleteok", $_REQUEST)) 
        {
            return $this->_delete_record($id);
            // This will redirect to the welcome page on success.
        } 
        else if (array_key_exists("de_linkm_events_deletecancel", $_REQUEST)) 
        {
            // Redirect to view page.
            $GLOBALS['midcom']->relocate("view/{$id}.html");
            // This will exit()
        } 
        else 
        {
            $this->_view = "deletecheck";
        }
        return true;
    }


    function show () {
        global $view_title;

        // get l10n libraries
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $GLOBALS["view_l10n"] =& $i18n->get_l10n("de.linkm.events");
        $GLOBALS["view_l10n_midcom"] =& $i18n->get_l10n("midcom");

        $view_title = $this->_content_topic->extra;
        eval("\$result = \$this->_show_$this->_view();");
        return $result;
    }


    function _show_welcome() {
        global $midcom;
        global $title;
        global $view_config;
        $view_config = $this->_config;
        $title = htmlspecialchars($this->_config->get("title"));
        midcom_show_style("admin_welcome");
    }


    function _show_view() {
        global $midcom;
        global $view;
        global $view_id;
        global $view_descriptions;
        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager->get_array();
        $view_id = $this->_article->id;
        midcom_show_style("admin_view");
    }


    function _show_edit() {
        global $midcom;
        global $view;
        global $view_id;
        global $view_descriptions;
        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager;
        $view_id = $this->_article->id;
        midcom_show_style("admin_edit");
    }


    function _show_create() {
        global $midcom;
        global $view;
        global $view_id;
        global $view_descriptions;
        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager;
        $view_id = $this->_article->id;
        midcom_show_style("admin_create");
    }


    function _show_deletecheck() {
        global $midcom;
        global $view;
        global $view_id;
        global $view_descriptions;
        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager->get_array();
        $view_id = $this->_article->id;
        midcom_show_style("admin_deletecheck");
    }


    function _delete_record($id) {
        $article = mgd_get_article ($id);
        
        // Backup the GUID first, this is required to update the index later.
        $guid = $article->guid();
        
        if (!mgd_delete_extensions($article)) 
        {
            $this->errstr = "Could not delete Article $id extensions: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add ($this->_errstr, MIDCOM_LOG_ERROR);
            return false;
        }
        
        if (!$article->delete()) 
        {
            $this->errstr = "Could not delete Article $id: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add ($this->_errstr, MIDCOM_LOG_ERROR);
            return false;
        }
        
        // Update the index
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $indexer->delete($guid);
        
        // Invalidate the cache modules
        $GLOBALS['midcom']->cache->invalidate($guid);
        
        // Redirect to welcome page.
        $GLOBALS['midcom']->relocate('');
        // This will exit()
    }
    
    /**
     * Small indexing helper, replaces abstract with the date range.
     * 
     * Requires an up-to-date datamanager instance.
     */
    function _index()
    {
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $document = $indexer->new_document($this->_datamanager);
        
        $document->abstract = "{$this->_datamanager->data['startdate']['local_strfulldate']} - {$this->_datamanager->data['date']['local_strfulldate']}";
        
        $indexer->index($document);
    }
}

?>