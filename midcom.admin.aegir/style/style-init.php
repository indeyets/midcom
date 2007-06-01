<?php
/*
 * */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

// Processing message
$session =  new midcom_service_session();
$msg = "";
if ($session->exists('msg')) {
    $msg = $session->get('msg'); 
    $session->remove('msg');
}

echo '<?xml version="1.0" encoding="utf-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
  <title>Aegir 2 </title>
<?
     $_MIDCOM->print_head_elements();
?>

<!--[if IE]>
  <style type="text/css">
    body {behavior: url(<? echo MIDCOM_STATIC_URL ;  ?>/midcom.admin.content/csshover.htc);}
  </style>
<? if (0) { ?>
  <noscript>
    <style type="text/css">
<!-- TODO: 
.nav .dropdown, .nav .dropdown div {width: 189px;}
.nav .button .dropdown ul {margin: 0px;}
.nav .dropdown, .nav .dropdown div {position: static;}
.nav .dropdown ul {border: 0;}
.mini-zone {display: none;}
            
    </style>
  </noscript>
<? }  
 // TODO: change these so the fit with the css and thus make for better degrading in ie/mac
 /*
  * The above block calls the special .htc script that forces compliance in IE/win,
and also "dumbs down" the nav when IE is set not to allow scripting. Only IEwin 
can read the noscrip block. (that is commented out)
  Source: http://www.positioniseverything.net/css-dropdowns.html
  * */
?> 
<![endif]-->
</head>
<body <?php  $_MIDCOM->print_jsonload(); ?>   >

<div id="ais_container">

<div id="ais_top">
    
    <div id="ais_top_menu">
        <?php 
            $toolbars = &midcom_helper_toolbars::get_instance();
            echo $toolbars->aegir_menu->render();
        ?>
    </div>
    <div id="ais_location_bar">
            <?php
            midcom_show_style('location');
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
	    midcom_show_style("toolbar");
	    ?>
		</div>
        <?php if ($msg != ""  || 1): ?>
            <div id="aegir_msg"  class="processing_message">&(msg:h);</div>
        <?php else: ?>
            <div id="aegir_msg" style="display:none" class="processing_message">&(msg:h);</div>
            <?php endif;        ?>
        <?php $_MIDCOM->uimessages->show(); ?>
