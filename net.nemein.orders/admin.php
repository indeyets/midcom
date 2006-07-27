<?php
/**
 * @package net.nemein.orders
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Orders AIS interface class.
 *
 * @package net.nemein.orders
 */
class net_nemein_orders_admin {

    var $_debug_prefix;

    var $_prefix;
    var $_config;
    var $_topic;
    var $_l10n;
    var $_l10n_midcom;
    var $_config_dm;
    var $_root_order_event;
    var $_mailing_company_group;
    var $_auth;
    var $_order_query_result;

    var $_product;
    var $_order;

    var $_mode;

    var $errcode;
    var $errstr;

    var $_local_toolbar;
    var $_topic_toolbar;

    function net_nemein_orders_admin($topic, $config) {
        $this->_debug_prefix = "net.nemein.orders.admin::";

        $this->_prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_config = $config;
        $this->_topic = $topic;
        $this->_config_dm = null;
        $this->_mode = "";
        $this->_root_order_event = null;
        $this->_auth = null;
        $this->_order_query_result = Array();

        $this->_product = null;
        $this->_order = null;

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.orders");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar =& $toolbars->top;
        $this->_local_toolbar =& $toolbars->bottom;

        $this->_check_root_event();
        $this->_prepare_config_dm();
        $this->_load_mailing_group();
    }


    function can_handle($argc, $argv)
    {
        /* Don't do this in the constructor, your references will get lost ... */
        $GLOBALS["midcom"]->set_custom_context_data("configuration", $this->_config);
        $GLOBALS["midcom"]->set_custom_context_data("configuration_dm", $this->_config_dm);
        $GLOBALS["midcom"]->set_custom_context_data("l10n", $this->_l10n);
        $GLOBALS["midcom"]->set_custom_context_data("l10n_midcom", $this->_l10n_midcom);
        $GLOBALS["midcom"]->set_custom_context_data("errstr", $this->errstr);
        $GLOBALS["midcom"]->set_custom_context_data("root_order_event", $this->_root_order_event);
        $GLOBALS["midcom"]->set_custom_context_data("mailing_company_group", $this->_mailing_company_group);
        $GLOBALS["midcom"]->set_custom_context_data("auth", $this->_auth);
        $GLOBALS["midcom"]->set_custom_context_data("product", $this->_product);
        $GLOBALS["midcom"]->set_custom_context_data("order", $this->_order);
        $GLOBALS["midcom"]->set_custom_context_data("view_toolbar", $this->_view_toolbar);

        $this->_auth = new net_nemein_orders_auth();

        if ($argc == 0)
        {
            return TRUE;
        }

        switch ($argv[0])
        {
            case "config":
            case "create_subcategory":
                return ($argc == 1); /* config.html */

            case "product":
                switch ($argv[1])
                {
                    case "create":
                    case "update_storage":
                        return ($argc == 2); /* product/(create|update_storage).html */

                    case "view":
                    case "edit":
                    case "delete":
                        return ($argc == 3); /* product/(edit|view|delete)/$id.html */
                }
                break;

            case "order":
                if ($argc < 2)
                {
                    return false;
                }
                switch ($argv[1])
                {
                    case "query_delivered":
                    case "show_undelivered":
                    case "maintain":
                        return ($argc == 2);

                    case "mark_as_sent":
                    case "mark_as_unsent":
                    case "mark_as_paid":
                    case "mark_as_unpaid":
                    case "edit":
                    case "edititems":
                    case "delete":
                        return ($argc == 3); /* order/(edit|delete|mark_as_sent)/$id.html */
                }
                break;
        }
        return false;
    }


    function handle($argc, $argv)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_prepare_toolbar();
        debug_pop();

        if ($argc == 0)
        {
            return $this->_init_welcome();
        }

        switch($argv[0])
        {
            case "config":
                $this->_auth->check_is_admin();
                return $this->_init_config();

            case "create_subcategory":
                $this->_auth->check_is_owner();
                $this->_auth->check_is_poweruser();
                return $this->_init_create_subcategory();

            case "product":
                $this->_auth->check_is_not_mailing_company();
                $this->_auth->check_is_owner();
                switch ($argv[1])
                {
                    case "create":
                        return $this->_init_product_create();
                    case "view":
                        return $this->_init_product_view($argv[2]);
                    case "edit":
                        return $this->_init_product_edit($argv[2]);
                    case "delete":
                        return $this->_init_product_delete($argv[2]);
                    case "update_storage":
                        return $this->_init_product_update_storage();
                }
                break;

            case "order":
                $this->_auth->check_is_owner();

                switch($argv[1])
                {
                    case "query_delivered":
                        $this->_auth->check_is_not_mailing_company();
                        return $this->_init_order_query_delivered();

                    case "maintain":
                        $this->_auth->check_is_admin();
                        return $this->_init_order_maintain();

                    case "show_undelivered":
                        return $this->_init_order_show_undelivered();

                    case "mark_as_sent":
                        return $this->_init_order_mark_as_sent($argv[2]);

                    case "mark_as_unsent":
                        $this->_auth->check_is_poweruser();
                        return $this->_init_order_mark_as_unsent($argv[2]);

                    case "mark_as_paid":
                        return $this->_init_order_mark_as_paid($argv[2]);

                    case "mark_as_unpaid":
                        return $this->_init_order_mark_as_unpaid($argv[2]);

                    case "edit":
                        $this->_auth->check_is_not_mailing_company();
                        return $this->_init_order_edit($argv[2]);

                    case "edititems":
                        $this->_auth->check_is_not_mailing_company();
                        return $this->_init_order_edititems($argv[2]);

                    case "delete":
                        $this->_auth->check_is_poweruser();
                        return $this->_init_order_delete($argv[2]);
                }
                break;
        }

        /* We should not get here */

