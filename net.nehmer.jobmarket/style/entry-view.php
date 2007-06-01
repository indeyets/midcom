<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$entry =& $data['entry'];

// Available request data: entry, datamanager, type_list, type_config, type, mode,
//     edit_url, delete_url, search_result_url, (next|previous)_search_result,
//     (next|previous)_search_result_url
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

<?php if ($data['search_result_url']) { ?>
    <p>
        <a href="&(data['search_result_url']);"><?php $data['l10n_midcom']->show('back'); ?></a>&nbsp;&nbsp;
        <?php if($data['prev_search_result_url']) { ?>
            <a href="&(data['prev_search_result_url']);"><?php $data['l10n_midcom']->show('previous'); ?></a>&nbsp;&nbsp;
        <?php } ?>
        <?php if($data['next_search_result_url']) { ?>
            <a href="&(data['next_search_result_url']);"><?php $data['l10n_midcom']->show('next'); ?></a>&nbsp;&nbsp;
        <?php } ?>
    </p>
<?php } ?>

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