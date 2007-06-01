<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['summary'];
$i = $data['items'];

$velocity_per_weight = @round(($view['netweight']) / ($view['sakki150m']*150 + $view['f140lastia']*140 + $view['f240lastia']*240 + $view['f300lastia']*300 + $view['f600lastia']*600)*1000, 1);
$containers = @round(($view['sakki150m'] + $view['f140lastia'] + $view['f240lastia'] + $view['f300lastia'] + $view['f600lastia']), 1);
$containers_per_km = @round(($view['sakki150m'] + $view['f140lastia']*140 + $view['f240lastia'] + $view['f300lastia'] + $view['f600lastia']) / ($view['mileage']), 1);
$containers_per_hour = @round(($view['sakki150m'] + $view['f140lastia'] + $view['f240lastia'] + $view['f300lastia'] + $view['f600lastia']) / ($view['hours']), 0);
?>
    </tbody>
    <tfoot>
        <tr class="summary">
            <td colspan="3"><?php echo $data['l10n']->get('total'); ?></td>
            <td>&(view['hours']:h);</td>
            <td>&(view['mileage']:h);</td>
            <td>&(view['cargos']:h);</td>
            <td>&(view['siirto1120m']:h);</td>
            <td>&(view['siirto2130m']:h);</td>
            <td>&(view['siirto3140m']:h);</td>
            <td>&(view['siirto4150m']:h);</td>
            <td>&(view['siirto51plus']:h);</td>
            <td>&(view['sakki150m']:h);</td>
            <td>&(view['f140lastia']:h);</td>
            <td>&(view['f240lastia']:h);</td>
            <td>&(view['f300lastia']:h);</td>
            <td>&(view['f600lastia']:h);</td>
            <td>&(view['netweight']:h);</td>
            <td>&(velocity_per_weight:h);</td>
            <td>&(containers:h);</td>
            <td>&(containers_per_km:h);</td>
            <td>&(containers_per_hour:h);</td>
            <td>&(view['cost']:h);</td>
        </tr>
        <tr class="average">
            <td colspan="3"><?php echo $data['l10n']->get('average'); ?></td>
            <td><?php echo round(($view['hours'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['mileage'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['cargos'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['siirto1120m'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['siirto2130m'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['siirto3140m'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['siirto4150m'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['siirto51plus'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['sakki150m'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['f140lastia'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['f240lastia'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['f300lastia'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['f600lastia'] / $i), $data['rounding_precision']); ?></td>
            <td><?php echo round(($view['netweight'] / $i), $data['rounding_precision']); ?></td>
            <td>&(velocity_per_weight:h);</td>
            <td>&(containers:h);</td>
            <td>&(containers_per_km:h);</td>
            <td>&(containers_per_hour:h);</td>
            <td><?php echo round(($view['cost'] / $i), $data['rounding_precision']); ?></td>
        </tr>
    </tfoot>
</table>
    