<?php
global $view_new, $view_id, $view_title, $view_topic;
    
//$data = $view->get_array();
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div id="discussion_topic">test
<?php
      $view_new->display_form();
      //$view->display_form(); 
?>
</div>