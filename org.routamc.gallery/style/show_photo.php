<?php
$data = & $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['photo_view'];
?>
<div class="org_routamc_photostream_photo">
    <h1><?php echo $view['title']; ?></h1>

    <div class="photo">
        &(view['photo']:h);
    </div>

    <div class="taken location">
        <?php
        if ($GLOBALS['midcom_config']['positioning_enable'])
        {
            $position_object = new org_routamc_positioning_object($data['photo']);
            $coordinates = $position_object->get_coordinates($data['photo']->photographer, $data['photo']->taken);
            echo sprintf($data['l10n']->get('taken on %s in %s'), strftime('%x %X', $data['photo']->taken), org_routamc_positioning_utils::pretty_print_location($coordinates['latitude'], $coordinates['longitude']));
        }
        else
        {
            echo sprintf($data['l10n']->get('taken on %s'), strftime('%x %X', $data['photo']->taken));
        }
        ?>
    </div>

    <div class="description">
        &(view['description']:h);
    </div>

    <div class="rating">
        <?php
        echo $data['l10n']->get('rating') . ': ';
        echo "<a href=\"{$prefix}rated/{$data['user_url']}/{$data['photo']->rating}\">{$view['rating']}</a>\n";
        ?>
    </div>

    <?php
    // List tags used in this wiki page
    $tags_by_context = net_nemein_tag_handler::get_object_tags_by_contexts($data['photo']);
    if (count($tags_by_context) > 0)
    {
        echo "<dl class=\"tags\">\n";
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