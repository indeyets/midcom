<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_array();
$columns = $data['columns'];
?>
        <tr>
<?php
foreach ($columns as $key => $name)
{
?>
            <td class="&(key:h);">
                <?php echo utf8_decode($view[$key]); ?>
            </td>
<?php
}
?>
        </tr>
