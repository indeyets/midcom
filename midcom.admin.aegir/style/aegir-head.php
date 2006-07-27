<?php echo '<?xml version="1.0" encoding="utf-8" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
  <title>Aegir 2 </title>
<?
  if (method_exists($_MIDCOM,"print_head_elements")) {
     $_MIDCOM->print_head_elements();
  
  }
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
<body <?php if (method_exists($_MIDCOM,"print_jsonload")) {
 $_MIDCOM->print_jsonload(); 
}
?>   >

