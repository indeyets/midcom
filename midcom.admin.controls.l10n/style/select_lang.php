<?php
global $midcom;

$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$components = array_keys($_MIDCOM->componentloader->manifests);
asort($components);

$i18n =& $midcom->get_service("i18n");
$languages = $i18n->get_language_db();
?>


<h2 class="h2">Select Language and Component</h2>

<form method="POST" action="&(prefix);edit" enctype="multipart/form-data">

<div class="form_description">Language:</div>
<div class="form_field">
  <select class="dropdown" name="f_lang">
<?php
    foreach ($languages as $lang => $lang_desc) {
        $desc = $lang_desc["enname"];
        ?><option value="&(lang);">&(desc);</option><?php
    }
?>
  </select>
</div>

<div class="form_description">Component:</div>
<div class="form_field">
  <select class="dropdown" name="f_component">
    <option value="midcom">MidCOM Core</option>
<?php
    foreach ($components as $path) 
    {
    	$_MIDCOM->componentloader->manifests[$path]->get_name_translated();
        $name = "{$_MIDCOM->componentloader->manifests[$path]->name}: {$_MIDCOM->componentloader->manifests[$path]->name_translated}";
        ?><option value="&(path);">&(name);</option><?php
    }
?>
  </select>
</div>

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="Next &raquo;">
</div>

</form>