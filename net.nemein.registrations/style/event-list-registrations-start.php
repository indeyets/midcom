<?php
// Available request keys:
// event, view_url, edit_url, delete_url
// registrations

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$title = sprintf($data['l10n']->get('list registrations of %s'), $data['event']->title);
$data['event_dm'] =& $data['event']->get_datamanager();
$event_dm =& $data['event_dm'];
?>
<h2>&(title);</h2>

<form method="post">
    <table class="net_nemein_registrations_list sortable">
        <thead>
            <tr>
                <th><?php echo $data['l10n']->get('registrar'); ?></th>
                <th><?php echo $data['l10n']->get('registration date'); ?></th>
<?php
if (   !isset($event_dm->types['auto_approve'])
    || !$event_dm->types['auto_approve']->value)
{
    echo "                <th>" . $data['l10n_midcom']->get('approved') . "</th>\n";
}
if ($data['config']->get('pricing_plugin'))
{
    echo "                <th>" . $data['l10n']->get('reference') . "</th>\n";
    echo "                <th>" . $data['l10n']->get('price') . "</th>\n";
    echo "                <th>" . $data['l10n']->get('paid') . "</th>\n";
}
?>
            </tr>
        </thead>
        <tbody>
