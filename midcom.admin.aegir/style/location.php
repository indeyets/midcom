<div id="aislocation">
<?php
 
$separator = " &gt; ";
$toolbars = &midcom_helper_toolbars::get_instance();


foreach ($toolbars->aegir_location->items as $item)  {
    if ($item[MIDCOM_TOOLBAR_ENABLED]) {
       echo  $separator . "<a href=\"{$item[MIDCOM_TOOLBAR_URL]}\">"
      . htmlspecialchars($item[MIDCOM_TOOLBAR_LABEL]) 
      . "</a>";
    } else {
        echo $separator . " " . htmlspecialchars($item[MIDCOM_TOOLBAR_LABEL]) ; 
    }
}

?>
</div>
