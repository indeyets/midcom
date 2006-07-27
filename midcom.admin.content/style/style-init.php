<?php
/*
 * */
$GLOBALS["view_attprefix"] = $_MIDGARD['self'] . "midcom-serveattachmentguid-";
$GLOBALS["view_data"] =& $GLOBALS["view_contentmgr"]->viewdata;

// get l10n libraries
$i18n =& $_MIDCOM->get_service("i18n");
$GLOBALS["view_l10n"] = $i18n->get_l10n("midcom.admin.content");
$GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

// Processing message
$msg = trim($GLOBALS["view_contentmgr"]->msg);
?>
<div id="ais_container">

<div id="ais_top">
    <?php
    
        midcom_show_style("top-simple"); 
     
    ?>
</div>
<!-- end top -->
<div id="ais_bottom">

    <div id="ais_bottom_navigation">
	<?php midcom_show_style("navigation"); ?>
    </div>
	<div id="ais_content_admin">
	<div id="ais_toolbar">
    <?php
    midcom_show_style("toolbar");
    
    if ($GLOBALS['view_data']['adminmode'] == 'data')
    {
        $toolbars =& midcom_helper_toolbars::get_instance();
        
        echo $toolbars->top->render();
        echo $toolbars->bottom->render();
        echo $toolbars->meta->render();
        
    }  
    if ($msg != "") 
    { 
        ?>
        <div class="processing_message">&(msg:h);</div>
        <?php 
    } 
    ?>
	</div>