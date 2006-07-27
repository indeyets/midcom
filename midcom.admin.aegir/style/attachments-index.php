<?php
global $view_l10n;
global $view_l10n_midcom;
midcom_show_style ("attachments-index-title");
?>

<table border="0" cellspacing="0" class="contentadm_attachments">
  <tr>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("filename"); ?></td>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("short description"); ?></td>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("type"); ?></td>
    <td class="contentadm_attachments_heading"><?php echo $view_l10n->get("preview"); ?></td>
  </tr>