<?php

class net_nemein_bookmarks_admin 
{
    var $_debug_prefix;
    var $_config;
    var $_config_dm;
    var $_topic; /* Data Topic */
    var $_config_topic; /* Config Topic */
    var $_view;
    var $_article;
    var $_attachment;
    var $_layout;
    var $_l10n;
    var $_l10n_midcom;
    var $errcode;
    var $errstr;
    var $_local_toolbar;
    var $_topic_toolbar;    
    var $_tag;
    var $schemadb_index;

    function net_nemein_bookmarks_admin($topic, $config) 
    {
        $this->_debug_prefix = "net.nemein.bookmarks admin::";
        $this->_config = $config;
        $this->_config_dm = null;
        $this->_config_topic = $topic;
        $this->_view = "";
        $this->_article = false;
        $this->_attachment = false;
        $this->_layout = false;
        $i18n =& $_MIDCOM->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.bookmarks");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $this->_check_for_content_topic();
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;
        $this->_schemadb_index = null;
    }
    
    function _check_for_content_topic() 
    {
        $guid = $this->_config->get("symlink_topic");
        if (is_null($guid)) 
        {
            /* No Symlink Topic set */
            $this->_topic = $this->_config_topic;
            return;
        }
        $object = mgd_get_object_by_guid($guid);
        if (! $object || $object->__table__ != "topic") 
        {
            debug_add("Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: " 
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $_MIDCOM->generate_error("Failed to open symlink content topic.");
        }
        
        /* Check topic validity */
        $root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        if ($object->parameter("midcom", "component") != "net.nemein.bookmarks") 
        {
            debug_add("Content Topic is invalid, see LOG_INFO object dump", MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            debug_print_r("ROOT topic object was:", $root, MIDCOM_LOG_INFO);
            $_MIDCOM->generate_error("Failed to open symlink content topic.");
        }
        
        $this->_topic = $object;
    }
    
    function can_handle($argc, $argv) 
    {
        global $net_nemein_bookmarks_layouts;
        debug_push ($this->_debug_prefix . "can_handle");

        // Load Schema Database
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $path = $this->_config->get("schemadb");
        $data = midcom_get_snippet_content($path);
        eval("\$net_nemein_bookmarks_layouts = Array ( " . $data . ");");
        
        $view_layouts = array ();
        if (is_array($GLOBALS['net_nemein_bookmarks_layouts']))
        {
            foreach ($GLOBALS['net_nemein_bookmarks_layouts'] as $layout)
            {
                $view_layouts[$layout["name"]] = $layout["description"];
            }
        }
        $this->_schemadb_index = $view_layouts;
        
        if ($argc == 0)
        {
            return true;
        }

        switch ($argv[0]) 
        {
            case "config":
                return ($argc == 1);
                
            case "view":
                // Fall-through
                
            case "list":
                // Fall-through
                
            case "edit":
                // Fall-through
                
            case "delete":
                return ($argc == 2);
                
            case "create":
                return ($argc < 3);
                  
            default:
                return false;
        }
    }


    function handle($argc, $argv) 
    {
        debug_push($this->_debug_prefix . "handle");
        
        /* Add the topic configuration item */
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
                /* Add the new article links at the beginning*/
        // We need to reverse the array, as we are prepending, not appending
        $view_layouts = array_reverse($this->_schemadb_index, true);
        foreach ($view_layouts as $name => $desc) 
        { 
            $text = sprintf($this->_l10n_midcom->get('create %s'), $desc);
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                MIDCOM_TOOLBAR_LABEL => $text,
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ), 0);
        }

        /* Add the new article link at the beginning*/
/*
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create article'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ), 0);
*/                
        if ($argc == 0) 
        {
            $this->_view = "welcome";
            debug_add("viewport = welcome");
            debug_pop();
            return true;
        }

        switch ($argv[0]) 
        {
            case "view":
                $result = $this->_init_view($argv[1]);
                break;
                
            case "list":
                $result = $this->_init_list($argv[1]);
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
                
            case "config":
                $result = $this->_init_config();
                break;
                
            default:
                $result = false;
                break;
        }
        debug_pop();
        return $result;
    }

