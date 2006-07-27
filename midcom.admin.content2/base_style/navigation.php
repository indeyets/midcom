<?php
  
  $nav = 'ajaxmenu';
  $class_loaded = false;
  /* somehow relative paths didn't work :/  */
  if ($nav != '' && file_exists(MIDCOM_ROOT . "/midcom/admin/aegir/style/navigation/$nav.php")) {
    require_once (MIDCOM_ROOT . "/midcom/admin/aegir/style/navigation/$nav.php");
    $nav_object_class = 'midcom_admin_aegir_navigation_' . $nav;
         
  }

  
  $nav_object = new $nav_object_class();

  
  echo $nav_object->to_html_simple();
  

?>