<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$group =& $view_data['current_group'];
$query_data =& $view_data['query_data'];
$span = 4;
if (array_key_exists('hour_type_filter', $query_data))
{
    $span++;
}
if (array_key_exists('invoiceable_filter', $query_data))
{
    $span++;
}
?>
                <tbody class="group">
                    <tr class="header">
                        <th colspan=&(span);>&(group['title']);</th>
                    </tr>
