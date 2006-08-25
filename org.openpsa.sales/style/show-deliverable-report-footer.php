<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$sums_all = $view_data['sums_all'];
?>
    </tbody>
    <tfoot>
        <?php
        $colspan = 4;
        if ($view_data['handler_id'] != 'deliverable_report')
        {
            $colspan++;
            foreach ($view_data['sums_per_person'] as $person_id => $sums)
            {
                $owner = new midcom_db_person($person_id);
                $owner_card = new org_openpsa_contactwidget($owner);
                ?>
                <tr>
                    <td colspan="&(colspan);"><?php echo $owner_card->show_inline(); ?></td>
                    <td>&(sums['price']);</td>
                    <td>&(sums['cost']);</td>
                    <td>&(sums['profit']);</td>
                    <td></td>
                </tr>
                <?php
            }
        }
        ?>
        <tr>
            <td colspan="&(colspan);"><?php echo $view_data['l10n']->get('total'); ?></td>
            <td>&(sums_all['price']);</td>
            <td>&(sums_all['cost']);</td>
            <td>&(sums_all['profit']);</td>
            <td></td>
        </tr>
    </tfoot>
</table>
