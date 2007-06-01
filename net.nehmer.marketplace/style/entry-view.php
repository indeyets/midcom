<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$entry =& $data['entry'];
// Available request data: entry, datamanager, mode, edit_url, delete_url
?>

<h2><?php echo $data['l10n']->get($data['mode']) . ": {$entry->title}"; ?></h2>

<p>
&(entry.description:f);
</p>

<dl>
<?php
foreach ($data['datamanager']->schema->field_order as $name)
{
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
    <?php if ($data['previous']) { ?>
        <a href="&(data['previous_url']);"><?php $data['l10n_midcom']->show('previous'); ?></a>&nbsp;&nbsp;
    <?php
    }
    if ($data['next'])
    {
    ?>
        <a href="&(data['next_url']);"><?php $data['l10n_midcom']->show('next'); ?></a>&nbsp;&nbsp;
    <?php } ?>
    <a href="&(data['category_url']);"><?php $data['l10n']->show('back to category'); ?></a>
</p>

<?php if ($data['edit_url'] || $data['delete_url']) { ?>
    <p>
        <?php if ($data['edit_url']) { ?>
            <a href="&(data['edit_url']);"><?php $data['l10n_midcom']->show('edit'); ?></a>&nbsp;&nbsp;
        <?php
        }
        if ($data['delete_url'])
        {
        ?>
            <a href="&(data['delete_url']);"><?php $data['l10n_midcom']->show('delete'); ?></a>
        <?php } ?>
    </p>
<?php } ?>