<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['view_title']:h);</h1>
<?php
if (($description_before_listing = $data['description_before_listing']) != '')
{
    echo '<div id="net_nemein_resources_list_description">';
    echo $description_before_listing;
    echo '</div>';
}
?>