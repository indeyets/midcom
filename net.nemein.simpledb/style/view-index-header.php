<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<table border="1" cellspacing="0">
    <tr>
        <?php
        foreach($data['columns'] as $key => $field) 
        {
            ?>
            <th class="&(key);">&(field);</th>
            <?php
        }
        ?>
    </tr>
