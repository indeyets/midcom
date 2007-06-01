<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['view_title']:h);</h1>
<?php
if (($display_text_before_form = $data['display_text_before_form']) != '')
{
    echo '<div id="net_nemein_updatenotification_list_description">';
    echo $display_text_before_form;
    echo '</div>';
}
?>
<form method="post" action="save/">