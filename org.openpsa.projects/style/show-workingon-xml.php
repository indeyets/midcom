<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$workingon =& $data['workingon'];
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<workingon>
    <task><?php echo $workingon->task->guid; ?></task>
    <start><?php echo $workingon->start; ?></start>
    <time><?php echo $workingon->format_time(); ?></time>
</workingon>