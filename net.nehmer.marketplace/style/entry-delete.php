<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$entry =& $data['entry'];

// Available request data: entry, datamanager, mode, edit_url, view_url
?>

<h2><?php echo $data['l10n']->get($data['mode']) . ": {$entry->title}"; ?></h2>

<form action="" method="POST">
<p>
    <?php $data['l10n']->show('are you sure you want to delete this entry?'); ?>
    <input type="submit" name="net_nehmer_marketplace_deleteok" value="<?php $data['l10n_midcom']->show("yes"); ?>" /><br />
    <a href="&(data['view_url']);"><?php $data['l10n_midcom']->show('back'); ?></a>
</p>
</form>

<p>
&(entry.description:f);
</p>

<dl>
<?php
foreach ($data['datamanager']->schema->field_order as $name)
{
    debug_print_type("Type:", $data['datamanager']->types[$name]);
    $title = $data['datamanager']->schema->translate_schema_string($data['datamanager']->schema->fields[$name]['title']);
    $content = $data['datamanager']->types[$name]->convert_to_html();
    if (! $content)
    {
        continue;
    }
?>
<dt style="font-weight: bold;">&(title:h);</dt>
<dd>&(content:h);</dd>
<?php } ?>
</dl>

<p>
    <?php if ($data['edit_url']) { ?>
        <a href="&(data['edit_url']);"><?php $data['l10n_midcom']->show('edit'); ?></a>&nbsp;&nbsp;
    <?php } ?>
</p>
