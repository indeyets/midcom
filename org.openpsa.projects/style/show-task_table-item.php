<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$sales_node =& $data['sales_node'];

$cells =& $data['cells'];

$task_url = $data['prefix'] . "task/{$data['task']->guid}/";
$task =& $data['task'];

$class = $data['task']->status_type;
if ($data['even'])
{
    $class .= ' even';
}
?>
    <tr class="&(class);">
        <td class="title" title="<?php echo $data['l10n']->get($data['task']->status_type); ?>">
            <a href="&(task_url);"><?php echo $data['task']->title; ?></a>
        </td>
        <td>
            &(cells['parent']:h);
        </td>
        <?php
        if ($data['view_identifier'] != 'agreement')
        {
            ?>
            <td>
                &(cells['customer']:h);
            </td>
            <td>
                &(cells['agreement']:h);
            </td>
            <?php
        }
        ?>
        <td>
             &(cells['manager']:h);
        </td>
        <td>
            <?php echo strftime('%x', $task->start) . ' - ' . strftime('%x', $task->end); ?>
        </td>
        <td class="hours">
            <?php echo $data['hours']['invoiceable']; ?>
        </td>
        <td class="hours">
            <?php echo $data['hours']['invoiced']; ?>
        </td>
        <td class="hours">
            <?php
            echo $data['hours']['reported'];
            if ($task->plannedHours > 0)
            {
                echo  ' / ' . $task->plannedHours;
            }
            ?>
        </td>
    </tr>