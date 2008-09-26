<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$enable_delete = false;
if ($data['topic']->can_do('midgard:delete'))
{
    $enable_delete = true;
}
$enable_update = false;
if ($data['topic']->can_do('midgard:update'))
{
    $enable_update = true;
}
?>
<h1><?php echo $data['topic']->extra; ?></h1>

<?php
echo "<a name=\"top\"></a>\n";
echo $data['alphabets_nav'];
?>

<div class="net_nemein_alphabeticalindex items" id="net_nemein_alphabeticalindex_items">
    <?php
    foreach ($data['alphabets'] as $letter => $content)
    {
        if ($content)
        {
            echo "<a name=\"{$letter}\" id=\"{$letter}\"></a>\n";
            echo "<h2>{$letter}</h2>\n";
            echo "<ul>\n";
            foreach ($content as $item)
            {
                $class = 'external';
                $target = "_blank";
                if ($item->internal)
                {
                    $class = 'internal';
                    $target = "_self";
                }
                echo "    <li class=\"{$class}\"><a href=\"" . $item->resolve_url() . "\" target=\"{$target}\" rel=\"Bookmark\" title=\"{$item->title}\">{$item->title}</a>";
                
                if (   $enable_update
                    || $enable_delete)
                {
                    echo "<div class=\"actions\">";                    
                }

                if ($enable_update)
                {
                    echo "<a href=\"{$prefix}edit/{$item->guid}/\">";
                    echo "<img src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/properties.png\" alt=\"" . $data['l10n_midcom']->get('edit') . "\" border=\"0\"/>";
                    echo "</a>";
                }
                if ($enable_delete)
                {
                    echo "<a href=\"{$prefix}delete/{$item->guid}/\">";
                    echo "<img src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/trash.png\" alt=\"" . $data['l10n_midcom']->get('delete') . "\" border=\"0\"/>";
                    echo "</a>";
                }

                if (   $enable_update
                    || $enable_delete)
                {
                    echo "</div>";
                }

                if ($item->description != '') {
                    echo "<div class=\"link_description\">\n";
                    echo nl2br($item->description);
                    echo "</div>\n";
                }

                echo "</li>\n";
            }
            echo "</ul>\n";
            echo "<a href=\"{$prefix}#top\" class=\"top_link\">" . $data['l10n']->get('to top') . "</a>\n";
        }
    }
    ?>
</div>