<?php

class midcom_admin_content__cmdtopic {

    var $_argv;
    var $_contentadm;
    var $_topic;
    var $_view;
    var $_redirect;
    var $_config;

    function midcom_admin_content__cmdtopic ($argv, &$contentadm) {
        $this->_argv = $argv;
        $this->_contentadm = &$contentadm;
        $this->_topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        $this->_view = "index";
        $this->_redirect = ""; // This one is _relative_ to the MidCOM Page !!! (e.g. 150/data)
        $this->_config = $GLOBALS["midcom_admin_content_topicadmin_config"];

        $midgard = $GLOBALS["midcom"]->get_midgard();
        $data =& $GLOBALS["view_data"];
    }

    function execute ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $return = true;

        if (count($this->_argv) == 0)
        {
            $this->_view = "index";
            $return = true;
        }
        else
        {
            switch($this->_argv[0])
            {
                case "create":
                    $_MIDCOM->auth->require_do('midcom.admin.content:topic_management', $this->_topic);
                    $_MIDCOM->auth->require_do('midgard:create', $this->_topic);
                    $this->_view = "create";
                    break;

                case "createok":
                    $_MIDCOM->auth->require_do('midcom.admin.content:topic_management', $this->_topic);
                    $_MIDCOM->auth->require_do('midgard:create', $this->_topic);
                    $return = $this->_admin_topic_create();
                    break;

                case "delete":
                    $_MIDCOM->auth->require_do('midcom.admin.content:topic_management', $this->_topic);
                    $_MIDCOM->auth->require_do('midgard:delete', $this->_topic);
                    $this->_view = "delete";
                    break;

                case "deleteok":
                    $_MIDCOM->auth->require_do('midcom.admin.content:topic_management', $this->_topic);
                    $_MIDCOM->auth->require_do('midgard:delete', $this->_topic);
                    $return = $this->_admin_topic_delete();
                    break;

                case "edit":
                    $_MIDCOM->auth->require_do('midcom.admin.content:topic_management', $this->_topic);
                    $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
                    $this->_view = "edit";
                    break;

                case "editok":
                    $_MIDCOM->auth->require_do('midcom.admin.content:topic_management', $this->_topic);
                    $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
                    $return = $this->_admin_topic_edit();
                    break;

                case "score":
                    $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
                    $this->_view = "score";
                    break;

                case "scoreok":
                    $_MIDCOM->auth->require_do('midgard:update', $this->_topic);
                    $return = $this->_admin_topic_score();
                    break;

                default:
                    $this->_contentadm->errstr = "Unkown Command in Execute Handler, this should not happen.";
                    $this->_contentadm->errcode = MIDCOM_ERRCRIT;
                    debug_add($this->_contentadm->errstr, MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
            }
        }

        debug_pop();

        if ($this->_view == "redirect")
        {
            $this->_admin_redirect();
        }

        debug_pop();
        return $return;
    }

    function _admin_redirect() {
        $GLOBALS["midcom"]->relocate($this->_contentadm->viewdata["adminprefix"] . $this->_redirect);
        // This will exit.
    }

