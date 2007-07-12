<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);


// Showing a form
echo "<form method=\"post\">";
echo "<input type=\"hidden\" name=\"net_nemein_favourites_referer\" value=\"{$data['my_way_back']}\"/>";
echo "<input type=\"text\" name=\"net_nemein_favourite_title\" value=\"{$data['favourite_title']}\"/><br/>";
echo "<input type=\"submit\" name=\"net_nemein_favourite_submit\" value=\"Submit\"/>";
echo "</form>";

?>
