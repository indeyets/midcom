<?php
global $title;
global $midcom;
global $view_config;
global $view_title;
$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$datamanager = new midcom_helper_datamanager($view_config->get("schemadb"));
if (!$datamanager)
    die("Schema Database is invalid, could not create Datmanager instance");
$schemadb = $datamanager->get_layout_database();
?>

<form method="post" action="&(prefix);update_prefs" enctype="multipart/form-data">


<h2><?php echo $GLOBALS["view_l10n"]->get("settings"); ?></h2>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("title"); ?></div>
<div class="form_field">
  <input class="shorttext" name="fpref_title" type="text" size="50" maxlength="255" value="<?php echo $view_config->get("title"); ?>" />
</div>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("enable detail pages"); ?></div>
<div class="form_field_radiobutton">
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_enable_details" value="1"<?php if ($view_config->get("enable_details") == true) echo ' checked="checked"'; ?> />
    <?php echo $GLOBALS["view_l10n_midcom"]->get("yes"); ?>
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_enable_details" value="0"<?php if ($view_config->get("enable_details") == false) echo ' checked="checked"'; ?> />
    <?php echo $GLOBALS["view_l10n_midcom"]->get("no"); ?>
  </span>
</div>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("show old events"); ?></div>
<div class="form_field_radiobutton">
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_index_list_old" value="1"<?php if ($view_config->get("index_list_old") == true) echo ' checked="checked"'; ?> />
    <?php echo $GLOBALS["view_l10n_midcom"]->get("yes"); ?>
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_index_list_old" value="0"<?php if ($view_config->get("index_list_old") == false) echo ' checked="checked"'; ?> />
    <?php echo $GLOBALS["view_l10n_midcom"]->get("no"); ?>
  </span>
</div>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("sort order"); ?></div>
<div class="form_field">
  <select class="dropdown" name="fpref_sort_order">
<?php foreach ($view_config->get("sort_orders") as $order => $name) { ?>
<?php if ($order == $view_config->get("sort_order")) { ?>
    <option value="&(order);" selected="selected">&(name);</option>
<?php } else { ?>
    <option value="&(order);">&(name);</option>
<?php } // if ?>
<?php } // foreach ?>
    <option value=""><?php echo $GLOBALS["view_l10n"]->get("revert to default"); ?></option>
  </select>
</div>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("create article names from titles"); ?></div>
<div class="form_field_radiobutton">
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_create_name_from_title" value="1"<?php if ($view_config->get("create_name_from_title") == true) echo ' checked="checked"'; ?> />
    <?php echo $GLOBALS["view_l10n_midcom"]->get("yes"); ?>
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_create_name_from_title" value="0"<?php if ($view_config->get("create_name_from_title") == false) echo ' checked="checked"'; ?> />
    <?php echo $GLOBALS["view_l10n_midcom"]->get("no"); ?>
  </span>
</div>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("add seconds after endtime"); ?></div>
<div class="form_field">
  <input class="shorttext" name="fpref_index_add_seconds_after_endtime" type="text" size="50" maxlength="255" value="<?php echo $view_config->get("index_add_seconds_after_endtime"); ?>" />
</div>

<h2><?php echo $GLOBALS["view_l10n"]->get("expert settings"); ?></h2>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("schema database"); ?></div>
<div class="form_field">
  <select class="dropdown" name="fpref_schemadb">
<?php foreach ($view_config->get("schemadbs") as $path => $name) { ?>
<?php if ($path == $view_config->get("schemadb")) { ?>
    <option value="&(path);" selected="selected">&(name);</option>
<?php } else { ?>
    <option value="&(path);">&(name);</option>
<?php } // if ?>
<?php } // foreach ?>
    <option value=""><?php echo $GLOBALS["view_l10n"]->get("revert to default"); ?></option>
  </select>
</div>

<div class="form_description"><?php echo $GLOBALS["view_l10n"]->get("topic schema"); ?></div>
<div class="form_field">
  <select class="dropdown" name="fpref_default_schema">
<?php foreach ($schemadb as $name => $config) { ?>
<?php if ($name == $view_config->get("default_schema")) { ?>
    <option value="&(name);" selected="selected">&(config["description"]);</option>
<?php } else { ?>
    <option value="&(name);">&(config["description"]);</option>
<?php } // if ?>
<?php } // foreach ?>
    <option value=""><?php echo $GLOBALS["view_l10n"]->get("revert to default"); ?></option>
  </select>
</div>

<div class="form_toolbar">
  <input type="submit" name="fpref_submit" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("save"); ?>" />
  <input type="reset" value="<?php echo $GLOBALS["view_l10n_midcom"]->get("reset"); ?>" />
</div>

</form>