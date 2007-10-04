<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$device =& $data['device'];
?>
    <li>
        <a href="&(data['prefix']);device/applyfor/&(device.guid);">&(device.title);</a>
        <?php
        if (   !empty($device->end)
            && $device->end !== '0000-00-00 00:00:00')
        {
            $deadline_ts = strtotime($device->end);
            $deadline_f = strftime('%x', $deadline_ts);
            $days = floor(($deadline_ts - time()) / (3600*24));
            if ($days > 0)
            {
                echo sprintf($data['l10n']->get('apply before %s'), $deadline_f);
            }
            else
            {
                echo $data['l10n']->get('apply now');
            }
        }
        ?>
    </li>
