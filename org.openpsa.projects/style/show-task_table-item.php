<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

$task_url =  "{$node[MIDCOM_NAV_FULLURL]}task/{$data['task']->guid}/";
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
            <?php
            if ($data['task']->up)
            {
                $parent = $data['task']->get_parent();
                if ($parent->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_PROJECT)
                {
                    $parent_url = "{$node[MIDCOM_NAV_FULLURL]}project/{$parent->guid}/";
                }
                else
                {
                    $parent_url = "{$node[MIDCOM_NAV_FULLURL]}task/{$parent->guid}/";
                }
                echo "<a href=\"{$parent_url}\">{$parent->title}</a>";
            }
            ?>
        </td>
        <?php
        if ($data['view_identifier'] != 'agreement')
        {
            ?>
            <td>
                <?php
                if ($task->customer)
                {
                    $customer = new org_openpsa_contacts_group($task->customer);
                    $contacts_node = midcom_helper_find_node_by_component('org.openpsa.contacts');
                    $customer_url = "{$contacts_node[MIDCOM_NAV_FULLURL]}group/{$customer->guid}";
                    echo "<a href='{$customer_url}'>{$customer->official}</a>";
                }
                else
                {
                    echo "&nbsp;";
                }
                ?>
            </td>
            <td>
                <?php
                if ($task->agreement)
                {
                    $agreement = new org_openpsa_sales_salesproject_deliverable($task->agreement);
                    $salesproject = new org_openpsa_sales_salesproject($agreement->salesproject);
                    $sales_node = midcom_helper_find_node_by_component('org.openpsa.sales');
                    $agreement_url = "{$sales_node[MIDCOM_NAV_FULLURL]}salesproject/{$salesproject->guid}";
                    echo "<a href='{$agreement_url}'>{$agreement->title}</a>";
                }
                else
                {
                    echo "&nbsp;";
                }
                ?>
            </td>
            <?php
        }
        ?>
        <td>
            <?php
            $manager = new org_openpsa_contacts_person($task->manager);
            $widget = new org_openpsa_contactwidget($manager);
            echo $widget->show_inline();
            ?>
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