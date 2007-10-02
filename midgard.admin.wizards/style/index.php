<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('Available wizards'); ?></h1>

<ul class="midgard_admin_wizards_plugin_groups">

<?php

    foreach ($data['plugin_groups'] as $group => $value)
    {
        if (count($value['plugins']) > 0)
        {
            echo "<li><a href=\"" . $group . "/\">" . $value['title'] . "</a></li>";
        
            if (isset($value['plugins']))
            {
                echo "<ul>";
                foreach ($value['plugins'] as $plugin => $value)
                {
                    echo "<li>" . $value['name']. "</li>";            
            
                }
                echo "</ul>";
            }
        }    
    }

?>

</ul>