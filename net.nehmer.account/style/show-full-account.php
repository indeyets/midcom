<?php
// Request Keys:
// datamanager, fields, schema, account, avatar, avatar_thumbnail, form_submit_name,
// processing_msg, profile_url, edit_url, avatar_url, avatar_thumbnail_url
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$account =& $data['account'];
$visible_data =& $data['visible_data'];
$schema =& $data['datamanager']->schema;

$online_state = $data['user']->is_online();
$first_login = strftime('%x %X', $data['user']->get_first_login());
$last_login = strftime('%x %X', $data['user']->get_last_login());
?>

<h2>&(account.name); (&(schema.description);)</h2>

<p>
    <?php if ($online_state != 'unknown') { ?>
        <?php $data['l10n']->show("the user is {$online_state}.");?><br />
        <?php echo $data['l10n']->get('last login') . ": {$last_login}";?> <br />
    <?php } ?>
    <?php echo $data['l10n']->get('first login') . ": {$first_login}";?> <br />
</p>

<?php if ($data['avatar']) { ?>
    <p><a href="&(data['avatar_url']);"><img src="&(data['avatar_thumbnail_url']);" alt="" /></a></p>
<?php } ?>

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

<p style="font-size: 75%">
    <?php
    echo $data['l10n_midcom']->get('last modified') . ': '
        . $data['revised']->format($data['l10n_midcom']->get('short date') . " %T");
    if ($data['config']->get('allow_publish'))
    {
    ?><br />
    <?php
        if ($data['published'])
        {
            echo sprintf
            (
                $data['l10n']->get('account details published on %s.'),
                $data['published']->format($data['l10n_midcom']->get('short date') . " %T")
            );
        }
        else
        {
            $data['l10n']->show('account details not published.');
        }
    }
    ?>
</p>