<?php
// Available request keys: resource, calendar, datamanager
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>  
    <div class="times">
    <?php
    $data['calendar']->show();
    ?>
    </div>