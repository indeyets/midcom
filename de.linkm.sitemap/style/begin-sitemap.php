<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(data['view_title']);</h1>
<div id="de_linkm_sitemap_settings">
<form action="" method="POST">
<div style="float:left;" id="de_linkm_sitemap_settings_root">
<?php echo $data['l10n']->get('Select root to show'); ?>
<br />
<select name="de_linkm_sitemap_set_root">
<?php
$roots = de_linkm_sitemap_viewer::list_root_nodes();
$topics_to_skip = explode(',',$data['skip_topics']);

echo "\t<option value=\"\">".$data['l10n']->get('root topic')."</option>\n";

foreach($roots as $root_guid => $root_extra)
{
    if(!in_array($root_guid,$topics_to_skip) && $root_extra && strlen($root_extra)>0)
    {
        if(isset($_REQUEST['de_linkm_sitemap_set_root']) && $_REQUEST['de_linkm_sitemap_set_root'] == $root_guid)
        {
            echo "\t<option value=\"".$root_guid."\" selected>".$root_extra."</option>\n";
        }
        else
        {
            echo "\t<option value=\"".$root_guid."\">".$root_extra."</option>\n";
        }
    }
}
?>
</select>
</div>
<div style="float:left; margin-left:10px;" id="de_linkm_sitemap_settings_levels">
<?php echo $data['l10n']->get('Show levels'); ?>
<br />
<select name="de_linkm_sitemap_set_levels">
<?php
$i = 1;
$max_levels = 6;
while($i <= $max_levels)
{
    if(isset($_REQUEST['de_linkm_sitemap_set_levels']) && $_REQUEST['de_linkm_sitemap_set_levels'] == $i)
    {
        echo "\t<option value=\"$i\" selected>$i</option>\n";
    }
    else
    {
        echo "\t<option value=\"$i\">$i</option>\n";
    }
    $i++;
}
?>
</select>
</div>
<input style="float:left; margin-left:10px; margin-top:15px;" type="submit" value="<?php echo $data['l10n']->get('Submit');?>">
</form>
</div>
<div style="clear:both"></div>
<br />
