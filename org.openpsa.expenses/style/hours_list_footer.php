<?php
$colspan = 4;
if ($data['mode'] == 'simple')
{
    $colspan = 3;
}
?>
    </tbody>
    <tfoot>
        <td><?php $data['l10n']->show('total'); ?></td>
        <td class="numeric">&(data['total_hours']);</td>
        <td colspan="&(colspan);"></td>
    </tfoot>
</table>