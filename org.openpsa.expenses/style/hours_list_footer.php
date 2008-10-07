<?php
$colspan = 5;
if ($data['mode'] == 'simple')
{
    $colspan = 4;
}
?>
    </tbody>
    <tfoot>
        <td><?php $data['l10n']->show('total'); ?></td>
        <td colspan="&(colspan);">&(data['total_hours']);</td>
    </tfoot>
</table>