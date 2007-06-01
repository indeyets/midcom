<?php
global $view_title;
global $view;
global $view_startfrom;
global $view_enable_notes;
$view_object = mgd_get_object_by_guid($view->view);

$data = $view->datamanager->data;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $_MIDCOM->midgard->self . "midcom-serveattachmentguid-";
?>

<h2><?php echo $GLOBALS["view_l10n"]->get("edit photo"); ?></h2>

<?php
if ($view_enable_notes)
{
    ?>
    <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="&(view_object.size_x);" height="&(view_object.size_y);" scale="noscale" id="mynotes_2" align="middle" />
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="movie" value="<?php echo MIDCOM_STATIC_URL; ?>/net.siriux.photos/mynotes_2.swf" />
    <param name="quality" value="high" />
    <param name="bgcolor" value="#ffffff" />
    <embed src="<?php echo MIDCOM_STATIC_URL; ?>/net.siriux.photos/mynotes_2.swf?localesurl=&(prefix);notes_locale/&imgurl=&(attachmentserver);&(view.view);/thumbnail_&(view.name);.jpg&scalefactor=100&saveurl=&(prefix);notes_save/&(view.id);.html&noteurl=&(prefix);notes/&(view.id);.html" scale="noscale" quality="high" bgcolor="#ffffff" width="&(view_object.size_x);" height="&(view_object.size_y);" name="mynotes_2" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
    <?php
}
else
{
    ?>
    <img src="&(attachmentserver);&(view.view);/thumbnail_&(view.name);.jpg" />
    <?php
}
?>

<?php $view->datamanager->display_form(); ?>
