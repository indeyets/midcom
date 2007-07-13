<?php
// Available request keys: resource, calendar, datamanager
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_resource'];
?>  
    <div class="times">
    <?php
    $data['calendar']->additional_name_for_links = $view['name'];
    $data['calendar']->show();
    ?>
    </div>