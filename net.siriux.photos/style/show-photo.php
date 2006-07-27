<?php
global $view;
global $view_enable_notes;
$data = $view->datamanager->data;
$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $GLOBALS["midcom"]->midgard->self . "midcom-serveattachmentguid-";

$view_object = mgd_get_object_by_guid($view->view);
?>

<h1>&(data['title']);</h1>

<table border="0">
  <tr>
    <td colspan="2">
    <?php
    if ($view_enable_notes)
    {
        ?>
        <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="&(view_object.size_x);" height="&(view_object.size_y);" id="infoarea" align="middle">
        <param name="allowScriptAccess" value="sameDomain" />
        <param name="movie" value="<?php echo MIDCOM_STATIC_URL; ?>/net.siriux.photos/infoarea.swf?imgurl=&(attachmentserver);&(view.view);/view_&(data['name']);&scalefactor=100&noteurl=&(prefix);notes/&(data["name"]);.html" />
        <param name="quality" value="high" />
		<param name="scale" value="noscale" />
        <param name="bgcolor" value="#ffffff" />
        <embed src="<?php echo MIDCOM_STATIC_URL; ?>/net.siriux.photos/infoarea.swf?imgurl=&(attachmentserver);&(view.view);/view_&(data['name']);&scalefactor=100&noteurl=&(prefix);notes/&(data["name"]);.html" quality="high" bgcolor="#ffffff" width="&(view_object.size_x);" height="&(view_object.size_y);" scale="noscale" name="infoarea" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
		</object>
        <?php
    }
    else
    {
        ?>
        <img src="&(attachmentserver);&(view.view);/view_&(data['name']);" />
        <?php
    }
    ?>
    </td>
  </tr>
  </tr>
    <td align="left"><small><?php echo $GLOBALS["view_l10n"]->get("taken by"); ?> <i>&(data['photographer']); <?php
if ($data['taken']['timestamp'] > 0) { echo $data['taken']['local_strfulldate']; } ?></i></small></td>
    <td align="right"><?php if ($view->fullscale) { ?><small><a target="_blank" href="&(attachmentserver);&(view.fullscale);/fullscale_&(data['name']);"><?php echo $GLOBALS["view_l10n"]->get("full scale version"); ?></a></small><?php } ?></td>
  </tr>
  <tr>
    <td colspan="2"><div class="abstract">&(data['abstract']:F);</div>&(data['description']:F);</td>
  </tr>
</table>