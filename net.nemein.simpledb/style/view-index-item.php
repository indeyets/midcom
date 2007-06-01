<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<tr>
    <?php
    $i = 0;
    foreach($data['columns'] as $key => $field) 
    {
        ?>
        <td>
            <?php 
            if ($data['view'][$key]) 
            {
                if ($i == 0) 
                {
                    echo "<a href=\"{$prefix}{$data['view_name']}\">";
                    $data['datamanager']->display_view_field($key);
                    echo "</a>";
                } 
                else
                {
                    $data['datamanager']->display_view_field($key); 
                }
            }
            $i++;
            ?>
        </td>
        <?php        
    }
    ?>
</tr>