    function _generate_urlname($article=null) 
    {
        if (!$article) 
        {
            $article = $this->_article;
        }

        $updated = false;
        $tries = 0;
        $maxtries = 99;
        while(!$updated && $tries < $maxtries) 
        {
            $article->name = midcom_generate_urlname_from_string($article->title);
            if ($tries > 0) 
            {
                // Append an integer if articles with same name exist
                $article->name .= sprintf("-%03d", $tries);
            }
            $updated = $article->update();
            $tries++;
        }
        if (! $updated) 
        {
            debug_print_r("Failed to update the Article with a new URL, last article state:", $article);
            $_MIDCOM->generate_error("Could not update the article's URL Name: " . mgd_errstr());
            // This will exit()
        }
        return $article;      
    }
    function _init_list($name) 
    {
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        $this->_tag = $name;
        $GLOBALS['midcom_component_data']['net.nemein.bookmarks']['active_leaf'] = $name;
        return true;
    }
    
    function _init_view($id) 
    {
        global $net_nemein_bookmarks_layouts;

        $article = mgd_get_article($id);
        if (!$article) 
        {
            debug_add("Article $id could not be loaded: " . mgd_errstr(),
              MIDCOM_LOG_INFO);
            $this->errstr = "Article $id could not be loaded: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            return false;
        }
        $this->_article = $article;
        $GLOBALS["net_nemein_bookmarks_nap_activeid"] = $id;
        
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
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        $this->_layout = new midcom_helper_datamanager($net_nemein_bookmarks_layouts);
          
        if (!$this->_layout) 
        {
            $this->errstr = "Could not create layout, see Debug Log";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_CRIT);
            return false;
        }

