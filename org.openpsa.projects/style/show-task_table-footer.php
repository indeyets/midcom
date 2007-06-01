<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

if ($data['view_identifier'] == 'agreement')
{
    $colspan = 4;
}
else
{
    $colspan = 6;
}
?>
    </tbody>
    <tfoot>
        <td colspan="&(colspan);">
            <?php echo $data['l10n']->get('totals'); ?>
        </td>
        <td class="hours">
            <?php echo $data['total_hours']['invoiceable']; ?>
        </td>
        <td class="hours">
            <?php echo $data['total_hours']['invoiced']; ?>
        </td>
        <td class="hours">
            <?php echo $data['total_hours']['reported']; ?>
        </td>
    </tfoot>
</table>