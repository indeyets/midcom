<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td class="label total" colspan="2"><?php echo $data['l10n']->get('total'); ?></td>
                <td class="numeric total"><?php echo sprintf('%01.2f', round($data['total_value'], 2)); ?></td>
            </tr>