        if (!$this->_layout->init($this->_article)) 
        {
            $this->errstr = "Could not initialize layout, see Debug Log";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_CRIT);
            return false;
        }
        
        $this->_view = "view";
        
        return true;
    }

    function _init_edit($id) 
    {
        if (!$this->_init_view($id))
        {
            return false;
        }
        
        $this->_topic_toolbar->disable_item('config.html');
        foreach ($this->_schemadb_index as $name => $desc) 
		{ 
		    $this->_topic_toolbar->disable_item("create/{$name}.html");
		}
        $this->_local_toolbar->disable_item("edit/{$id}.html");
        $this->_local_toolbar->disable_item("delete/{$id}.html");
        $this->_local_toolbar->disable_view_page();
        
        switch ($this->_layout->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                $this->_view = "edit";
                $GLOBALS['midcom_component_data']['net.nemein.bookmarks']['active_leaf'] = $id;
                //$GLOBALS["net_nemein_bookmarks_nap_activeid"] = $id;
                return true;

            case MIDCOM_DATAMGR_SAVED:
                if ($this->_article->name == "" && $this->_config->get("create_name_from_title")) 
                {
                    // Empty URL name, regenerate it
                    $this->_article = $this->_generate_urlname($this->_article);
                }
                // Update the Index 
                //$indexer =& $_MIDCOM->get_service('indexer');
                //$indexer->index($this->_datamanager);

	            // Redirect to view page.
	            $_MIDCOM->relocate("view/$id.html");
	            // This will exit()
                
                
	            // Redirect to view page.
	            //$_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
	            //     . "view/$id.html");
	            // This will exit()
                
            case MIDCOM_DATAMGR_CANCELLED:
            	// Redirect to view page.
	            $_MIDCOM->relocate("view/$id.html");
	            // This will exit()
       			/*
	            // Redirect to view page.
	            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
	                 . "view/$id.html");
	            // This will exit()
                */
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
    }

    function _init_create ($schema) {
        debug_push($this->_debug_prefix . "_init_create");
        
        debug_add("Entering creation mode with schema {$schema}.");
        
        /* Disable the View Page Link */
        $this->_local_toolbar->disable_view_page();

        // Initialize sessioning and the datamanager        
        $session = new midcom_service_session();
        $this->_datamanager = new midcom_helper_datamanager($GLOBALS['net_nemein_bookmarks_layouts']);
        if (! $this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
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
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failied to initialize the datamanager in creation mode for schema '{$schema}'.");
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
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Could not load created article, error was: ' . mgd_errstr());
                // This will exit
            }
            if (! $this->_datamanager->init($this->_article))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failied to initialize the datamanager to article ID '{$id}'.");
                // This will exit
            }
            $GLOBALS['midcom_component_data']['net.nemein.bookmarks']['active_leaf'] = $this->_article->id;
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
                debug_add('Datamanager has saved, relocating to view.');
                $session->remove('admin_create_id');
	            if ($this->_article->name == '' && $this->_config->get("create_name_from_title")) 
	            {
	                // Since this is the first editing round generate the URL name from title
	                // Don't touch the URL names after this to preserve links
	                // But do it only if no url name has been supplied.
	                $this->_article = $this->_generate_urlname($this->_article);
	            }
                
                // index the article 
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                
	            // Redirect to view page.
                $_MIDCOM->relocate("view/{$this->_article->id}.html");
            // This will exit
           
            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                if (! $create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.';
                    debug_pop();
                    return false;
                } else {
                    debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                    $_MIDCOM->relocate('');
                    // This will exit

                }
            
            case MIDCOM_DATAMGR_CANCELLED:
                if ($create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                    debug_pop();
                    return false;
                } else {
                    debug_add('Cancel with a temporary object, deleting it and redirecting to the welcome screen.');
                    if (! mgd_delete_extensions($this->_article) || ! $this->_article->delete())
                    {
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                            'Failed to remove temporary article or its dependants.');
                        // This will exit
                    }
                    $session->remove('admin_create_id');
                    $_MIDCOM->relocate('');
                    // This will exit
                }
            
            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanager failed to process the request, see the Debug Log for details';
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
	        
	    $midgard = $_MIDCOM->get_midgard();
	    $this->_article = mgd_get_article();
	    $this->_article->topic = $this->_topic->id;
	    $this->_article->author = $midgard->user;
	    $id = $this->_article->create();
	    if (! $id) {
	        debug_add ("net.nemein.bookmarks::admin::_dm_create_callback Could not create article: " . mgd_errstr());
	        return null;
	    }
	    $this->_article = mgd_get_article($id);
	    $result["storage"] =& $this->_article;
	    return $result;
	}

    function _init_delete($id) 
    {
        if (!$this->_init_view($id))
        {
            return false;
        }
        
        if (array_key_exists("net_nemein_bookmarks_deleteok", $_REQUEST)) 
        {
            return $this->_delete_record($id);
            // This will redirect to the welcome page on success.
        }
        else
        { 
            if (array_key_exists("net_nemein_bookmarks_deletecancel", $_REQUEST)) 
            {
                // Redirect to view page.
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                     . "view/$id.html");
                // This will exit()
            } 
            else 
            {
                $GLOBALS["net_nemein_bookmarks_nap_activeid"] = $id;
                $this->_view = "deletecheck";
            }
        }
        return true;
    }
    
    function _init_config() 
    {
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
        
        switch ($this->_config_dm->process_form()) 
        {
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

    function show() 
    {
        $GLOBALS["view_l10n"] = $this->_l10n;
        $GLOBALS["view_l10n_midcom"] = $this->_l10n_midcom;
        $GLOBALS["view_topic"] =& $this->_topic;
        $GLOBALS["view_config_topic"] =& $this->_config_topic;
        $GLOBALS["view_title"] = $this->_config_topic->extra;
        $GLOBALS["view_msg"] = "";
        
        eval("\$result = \$this->_show_$this->_view();");
        return $result;
    }

    function _show_welcome() 
    {
        global $net_nemein_bookmarks_layouts;
        global $view_layouts;
        global $view;
        global $config;
        global $unification_errors_bookmarks;
        global $unification_errors_delicious;
        
        $unification = false;
        $config = $this->_config->get("delicius");
        $not_in_bookmarks = array();
        $not_in_delicious = array();
        $bookmarks = array();
        $unification_errors_bookmarks = array();
        $unification_errors_delicious = array();
      
        // Unification with http://del.icio.us
        if (array_key_exists ("net_nemein_bookmarks_unify_submit", $_REQUEST)) 
        {
            $delicious = net_nemein_bookmarks_delicius_getlist(
                $_REQUEST['net_nemein_bookmarks_unify_username'], 
                $_REQUEST['net_nemein_bookmarks_unify_password']
            );
            if (is_array($delicious))
            {
                $articles = mgd_list_topic_articles($this->_topic->id);
                if ($articles->N > 0) 
                {
                    while ($articles->fetch())
                    {
                        if (empty($articles->content))
                        {
                            $articles->content = "system:unfiled";
                        }
                        $bookmarks[] = array(
                            "href" => $articles->url,
                            "description" => $articles->title,
                            "extended" => $articles->abstract,
                            "tag" => $articles->content
                        );
                    }
                }
                if (count($bookmarks) > 0 || count($delicious) > 0) 
                {
                    $not_in_delicious = $bookmarks;
                    $not_in_bookmarks = $delicious;
                   
                    if (count($bookmarks) > 0 && count($delicious) > 0)
                    {          
                        foreach ($delicious as $key => $bookmark_delicious)
                        {
                            foreach ($bookmarks as $bookmark_bookmarks)
                            {
                                if ($bookmark_delicious['href'] == $bookmark_bookmarks['href'])
                                {
                                    unset($not_in_bookmarks[$key]);
                                }
                            }
                        }
                        reset($delicious);
                        reset($bookmarks);
                        foreach ($bookmarks as $key => $bookmark_bookmarks)
                        {
                            foreach ($delicious as $bookmark_delicious)
                            {
                            
                                if ($bookmark_bookmarks['href'] == $bookmark_delicious['href'])
                                {
                                    unset($not_in_delicious[$key]);
                                }
                            }
                        }
                    }
                    elseif ($articles->N == 0 && count($delicious) > 0)
                    {
                        $not_in_bookmarks = $delicious;
                    }
                    elseif ($articles->N > 0 && count($delicious) == 0)
                    {
                        $not_in_delicious = $bookmarks;                       
                    }
                    else 
                    {
                        echo "There are no new bookmarks";
                    }
                  
                    // Add these to net.nemein.bookmarks
                    if (count($not_in_bookmarks) > 0)
                    {
                        foreach ($not_in_bookmarks as $bookmark)
                        {
                            $pieces = explode("T", $bookmark['time']);
                            $date = explode("-", $pieces[0]);
                            $time = explode(":", $pieces[1]);
                            $time[2] = str_replace("Z", "", $time);
                            $created = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
                            
                            usleep(1000000);
                            $article = mgd_get_article();
                            if ($article && !empty($bookmark['href']))
                            {
                                $article->topic = $this->_topic->id;
                                $article->url = $bookmark['href'];
                                $article->title = $bookmark['description'];
                                $article->content = implode(" ", $bookmark['tag']);
                                $article->author = $_MIDGARD['user'];
                                $article->name = time();
                                if (isset($bookmark['extended']))
                                {
                                    $article->abstract = $bookmark['extended'];
                                }
                                $id = $article->create();
                                
                                if (!$id)
                                {
                                    $this->errstr = "Failed: Bookmarks(" . $bookmarks['href'] . "): " . mgd_errstr();
                                    $this->errcode = MIDCOM_ERRFORBIDDEN;
                                    $unification_errors_bookmarks[] = $this->errstr; 
                                } 
                                else 
                                {                                	
                                    debug_add ("Article $id created");
                                }                                
                                
                                $update = mgd_update_article_created($id, $created);                                
                                                                
                                if (!$update)
                                {
                                    $this->errstr = "Failed: Created(" . $bookmarks['href'] . "): " . mgd_errstr();
                                    $this->errcode = MIDCOM_ERRFORBIDDEN; 
                                } 
                                else 
                                {
                                	// Invalidating the cache
                                	$_MIDCOM->cache->invalidate_all();

                                    debug_add ("Created $id updated");
                                }
                            }
                            else
                            {
                                $this->errstr = "Could not create Article: " . mgd_errstr() . "<br/>";
                                $this->errcode = MIDCOM_ERRFORBIDDEN;
                                echo $this->errcode . " " . $this->errstr;
                            }
                        }
                    }
                                                
                    // Add these to http://del.icio.us
                    if (count($not_in_delicious) > 0) 
                    {        
                        foreach ($not_in_delicious as $bookmark)
                        {
                            if (!empty($bookmark['href']) && !empty($bookmark['description']))
                            {
                                $put = net_nemein_bookmarks_delicius_put(
                                    $bookmark['href'], $bookmark['description'], $bookmark['extended'], 
                                    $bookmark['tag'], $_REQUEST['net_nemein_bookmarks_unify_username'],
                                    $_REQUEST['net_nemein_bookmarks_unify_password']
                                 );
                                if (!$put)
                                {
                                    $this->errstr = "Failed: Delicious(" . $bookmarks['href'] . "): " . mgd_errstr();
                                    $this->errcode = MIDCOM_ERRFORBIDDEN;
                                    $unification_errors_delicious[] = $this->errstr; 
                                }
                                else
                                {
                                    debug_add ("Bookmarks created");
                                }
                            }
                        }
                    }
                } 
            }
            else
            {
                echo "Failed to connect http://del.icio.us, possible reason: wrong username or password";
            }
        }
        
        $view_layouts = array ();
        if (is_array($net_nemein_bookmarks_layouts))
        {
            foreach ($net_nemein_bookmarks_layouts as $layout)
            {
                $view_layouts[$layout["name"]] = $layout["description"];
            }
        }

        midcom_show_style("admin_welcome");
        $view = mgd_list_topic_articles($this->_topic->id);
        if ($view->N == 0) 
        {
            echo "No bookmarks";
        } 
    }

    function _show_view() 
    {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view = $this->_layout;
        $view_id = $this->_article->id;

        midcom_show_style("admin_view");
    }
    
    function _show_() 
    {
        global $view;
        global $view_tag;
        
        midcom_show_style("admin_view_list_header");
        
        $view_tag = $this->_tag;
        $tag_bookmarks = net_nemein_bookmarks_helper_list_bookmarks_by_tag($this->_topic->id, $this->_tag);
        foreach($tag_bookmarks as $view)
        {
            if ($_MIDGARD['user'] == $view->author || $_MIDGARD['admin'])
            {
                midcom_show_style("admin_view_list");
            }
        }        
    }
   
    function _show_edit() 
    {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view = $this->_layout;
        $view_id = $this->_article->id;

        midcom_show_style("admin_edit");
    }

    function _show_deletecheck() 
    {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_layout->get_fieldnames();
        $view = $this->_layout->get_array();
        $view_id = $this->_article->id;

        midcom_show_style("admin_deletecheck");
    }

    function _show_create() {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager;
        $view_id = $this->_article->id;

        midcom_show_style("admin_create");
    }

    function _show_config() 
    {
        global $view_config;
        global $view_topic;
        $view_config = $this->_config_dm;
        $view_topic = $this->_topic;
        midcom_show_style("admin_config");
    }

    function _delete_record($id) 
    {
        $article = mgd_get_article($id);
        
        // Backup the GUID first, this is required to update the index later.
        $guid = $article->guid();
    
        if (!mgd_delete_extensions($article)) 
        {
            $this->errstr = "Could not delete Article $id extensions: " 
              . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add ($this->_errstr, MIDCOM_LOG_ERROR);
            return false;
        }

        if (!$article->delete()) 
        {
            $this->errstr = "Could not delete Article $id: "  . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add ($this->_errstr, MIDCOM_LOG_ERROR);
            return false;
        }
        
        
        // Update the index
        $indexer =& $_MIDCOM->get_service('indexer');
        $indexer->delete($guid);
        
        // Invalidate the cache modules
        $_MIDCOM->cache->invalidate($guid);
        
        // Redirect to welcome page.
        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        // This will exit()
    }


    function get_metadata() 
    {
        if ($this->_article) 
        {
            return array (
                MIDCOM_META_CREATOR => $this->_article->creator,
                MIDCOM_META_EDITOR  => $this->_article->revisor,
                MIDCOM_META_CREATED => $this->_article->created,
                MIDCOM_META_EDITED  => $this->_article->revised
            );
        } else
            return false;
        }


    function get_current_leaf() 
    {
        if ($this->_article)
        {
            return $this->_article->id;
        }
        else
        {
            return false;
        }
    }
  
    function _prepare_config_dm () 
    {
        /* Set a global so that the schema gets internationalized */
        $GLOBALS["view_l10n"] = $this->_l10n;
        $GLOBALS["view_l10n_midcom"] = $this->_l10n_midcom;
        $schemadbs = $this->_config->get("schemadbs");
        $GLOBALS["view_schemadbs"] = array_merge(
            Array("" => $this->_l10n_midcom->get("default setting")), 
            $schemadbs);
        $this->_config_dm = new midcom_helper_datamanager("file:/net/nemein/bookmarks/config/schemadb_config.dat");
        
        if ($this->_config_dm == false) 
        {
            debug_add("Failed to instantinate configuration datamanager.", MIDCOM_LOG_CRIT);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 
                "Failed to instantinate configuration datamanager.");
        }
        
        $person = mgd_get_person($_MIDGARD['user']);
        
        if (!$_MIDGARD['admin'] && $person->parameter("Interface", "Power_User") == "NO") 
        {
            /* Hack Schema to make the symlink field invisible for non-power-users. */
            $this->_config_dm->_layoutdb["config"]["fields"]["symlink_topic"]["hidden"] = true;
        }
        
        if (! $this->_config_dm->init($this->_config_topic)) 
        {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
            debug_print_r("Topic object we tried was:", $this->_config_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 
                "Failed to initialize configuration datamanager.");
        }
    }
} // admin

?>
