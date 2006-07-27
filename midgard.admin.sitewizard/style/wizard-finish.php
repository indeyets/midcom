<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');

// Create the link to the site
$protocol = "http://";
if ($data['host']->port == 443)
{
    $protocol = "https://";
}
$port = ":".$data['host']->port;
if ($data['host']->port == 80 || $data['host']->port == 443 || $data['host']->port == 0)
{
    $port = "";
}
$view_host_url = "{$protocol}{$data['host']->name}{$port}{$data['host']->prefix}/";
?>
<p>
<?php echo sprintf($data['l10n']->get("congratulations, your site has been set up to %s"), "<a href=\"{$view_host_url}\">{$view_host_url}</a>"); ?>
</p>

<p>
<?php echo $data['l10n']->get("to work the site must have been set up in apache configuration and dns"); ?>
</p>

<!-- TODO: Figure out if user needs to set up the host via datagard -->