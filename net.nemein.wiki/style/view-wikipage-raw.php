<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $request_data['wikipage_view'];

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
?>