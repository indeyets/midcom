<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$columns_count = count($data['columns']);
?>
<tr>
    <td colspan="&(columns_count);">
        <?php echo $data['l10n']->get('no matches'); ?>
    </td>
</tr>