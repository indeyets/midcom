<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_article'];
?>

<h1>&(view['title']:h);</h1>

<?php 
if (   array_key_exists('image', $view)
    && $view['image']) 
{ 
    ?>
    <div style="float: right; padding: 5px;">&(view['image']:h);</div>
    <?php 
} 
?>

&(view["content"]:h);
