<?php  
    global $view_config;
    $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<div class="aish1">Sitemap Configuration</div>

<form method="post" action="&(prefix);update_prefs" enctype="multipart/form-data">

<div class="aish2">Configuration</div>

<div class="form_description">Display the root topic level:</div>
<div class="form_field_radiobutton">
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_display_root" value="1"<?php 
      if ($view_config->get("display_root") == true) echo " checked"; ?>>Yes
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_display_root" value="0"<?php 
      if ($view_config->get("display_root") == false) echo " checked"; ?>>No
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_display_root" value="">Revert to Site Default
  </span>  
</div>

<div class="form_description">Hide leaves</div>
<div class="form_field_radiobutton">
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_hide_leaves" value="1"<?php 
      if ($view_config->get("hide_leaves") == true) echo " checked"; ?>>Yes
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_hide_leaves" value="0"<?php 
      if ($view_config->get("hide_leaves") == false) echo " checked"; ?>>No
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_hide_leaves" value="">Revert to Site Default
  </span>  
</div>

<?php if (! $view_config->get("hide_leaves")) { ?>

<div class="form_description">Show leaves first</div>
<div class="form_field_radiobutton">
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_leaves_first" value="1"<?php 
      if ($view_config->get("leaves_first") == true) echo " checked"; ?>>Yes
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_leaves_first" value="0"<?php 
      if ($view_config->get("leaves_first") == false) echo " checked"; ?>>No
  </span>
  <span class="form_description_radiobutton">
    <input type="radio" name="fpref_leaves_first" value="">Revert to Site Default
  </span>  
</div>

<?php } // leaves are not hidden ?>

<div class="form_description">
  GUID of the topic used as a root of the sitemap. 
  Leave empty to use the root topic of your website automatically.
</div>
<div class="form_field">
  <input class="shorttext" name="fpref_root_topic" maxlength="80" value="<?php
      echo $view_config->get("root_topic");
  ?>">
</div>

<div class="form_toolbar">
  <input type="submit" name="fpref_submit" value="Save">
  <input type="reset" value="Reset">
</div>

</form>