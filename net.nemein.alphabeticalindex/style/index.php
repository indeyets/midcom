<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$enable_delete = false;
if ($data['topic']->can_do('midgard:delete'))
{
    $enable_delete = true;
}
?>
<h1><?php echo $data['l10n']->get('index'); ?></h1>

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
                echo "    <li class=\"{$class}\"><a href=\"{$item->url}\" target=\"{$target}\" rel=\"Bookmark\" title=\"{$item->title}\">{$item->title}</a>";
                
                if ($enable_delete)
                {
                    echo "<div class=\"actions\">";
                    echo "<a href=\"{$prefix}delete/{$item->guid}.html\">";
                    echo "<img src=\"" . MIDCOM_STATIC_URL . "/stock-icons/16x16/trash.png\" alt=\"" . $data['l10n_midcom']->get('delete') . "\" border=\"0\"/>";
                    echo "</a>";
                    echo "</div>";
                }
                
                echo "</li>\n";
            }
            echo "</ul>\n";
            echo "<a href=\"{$prefix}#top\" class=\"top_link\">" . $data['l10n']->get('to top') . "</a>\n";
        }
    }
    ?>
</div>