<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

if (!$data['in_listing'])
{
    ?>
    <ul class="eventlist">
    <?php
    $data['in_listing'] = true;
}
?>