<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_view
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
$account =& $data['account'];
$visible_data =& $data['visible_data'];
$schema =& $data['datamanager']->schema;
?>

<p style="font-weight: bold;"><a href="&(data['profile_url']);">&(account.name);</a></p>

<table cellspacing='0' cellpadding='' border='0'>
<?php
foreach ($data['visible_fields'] as $name)
{
    $title = $schema->translate_schema_string($schema->fields[$name]['title']);
    $content = $visible_data[$name];
?>
    <tr>
        <td style="font-weight: bold; padding-right: 5px;">&(title);:</td>
        <td>&(content:h);</td>
    </tr>

<?php } ?>
</table>

<p style="font-size: 75%"><?php echo $data['l10n_midcom']->get('last modified') . ': ' . $data['revised']->format($data['l10n_midcom']->get('short date') . " %T"); ?></p>

