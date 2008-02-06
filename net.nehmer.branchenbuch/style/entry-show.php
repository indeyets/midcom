<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['entry_dm']->get_content_html();
$schema =& $data['entry_dm']->schema;
?>
<h2><?php echo $data['topic']->extra; ?>: <?php echo $data['branche']->get_full_name(); ?></h2>
<h3>&(view['firstname']); &(view['lastname']);</h3>

<table cellspacing='0' cellpadding='' border='0'>
<?php
foreach ($view as $name => $content)
{
    if ($schema->fields[$name]['hidden'])
    {
        continue;
    }
    $title = $schema->translate_schema_string($schema->fields[$name]['title']);
?>
    <tr>
        <td style="font-weight: bold; padding-right: 5px;">&(title);:</td>
        <td>&(content:h);</td>
    </tr>
<?php } ?>
</table>

<p>
<?php if ($data['previous_entry_url']) { ?>
    <a href="&(data['previous_entry_url']);"><?php $data['l10n_midcom']->show('previous'); ?></a>&nbsp;&nbsp;
<?php } ?>
<?php if ($data['next_entry_url']) { ?>
    <a href="&(data['next_entry_url']);"><?php $data['l10n_midcom']->show('next'); ?></a>&nbsp;&nbsp;
<?php } ?>
</p>

<p><?php
$revised = $data['entry']->get_revised();
echo $data['l10n_midcom']->get('last modified') . ': ' . $revised->format($data['l10n_midcom']->get('short date') . ' %T');
?></p>

<p><a href="&(data['return_url']);"><?php $data['l10n_midcom']->show('back'); ?></a></p>

<?php if ($data['update_url'] !== null) { ?>
<p><a href="&(data['update_url']);"><?php $data['l10n_midcom']->show('update'); ?></a><br />
<?php } ?>
<?php if ($data['delete_url'] !== null) { ?>
<p><a href="&(data['delete_url']);"><?php $data['l10n_midcom']->show('delete'); ?></a></p>
<?php } ?>