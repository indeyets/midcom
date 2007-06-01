<?php

//$data = & $_MIDCOM->get_custom_context_data('request_data');
$nav = $data['aegir_interface']->get_navigation();
?><ul>

<?php

foreach ($nav->list_leaves() as $key =>  $file ) {
    $leaf = $nav->get_leaf($file);
    
    echo "<li>{$leaf[MIDCOM_NAV_NAME]}:<br/> <ul>\n";
    /*
    foreach ($data['files'] as $key => $file ) {
    
        ?><li><a href="<? echo urlencode($file); ?>"><? echo $file ?></a></li><? 
    }
    */
    echo "</ul></li>";

}
?>
</ul>
