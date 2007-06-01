<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$hour =& $data['current_row']['hour'];
$task =& $data['current_row']['task'];
$person =& $data['current_row']['person'];
$query_data =& $data['query_data'];
?>
                    <tr class="item">
<?php   switch($data['grouping'])
        {
            case 'date': ?>
                        <td>&(person->rname);</td>
<?php           break;
            case 'person': ?>
                        <td><?php echo strftime('%x', $hour->date); ?></td>
<?php           break;
        } ?>
                        <td>&(task->title);</td>
<?php   if (   array_key_exists('hour_type_filter', $query_data)
            /* Cannot be checked from this array
            && !(   array_key_exists('hidden', $query_data['hour_type_filter'])
                 && !empty($query_data['hour_type_filter']['hidden']))
            */
            )
        {   ?>
                        <td>&(hour->reportType);</td>
<?php   }   ?>
<?php   if (   array_key_exists('invoiceable_filter', $query_data)
            /* Cannot be checked from this array
            && !(   array_key_exists('hidden', $query_data['invoiceable_filter'])
                 && !empty($query_data['invoiceable_filter']['hidden']))
            */
            )
        {
            if ($hour->invoiceable)
            {
                $hour_invoiceable_str = $data['l10n']->get('yes');
            }
            else
            {
                $hour_invoiceable_str = $data['l10n']->get('no');
            }   ?>
                        <td>&(hour_invoiceable_str);</td>
<?php   }       ?>
                        <td>&(hour->description);</td>
                        <td><?php printf('%01.2f', $hour->hours); ?></td>
                    </tr>