<?php
/*
 * 
$GLOBALS["view_attprefix"] = $_MIDGARD['self'] . "midcom-serveattachmentguid-";
$GLOBALS["view_data"] =& $GLOBALS["view_contentmgr"]->viewdata;

// get l10n libraries
$i18n =& $_MIDCOM->get_service("i18n");
$GLOBALS["view_l10n"] = $i18n->get_l10n("midcom.admin.content");
$GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

// Processing message
$msg = trim($GLOBALS["view_contentmgr"]->msg);
*/
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$msg = $request_data['msg']; 
$toolbars =& midcom_helper_toolbars::get_instance();

//var_dump(array_keys($request_data));

?>
<div id="ais_container">

<div id="ais_top">
    <div id="aistitlebar">
<h1><?php 
    echo $request_data['l10n_midcom']->get('midcom administration'); 
?>: <?php

    $context = $request_data['context'];
    $topic = $GLOBALS['midcom']->get_context_data($context, MIDCOM_CONTEXT_CONTENTTOPIC);
    $component = $GLOBALS['midcom']->get_context_data($context, MIDCOM_CONTEXT_COMPONENT);
    $i18n =& $GLOBALS['midcom']->get_service('i18n');
    $l10n = $i18n->get_l10n($component);
    $component_name = $l10n->get($component);
    echo "{$topic->extra} (<abbr title='{$component}'>{$component_name}</abbr>)";
    
?></h1>
</div>

        <div id="ais_location_bar">
            <?php
             echo $toolbars->aegir_location->render();
            ?>
        </div>
   
     
    
</div>
<!-- end top -->
<div id="ais_bottom">

    <div id="ais_bottom_navigation">
	<?php midcom_show_style("navigation"); ?>
    </div>
	<div id="ais_content_admin">
	<div id="ais_toolbar">
    <?php
    //midcom_show_style("toolbar");
    
        
        
        echo $toolbars->top->render();
        echo $toolbars->bottom->render();
        echo $toolbars->meta->render();
        
      
    if ($msg != "") 
    { 
        ?>
        <div class="processing_message">&(msg:h);</div>
        <?php 
    } 
    ?>
	</div>