    function _admin_topic_create() {
        debug_push("Data Manager, Topic Command Create");

        debug_print_r("We have to create a topic under Topic $this->_topic->id, _REQUEST is:", $_REQUEST, MIDCOM_LOG_DEBUG);

        $this->_view = "create";

        if (array_key_exists("f_cancel", $_REQUEST)) {
            debug_pop();
            $this->_view = "redirect";
            $this->_redirect = $this->_topic->id . "/data";
            return true;
        }


        if (!array_key_exists("f_submit", $_REQUEST)) {
            $this->_contentadm->errstr = "The submit button was not in the request data.";
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_add($this->_contentadm->errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (trim($_REQUEST["f_title"]) == "") {
            $this->_contentadm->msg = "Error: Title was empty";
            debug_pop();
            debug_add("Cancelled topic creation, Title was empty.", MIDCOM_LOG_INFO);
            return true;
        }
        if (mgd_get_topic_by_name($this->_topic->id, $_REQUEST["f_name"])) {
            $this->_contentadm->msg = "Error: a Topic with this name already exists.";
            debug_add("A topic with this name already exists.", MIDCOM_LOG_INFO);
            debug_pop();
            return true;
        }

        if (trim($_REQUEST["f_name"]) == "")
        {
            // No URL name given, generate from title
            $name = midcom_generate_urlname_from_string($_REQUEST['f_title']);
        } else {
            $name = midcom_generate_urlname_from_string($_REQUEST['f_name']);
        }

        $topic = new midcom_db_topic();
        $topic->up = $this->_topic->id;
        $topic->name = $name;
        $topic->extra = $_REQUEST['f_title'];
        $topic->owner = $_REQUEST['f_owner'];

        if (! $topic->create())
        {
            $this->_contentadm->msg = "Could not create Topic: " . mgd_errstr();
            debug_add("Could not create topic: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return true;
        }

        $newid = $topic->id;
        $topic->parameter('midcom', 'component', $_REQUEST['f_type']);
        if (! is_null($this->_config))
        {
            $topic->parameter("midcom", "style", $this->_config["components"][$_REQUEST["f_type"]]["default style"]);
        }
        $topic->parameter("midcom.helper.nav", "navorder", $_REQUEST["f_navorder"]);

        $this->_view = "redirect";
        $this->_redirect = $newid . "/data";

        // We have to invalidate the current topic (so that it can be reread with the correct
        // childs), *not* the newly created topic (which won't be in any cache anyway, as it
        // has just been created with a new GUID...
        $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());

        debug_pop();
        return true;
    }

    function _admin_topic_delete ()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_view = "delete";

        if (array_key_exists("f_cancel", $_REQUEST))
        {
            debug_pop();
            $this->_view = "redirect";
            $this->_redirect = $this->_topic->id . "/data";
            return true;
        }

        if (!array_key_exists("f_submit", $_REQUEST))
        {
            $this->_contentadm->errstr = "The submit button was not in the request data.";
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_add($this->_contentadm->errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        debug_add("We have to delete topic {$this->_topic->name} [{$this->_topic->id}]");

        $this->_delete_topic_update_index();

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $articles = $qb->execute();

        if (is_null($articles))
        {
            debug_add("Failed to query the articles of this topic: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = "Error: Could not delete Topic contents: " . mgd_errstr();
            return false;
        }

        foreach ($articles as $article)
        {
            if (!$article->delete())
            {
                debug_add("Could not delete Article {$article->id}:" . mgd_errstr(), MIDCOM_LOG_ERROR);
                $this->_contentadm->msg = "Error: Could not delete Topic contents: " . mgd_errstr();
                return false;
            }
        }

        $newtopic = $this->_topic->up;
        $guid = $this->_topic->guid();

        if (!$this->_topic->delete())
        {
            debug_add("Could not delete Topic {$this->_topic->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = "Error: Could not delete Topic contents: " . mgd_errstr();
            return false;
        }

        $this->_view = "redirect";
        $this->_redirect = $newtopic . "/data";

        // Invalidate everything since we operate recursive here.
        $GLOBALS['midcom']->cache->invalidate_all();

        debug_pop();
        return true;
    }

    function _delete_topic_update_index()
    {
        if ($GLOBALS['midcom_config']['indexer_backend'] === false)
        {
            // Indexer is not configured.
            return;
        }

        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("Dropping all NAP registered objects from the index.");

        // First we collect everything we have to delete, this might take a while
        // so we keep an eye on the script timeout.
        $guids = Array();
        $nap = new midcom_helper_nav();

        $node_list = Array($nap->get_current_node());

        while (count($node_list) > 0)
        {
            set_time_limit(30);

            // Add the node being processed.
            $nodeid = array_shift($node_list);
            debug_add("Processing node {$nodeid}");

            $node = $nap->get_node($nodeid);
            $guids[] = $node[MIDCOM_NAV_GUID];

            debug_add("Processing leaves of node {$nodeid}");
            $leaves = $nap->list_leaves($nodeid, true);
            debug_add("Got " . count($leaves) . " leaves.");
            foreach ($leaves as $leafid)
            {
                $leaf = $nap->get_leaf($leafid);
                $guids[] = $leaf[MIDCOM_NAV_GUID];
            }

            debug_add("Loading subnodes");
            $node_list = array_merge($node_list, $nap->list_nodes($nodeid, true));
            debug_print_r("Remaining node queue", $node_list);
        }

        debug_add("We have to delete " . count($guids) . " objects from the index.");

        // Now we go over the entire index and delete the corresponding objects.
        // We load all attachments of the corresponding objects as well, to have
        // them deleted too.
        //
        // Again we keep an eye on the script timeout.
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        foreach ($guids as $guid)
        {
            set_time_limit(60);

            $object = mgd_get_object_by_guid($guid);
            $atts = $object->listattachments();
            if ($atts)
            {
                while ($atts->fetch())
                {
                    debug_add("Deleting attachment {$atts->id} from the index.");
                    $indexer->delete($atts->guid());
                }
            }

            debug_add("Deleting guid {$guid} from the index.");
            $indexer->delete($guid);
        }

        debug_pop();
    }

    function _admin_topic_edit() {
        debug_push("Data Manager, Topic Command Edit");

        debug_print_r("We have to edit Topic $this->_topic->id, _REQUEST is:", $_REQUEST, MIDCOM_LOG_DEBUG);

        $this->_view = "edit";

        // do some validation

        if (array_key_exists("f_cancel", $_REQUEST)) {
            debug_pop();
            $this->_view = "redirect";
            $this->_redirect = $this->_topic->id . "/data";
            return true;
        }

        if (!array_key_exists("f_submit", $_REQUEST)) {
            $this->_contentadm->errstr = "The submit button was not in the request data.";
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            debug_add($this->_contentadm->errstr);
            debug_pop();
            return false;
        }
        if (trim($_REQUEST["f_name"]) == "") {
            $this->_contentadm->msg = "Error: URL name was empty.";
            debug_add("Cancelled topic creation, Name was empty.", MIDCOM_LOG_INFO);
            debug_pop();
            return true;
        }
        if (trim($_REQUEST["f_title"]) == "") {
            $this->_contentadm->msg = "Error: Title was empty.";
            debug_pop();
            debug_add("Cancelled topic creation, Title was empty.", MIDCOM_LOG_INFO);
            return true;
        }
        if (mgd_get_topic_by_name($this->_topic->id, $_REQUEST["f_name"])) {
            $this->_contentadm->msg = "Error: a Topic with this name already exists.";
            debug_add("A topic with this name already exists.", MIDCOM_LOG_INFO);
            debug_pop();
            return true;
        }

        // store form data in topic object
        // FIXME: nap hidden
        $this->_topic->name = midcom_generate_urlname_from_string($_REQUEST["f_name"]);    // name
        $this->_topic->extra = $_REQUEST["f_title"];  // title
        $this->_topic->score = $_REQUEST["f_score"];  // Score
        $this->_topic->owner = $_REQUEST["f_owner"];  // Owner
        $this->_topic->parameter("midcom", "style", $_REQUEST["f_style"]); // style
        $this->_topic->parameter("midcom.helper.nav", "navorder", $_REQUEST["f_navorder"]); // sort
        if (array_key_exists("f_style_inherit", $_REQUEST) && $_REQUEST["f_style_inherit"] == "on")
            $this->_topic->parameter("midcom", "style_inherit", "true");
        else
            $this->_topic->parameter("midcom", "style_inherit", "");

        /* Viewer Groups: First clear everything, then store the new selection * /
        $params = $this->_topic->listparameters("ViewerGroups");
        if ($params)
            while ($params->fetch())
                $this->_topic->parameter("ViewerGroups", $params->name, "");

        if (   ! in_array("all", $_REQUEST["f_viewer_groups"])
            && count ($_REQUEST["f_viewer_groups"]) > 0)
        {
            foreach ($_REQUEST["f_viewer_groups"] as $guid)
                $this->_topic->parameter("ViewerGroups", $guid, $guid);
        }
        */

        if (! $this->_topic->update()) {
            $this->_contentadm->msg = "Could not save Topic: " . mgd_errstr();
            debug_add("Could not update topic: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return true;
        }

        $this->_view = "redirect";
        $this->_redirect = $this->_topic->id . "/data";

        $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());

        debug_pop();
        return true;
    }

    function _admin_topic_score() {
        debug_push("Data Manager, Topic Command Score");

        debug_print_r("We have to order Topic $this->_topic->id, _REQUEST is:", $_REQUEST, MIDCOM_LOG_DEBUG);
        $this->_view = "score";

        // done, show leave score admin, show content admin
        if (array_key_exists("f_finish_nosave", $_REQUEST)) {
            $this->_view = "redirect";
            $this->_redirect = $this->_topic->id . "/data";
            debug_pop();
            return true;
        } elseif ( array_key_exists("f_finish_save", $_REQUEST)) {
            $this->_view = "redirect";
            $this->_redirect = $this->_topic->id . "/data";
        }

        $items = explode('|', $_REQUEST['topicresult']); // teh resultset is like this: <guid>|<guid>|||
        for ($i = 0; $i < (count($items)-1 ); $i++) {
            if ($items[$i] != '') { /* this tests for empty || in the resultset. This may occur.  */
                $obj = mgd_get_object_by_guid($items[$i]);
                if ($obj) {
                    $obj->setscore($i);
                    $GLOBALS['midcom']->cache->invalidate($items[$i]);
                }
            }
        }

        $items = explode('|', $_REQUEST['articleresult']);
        for ($i = 0; $i < (count($items)-1 ); $i++) {
            if ($items[$i] != '') {
                $obj = mgd_get_object_by_guid($items[$i]);
                if ($obj) {
                    $obj->setscore($i);
                    $GLOBALS['midcom']->cache->invalidate($items[$i]);
                }
            }
        }

        debug_pop();
        return true;
    }

    function show () {
        eval("\$this->_show_$this->_view();");
    }

    function _show_index() {
        die("The Method _show_index of midcom_admin_content__cmdtopic is deprecated. If you see this message, this is a bug. See http://www.midgard-project.org for a bugtracker.");
    }

    function _show_create() {
        $GLOBALS["view_config"] = $this->_config;
        $GLOBALS["view"] = &$this->_topic;
        midcom_show_style("topic-create");
    }

    function _show_delete() {
        $GLOBALS["view"] = &$this->_topic;
        midcom_show_style("topic-delete");
    }

    function _show_edit() {
        $GLOBALS["view"] = &$this->_topic;
        $GLOBALS["view_config"] = $this->_config;
        midcom_show_style("topic-edit");
    }

    function _show_score() {
        $GLOBALS["view"] = &$this->_topic;
        midcom_show_style("topic-score");
    }

}

?>
