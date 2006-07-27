<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $request_data['datamanager']->get_array();
?>

<h1>&(view['title']);</h1>

<?php if (isset($view['image']) && array_key_exists('thumbnail', $view['image']) && ($view['image']['thumbnail'] !== null)) 
{ 
    $image = $view['image'];
    $thumb = $image['thumbnail'];
    if ($image['description'] != '')
    {
        $desc = "{$image['description']} ({$image['size_x']}x{$image['size_y']}, {$image['formattedsize']} Bytes)";
    }
    else
    {
        $desc = "{$image['filename']} ({$image['size_x']}x{$image['size_y']}, {$image['formattedsize']} Bytes)";
    }
?>
<a href="&(image['url']);"><img src="&(thumb['url']);" class="right" alt="&(desc);" title="&(desc);" &(thumb['size_line']:h);></a>
<?php } ?>

&(view["content"]:h);