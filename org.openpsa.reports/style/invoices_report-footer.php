<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$sums_all = $data['sums_all'];
?>
                </tbody>
                <tfoot>
                    <?php
                    $colspan = 4;
                    foreach ($data['sums_per_person'] as $person_id => $sums)
                    {
                        $owner = new midcom_db_person($person_id);
                        $owner_card = new org_openpsa_contactwidget($owner);
                        ?>
                        <tr>
                            <td colspan="&(colspan);"><?php echo $owner_card->show_inline(); ?></td>
                            <td class="sum"><?php echo sprintf("%01.2f", $sums['price']); ?></td>
                            <td></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan="&(colspan);"><?php echo $data['l10n']->get('total'); ?></td>
                        <td class="sum"><?php echo sprintf("%01.2f", $sums_all['price']); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>