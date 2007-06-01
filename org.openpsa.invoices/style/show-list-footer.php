<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        </tbody>
        <tfoot>
        <?php
        foreach ($data['totals'] as $label => $sum)
        {
            if (!$sum)
            {
                continue;
            }
            ?>
            <tr>
                <td colspan="3">
                    <?php echo $data['l10n']->get($label); ?>
                </td>
                <td class="sum">
                    <?php  echo sprintf("%01.2f", $sum); ?>
                </td>
                <td>&nbsp;</td>
            </tr>
            <?php
        }
        ?>
        </tfoot>
    </table>
</div>