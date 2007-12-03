<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');

$class = '';

if ($data['outside_month'])
{
    $class .= ' outside';
}

if ($data['events'])
{
    $class .= ' events';
}

if ($data['today'])
{
    $class .= ' today';
}
?>
        <td class="<?php echo strtolower(date('l', $data['day'])); ?>&(class:h);">
            <span class="date"><?php echo strftime('%d', $data['day']); ?></span>
