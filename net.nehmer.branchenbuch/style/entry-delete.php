<?php
// The available request keys can be found in the components' API documentation
// of net_nehmer_account_handler_register
//
// Bind the view data, remember the reference assignment:
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['entry_dm']->get_content_html();
$schema =& $data['entry_dm']->schema;
?>
<h2><?php echo $data['topic']->extra; ?>: <?php echo $data['branche']->get_full_name(); ?></h2>
<h3>&(view['firstname']); &(view['lastname']);</h3>

<form action="" method="POST">
<p>
    <?php $data['l10n']->show('are you sure you want to delete this entry?'); ?>
    <input type="submit" name="net_nehmer_branchenbuch_deleteok" value="<?php $data['l10n_midcom']->show("yes"); ?>" /><br />
    <a href="&(data['return_url']);"><?php $data['l10n_midcom']->show('back'); ?></a>
</p>
</form>

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
