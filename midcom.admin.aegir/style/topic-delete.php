<?php  $prefix = $GLOBALS["view_contentmgr"]->viewdata["admintopicprefix"] . "topic/";
    global $view;
    global $view_l10n;
    global $view_l10n_midcom;
?>

<div class="aish1"><?php echo $view_l10n->get("delete topic"); ?></div>

<form method="post" action="&(prefix);deleteok" enctype="multipart/form-data">

<div class="form_description"><?php echo $view_l10n->get("url name"); ?>:</div>
<div class="form_shorttext">&(view.name);</div>

<div class="form_description"><?php echo $view_l10n->get("title"); ?>:</div>
<div class="form_shorttext">&(view.extra);</div>

<p style="font-weight:bold; color: red;"><?php echo $view_l10n->get("descendants are deleted"); ?></p>

<p style="font-weight:bold; color: red;"><?php echo $view_l10n->get("are you sure to delete"); ?></p>

<h3><?php echo $view_l10n->get("topics to delete"); ?></h3>
<?php

function midcom_admin_content_topic_delete_recursor($nodeid = null)
{
	static $nap = null;
    $firstcall = false;
    
    if (is_null($nodeid))
    {
        $nap = new midcom_helper_nav();
        $nodeid = $nap->get_current_node();
        $node = $nap->get_node($nodeid);
        if (! $node)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load node {$nodeid} while recursing for the delete topic listing.");
            // This will exit.
        }
        echo "<ul><li>{$node[MIDCOM_NAV_NAME]} ({$node[MIDCOM_NAV_ABSOLUTEURL]})";
        $firstcall = true;
    }
    else
    {
        $node = $nap->get_node($nodeid);
        if (! $node)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load node {$nodeid} while recursing for the delete topic listing.");
            // This will exit.
        }
    }
    
    $subnodes = $nap->list_nodes($nodeid);
    if (count($subnodes) > 0)
    {
        echo "\n<ul>\n";
	    foreach ($subnodes as $subnodeid)
	    {
	        $subnode = $nap->get_node($subnodeid);
	        if (! $subnode)
	        {
	            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load node {$subnodeid} while recursing for the delete topic listing.");
	            // This will exit.
	        }
	        echo "<li>{$subnode[MIDCOM_NAV_NAME]} ({$subnode[MIDCOM_NAV_ABSOLUTEURL]})";
	        midcom_admin_content_topic_delete_recursor($subnodeid);
	        echo "</li>\n";
	    }
        echo "\n</ul>";
    }
    
    if ($firstcall)
    {
        echo "</li></ul>\n";
    }
}

midcom_admin_content_topic_delete_recursor();
?>

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="<?php echo $view_l10n_midcom->get("delete"); ?>">
  <input type="submit" name="f_cancel" value="<?php echo $view_l10n_midcom->get("cancel"); ?>">
</div>

</form>

