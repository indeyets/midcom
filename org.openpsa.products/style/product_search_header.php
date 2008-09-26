<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['view_title']; ?></h1>

<ul class="search_navigation">
    <?php
    foreach ($data['schemadb_product'] as $name => $schema)
    {
        $selected = '';
        if ($name == $data['search_schema'])
        {
            $selected = ' class="selected"';
        }
        echo "<li><a href=\"{$prefix}search/{$name}/\"{$selected}>" . $data['l10n']->get($data['schemadb_product'][$name]->description) . "</a></li>\n";
    }
    ?>
</ul>