<?php global $midgard; 
   global $view; 
   $guid = $view->guid(); // for later use
   $prefix = $GLOBALS["view_contentmgr"]->viewdata["admintopicprefix"] . "attachment/"; ?>
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
            <?php echo $GLOBALS["view_l10n"]->get("no preview available"); ?>
        <?php } ?>
    </td>
  </tr>
  <tr>
    <td colspan="4" class="contentadm_attachments_commands">
        <a href="&(prefix);delete/&(view.id);" class="aisbutton"><?php midcom_show_style("image-delete");?> <?php echo $GLOBALS["view_l10n_midcom"]->get("delete"); ?></a>
        (&nbsp;<code>&(guid);/&(view.name);</code>&nbsp;)
    </td>
  </tr>