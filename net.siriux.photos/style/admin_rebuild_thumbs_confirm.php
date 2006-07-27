<?php
global $view_title;
global $view_startfrom;
global $view_msg;

$prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $GLOBALS["midcom"]->midgard->self . "midcom-serveattachmentguid-";
?>

<?php if ($view_msg != "") { ?>
<div class="processing_message">&(view_msg:h);</div>
<?php } ?>

<form method="post" action="" enctype="multipart/form-data">

  <input type="hidden" name="startfrom" value="&(view_startfrom);" />

  <h2><?php echo $GLOBALS["view_l10n"]->get("rebuild thumbnails heading"); ?></h2>

  <p><b><?php echo $GLOBALS["view_l10n"]->get("rebuild thumbnails confirmation msg"); ?></b></p>

  <div class="form_toolbar">
    <input type="submit" name="f_submit" value="<?php echo $GLOBALS["view_l10n"]->get("rebuild thumbnails start"); ?>" />
  </div>

</form>