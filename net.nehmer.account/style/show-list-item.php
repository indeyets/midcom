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
            <td>
                <a href="&(url);">
                    <?php 
                    if (in_array('firstname', $data['visible_fields']))
                    {
                        echo $data['user']->firstname; 
                    }
                    if (in_array('firstname', $data['visible_fields']))
                    {
                        echo ' ' . $data['user']->lastname; 
                    }
                    ?>
                    </a>
                </td>
            <td><?php echo $data['user']->metadata->score; ?></td>
        </tr>