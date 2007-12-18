<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix =& $data['prefix'];
$view = $data['photo_view'];
?>
<div class="org_routamc_photostream_photo">
    <h1><?php echo $view['title']; ?></h1>

    <div class="photo">
        &(view['photo']:h);
    </div>

    <div class="description">
        &(view['description']:h);
    </div>
    <?php
    // List tags used in this photo page
    $tags_by_context = net_nemein_tag_handler::get_object_tags_by_contexts($data['photo']);
    if (count($tags_by_context) > 0)
    {
        echo "\n<dl class=\"tags\">\n";
        foreach ($tags_by_context as $context => $tags)
        {
            if (!$context)
            {
                $context = $_MIDCOM->i18n->get_string('tagged', 'net.nemein.tag');
            }
            echo "    <dt>{$context}</dt>\n";
            foreach ($tags as $tag => $url)
            {
                echo "        <dd class=\"tag\"><a href=\"{$prefix}tag/{$data['user_url']}/{$tag}\">{$tag}</a></dd>\n";
            }
        }
        echo "</dl>\n";
    }
    ?>
</div>
