<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$group =& $view_data['current_group'];
$query_data =& $view_data['query_data'];
$span = 2;
?>
                <tbody class="group">
                    <tr class="header">
                        <th colspan=&(span);>&(group['title']);</th>
                        <th>&nbsp;</th>
                    </tr>
