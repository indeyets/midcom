<?php
    global $view_categories;
    global $view_config;
    global $view_name;
    global $view_message;
    
    $mainprefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX,0);
    $toolbar = new midcom_helper_toolbar('midcom_toolbar midcom_toolbar_in_content');
    $toolbar->add_item(Array(
        MIDCOM_TOOLBAR_URL => '',
        MIDCOM_TOOLBAR_LABEL => $GLOBALS['view_l10n_midcom']->get('edit'),
        MIDCOM_TOOLBAR_HELPTEXT => '',
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
        MIDCOM_TOOLBAR_ENABLED => true 
    ));
	$toolbar->add_item(Array(
	    MIDCOM_TOOLBAR_URL => '',
	    MIDCOM_TOOLBAR_LABEL => $GLOBALS['view_l10n_midcom']->get('delete'),
	    MIDCOM_TOOLBAR_HELPTEXT => '',
	    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
	    MIDCOM_TOOLBAR_ENABLED => true 
    ));
?>

<?php if (trim($view_message) != "") { ?>
<div class="processing_message">&(view_message);</div>
<?php } ?>

<form method="POST" action="" enctype="multipart/form-data">
<h3><?php echo $GLOBALS["view_l10n"]->get("available categories"); ?>:</h3>
<table cellspacing="4" cellpadding="0" border="0" width="100%">

<?php //******************* CATEGORY LISTING **************** ?>

<tr>
  <td style="font-weight:bold;"><?php echo $GLOBALS["view_l10n"]->get("category name"); ?></td>
  <td style="font-weight:bold;"><?php echo $GLOBALS["view_l10n"]->get("category url"); ?></td>
  <td> </td>
</tr>
<?php foreach ($view_categories as $id => $data) { ?>
<tr>
  <td><a href="&(mainprefix);&(id);/data">&(data['name']);</a></td>
  <td><span style="font-family:monospace;">&(data['url']);</span></td>
  <td width="100%">
<?php
    $toolbar->items[0][MIDCOM_TOOLBAR_URL] = "{$mainprefix}{$id}/topic/edit";
    $toolbar->items[1][MIDCOM_TOOLBAR_URL] = "{$mainprefix}{$id}/topic/delete";
    echo $toolbar->render();
?>
  </td>
</tr>

<?php } ?>

<?php //****************** CREATE CATEGORY ****************** ?>

<tr>
  <td colspan="3" style="padding-top:10px;padding-bottom:5px">
    <h3><?php echo $GLOBALS["view_l10n"]->get("create category"); ?>:</h3>
  </td>
</tr>
<tr>
  <td><?php echo $GLOBALS["view_l10n"]->get("category url"); ?>:</td>
  <td><input type="text" name="form_url" width="50"></td>
  <td></td>
</tr>
<tr>
  <td><?php echo $GLOBALS["view_l10n"]->get("category name"); ?>:</td>
  <td><input type="text" name="form_name" width="50"></td>
  <td><input type="submit" name="form_create_submit" 
        value="<?php echo $GLOBALS["view_l10n_midcom"]->get("create"); ?>"></td>
</tr>

<?php //****************** MOVE ELEMENT ****************** ?>

<tr>
  <td colspan="3" style="padding-top:10px;padding-bottom:5px">
    <h3><?php echo $GLOBALS["view_l10n"]->get("move element"); ?>:</h3>
  </td>
</tr>
<tr>
  <td><?php echo $GLOBALS["view_l10n"]->get("element id"); ?>:</td>
  <td><input type="text" name="form_id" width="50"></td>
  <td></td>
</tr>
<tr>
  <td><?php echo $GLOBALS["view_l10n"]->get("category"); ?>:</td>
  <td>
    <select name="form_dest" size="1">
<?php foreach ($view_categories as $id => $data) { ?>
      <option value="&(id);">&(data['name']);</option>
<?php } ?>  
    </select>
  </td>
  <td><input type="submit" name="form_move_submit"
        value="<?php echo $GLOBALS["view_l10n_midcom"]->get("move"); ?>"></td>
</tr>

</table>
</form>

<div class="aish1"><?php echo $GLOBALS["view_l10n"]->get("meta information"); ?>:</div>

<?php $GLOBALS["view_datamanager"]->display_form(); ?>

<!--
<hr>
<pre>
DATA DUMP

<?php

print_r($GLOBALS["view_config"]);
print_r($GLOBALS["view_categories"]);

?>

</pre>
-->