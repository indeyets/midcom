<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

echo "<h1>{$data['topic']->extra}</h1>";

echo "<p class=\"helptext\">{$data['l10n']->get('you are adding the following item to favourites')}</p>\n";

// Showing a form
echo "<form method=\"post\">";
echo "    <input type=\"hidden\" name=\"net_nemein_favourites_referer\" value=\"{$data['my_way_back']}\" />";
echo "    <label class=\"net_nemein_favourites_label\"><span>{$data['l10n_midcom']->get('title')}</span>";
echo "        <input type=\"text\" name=\"net_nemein_favourite_title\" value=\"{$data['favourite_title']}\"/> ";
echo "    </label>\n";
echo "<input type=\"submit\" name=\"net_nemein_favourite_submit\" value=\"{$data['l10n']->get('add')}\"/>";
echo "</form>";

echo "<p><a href=\"{$data['my_way_back']}\">{$data['l10n']->get('return and dont add')}</a></p>\n";
?>
