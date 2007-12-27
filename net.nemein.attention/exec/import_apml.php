<?php
$_MIDCOM->auth->require_valid_user();

if (isset($_REQUEST['path']))
{
    $importer = net_nemein_attention_importer::create('apml');
    $importer->import($_REQUEST['path'], $_MIDGARD['user']);
    
    $imported = count($importer->concepts);
    echo "<p>{$imported} concepts imported</p>\n";
}
?>
<form method="post">
    <label>
        APML file URL
        <input type="text" name="path" />
    </label>
    <input type="submit" value="Import" />
</form>