<?php
global $view_layouts;
global $view_releases;
global $view_current_release;
global $view_topic;
global $midcom;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<form name="net_nemein_downloads_createform" method="POST" action="&(prefix);/create/">

<h2><?php echo $GLOBALS["view_l10n"]->get("create new release"); ?></h2>

<?php

if (count($view_layouts) < 1)
    echo "<p><b>Error:</b> No Layouts available!</p>";
else {

?><p><?php echo $GLOBALS["view_l10n"]->get("select layout"); ?>:<br>
<select name="net_nemein_downloads_createlayout"><?php

foreach ($view_layouts as $layout => $desc)
{ ?><option value="&(layout);">&(desc);</option><?php }

?></select></p>
<p><input type="submit" name="net_nemein_downloads_submit" value="Next">
</p><?php

}

?>

</form>

<form name="net_nemein_downloads_createform" method="POST" action="&(prefix);">

<h3><?php echo $GLOBALS["view_l10n"]->get("set current release"); ?></h3>

<?php

if (count($view_releases) > 0) {
    
  ?><p>Select release:<br>
<select name="net_nemein_downloads_setcurrentrelease"><?php

  foreach ($view_releases as $release_guid => $release_name) { 
    ?><option value="&(release_guid);"<?php
    if ($view_current_release == $release_guid) {
      echo " selected=\"selected\"";
    }
    ?>>&(release_name);</option><?php }

?></select></p>
<p><input type="submit" name="net_nemein_downloads_submit" value="Select">
</p><?php

}

?>

</form>