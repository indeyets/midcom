<?php

/* Temporary topic management replacement, refactored out of _cmdtopic and _cmdmeta.
 * no comments etc. because this is already superseeded by AIS2 and just
 * a proof-of-concept implementation
 */

class midcom_admin_content_topic_plugin extends midcom_baseclasses_components_handler
{
    var $_anchor_prefix = null;
    var $_processing_msg = '';
    var $_newtopic = null;

    function midcom_admin_content_topic_plugin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $config = $this->_request_data['plugin_config'];
        if ($config)
        {
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
        }
        $this->_anchor_prefix = $this->_request_data['plugin_anchorprefix'];
    }

    function get_plugin_handlers()
    {
        return Array
        (
            'create' => Array
            (
                'handler' => Array('midcom_admin_content_topic_plugin', 'create'),
                'fixed_args' => 'create',
            ),
            'edit' => Array
            (
                'handler' => Array('midcom_admin_content_topic_plugin', 'edit'),
                'fixed_args' => 'edit',
            ),
            'delete' => Array
            (
                'handler' => Array('midcom_admin_content_topic_plugin', 'delete'),
                'fixed_args' => 'delete',
            ),
            'approve' => Array
            (
                'handler' => Array('midcom_admin_content_topic_plugin', 'approval'),
                'fixed_args' => 'approve',
            ),
            'unapprove' => Array
            (
                'handler' => Array('midcom_admin_content_topic_plugin', 'approval'),
                'fixed_args' => 'unapprove',
            ),
            'metadata' => Array
            (
                'handler' => Array('midcom_admin_content_topic_plugin', 'metadata'),
                'fixed_args' => 'metadata',
                'variable_args' => 1,
            ),
        );
    }


    function _handler_metadata($handler_id, $args, &$data)
    {
        $object = $_MIDCOM->dbfactory->get_object_by_guid($args[0]);
        if (! $object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$args[0]}' was not found.");
            // This will exit.
        }
        $object->require_do('midcom:approve');

        $metadata =& midcom_helper_metadata::retrieve($object);
        if (! $metadata)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to retrieve Metadata for '{$object->__table__}' ID {$object->id}.");
            // This will exit.
        }

        $dm =& $metadata->get_datamanager();
        switch ($dm->process_form())
        {
            case MIDCOM_DATAMGR_SAVED:
            case MIDCOM_DATAMGR_CANCELLED:
                $_MIDCOM->relocate($_MIDCOM->permalinks->create_permalink($object->guid));
                // This will exit.
            
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }
        
        $data['dm'] =& $dm;
        $data['object'] =& $object;
        $data['metadata'] =& $metadata;
        
        return true;
    }

    function _show_metadata($handler_id, &$data)
    {
        $title = '<h2>';
        $title .= $_MIDCOM->i18n->get_string('edit metadata', 'midcom.admin.content');
        if (array_key_exists('title', $data['object']))
        {
            $title .= ": {$data['object']->title}";
        }
        else if (is_a($data['object'], 'midcom_baseclasses_database_topic'))
        {
            $title .= ": {$data['object']->extra}";
        }
        else if (array_key_exists('name', $data['object']))
        {
            $title .= ": {$data['object']->name}";
        }
        else
        {
            $title .= ': ' . get_class($data['object']) . " GUID {$data['object']->guid}"; 
        }
        $title .= "</h2>\n";
        
        echo $title;
        $data['dm']->display_form();
    }


    function _handler_approval($handler_id, $args, &$data)
    {
        if (   ! array_key_exists('guid', $_REQUEST)
            || ! array_key_exists('return_to', $_REQUEST))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Cannot process approval request, request is incomplete.');
            // This will exit.
        }
        
        $object = $_MIDCOM->dbfactory->get_object_by_guid($_REQUEST['guid']);
        if (! $object)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The GUID '{$_REQUEST['guid']}' was not found.");
            // This will exit.
        }
        $object->require_do('midcom:approve');

        $metadata =& midcom_helper_metadata::retrieve($object);
        if (! $metadata)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to retrieve Metadata for '{$object->__table__}' ID {$object->id}.");
            // This will exit.
        }

        if ($handler_id == '____ais-topic-approve')
        {
            $metadata->approve();
        }
        else
        {
            $metadata->unapprove();
        }

        $_MIDCOM->relocate($_REQUEST['return_to']);
        // This will exit.
    }

    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:delete');
        
        if (array_key_exists("f_cancel", $_REQUEST))
        {
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists("f_submit", $_REQUEST))
        {
            if ($this->_process_delete_form())
            {
                $nav = new midcom_helper_nav();
                $node = $nav->get_node($this->_topic->up);
                $_MIDCOM->relocate($node[MIDCOM_NAV_FULLURL]);
                // This will exit.
            }
        }

        return true;
    }

    function _process_delete_form()
    {
        $this->_delete_topic_update_index();

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $articles = $qb->execute();

        if (is_null($articles))
        {
            debug_add("Failed to query the articles of this topic: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = "Error: Could not delete Folder contents: " . mgd_errstr();
            return false;
        }

        foreach ($articles as $article)
        {
            if (!$article->delete())
            {
                debug_add("Could not delete Article {$article->id}:" . mgd_errstr(), MIDCOM_LOG_ERROR);
                $this->_contentadm->msg = "Error: Could not delete Folder contents: " . mgd_errstr();
                return false;
            }
        }

        if (!$this->_topic->delete())
        {
            debug_add("Could not delete Folder {$this->_topic->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = "Error: Could not delete Folder contents: " . mgd_errstr();
            return false;
        }

        // Invalidate everything since we operate recursive here.
        $GLOBALS['midcom']->cache->invalidate_all();

        debug_pop();
        return true;
    }

    function _show_delete($handler_id, &$data)
    {
?>
<div class="aish1">Delete Folder</div>

<form method="post" action="" enctype="multipart/form-data">

<div class="form_description">URL Name:</div>
<div class="form_shorttext"><?php echo $this->_topic->name; ?></div>

<div class="form_description">Title:</div>
<div class="form_shorttext"><?php echo $this->_topic->extra; ?></div>

<p style="font-weight:bold; color: red;">All decendants will be deleted!</p>

<p style="font-weight:bold; color: red;">Are you sure you want to delete this topic?</p>

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="Delete">
  <input type="submit" name="f_cancel" value="Cancel">
</div>

</form>
<?php
    }

    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        if (array_key_exists("f_cancel", $_REQUEST))
        {
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists("f_submit", $_REQUEST))
        {
            if ($this->_process_create_form())
            {
                $_MIDCOM->relocate("{$this->_newtopic->name}/");
                // This will exit.
            }
        }

        return true;
    }

    function _process_create_form()
    {
        if (trim($_REQUEST["f_title"]) == "")
        {
            $this->_processing_msg = "Error: Title was empty";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->_processing_msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (mgd_get_topic_by_name($this->_topic->id, $_REQUEST["f_name"]))
        {
            $this->_processing_msg = "Error: a Folder with this name already exists.";
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->_processing_msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (trim($_REQUEST["f_name"]) == "")
        {
            // No URL name given, generate from title
            $name = midcom_generate_urlname_from_string($_REQUEST['f_title']);
        }
        else
        {
            $name = midcom_generate_urlname_from_string($_REQUEST['f_name']);
        }

        $this->_newtopic = new midcom_db_topic();
        $this->_newtopic->up = $this->_topic->id;
        $this->_newtopic->name = $name;
        $this->_newtopic->extra = $_REQUEST['f_title'];

        if (! $this->_newtopic->create())
        {
            $this->_processing_msg = "Could not create Folder: " . mgd_errstr();
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add($this->_processing_msg, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $newid = $this->_newtopic->id;
        $this->_newtopic->parameter('midcom', 'component', $_REQUEST['f_type']);
        $this->_newtopic->parameter("midcom.helper.nav", "navorder", $_REQUEST["f_navorder"]);

        // We have to invalidate the current topic (so that it can be reread with the correct
        // childs), *not* the newly created topic (which won't be in any cache anyway, as it
        // has just been created with a new GUID...
        $_MIDCOM->cache->invalidate($this->_topic->guid);

        return true;

    }

    function _show_create($handler_id, &$data)
    {
        // Get parent component and navorder
        $parent_component = $this->_topic->get_parameter("midcom", "component");
        $parent_navorder = $this->_topic->get_parameter("midcom", "navorder");

        $view_navorder_list = array(
            MIDCOM_NAVORDER_DEFAULT => "default sort order",
            MIDCOM_NAVORDER_TOPICSFIRST => "topics first",
            MIDCOM_NAVORDER_ARTICLESFIRST => "articles first",
            MIDCOM_NAVORDER_SCORE => "by score",
        );

        // Get available components
        $components = Array();
        foreach ($_MIDCOM->componentloader->manifests as $manifest)
        {
            // Skip pure code libraries
            if ($manifest->purecode)
            {
                continue;
            }
            $manifest->get_name_translated();
            $components[$manifest->name] = "{$manifest->name_translated} ($manifest->name)";
        }
        asort($components);
?>
<h1>Create Folder</h1>

<form method="post" action="" enctype="multipart/form-data">

<div class="form_description">URL Name:</div>
<div class="form_field"><input class="shorttext" name="f_name" type="text" size="50" maxlength="255 value="" /></div>

<div class="form_description">Title:</div>
<div class="form_field"><input class="shorttext" name="f_title" type="text" size="50" maxlength="255" value="" /></div>

<div class="form_description">Type:</div>
<div class="form_field">
  <select class="dropdown" name="f_type">
    <?php foreach ($components as $path => $name) { ?>
      <option value="<?php echo $path; ?>"<?php if ($path == $parent_component) { echo ' selected="selected"'; } ?>><?php echo $name; ?></option>
    <?php } ?>
  </select>
</div>

<div class="form_description">Nav Order:</div>
<div class="form_field">
  <select class="dropdown" name="f_navorder">
<?php
    foreach ($view_navorder_list as $value => $caption)
    {
?>
    <option value="<?php echo htmlspecialchars($value); ?>"><?php echo $caption; ?></option>
<?php
    }
?>
  </select>
</div>

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="Create" />
  <input type="submit" name="f_cancel" value="Cancel" />
</div>

</form>
<?php
    }

    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        
        if (array_key_exists("f_cancel", $_REQUEST))
        {
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists("f_submit", $_REQUEST))
        {
            if ($this->_process_edit_form())
            {
                $_MIDCOM->relocate('');
                // This will exit.
            }
        }

        return true;
    }

    function _process_edit_form()
    {
        if (trim($_REQUEST["f_name"]) == "")
        {
            $this->_processing_msg = "Error: URL name was empty.";
            return false;
        }
        if (trim($_REQUEST["f_title"]) == "")
        {
            $this->_processing_msg = "Error: Title was empty.";
            return false;
        }
        if (mgd_get_topic_by_name($this->_topic->id, $_REQUEST["f_name"]))
        {
            $this->_processing_msg = "Error: a Folder with this name already exists.";
            return false;
        }

        // store form data in topic object
        $this->_topic->name = midcom_generate_urlname_from_string($_REQUEST["f_name"]);
        $this->_topic->extra = $_REQUEST["f_title"];
        $this->_topic->score = $_REQUEST["f_score"];
        $this->_topic->owner = $_REQUEST["f_owner"];
        $this->_topic->parameter("midcom", "style", $_REQUEST["f_style"]);
        $this->_topic->parameter("midcom.helper.nav", "navorder", $_REQUEST["f_navorder"]);
        if (   array_key_exists("f_style_inherit", $_REQUEST)
            && $_REQUEST["f_style_inherit"] == "on")
        {
            $this->_topic->parameter("midcom", "style_inherit", "true");
        }
        else
        {
            $this->_topic->parameter("midcom", "style_inherit", "");
        }

        if (! $this->_topic->update())
        {
            $this->_processing_msg = "Could not save Folder: " . mgd_errstr();
            return false;
        }

        $_MIDCOM->cache->invalidate($this->_topic->guid());

        return true;
    }

    function _show_edit($handler_id, &$data)
    {
        $view = $this->_topic;
        $GLOBALS['view'] = $view;
        $view_style_inherit = $view->parameter("midcom", "style_inherit");
        $view_style = $view->parameter("midcom", "style");
        $view_navorder = $view->parameter("midcom.helper.nav", "navorder");
        $view_navorder_list = array(
            MIDCOM_NAVORDER_DEFAULT => "default sort order",
            MIDCOM_NAVORDER_TOPICSFIRST => "topics first",
            MIDCOM_NAVORDER_ARTICLESFIRST => "articles first",
            MIDCOM_NAVORDER_SCORE => "by score",
        );

?>
<div class="aish1">Edit Folder</div>

<form method="post" action="" enctype="multipart/form-data">

<div class="form_description">URL Name:</div>
<div class="form_field"><input class="shorttext" name="f_name" type="text" size="50" maxlength="255" value="<?php
    echo htmlspecialchars($view->name);?>" /></div>

<div class="form_description">Title:</div>
<div class="form_field"><input class="shorttext" name="f_title" type="text" size="50" maxlength="255" value="<?php
    echo htmlspecialchars($view->extra);?>" /></div>

<div class="form_description">Style:</div>
  <div class="form_field">
    <select class="dropdown" name="f_style">
      <option value="">Default</option>
<?php
      midcom_admin_content_list_styles_selector2();
      midcom_admin_content_list_styles_selector2(null,'',"/",true);
?>
  </select>
</div>

<div class="form_description">Score:</div>
<div class="form_field"><input class="shorttext" name="f_score" type="text" size="50" maxlength="5" value="<?php
    echo $view->score;?>"></div>

<div class="form_description">Nav Order:</div>
<div class="form_field">
  <select class="dropdown" name="f_navorder"><?php
  foreach ($view_navorder_list as $value => $caption)
  {
    ?>
    <option value="<?php echo $value; ?>"<?php if ($view_navorder == $value) { ?> selected <?php
     } ?>><?php echo htmlspecialchars($caption); ?></option><?php
  } ?>
  </select>
</div>

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="Save">
  <input type="submit" name="f_cancel" value="Cancel">
</div>

</form>
<?php
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

            $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
            if ($object)
            {
                $atts = $object->list_attachments();
                if ($atts)
                {
                    foreach ($atts as $attachment)
                    {
                        debug_add("Deleting attachment {$atts->id} from the index.");
                        $indexer->delete($atts->guid);
                    }
                }
            }

            debug_add("Deleting guid {$guid} from the index.");
            $indexer->delete($guid);
        }

        debug_pop();
    }

}

function midcom_admin_content_list_styles_selector2($up = null, $spacer = '', $path = '/', $show_shared = false) {
  $midgard = $GLOBALS["midcom"]->get_midgard();

  if (array_key_exists("view",$GLOBALS)) {
    $current_style = $GLOBALS["view"]->parameter("midcom", "style");
  } else {
    $current_style = '';
  }

  if (is_null ($up)) {
    $styles = mgd_list_styles();
  } else {
    $styles = mgd_list_styles($up);
  }

  if ($styles) {
    while ($styles->fetch()) {
      $style = mgd_get_style($styles->id);

      if (!$show_shared && ($style->sitegroup != $midgard->sitegroup)) {
        continue;
      }
      if ($show_shared && ($style->sitegroup == $midgard->sitegroup)) {
        continue;
      }

      // Don't show groups deeper in hierarchy as toplevel
      if (is_null($up)) {
        if ($style->up != 0) {
          continue;
        }
      }

      if ($current_style == $path.$styles->name) {
        echo '<option value="' . $path.$styles->name . '" selected="selected">' . $spacer . $styles->name . "</option>\n";
      } else {
        echo '<option value="' . $path.$styles->name . '">' . $spacer . $styles->name . "</option>\n";
      }
      midcom_admin_content_list_styles_selector2($styles->id, $spacer."&nbsp;&nbsp;&nbsp;&nbsp;",$path.$styles->name."/",$show_shared);
    }
  }
}


?>
