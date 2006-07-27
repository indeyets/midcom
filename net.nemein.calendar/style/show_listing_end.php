<?php
$request_data =& $_MIDCOM->get_custom_context_data('request_data');

if ($request_data['in_listing'])
{
    ?>
    </ul>
    <?php
    $request_data['in_listing'] = false;
}
?>