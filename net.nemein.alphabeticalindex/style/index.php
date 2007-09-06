<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('index'); ?></h1>

<?php
echo $data['alphabets_nav'];
?>

<div class="net_nemein_alphabeticalindex items" id="net_nemein_alphabeticalindex_items">
    <?php
    foreach ($data['alphabets'] as $letter => $content)
    {
        if ($content)
        {
            $cap_letter = strtoupper($letter);
            echo "<a name=\"{$letter}\" id=\"{$letter}\"></a>\n";
            echo "<h2>{$cap_letter}</h2>\n";
            echo "<ul>\n";
            foreach ($content as $item)
            {
                $class = 'external';
                if ($item->internal)
                {
                    $class = 'internal';
                }
                echo "    <li class=\"{$class}\"><a href=\"{$item->url}\">{$item->title}</a></li>\n";
            }
            echo "</ul>\n";
        }
    }
    ?>
</div>