<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
    </tbody>
    <tfoot>
        <td>&nbsp;</td>
        <td class="hours">
            <?php
            echo sprintf($data['l10n']->get('%d hours (%d invoiceable) reported'), $data['day_hours_total'], $data['day_hours_invoiceable']);
            ?>
        </td>
    </tfoot>
</table>