<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

echo "<li><a href=\"{$data['feed']->url}\"><img src=\"" . MIDCOM_STATIC_URL . "/net.nemein.rss/feed-icon-14x14.png\" alt=\"{$data['feed']->url}\" title=\"{$data['feed']->url}\" /></a>";
if ($data['feed']->can_do('midgard:update'))
{
    echo "<a href=\"{$prefix}feeds/edit/{$data['feed']->guid}/\">{$data['feed']->title}</a>\n";
}
else
{
    echo "{$data['feed']->title}\n";
}
echo "    <ul class=\"details\">\n";
echo "        <li></li>\n";
echo "        <li><a href=\"{$prefix}category/{$data['feed_category']}/\">" . sprintf($_MIDCOM->i18n->get_string('%s items', 'net.nemein.rss'), $data['feed_items']) . "</a></li>\n";
if ($data['feed']->latestupdate)
{
    echo "        <li>" . sprintf($_MIDCOM->i18n->get_string('latest item from %s', 'net.nemein.rss'), strftime('%x %X', $data['feed']->latestupdate)) . "</li>\n";
}
if ($data['feed']->latestfetch)
{
    echo "        <li>" . sprintf($_MIDCOM->i18n->get_string('latest fetch %s', 'net.nemein.rss'), strftime('%x %X', $data['feed']->latestfetch)) . "</li>\n";
}
echo "    </ul>\n";
echo $data['feed_toolbar']->render();
echo "</li>\n";
?>