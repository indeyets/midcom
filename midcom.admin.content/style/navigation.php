<?php
  global $view_contentmgr;
  $nav = $view_contentmgr->config->get("nav");
  // Disabled by tn, doesn't work yet, no idea why
  $nav = $GLOBALS["midcom_admin_content_ais_config"]->get('nav_menu');
  //$nav = '';
  $class_loaded = false;
  /* somehow relative paths didn't work :/  */
  if ($nav != '' && file_exists(MIDCOM_ROOT . "/midcom/admin/content/style/navigation/$nav.php")) {
    require_once (MIDCOM_ROOT . "/midcom/admin/content/style/navigation/$nav.php");
    $nav_object_class = 'midcom_admin_content_navigation_' . $nav;

  } else { print "OOO"; }
  if (!$class_loaded) {
  /* revert to the old nav  */
    require_once (MIDCOM_ROOT . "/midcom/admin/content/style/navigation/onelevel.php");
    $nav_object_class = 'midcom_admin_content_navigation_onelevel';
  }

  $nav_object = new $nav_object_class();

  echo $nav_object->to_html();

?>
