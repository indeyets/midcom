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
      <tr>
        <td colspan="&(colspan);">
            <?php echo $data['l10n']->get('totals'); ?>
        </td>
        <td class="numeric">
            <?php echo $data['total_hours']['invoiceable']; ?>
        </td>
        <td class="numeric">
            <?php echo $data['total_hours']['invoiced']; ?>
        </td>
        <td class="numeric">
            <?php echo $data['total_hours']['reported']; ?>
        </td>
      </tr>
    </tfoot>
</table>