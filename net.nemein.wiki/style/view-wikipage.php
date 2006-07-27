<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $request_data['wikipage_view'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>

<h1>&(view['title']:h);</h1>

<?php 
if ($view['content'] != '')
{
    ?>
    &(view["content"]:h);
    <?php
} 
else
{
    echo "<p class=\"stub\">".$GLOBALS['request_data']['l10n']->get('this page is stub')."</p>";
}

echo "<div style=\"clear: both;\"></div>\n";    
$_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$request_data['wikipage']->guid}/out/normal"); 
?>