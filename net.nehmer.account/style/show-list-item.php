<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

if ($data['user']->username)
{
    $url = "{$prefix}view/{$data['user']->username}";
}
else
{
    $url = "{$prefix}view/{$data['user']->guid}";
}
?>
        <tr>
            <td><a href="&(url);"><?php echo $data['user']->rname; ?></a></td>
            <td><?php echo $data['user']->metadata->score; ?></td>
        </tr>