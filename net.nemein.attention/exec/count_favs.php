<?php
$_MIDCOM->auth->require_valid_user();

$importer = net_nemein_attention_importer::create('favourites');
$nodes = $importer->calculate($_MIDCOM->auth->user->guid);

foreach ($nodes as $type => $elements)
{
    echo "<h2>" . $_MIDCOM->i18n->get_string($type, 'net.nemein.attention') . "</h2>\n";
    
    if (empty($elements))
    {
        echo "<p>" . $_MIDCOM->i18n->get_string('no data', 'net.nemein.attention') . "</p>\n";
        continue;
    }

    echo "<ul>\n";
    
    foreach ($elements as $key => $value)
    {
        $key = str_replace('&', '&amp;', $key);
        echo "<li>{$key}: {$value}</li>\n";
    }
    
    echo "</ul>\n";
}
?>