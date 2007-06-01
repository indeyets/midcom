<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$velocity_per_weight = @round(($view['netweight']) / ($view['sakki150m']*150 + $view['f140lastia']*140 + $view['f240lastia']*240 + $view['f300lastia']*300 + $view['f600lastia']*600)*1000, 1);
$containers = @round(($view['sakki150m'] + $view['f140lastia'] + $view['f240lastia'] + $view['f300lastia'] + $view['f600lastia']), 1);
$containers_per_km = @round(($view['sakki150m'] + $view['f140lastia']*140 + $view['f240lastia'] + $view['f300lastia'] + $view['f600lastia']) / ($view['mileage']), 1);
$containers_per_hour = @round(($view['sakki150m'] + $view['f140lastia'] + $view['f240lastia'] + $view['f300lastia'] + $view['f600lastia']) / ($view['hours']), 0);
?>
        <tr class="&(data['row_class']:h);">
            <td><a href="&(prefix);<?php echo "log/{$data['log']->guid}/"; ?>">&(view['recorddate']:h);</a></td>
            <td>&(view['persons']:h);</td>
            <td>&(view['vehicle']:h);</td>
            <td><?php echo round($view['hours'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['mileage'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['cargos'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['siirto1120m'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['siirto2130m'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['siirto3140m'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['siirto4150m'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['siirto51plus'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['sakki150m'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['f140lastia'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['f240lastia'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['f300lastia'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['f600lastia'], $data['rounding_precision']); ?></td>
            <td><?php echo round($view['netweight'], $data['rounding_precision']); ?></td>
            <td>&(velocity_per_weight:h);</td>
            <td>&(containers:h);</td>
            <td>&(containers_per_km:h);</td>
            <td>&(containers_per_hour:h);</td>
            <td><?php echo round($view['cost'], $data['rounding_precision']); ?></td>
        </tr>