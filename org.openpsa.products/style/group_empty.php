<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

if (array_key_exists('view_group', $data))
{
    $view = $data['view_group'];
    ?>
    <h1>&(view['code']:h); &(view['title']:h);</h1>

    <table>
        <tbody>
            <tr>
                <td><?php echo $data['l10n']->get('parent group'); ?></td>
                <td>&(view['up']:h);</td>
            </tr>
        </tbody>
    </table>

    &(view['description']:h);
    <?php
}
else
{
    echo "<h1>{$data['view_title']}</h1>\n";
}
?>