        $this->errcode = MIDCOM_ERRCRIT;
        $this->errstr = "Method unknown";
        debug_print_r("Unknown Request to this component, this should not happen, argv was:", $argv);
        return false;

    }


    function show() {
        eval("\$this->_show_" . $this->_mode . "();");

        return TRUE;
    }

    function get_metadata() {
        return FALSE;
    }

    /******************* CODE INIT/SHOW HELPER METHODS ******************************/

    function _init_welcome()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_mode = "welcome";

        debug_pop();
        return true;
    }

    function _show_welcome()
    {
        midcom_show_style('admin-welcome');
    }


    function _init_config()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Check wether we are in a symlink config topic. If yes, redirect to
        // the true configuration topic.
        $config_topic = $this->_config->get('symlink_config_topic');
        if ($config_topic)
        {
            $GLOBALS['midcom']->relocate("{$GLOBALS['view_contentmgr']->viewdata['adminprefix']}{$config_topic->id}/data/config.html");
            // This will exit.
        }

        /* Configure toolbar */
        $this->_view_toolbar = Array();
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));


        switch ($this->_config_dm->process_form())
        {
            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                // We stay here whatever happens here, at least as long as
                // there is no fatal error.
                break;

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }

        $this->_mode = "config";

        debug_pop();
        return true;
    }

    function _show_config() {
        midcom_show_style("admin-config");
    }


    /**
     * Init handler for creating subcategories using the symlink config feature.
     * If no request data is present, a simple form is shown. Otherwise, the subtopic
     * gets created and configured and we are redirected to there afterwards.
     *
     * @return bool Indicating success.
     */
    function _init_create_subcategory()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (array_key_exists('f_submit', $_REQUEST))
        {
            if (! array_key_exists('f_name', $_REQUEST))
            {
                $this->errstr = "Request incomplete, f_name missing.";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            }

            $topic = mgd_get_topic();
            $topic->name = midcom_generate_urlname_from_string($_REQUEST['f_name']);
            $topic->extra = $_REQUEST['f_name'];
            $topic->owner = $this->_topic->owner;
            $topic->up = $this->_topic->id;
            $id = $topic->create();

            if (! $id)
            {
                debug_print_r("Failed to create the new topic, used this object for creation:", $topic);
                $this->errstr = "Failed to create new topic: " . mgd_errstr();
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            }

            // Determine the GUID of the configuration topic.
            $config_topic = $this->_config->get('symlink_config_topic');
            $config_topic_guid = (is_null($config_topic)) ? $this->_topic->guid() : $config_topic->guid();

            $topic = mgd_get_topic($id);
            $topic->parameter('midcom', 'component', 'net.nemein.orders');
            $topic->parameter('net.nemein.orders', 'symlink_config_topic', $config_topic_guid);

            $GLOBALS['midcom']->cache->invalidate_all();
            $GLOBALS['midcom']->relocate("{$GLOBALS['view_contentmgr']->viewdata['adminprefix']}{$topic->id}/data/");
            // This will exit.
        }

        $this->_mode = 'create_subcategory';

        debug_pop();
        return true;
    }

    /**
     * View handler for creating subcategories. It displays a form with the name
     * of the desired category.
     */
    function _show_create_subcategory()
    {
        midcom_show_style('admin-create-subcategory');
    }


    function _init_product_create() {
        debug_push_class(__CLASS__, __FUNCTION__);

        /* Disable the View Page Link */
        $this->_local_toolbar->disable_view_page();

        /* First, fire up sessioning to make the guid of the active article transient */
        $session = new midcom_service_session();

        /* Hide the toolbar while editing */
        $this->_view_toolbar = Array();

        if (! $session->exists("admin_create_product_guid")) {
            debug_add("We don't have a content object yet, setting fresh-created mode.");
            $this->_product = new net_nemein_orders_product();
            $create = true;
        } else {
            $guid = $session->get("admin_create_product_guid");
            debug_add("We do have the guid [$guid], trying to load.");
            $article = mgd_get_object_by_guid($guid);
            if (   ! $article
                || $article->__table__ != "article"
                || ! $article->topic = $this->_topic->id)
            {
                $this->errstr = "Invalid GUID, could not enter creation mode. See log for details.";
                $this->errcode = MIDCOM_ERRNOTFOUND;
                debug_add("GUID $guid invalid for creation mode, could be: guid nonexistant, no article or wrong topic.",
                          MIDCOM_LOG_WARN);
                debug_print_r("Retrieved object:", $article);
                $session->remove('admin_create_product_guid');
                return false;
            }
            $this->_product = new net_nemein_orders_product($article);
            $create = false;
        }
        if (! $this->_product) {
            $this->errstr = "Product could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Product could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }
        if ($create && ! $this->_product->init_create()) {
            $this->errstr = "Product could not be set into creation mode: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Product could not be into creation mode, this usually indicates a datamanager problem.");
            return false;
        }
        if (! $create) {
            $GLOBALS['midcom_component_data']['net.nemein.orders']['active_leaf'] = $this->_product->storage->id;
        }

        switch ($this->_product->datamanager->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                if (! $create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMANAGER_CREATING unknown for non-creation mode.";
                    debug_pop();
                    return false;
                } else {
                    /* First call, display from. */
                    debug_add("First call within creation mode");
                    $this->_mode = "product_dm_create";
                    break;
                }

            case MIDCOM_DATAMGR_EDITING:
                if ($create) {
                    $guid = $this->_product->storage->guid();
                    debug_add("First time submit, the DM has created an object, adding GUID [$guid] to session data");
                    $session->set("admin_create_product_guid", $guid);
                } else {
                    debug_add("Subsequent submit, we already have a guid in the session space.");
                }
                $this->_mode = "product_dm_create";
                break;

            case MIDCOM_DATAMGR_SAVED:
                debug_add("Datamanger has saved, relocating to view.");
                $session->remove("admin_create_product_guid");
                $this->_product->update_index();
                $this->_relocate("product/view/" . $this->_product->storage->id . ".html");
                /* This will exit() */

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                if (! $create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.";
                    debug_pop();
                    return false;
                } else {
                    debug_add("Cancel without anything being created, redirecting to the welcome screen.");
                    $this->_relocate("");
                    /* This will exit() */
                }

            case MIDCOM_DATAMGR_CANCELLED:
                if ($create) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.";
                    debug_pop();
                    return false;
                } else {
                    debug_add("Cancel with a temporary object, deleting it and redirecting to the welcome screen.");
                    $this->_product->delete();
                    $session->remove("admin_create_product_guid");
                    $this->_relocate("");
                    /* This will exit() */
                }

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add("The DM failed critically, see above.");
                $this->errstr = "The Datamanger failed to process the request, see the Debug Log for details";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                debug_pop();
                return false;

        }

        debug_pop();
        return true;
    }

    function _show_product_dm_create() {
        midcom_show_style("admin-heading-product-create");
        midcom_show_style("admin-dmform-product");
    }


    function _init_product_update_storage()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        if (array_key_exists('form_submit', $_REQUEST))
        {
            $articles = mgd_list_topic_articles($this->_topic->id, "score");
            if ($articles)
            {
                while ($articles->fetch())
                {
                    $field = "form_{$articles->id}";
                    if (! array_key_exists($field, $_REQUEST))
                    {
                        // Silently ignore missing fields, who knows what PHP might do with
                        // empty fields.
                        debug_add("Field {$field} not found in the request, skipping the article silently.", MIDCOM_LOG_INFO);
                        continue;
                    }

                    $this->_product = new net_nemein_orders_product(mgd_get_article($articles->id));

                    if (! is_numeric($_REQUEST[$field]))
                    {
                        $msg = sprintf($this->_l10n->get("failed to update article %s: invalid delivery amount %s"),
                            $this->_product->datamanager->data['code'], $_REQUEST[$field]);
                        $GLOBALS['view_contentmgr']->msg .= "{$msg}</br>\n";
                        debug_add("Failed to update article {$this->_product->datamanager->data['code']} ({$articles->id}), the input string '{$_REQUEST[$field]}' was invalid.", MIDCOM_LOG_INFO);
                        debug_add('Skipping article');
                        continue;
                    }

                    $amount = (integer) $_REQUEST[$field];
                    if ($amount < 0)
                    {
                        $msg = sprintf($this->_l10n->get("failed to update article %s: invalid delivery amount %s"),
                            $this->_product->datamanager->data['code'], $_REQUEST[$field]);
                        $GLOBALS['view_contentmgr']->msg .= "{$msg}</br>\n";
                        debug_add("Failed to update article {$this->_product->datamanager->data['code']} ({$articles->id}), the input string '{$_REQUEST[$field]}' was negative.", MIDCOM_LOG_INFO);
                        debug_add('Skipping article');
                        continue;
                    }
                    if ($amount == 0)
                    {
                        debug_add("No change for article {$this->_product->datamanager->data['code']} ({$articles->id}).");
                        continue;
                    }

                    // Update article
                    if (! $this->_product->add_to_storage($amount))
                    {
                        $GLOBALS['midcom']->generate_error(
                            MIDCOM_ERRCRIT,
                            "Failed to update article parameter {$this->_product->datamanager->data['code']} ({$articles->id}).");
                        // This will exit.
                    }
                }
            }
        }

        $this->_mode = 'product_update_storage';

        debug_pop();
        return true;
    }

    function _show_product_update_storage()
    {
        $articles = mgd_list_topic_articles($this->_topic->id, "score");

        midcom_show_style('admin-product-update-storage-begin');

        if ($articles)
        {
            while ($articles->fetch())
            {
                $this->_product = new net_nemein_orders_product(mgd_get_article($articles->id));
                midcom_show_style('admin-product-update-storage-item');
            }
        }

        midcom_show_style('admin-product-update-storage-end');
    }

    function _init_product_view ($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $article = mgd_get_article($id);
        if (   ! $article
            || $article->topic != $this->_topic->id)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "Product not found.";
            debug_add("The product could either not be loaded or was in the wrong topic.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved record:", $article);
            debug_pop();
            return false;
        }
        $this->_product = new net_nemein_orders_product($article);
        if (! $this->_product)
        {
            $this->errstr = "Product could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Product could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }

        $GLOBALS['midcom_component_data']['net.nemein.orders']['active_leaf'] = $article->id;
        $this->_mode = "product_view";

        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "product/edit/{$article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit product'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        if ($this->_auth->is_poweruser())
        {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "product/delete/{$article->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete product'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }

        debug_pop();
        return true;
    }

    function _show_product_view() {
        midcom_show_style("admin-heading-product-view");
        midcom_show_style("admin-dmview-product");
    }


    function _init_product_edit ($id) {
        debug_push_class(__CLASS__, __FUNCTION__);

        /* No toolbar while editing */
        $this->_local_toolbar->disable_view_page();

        $article = mgd_get_article($id);
        if (   ! $article
            || $article->topic != $this->_topic->id)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "Product not found.";
            debug_add("The product could either not be loaded or was in the wrong topic.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved record:", $article);
            debug_pop();
            return false;
        }
        $this->_product = new net_nemein_orders_product($article);
        if (! $this->_product) {
            $this->errstr = "Product could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Product could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }

        switch ($this->_product->datamanager->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
                debug_add("We are still editing.");
                break;

            case MIDCOM_DATAMGR_SAVED:
                $this->_product->update_index();
                /* Fall through */

            case MIDCOM_DATAMGR_CANCELLED:
                debug_add("Datamanger has saved or cancelled, return to view.");
                $this->_relocate("product/view/{$this->_product->storage->id}.html");
                /* This will exit() */

            case MIDCOM_DATAMGR_FAILED:
                debug_add("The DM failed critically, see above.");
                $this->errstr = "The Datamanger failed to process the request, see the Debug Log for details";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                debug_pop();
                return false;
        }

        $GLOBALS['midcom_component_data']['net.nemein.orders']['active_leaf'] = $article->id;
        $this->_mode = "product_edit";

        debug_pop();
        return true;
    }

    function _show_product_edit() {
        midcom_show_style("admin-heading-product-edit");
        midcom_show_style("admin-dmform-product");
    }


    function _init_product_delete ($id) {
        debug_push_class(__CLASS__, __FUNCTION__);

        $article = mgd_get_article($id);
        if (   ! $article
            || $article->topic != $this->_topic->id)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "Product not found.";
            debug_add("The product could either not be loaded or was in the wrong topic.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved record:", $article);
            debug_pop();
            return false;
        }
        $this->_product = new net_nemein_orders_product($article);
        if (! $this->_product) {
            $this->errstr = "Product could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Product could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }

        if (array_key_exists("ok", $_REQUEST) && $_REQUEST["ok"] == "yes") {
            $this->_product->delete();
            /* This will call generate_error on failure */
            $this->_relocate("");
            /* This will exit() */
        }

        $GLOBALS['midcom_component_data']['net.nemein.orders']['active_leaf'] = $article->id;
        $this->_mode = "product_delete";

        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "product/view/{$article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('view product'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "product/edit/{$article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit product'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        debug_pop();
        return true;
    }

    function _show_product_delete() {
        midcom_show_style("admin-heading-product-delete");
        midcom_show_style("admin-dmview-product");
    }

    function _init_order_show_undelivered()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        $this->_mode = "order_show_undelivered";

        debug_pop();
        return true;
    }

    function _show_order_show_undelivered() {
        /* Pending orders */
        $fact = new net_nemein_orders_order_factory();
        $pending = $fact->list_pending();

        midcom_show_style("admin-pending-orders-begin");
        if ($pending === false)
        {
            midcom_show_style("admin-pending-orders-item-none");
        }
        else
        {
            $this->_view_toolbar = new midcom_helper_toolbar('midcom_toolbar midcom_toolbar_in_content');
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as sent'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mark.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));

            $paid_index = false;
            $edit_index = 1;
            $edititems_index = 2;
            $delete_index = 3;

            if ($this->_config->get('enable_payment_management'))
            {
                $paid_index = 1;
                $edit_index = 2;
                $edititems_index = 3;
                $delete_index = 4;
            }

            if (! $this->_auth->is_mailing_company())
            {
                if ($paid_index !== false)
                {
                    $this->_view_toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => '',
                        MIDCOM_TOOLBAR_LABEL => '',
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => '',
                        MIDCOM_TOOLBAR_ENABLED => true
                    ));
                }
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order items'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_data-edit-table.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
            }
            if ($this->_auth->is_poweruser())
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete order'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
            }

            foreach ($pending as $guid => $order)
            {
                $this->_order = $order;

                $this->_view_toolbar->update_item_url(0, "order/mark_as_sent/{$order->storage->id}.html?return_to=order/show_undelivered.html");
                if (! $this->_auth->is_mailing_company() || $this->_auth->is_poweruser())
                {
                    if ($paid_index !== false)
                    {
                        if ($this->_order->data['paid']['timestamp'])
                        {
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_LABEL] = $this->_l10n->get('mark as unpaid');
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_ICON] = 'stock-icons/16x16/cancel.png';
                            $this->_view_toolbar->update_item_url($paid_index, "order/mark_as_unpaid/{$order->storage->id}.html?return_to=order/show_undelivered.html");
                        }
                        else
                        {
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_LABEL] = $this->_l10n->get('mark as paid');
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_ICON] = 'stock-icons/16x16/stock_mark.png';
                            $this->_view_toolbar->update_item_url($paid_index, "order/mark_as_paid/{$order->storage->id}.html?return_to=order/show_undelivered.html");
                        }
                    }
                    $this->_view_toolbar->update_item_url($edit_index, "order/edit/{$order->storage->id}.html");
                    $this->_view_toolbar->update_item_url($edititems_index, "order/edititems/{$order->storage->id}.html");
                }
                if ($this->_auth->is_poweruser())
                {
                    $this->_view_toolbar->update_item_url($delete_index, "order/delete/{$order->storage->id}.html");
                }

                midcom_show_style("admin-pending-orders-item");
            }
        }
        midcom_show_style("admin-pending-orders-end");

    }

    function _init_order_mark_as_sent($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_load_order($id)) {
            debug_pop();
            return false;
        }

        if (! $this->_order->mark_as_sent()) {
            debug_add("Mark as sent failed, aborting.");
            debug_pop();
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }

        if (array_key_exists('return_to', $_REQUEST))
        {
            $target = $_REQUEST['return_to'];
        }
        else
        {
            $target = "order/edit/{$id}.html";
        }
        $this->_relocate($target);
        /* This will exit */
    }


    function _init_order_mark_as_unsent($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_load_order($id)) {
            debug_pop();
            return false;
        }

        if (! $this->_order->mark_as_unsent()) {
            debug_add("Mark as unsent failed, aborting.");
            debug_pop();
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }

        if (array_key_exists('return_to', $_REQUEST))
        {
            $target = $_REQUEST['return_to'];
        }
        else
        {
            $target = "order/edit/{$id}.html";
        }
        debug_print_r("Relocating to {$target}, request was:", $_REQUEST);
        $this->_relocate($target);
        /* This will exit */
    }

    function _init_order_mark_as_paid($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_load_order($id)) {
            debug_pop();
            return false;
        }

        $this->_order->storage->parameter('midcom.helper.datamanager', 'data_paid', time());

        if (array_key_exists('return_to', $_REQUEST))
        {
            $target = $_REQUEST['return_to'];
        }
        else
        {
            $target = "order/edit/{$id}.html";
        }
        $this->_relocate($target);
        /* This will exit */
    }


    function _init_order_mark_as_unpaid($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_load_order($id)) {
            debug_pop();
            return false;
        }

        $this->_order->storage->parameter('midcom.helper.datamanager', 'data_paid', '');

        if (array_key_exists('return_to', $_REQUEST))
        {
            $target = $_REQUEST['return_to'];
        }
        else
        {
            $target = "order/edit/{$id}.html";
        }
        debug_print_r("Relocating to {$target}, request was:", $_REQUEST);
        $this->_relocate($target);
        /* This will exit */
    }


    function _init_order_edit($id) {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_load_order($id)) {
            debug_pop();
            return false;
        }

        /* Patch the Datamanager Schema so that admins have access to some more fields
         * This is a bit of a hack :)
         */
        if ($this->_auth->is_admin()) {
            $this->_order->datamanager->_fields["status"]["readonly"] = false;
        }

        $session = new midcom_service_session();
        if (! $session->exists("order_edit_returnurl")) {
            $headers = getallheaders();
            if (array_key_exists("Referer", $headers))
                $session->set("order_edit_returnurl", $headers["Referer"]);
            else
                $session->set("order_edit_returnurl", $this->_prefix);
        }

        // Prepare the toolbar
        $this->_local_toolbar->disable_view_page();
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        if ($this->_order->data["order_sent"]["timestamp"] == 0)
        {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "order/mark_as_sent/{$this->_order->storage->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as sent'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mark.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));

            if ($this->_config->get('enable_payment_management'))
            {
                if ($this->_order->data['paid']['timestamp'] == 0)
                {
                    $this->_local_toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "order/mark_as_paid/{$this->_order->storage->id}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as paid'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mark.png',
                        MIDCOM_TOOLBAR_ENABLED => true
                    ));
                }
                else
                {
                    $this->_local_toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "order/mark_as_unpaid/{$this->_order->storage->id}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as unpaid'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                        MIDCOM_TOOLBAR_ENABLED => true
                    ));
                }
            }

            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "order/edititems/{$this->_order->storage->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order items'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_data-edit-table.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        else
        {
            if ($this->_auth->is_poweruser())
            {
                $this->_local_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "order/mark_as_unsent/{$this->_order->storage->id}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as unsent'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));

                if ($this->_config->get('enable_payment_management'))
                {
                    if ($this->_order->data['paid']['timestamp'] == 0)
                    {
                        $this->_local_toolbar->add_item(Array(
                            MIDCOM_TOOLBAR_URL => "order/mark_as_paid/{$this->_order->storage->id}.html",
                            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as paid'),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mark.png',
                            MIDCOM_TOOLBAR_ENABLED => true
                        ));
                    }
                    else
                    {
                        $this->_local_toolbar->add_item(Array(
                            MIDCOM_TOOLBAR_URL => "order/mark_as_unpaid/{$this->_order->storage->id}.html",
                            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as unpaid'),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                            MIDCOM_TOOLBAR_ENABLED => true
                        ));
                    }
                }

                $this->_local_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "order/edititems/{$this->_order->storage->id}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order items'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_data-edit-table.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
            }
        }
        if ($this->_auth->is_poweruser())
        {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "order/delete/{$this->_order->storage->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete order'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }

        switch ($this->_order->datamanager->process_form()) {
            case MIDCOM_DATAMGR_EDITING:
                debug_add("We are still editing.");
                break;

            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                debug_add("Datamanger has saved or cancelled, return to view.");
                $this->_relocate($session->remove("order_edit_returnrul"));
                /* This will exit() */

            case MIDCOM_DATAMGR_FAILED:
                debug_add("The DM failed critically, see above.");
                $this->errstr = "The Datamanger failed to process the request, see the Debug Log for details";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Method unknown";
                debug_pop();
                return false;
        }

        $this->_mode = "order_edit";

        debug_pop();
        return true;
    }

    function _show_order_edit() {
        midcom_show_style("admin-heading-order-edit");
        midcom_show_style("admin-dmform-order");
    }


    function _init_order_edititems($id) {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_load_order($id)) {
            debug_pop();
            return false;
        }

        $session = new midcom_service_session();
        if (! $session->exists("order_edit_returnurl")) {
            $headers = getallheaders();
            if (array_key_exists("Referer", $headers))
                $session->set("order_edit_returnurl", $headers["Referer"]);
            else
                $session->set("order_edit_returnurl", $this->_prefix);
        }

        // Prepare the toolbar
        $this->_local_toolbar->disable_view_page();
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        if ($this->_order->data["order_sent"]["timestamp"] == 0)
        {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "order/mark_as_sent/{$this->_order->storage->id}.html?return_to=order/edititems/{$id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as sent'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mark.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            if ($this->_config->get('enable_payment_management'))
            {
                if ($this->_order->data['paid']['timestamp'] == 0)
                {
                    $this->_local_toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "order/mark_as_paid/{$this->_order->storage->id}.html?return_to=order/edititems/{$id}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as paid'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mark.png',
                        MIDCOM_TOOLBAR_ENABLED => true
                    ));
                }
                else
                {
                    $this->_local_toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "order/mark_as_unpaid/{$this->_order->storage->id}.html?return_to=order/edititems/{$id}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as unpaid'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                        MIDCOM_TOOLBAR_ENABLED => true
                    ));
                }
            }
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "order/edit/{$this->_order->storage->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
        else
        {
            if ($this->_auth->is_poweruser())
            {
                $this->_local_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "order/mark_as_unsent/{$this->_order->storage->id}.html?return_to=order/edititems/{$id}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as unsent'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
                if ($this->_config->get('enable_payment_management'))
                {
                    if ($this->_order->data['paid']['timestamp'] == 0)
                    {
                        $this->_local_toolbar->add_item(Array(
                            MIDCOM_TOOLBAR_URL => "order/mark_as_paid/{$this->_order->storage->id}.html?return_to=order/edititems/{$id}.html",
                            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as paid'),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mark.png',
                            MIDCOM_TOOLBAR_ENABLED => true
                        ));
                    }
                    else
                    {
                        $this->_local_toolbar->add_item(Array(
                            MIDCOM_TOOLBAR_URL => "order/mark_as_unpaid/{$this->_order->storage->id}.html?return_to=order/edititems/{$id}.html",
                            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as unpaid'),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                            MIDCOM_TOOLBAR_ENABLED => true
                        ));
                    }
                }
                $this->_local_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "order/edit/{$this->_order->storage->id}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
            }
        }
        if ($this->_auth->is_poweruser())
        {
            $this->_local_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "order/delete/{$this->_order->storage->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete order'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }

        if (array_key_exists("form_submit", $_REQUEST)) {
            $items = $this->_order->get_order();
            $changes = Array();
            $reservation_changes = Array();
            $success = true;
            foreach ($items as $guid => $item) {
                if (! array_key_exists ($guid, $_REQUEST)) {
                    $this->errstr = "Request incomplete.";
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_print_r ("Request was: ", $_REQUEST);
                    return false;
                }
                $quantity = 0;
                sscanf($_REQUEST[$guid], "%d", $quantity);
                if ($quantity <= 0) {
                    $msg = sprintf($this->_l10n->get("you must enter a positive quantity for product %s."),
                                   $item["product"]->data["code"]);
                    $GLOBALS["view_contentmgr"]->msg .= "{$msg}<br>\n";
                    $success = false;
                } else if ($quantity != $item["quantity"]) {
                    if (! $this->_auth->is_poweruser() && $quantity > $item["product"]->data["maxperorder"]) {
                        $msg = sprintf($this->_l10n->get("maxperorder limit for product %s exceeded"),
                                       $item["product"]->data["code"]);
                        $GLOBALS["view_contentmgr"]->msg .= "{$msg}<br>\n";
                        $changes[$guid] = $item["product"]->data["maxperorder"];
                        $success = false;
                    } else if (($quantity - $item["quantity"]) > $item["product"]->data["available"]) {
                        $changes[$guid] = $item["quantity"] + $item["product"]->data["available"];
                        $msg = sprintf($this->_l10n->get("Not enough copies for %s available, reduced to %d."),
                                       $item["product"]->data["code"], $changes[$guid]);
                        $GLOBALS["view_contentmgr"]->msg .= "{$msg}<br>\n";
                        $success = false;
                    } else {
                        $changes[$guid] = $quantity;
                        $reservation_changes[$guid] = $quantity - $item["quantity"];
                    }
                }
            }

            foreach ($changes as $guid => $quantity) {
                $this->_order->set_order($items[$guid]["product"], $quantity);
            }

            if ($success) {
                foreach ($reservation_changes as $guid => $quantity) {
                    if (! $items[$guid]["product"]->reserve_items($quantity)) {
                        $this->errcode = MIDCOM_ERRCRIT;
                        return false;
                    }
                }
                $this->_order->save_orders();
                $this->_relocate($session->remove("order_edit_returnrul"));
            }
        } else if (array_key_exists("form_cancel", $_REQUEST)) {
            $this->_relocate($session->remove("order_edit_returnrul"));
        }

        $this->_mode = "order_edititems";

        debug_pop();
        return true;
    }

    function _show_order_edititems() {
        midcom_show_style("admin-heading-order-edititems");
        midcom_show_style("admin-customform-orderitems");
    }


    function _init_order_delete($id) {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_load_order($id)) {
            debug_pop();
            return false;
        }

        if (array_key_exists("ok", $_REQUEST) && $_REQUEST["ok"] == "yes") {
            $this->_order->delete();
            /* This will call generate_error on failure */
            $this->_relocate("");
            /* This will exit() */
        }

        $this->_local_toolbar->disable_view_page();

        $this->_mode = "order_deletecheck";

        debug_pop();
        return true;
    }

    function _show_order_deletecheck() {
        midcom_show_style("admin-heading-order-delete");
        midcom_show_style("admin-show-order");
    }


    function _init_order_query_delivered() {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_local_toolbar->disable_view_page();
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'order/query_delivered.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('new query'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        if (array_key_exists("form_submit", $_REQUEST)) {
            /* We have a request */
            if (   ! array_key_exists("form_mode", $_REQUEST)
                || ! array_key_exists("form_start", $_REQUEST)
                || ! array_key_exists("form_end", $_REQUEST)
                || ! ($_REQUEST["form_mode"] == "string" || $_REQUEST["form_mode"] == "unix"))
            {
                $this->errstr = "Request incomplete";
                $this->errcode = MIDCOM_ERRCRIT;
                debug_print_r("Request was:", $_REQUEST);
                debug_pop();
                return false;
            }
            if ($_REQUEST["form_mode"] == "unix") {
                if (   ! is_numeric($_REQUEST["form_start"])
                    || ! is_numeric($_REQUEST["form_end"]))
                {
                    $this->errstr = "Request invalid, timestamps expected";
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_print_r("Request was:", $_REQUEST);
                    debug_pop();
                    return false;
                }
                $start = $_REQUEST["form_start"];
                $end = $_REQUEST["form_end"];
            } else {
                $start = strtotime($_REQUEST["form_start"]);
                $end = strtotime($_REQUEST["form_end"]);
                if ($start == -1 || $end == -1) {
                    $this->errstr = "Request invalid, strtotime compatible strings expected";
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_print_r("Request was:", $_REQUEST);
                    debug_pop();
                    return false;
                }
            }
            $fact = new net_nemein_orders_order_factory();
            $this->_order_query_result = $fact->list_delivered_between($start, $end);

            $this->_mode = "order_query_delivered_showresult";
            debug_pop();
            return true;
        } else {
            $this->_mode = "order_query_delivered_form";
            debug_pop();
            return true;
        }
    }

    function _show_order_query_delivered_form() {
        midcom_show_style("admin-heading-order-query-delivered");
        midcom_show_style("admin-query-delivered-form");
    }

    function _show_order_query_delivered_showresult() {
        midcom_show_style("admin-heading-order-query-delivered");

        midcom_show_style("admin-query-delivered-orders-begin");

        if ($this->_order_query_result === false)
        {
            midcom_show_style("admin-query-delivered-orders-item-none");
        }
        else
        {
            $this->_view_toolbar = new midcom_helper_toolbar('midcom_toolbar midcom_toolbar_in_content');

            $paid_index = false;
            $edit_index = 1;
            $edititems_index = 2;
            $delete_index = 3;

            if ($this->_config->get('enable_payment_management'))
            {
                $paid_index = 1;
                $edit_index = 2;
                $edititems_index = 3;
                $delete_index = 4;
            }

            if ($this->_auth->is_poweruser())
            {
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('mark as unsent'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
                if ($paid_index !== false)
                {
                    $this->_view_toolbar->add_item(Array(
                        MIDCOM_TOOLBAR_URL => '',
                        MIDCOM_TOOLBAR_LABEL => '',
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => '',
                        MIDCOM_TOOLBAR_ENABLED => true
                    ));
                }
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order items'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_data-edit-table.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
                $this->_view_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => '',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete order'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
            }

            foreach ($this->_order_query_result as $guid => $order)
            {
                $this->_order = $order;

                if ($this->_auth->is_poweruser())
                {
                    $return_to = urlencode("order/query_delivered.html?form_submit=a&form_mode={$_REQUEST['form_mode']}&form_start=");
                    $return_to .= urlencode($_REQUEST['form_start']);
                    $return_to .= urlencode('&form_end=');
                    $return_to .= urlencode($_REQUEST['form_end']);

                    $this->_view_toolbar->update_item_url(0, "order/mark_as_unsent/{$order->storage->id}.html?return_to={$return_to}");
                    if ($paid_index !== false)
                    {
                        if ($this->_order->data['paid']['timestamp'])
                        {
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_LABEL] = $this->_l10n->get('mark as unpaid');
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_ICON] = 'stock-icons/16x16/cancel.png';
                            $this->_view_toolbar->update_item_url($paid_index, "order/mark_as_unpaid/{$order->storage->id}.html?return_to={$return_to}");
                        }
                        else
                        {
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_LABEL] = $this->_l10n->get('mark as paid');
                            $this->_view_toolbar->items[$paid_index][MIDCOM_TOOLBAR_ICON] = 'stock-icons/16x16/stock_mark.png';
                            $this->_view_toolbar->update_item_url($paid_index, "order/mark_as_paid/{$order->storage->id}.html?return_to={$return_to}");
                        }
                    }
                    $this->_view_toolbar->update_item_url($edit_index, "order/edit/{$order->storage->id}.html");
                    $this->_view_toolbar->update_item_url($edititems_index, "order/edititems/{$order->storage->id}.html");
                    $this->_view_toolbar->update_item_url($delete_index, "order/delete/{$order->storage->id}.html");
                }
                midcom_show_style("admin-query-delivered-orders-item");
            }
        }

        midcom_show_style("admin-query-delivered-orders-end");
    }


    function _init_order_maintain() {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_local_toolbar->disable_view_page();
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to main'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        if (array_key_exists("form_mode", $_REQUEST)) {
            switch ($_REQUEST["form_mode"]) {
                case "delete_incomplete":
                    if (! array_key_exists("form_age", $_REQUEST)) {
                        $this->errstr = "Request incomplete.";
                        $this->errcode = MIDCOM_ERRCRIT;
                        debug_print_r("Request was:", $_REQUEST);
                        debug_pop();
                        return false;
                    }
                    $count = $this->_delete_incomplete($_REQUEST["form_age"]);
                    if ($count == -1) {
                        debug_add("delete_incomplete returned -1.");
                        debug_pop();
                        return false;
                    }
                    $msg = sprintf($this->_l10n->get("cleaned up %d incomplete events."), $count);
                    $GLOBALS["view_contentmgr"]->msg .= "{$msg}<br>\n";
                    break;

                default:
                    $this->errstr = "Unknown operation.";
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_print_r("Request was:", $_REQUEST);
                    debug_pop();
                    return false;
            }
        }

        $fact = new net_nemein_orders_order_factory();
        $this->_order_query_result = $fact->list_corrupt();

        $this->_mode = "order_maintain";

        debug_pop();
        return true;
    }

    function _show_order_maintain() {
        midcom_show_style("admin-heading-order-maintain");

        midcom_show_style("admin-query-delivered-orders-begin");

        if ($this->_order_query_result === false)
        {
            midcom_show_style("admin-query-delivered-orders-item-none");
        }
        else
        {
            $this->_view_toolbar = new midcom_helper_toolbar('midcom_toolbar midcom_toolbar_in_content');
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit order items'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_data-edit-table.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
            $this->_view_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete order'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true
                ));

            foreach ($this->_order_query_result as $guid => $order)
            {
                $this->_order = $order;
                $this->_view_toolbar->update_item_url(0, "order/edit/{$order->storage->id}.html");
                $this->_view_toolbar->update_item_url(1, "order/edititems/{$order->storage->id}.html");
                $this->_view_toolbar->update_item_url(2, "order/delete/{$order->storage->id}.html");
                midcom_show_style("admin-query-delivered-orders-item");
            }
        }

        midcom_show_style("admin-query-delivered-orders-end");
    }


    /************** PRIVATE HELPER METHODS **********************/

    function _delete_incomplete($age) {
        if (   ! is_numeric($age)
            || $age < 0)
        {
            $this->errstr = "Request invalid.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_print_r("Request was:", $_REQUEST);
            return -1;
        }
        $fact = new net_nemein_orders_order_factory();
        $end = time() - ((integer) $age);
        debug_add("Querying orders from 0 to {$end}");
        $orders = $fact->list_incomplete_between(0, $end);
        if (! $orders) {
            debug_add("No matches.");
            return 0;
        } else {
            foreach ($orders as $guid => $order) {
                debug_add("Deleting order {$guid}", MIDCOM_LOG_INFO);
                if (! $order->delete()) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    debug_add("Failed to delete order!, see above.");
                    return -1;
                }
            }
            return count($orders);
        }

    }


    function _load_order($order) {
        $event = null;
        if (mgd_is_guid($order)) {
            $event = mgd_get_object_by_guid($order);
        } else if (is_numeric($order)) {
            $event = mgd_get_event($order);
        } else if (is_a($order, "MidgardEvent")) {
            $event = $order;
        } else {
            $this->errstr = "Incorrect Datatype as parameter while loading order.";
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("_load_order: Got unknown parameter type, this is a major problem.", MIDCOM_LOG_INFO);
            debug_print_r("Got this:", $order);
            return false;
        }

        if (   ! $event
            || $event->__table__ != "event"
            || $event->up != $this->_root_order_event->id)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "Order not found.";
            debug_add("The order could either not be loaded or was below the wrong event.", MIDCOM_LOG_INFO);
            debug_print_r("Retrieved record:", $event);
            debug_pop();
            return false;
        }

        $this->_order = new net_nemein_orders_order($event);
        if (! $this->_order) {
            $this->errstr = "Order could not be loaded: " . $this->errstr;
            $this->errcode = MIDCOM_ERRCRIT;
            debug_add("Order could not be loaded, this usually indicates a datamanager problem.");
            return false;
        }

        return true;
    }

    function _prepare_config_dm ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        /* Set a global so that the schema gets internationalized */
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->_config_dm = new midcom_helper_datamanager("file:/net/nemein/orders/config/schemadb_config.inc");

        if ($this->_config_dm == false)
        {
            debug_add("Failed to instantinate configuration datamanager.", MIDCOM_LOG_CRIT);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "Failed to instantinate configuration datamanager.");
        }

        // Load the configuration topic
        if ($this->_config->get('symlink_config_topic'))
        {
            $config_topic = $this->_config->get('symlink_config_topic');
        }
        else
        {
            $config_topic = $this->_topic;
        }

        if (! $this->_config_dm->init($config_topic))
        {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
            debug_print_r("Topic object we tried was:", $this->_topic);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                                               "Failed to initialize configuration datamanager.");
        }

        debug_pop();
        return;
    }

    function _relocate ($url) {
        $GLOBALS["midcom"]->relocate($this->_prefix . $url);
    }

    function _create_root_event () {
        $event = mgd_get_event();
        $event->owner = $this->_topic->owner;
        $event->title = "n.n.orders root event for " . $this->_topic->guid();
        $event->description = "Autocreated by net.nemein.orders.";
        $event->up = 0;
        $event->type = 0;
        $id = $event->create();
        if ($id === false) {
            $msg = sprintf($this->_l10n->get("failed to auto-create order root event: %s"),
                           mgd_errstr());
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
            debug_add("Could not create root event: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
        $event = mgd_get_event($id);
        $this->_topic->parameter("net.nemein.orders","root_order_event", $event->guid());
        $msg = sprintf($this->_l10n->get("auto-created order root event <em>%s</em>"),
                       $event->title);
        $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
        $this->_root_order_event = $event;
        return true;
    }

    function _check_root_event() {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (is_null ($this->_config->get("root_order_event"))) {
            debug_add("Root Order Event config value is still null, we have to create it.");
            if ($this->_create_root_event()) {
                $this->_config->store(Array("root_order_event" => $this->_root_order_event->guid()), false);
                debug_pop();
                return true;
            }
            debug_pop();
            return false;
        } else {
            debug_add("Trying to load the root event [" . $this->_config->get("root_order_event") . "]");
            $event = mgd_get_object_by_guid($this->_config->get("root_order_event"));
            if (! $event || $event->__table__ != "event") {
                debug_add("Failed to load root event, the GUID references a non-existant or invalid object.", MIDCOM_LOG_ERROR);
                debug_print_r("Retrieved object was:", $event);
                debug_pop();
                return false;
            }
            $this->_root_order_event = $event;
            debug_add("Successfully loaded root event.");
            debug_pop();
            return true;
        }
    }

    function _load_mailing_group() {
        $guid = $this->_config->get("mailing_company_group");
        if (is_null($guid)) {
            $this->_mailing_company_group = null;
        } else {
            $grp = mgd_get_object_by_guid($guid);
            if (! $grp || $grp->__table__ != "grp") {
                debug_add("Could not load Mailing company group, invalid GUID detected: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_print_r("Retrieved object was:", $grp);
                $this->_mailing_company_group = null;
            } else {
                $this->_mailing_company_group = $grp;
            }
        }
    }

    function _prepare_toolbar()
    {
        // Don't forget to update admin-welcome.php too.

        if (! $this->_auth->is_owner())
        {
            // No toolbar if we are not the owner.
            return;
        }

        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'order/show_undelivered.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('pending orders'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        if (! $this->_auth->is_mailing_company())
        {
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'order/query_delivered.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('query delivered orders'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/search.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }

        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'product/update_storage.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('update storage amount'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));

        if ($this->_auth->is_poweruser())
        {
            // Add the topic configuration item, be sensitive for symlinked config topics.
            $config_topic = $this->_config->get('symlink_config_topic');
            if (is_null($config_topic))
            {
                $this->_topic_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
            }
            else
            {
                $this->_topic_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$GLOBALS['view_contentmgr']->viewdata['adminprefix']}{$config_topic->id}/data/config.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                    MIDCOM_TOOLBAR_ENABLED => true
                ));
            }

            // Add the create subcategory link.
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'create_subcategory.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create subcategory'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));

            // Add the new article link at the beginning
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'product/create.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create product'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ), 0);
        }
        if ($this->_auth->is_admin())
        {
            $this->_topic_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'order/maintain.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('incomplete/corrupt orders'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
    }

} // admin

?>