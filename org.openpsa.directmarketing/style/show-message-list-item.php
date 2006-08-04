<li><?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $view_data['message_array'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

echo "<div class=\"{$view_data['message_class']}\"><a href=\"{$node[MIDCOM_NAV_FULLURL]}message/{$view_data['message']->guid}/\">{$view['title']}</a>\n";
echo "<br />".sprintf($view_data['l10n']->get('created on %s'), strftime('%x %X', $view_data['message']->created))."\n";

if ($view_data['message']->sendStarted)
{
    echo ", ".sprintf($view_data['l10n']->get('sent on %s'), strftime('%x %X', $view_data['message']->sendStarted))."\n";
}

echo "</div>\n";
?></li>