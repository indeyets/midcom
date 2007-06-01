<?php
global $view_title;
global $view_startfrom;
global $view_msg;

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$attachmentserver = $_MIDCOM->midgard->self . "midcom-serveattachmentguid-";
?>

<?php if ($view_msg != "") { ?>
<div class="processing_message">&(view_msg:h);</div>
<?php } ?>

<form method="post" action="" enctype="multipart/form-data">

  <input type="hidden" name="startfrom" value="&(view_startfrom);" />

  <h2><?php echo $GLOBALS["view_l10n"]->get("sync article created heading"); ?></h2>

  <p><b><?php echo $GLOBALS["view_l10n"]->get("sync article created confirmation msg"); ?></b></p>

  <div class="form_toolbar">
    <input type="submit" name="f_submit" value="<?php echo $GLOBALS["view_l10n"]->get("sync article created start"); ?>" />
  </div>

</form>