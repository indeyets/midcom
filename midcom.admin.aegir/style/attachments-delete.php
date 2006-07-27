<?php
global $view;
global $midgard;
global $view_l10n;
global $view_l10n_midcom;
$prefix = $GLOBALS["view_contentmgr"]->viewdata["admintopicprefix"] . "attachment/";
?>

<h1 class="aish1"><?php echo $view_l10n->get("delete attachment"); ?> [&(view.id);]</h1>


<form method="post" action="&(prefix);deleteok/&(view.id);" enctype="multipart/form-data">

<table border="0" cellspacing="0" class="contentadm_attachments">
  <tr>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("filename"); ?></td>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("short description"); ?></td>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("type"); ?></td>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("preview"); ?></td>
  </tr>
  <tr valign="top">
    <td class="contentadm_attachments_filename">
        <a href="&(midgard.self);midcom-serveattachment-&(view.id);/&(view.name:u);"><?php midcom_show_style("image-download");?> &(view.name);</a>
    </td>
    <td class="contentadm_attachments_title">
        &(view.title);
    </td>
    <td class="contentadm_attachments_mimetype">
        &(view.mimetype);
    </td>
    <td class="contentadm_attachments_preview">
        <?php if (substr($view->mimetype,0,5) == "image") { ?>
          <div class="contentadm_attachments_preview">
            <img class="contentadm_attachments_preview" src="&(midgard.self);midcom-serveattachment-&(view.id);/&(view.name:u);">
          </div>
        <?php } else { ?>
          <?php echo $view_l10n->get("no preview available"); ?>
        <?php } ?>
    </td>
  </tr>
  <tr>
    <td colspan="4" class="contentadm_attachments_toolbar">
      <input type="submit" name="f_submit" value="<?php echo $view_l10n_midcom->get("delete"); ?>">
      <input type="submit" name="f_cancel" value="<?php echo $view_l10n_midcom->get("cancel"); ?>">
    </td>
  </tr>
</table>

</form>

