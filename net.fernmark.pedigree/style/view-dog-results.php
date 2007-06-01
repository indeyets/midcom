<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$dog =& $data['dog'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
echo "<div class='results'>\n";
echo "    <a name='results'></a><h3>" . $data['l10n']->get('results') . "<h3>\n";
$results = $dog->get_results();
foreach ($results as $result)
{
    $date_f = strftime('%x', strtotime($result->date));
    echo "    <h4 class='date'>{$date_f}: <a href='{$prefix}result/{$result->guid}.html' target='_BLANK'>{$result->eventname}</a></h4>\n";
    echo "    <h5 class='result'>{$result->result}</h5>\n";
    echo "    <div class='critique'>{$result->critique}</div>\n";
}
echo "</div>\n";
?>