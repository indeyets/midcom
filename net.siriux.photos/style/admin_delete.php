<?php
global $view_title;
global $view;
global $view_startfrom;

$data = $view->datamanager->data;
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $_MIDCOM->midgard->self . "midcom-serveattachmentguid-";
?>

<form method="post" action="&(prefix);delete/&(view.id);" enctype="multipart/form-data">

  <input type="hidden" name="startfrom" value="&(view_startfrom);" />

  <h2><?php echo $GLOBALS["view_l10n"]->get("delete photo"); ?></h2>

  <img src="&(attachmentserver);&(view.view);/thumbnail_&(view.name);.jpg" />

  <?php $view->datamanager->display_view(); ?>

  <p><b><?php echo $GLOBALS["view_l10n"]->get("are you sure to delete"); ?></b></p>

  <div class="form_toolbar">
    <input type="submit" name="fdelete_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("yes"); ?>" />
    <input type="submit" name="fdelete_cancel" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("no"); ?>" />
  </div>

</form>