<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

echo "<h1>{$data['topic']->extra}: {$data['view_title']}</h1>";

if ($data['bury'])
{
    echo "<p class=\"helptext\">{$data['l10n']->get('you are adding the following item to buries list')}</p>\n";
}
else
{
    echo "<p class=\"helptext\">{$data['l10n']->get('you are adding the following item to favourites')}</p>\n";
}

// Showing a form
echo "<form method=\"post\">\n";
echo "    <input type=\"hidden\" name=\"net_nemein_favourites_referer\" value=\"{$data['my_way_back']}\" />\n";
echo "    <label class=\"net_nemein_favourites_label\"><span>{$data['l10n_midcom']->get('title')}</span>\n";
echo "        <input type=\"text\" name=\"net_nemein_favourite_title\" value=\"{$data['favourite_title']}\"/>\n";
echo "    </label>\n";
echo "<input type=\"submit\" name=\"net_nemein_favourite_submit\" value=\"{$data['l10n']->get('add')}\"/>\n";
echo "</form>\n\n";

echo "<p><a href=\"{$data['my_way_back']}\">{$data['l10n']->get('return and dont add')}</a></p>\n";
?>