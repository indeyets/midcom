<?php
/**
 * @package de.linkm.newsticker
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Newsticker AIS interface class
 *
 * @todo document
 *
 * @package de.linkm.newsticker
 */
class de_linkm_newsticker_admin
{

    var $_debug_prefix;

    var $_config;
    var $_config_dm;
    var $_topic; /* Data Topic */
    var $_config_topic; /* Config Topic */
    var $_view;

    var $_article;
    var $_attachment;
    var $_datamanager;

    var $_l10n;
    var $_l10n_midcom;

    var $errcode;
    var $errstr;

    var $_local_toolbar;
    var $_topic_toolbar;

    var $_schemadb_index;

    function de_linkm_newsticker_admin($topic, $config)
    {
        $this->_config = $config;
        $this->_config_dm = null;
        $this->_config_topic = $topic;
        $this->_view = "";

        $this->_article = false;
        $this->_attachment = false;
        $this->_datamanager = false;
        $this->_schemadb_index = null;

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("de.linkm.newsticker");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $this->_check_for_content_topic();
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;

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
        $object = new midcom_db_topic($guid);
        if (! $object)
        {
            debug_add("Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: "
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $GLOBALS["midcom"]->generate_error("Failed to open symlink content topic.");
        }

        /* Check topic validity */
        $root = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        if ($object->get_parameter('midcom', 'component') != 'de.linkm.newsticker')
        {
            debug_add("Content Topic is invalid, see LOG_INFO object dump", MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            debug_print_r("ROOT topic object was:", $root, MIDCOM_LOG_INFO);
            $GLOBALS["midcom"]->generate_error("Failed to open symlink content topic.");
        }

        $this->_topic = $object;
    }

    function can_handle($argc, $argv)
    {
        global $de_linkm_newsticker_layouts;
        debug_push_class(__CLASS__, __FUNCTION__);

        // Load Schema Database
        $path = $this->_config->get("schemadb");
        $data = midcom_get_snippet_content($path);
        eval("\$de_linkm_newsticker_layouts = Array ({$data}\n);");

        $view_layouts = array ();
        if (is_array($GLOBALS['de_linkm_newsticker_layouts']))
        {
            foreach ($GLOBALS['de_linkm_newsticker_layouts'] as $layout)
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
            case "edit":
            case "delete":
            case "create":
                return ($argc == 2);

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
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
            )
        ));

        // Add the new article links at the beginning
        // We need to reverse the array, as we are prepending, not appending
        $view_layouts = array_reverse($this->_schemadb_index, true);
        $hide_create = ($_MIDCOM->auth->can_do('midgard:create', $this->_topic) == false);
        foreach ($view_layouts as $name => $desc)
        {
            $text = sprintf($this->_l10n_midcom->get('create %s'), $desc);
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                MIDCOM_TOOLBAR_LABEL => $text,
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_HIDDEN => $hide_create
            ), 0);
        }

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

            case "edit":
                $result = $this->_init_edit($argv[1]);
                break;

            case "delete":
                $result = $this->_init_delete($argv[1]);
                break;

            case "create":
                $result = $this->_init_create($argv[1]);
                break;

            case "config":
                $result = $this->_init_config();
                break;

            default:
                $this->errstr("The newsticker does not support the operation {$argv[0]}");
                $result = false;
                break;
        }

        debug_pop();
        return $result;
    }

    function _generate_urlname($article = null)
    {
        if (! $article)
        {
            $article = $this->_article;
        }

        $updated = false;
        $tries = 0;
        $maxtries = 99;
        while(   ! $updated
              && $tries < $maxtries)
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
            $GLOBALS['midcom']->generate_error("Could not update the article's URL Name: " . mgd_errstr());
            // This will exit()
        }
        return $article;
    }

    function _init_view($id)
    {
        global $de_linkm_newsticker_layouts;

        $article = new midcom_baseclasses_database_article($id);
        if (! $article)
        {
            debug_add("Article {$id} could not be loaded: " . mgd_errstr(),
              MIDCOM_LOG_INFO);
            $this->errstr = "Article {$id} could not be loaded: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRNOTFOUND;
            return false;
        }
        $this->_article = $article;
        $GLOBALS['midcom_component_data']['de.linkm.newsticker']['active_leaf'] = $id;

        // Add the toolbar items
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "edit/{$id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:update', $this->_article) == false)
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "delete/{$id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:delete', $this->_article) == false)
        ));

        $this->_datamanager = new midcom_helper_datamanager($de_linkm_newsticker_layouts);

        if (! $this->_datamanager)
        {
            $this->errstr = "Could not create a datamanager instance, see debug level log.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add($this->errstr, MIDCOM_LOG_CRIT);
            return false;
        }

        if (! $this->_datamanager->init($this->_article))
        {
            $this->errstr = "Could not initialize the datamanager, see debug level log.";
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

        $_MIDCOM->auth->require_do('midgard:update', $this->_article);

        $this->_topic_toolbar->disable_item('config.html');
        foreach ($this->_schemadb_index as $name => $desc)
        {
            $this->_topic_toolbar->disable_item("create/{$name}.html");
        }
        $this->_local_toolbar->disable_item("edit/{$id}.html");
        $this->_local_toolbar->disable_item("delete/{$id}.html");
        $this->_local_toolbar->disable_view_page();

        switch ($this->_datamanager->process_form())
        {
            case MIDCOM_DATAMGR_EDITING:
                $this->_view = "edit";
                $GLOBALS['midcom_component_data']['de.linkm.newsticker']['active_leaf'] = $id;
                return true;

            case MIDCOM_DATAMGR_SAVED:
                if (   $this->_article->name == ''
                    && $this->_config->get('create_name_from_title'))
                {
                    // Empty URL name, regenerate it
                    $this->_article = $this->_generate_urlname($this->_article);
                }

                // Update the Index
                $indexer =& $GLOBALS['midcom']->get_service('indexer');
                $nav = new midcom_helper_nav();
                $node = $nav->get_node($this->_topic->id);

                $document = $indexer->new_document($this->_datamanager);
                $document->component = 'de.linkm.newsticker';
                $document->topic_guid = $this->_topic->guid;
                $document->topic_url = $node[MIDCOM_NAV_FULLURL];
                $indexer->index($document);

                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/{$id}.html");
                // This will exit()

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


    function _init_create ($schema)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("Entering creation mode with schema {$schema}.");

        $_MIDCOM->auth->require_do('midgard:create', $this->_topic);

        // Disable the View Page Link
        $this->_local_toolbar->disable_view_page();

        // Initialize sessioning and the datamanager
        $session = new midcom_service_session();
        $this->_datamanager = new midcom_helper_datamanager($GLOBALS['de_linkm_newsticker_layouts']);
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
            $this->_article = new midcom_baseclasses_database_article($id);
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
            $GLOBALS['midcom_component_data']['de.linkm.newsticker']['active_leaf'] = $this->_article->id;
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
                if (   $this->_article->name == ''
                    && $this->_config->get("create_name_from_title"))
                {
                    // Since this is the first editing round generate the URL name from title
                    // Don't touch the URL names after this to preserve links
                    // But do it only if no url name has been supplied.
                    $this->_article = $this->_generate_urlname($this->_article);
                }

                // index the article
                $indexer =& $GLOBALS['midcom']->get_service('indexer');
                $nav = new midcom_helper_nav();
                $node = $nav->get_node($this->_topic->id);

                $document = $indexer->new_document($this->_datamanager);
                $document->component = 'de.linkm.newsticker';
                $document->topic_guid = $this->_topic->guid;
                $document->topic_url = $node[MIDCOM_NAV_FULLURL];
                $indexer->index($document);

                // Redirect to view page.
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
                    $GLOBALS['midcom']->relocate('');
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
                    if (! $this->_article->delete())
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
                $this->errstr = 'The Datamanger failed to process the request, see the Debug Log for details (' . mgd_errstr() . ')';
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

    function _dm_create_callback(&$datamanager)
    {
        $result = Array
        (
            "success" => true,
            "storage" => null,
        );

        $midgard = $GLOBALS["midcom"]->get_midgard();
        $this->_article = new midcom_baseclasses_database_article();
        $this->_article->topic = $this->_topic->id;

        // Temporary workaround until MgdSchma/DBA is again compatible to the old API (empty names must now be unique)
        $this->_article->name = (string) time();

        $this->_article->author = $midgard->user;
        if (! $this->_article->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add ('Could not create article: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r('Article was:', $this->_article);
            debug_pop();
            return null;
        }
        $result["storage"] =& $this->_article;
        return $result;
    }

    function _init_delete($id)
    {
        if (!$this->_init_view($id))
        {
            return false;
        }

        $_MIDCOM->auth->require_do('midgard:delete', $this->_article);

        if (array_key_exists("de_linkm_newsticker_deleteok", $_REQUEST))
        {
            return $this->_delete_record($id);
            // This will redirect to the welcome page on success.
        }
        else
        {
            if (array_key_exists("de_linkm_newsticker_deletecancel", $_REQUEST))
            {
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/$id.html");
                // This will exit()
            }
            else
            {
                $GLOBALS['midcom_component_data']['de.linkm.newsticker']['active_leaf'] = $id;
                $this->_view = "deletecheck";
            }
        }
        return true;
    }

    function _init_config()
    {
        debug_push($this->_debug_prefix . "_init_config");

        // Verify permissions
        $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
        $_MIDCOM->auth->require_do('midcom:component_config', $this->_topic);

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
        global $de_linkm_newsticker_layouts;
        global $view_layouts;

        $view_layouts = $this->_schemadb_index;

        midcom_show_style("admin_welcome");
    }


    function _show_view()
    {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager;
        $view_id = $this->_article->id;

        midcom_show_style("admin_view");
    }


    function _show_edit()
    {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager;
        $view_id = $this->_article->id;

        midcom_show_style("admin_edit");
    }


    function _show_create()
    {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager;
        $view_id = $this->_article->id;

        midcom_show_style("admin_create");
    }


    function _show_deletecheck()
    {
        global $view;
        global $view_id;
        global $view_descriptions;

        $view_descriptions = $this->_datamanager->get_fieldnames();
        $view = $this->_datamanager->get_array();
        $view_id = $this->_article->id;

        midcom_show_style("admin_deletecheck");
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
        $article = new midcom_baseclasses_database_article($id);

        // Backup the GUID first, this is required to update the index later.
        $guid = $article->guid;

        if (! $article->delete())
        {
            $this->errstr = "Could not delete Article $id: "  . mgd_errstr();
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


    function get_metadata()
    {
        if ($this->_article)
        {
            return array
            (
                MIDCOM_META_CREATOR => $this->_article->creator,
                MIDCOM_META_EDITOR  => $this->_article->revisor,
                MIDCOM_META_CREATED => $this->_article->created,
                MIDCOM_META_EDITED  => $this->_article->revised
            );
        }
        else
        {
            return false;
        }
    }


    function _prepare_config_dm ()
    {
        // Set a global so that the schema gets internationalized
        $GLOBALS["view_l10n"] = $this->_l10n;
        $GLOBALS["view_l10n_midcom"] = $this->_l10n_midcom;
        $schemadbs = $this->_config->get("schemadbs");
        $GLOBALS["view_schemadbs"] = array_merge
        (
            Array("" => $this->_l10n_midcom->get("default setting")),
            $schemadbs
        );
        $this->_config_dm = new midcom_helper_datamanager("file:/de/linkm/newsticker/config/schemadb_config.inc");

        if ($this->_config_dm == false)
        {
            debug_add("Failed to instantinate configuration datamanager.", MIDCOM_LOG_CRIT);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Failed to instantinate configuration datamanager.");
        }

        $midgard = mgd_get_midgard();

        if (! $_MIDCOM->auth->admin)
        {
            // Hack Schema to make the symlink field invisible for non-power-users.
            $this->_config_dm->_layoutdb["config"]["fields"]["symlink_topic"]["hidden"] = true;
        }

        if (! $this->_config_dm->init($this->_config_topic))
        {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
            debug_print_r("Topic object we tried was:", $this->_config_topic);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, "Failed to initialize configuration datamanager.");
        }
    }

}

function de_linkm_newsticker_symlink_topic_list()
{
    $midgard = mgd_get_midgard();
    $sg = $midgard->sitegroup;

    $param = Array (
        "result" => Array("" => $GLOBALS["view_l10n"]->get("symlink_topic disabled")),
        "stack" => Array(),
        "last_level" => 0,
        "last_topic" => null,
        "glue" => " > ",
        "sg" => $sg,
    );

    debug_push(__FUNCTION__);
    debug_add("Start building symlink_topic list", $param);
    // debug_print_r("Initial parameters:", $param);
    mgd_walk_topic_tree("de_linkm_newsticker_symlink_topic_list_loop", 0, 99, &$param, true, "name");
    debug_pop();

    return $param["result"];
}

function de_linkm_newsticker_symlink_topic_list_loop ($topicid, $level, &$param)
{
    if ($topicid == 0)
    {
        // debug_add("Topic ID is 0, skipping this one, it is the lists root.");
        return;
    }
    $topic = new midcom_db_topic($topicid);

    // Check if we can use this topic.
    if (! $topic)
    {
        debug_add("Skipping topic {$topicid}, it is not readable (ACL midgard:read?).");
        return;
    }
    if (   $param["sg"] != 0
        && $topic->sitegroup != $param["sg"])
    {
        debug_add("Skipping topic {$topicid}, it is in SG {$topic->sitegroup}, which is wrong.");
        return;
    }

    debug_add("Processing Topic {$topicid} in level {$level}");
    if ($level > $param["last_level"])
    {
        if ($param["last_level"] != 0)
        {
            array_push($param["stack"], $param["last_topic"]);
        }
        $param["last_level"] = $level;
    }
    else if ($level < $param["last_level"])
    {
        for ($i = $param["last_level"]; $i > $level; $i--)
        {
            array_pop($param["stack"]);
        }
        $param["last_level"] = $level;
    }

    // Check this only here to keep the "tree" stable.
    if ($topic->get_parameter('midcom', 'component') != 'de.linkm.newsticker')
    {
        debug_add("Skipping topic {$topicid}, wrong component.");
        return;
    }
    if ($topic->get_parameter('de.linkm.newsticker', 'symlink_topic'))
    {
        debug_add("Skipping topic {$topicid}, it is a symlink topic, this is prohibited to prevent loops.", MIDCOM_LOG_INFO);
        return;
    }

    $guid = $topic->guid;
    if ($topic->extra != "")
    {
        $topicname = substr($topic->extra, 0, 30);
    }
    else
    {
        $topicname = substr($topic->name, 0, 30);
    }
    $param["last_topic"] = $topicname;

    if ($level > 1)
    {
        $param["result"][$guid] = implode($param["glue"], $param["stack"]) . $param["glue"] . $topicname;
    }
    else
    {
        $param["result"][$guid] = $topicname;
    }
}


?>
