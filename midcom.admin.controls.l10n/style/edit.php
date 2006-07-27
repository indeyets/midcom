<?php
global $midcom;
global $view_component;
global $view_strings;
global $view_lang;

$prefix = $midcom->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

// available languages
$i18n =& $midcom->get_service("i18n");
$languages = $i18n->get_language_db();

// available compoments
$components = array_keys($_MIDCOM->componentloader->manifests);
asort($components);
?>

<h2 class="aish2">Edit Strings for &(view_component); [&(view_lang);]</h2>

<form method="POST" action="&(prefix);save" enctype="multipart/form-data">

<table border="1" cellspacing="0" cellpadding="2">
  <tr>
    <td>String ID</td>
    <td>en (Default)</td>
    <?php if ($view_lang != "en") { ?><td>&(view_lang);</td><?php } ?>
  </tr>

  <tr>
    <td><input type="text" name="new_stringid" size="35" /></td>
    <td><textarea type="text" name="new_en" cols="40" rows="2" wrap="virtual"></textarea></td>
    <?php if ($view_lang != "en") { ?><td><textarea type="text" name="new_loc" cols="40" rows="2" wrap="virtual"></textarea></td><?php } ?>
  </tr>

<?php

$count = 0;
foreach ($view_strings as $id => $str) {
    $en = $str["en"];
    $loc = $str[$view_lang];
    ?>
  <tr>
    <td><b>&(id);</b></td>
    <?php if ($view_lang != "en") { ?><td>&(en);</td><?php } ?>
    <td><input type="hidden" name="string_id[&(count);]" value="&(id);" /><textarea name="string_value[&(count);]" cols="40" rows="2" wrap="virtual"><?=htmlspecialchars($loc) ?></textarea></td>
  </tr>
<?php

    $count++;
}

?>

</table>

<input type="hidden" name="f_component" value="&(view_component);" />
<input type="hidden" name="f_lang" value="&(view_lang);" />

<div class="form_toolbar">
  <input type="submit" name="f_submit" value="Save">
  <input type="reset" value="Reset">
</div>

</form>


<form method="POST" action="&(prefix);edit" enctype="multipart/form-data">
<input type="hidden" name="f_component" value="&(view_component);" />

<div class="form_toolbar"> 
  <select class="dropdown" name="f_lang">
<?php
    foreach ($languages as $lang => $lang_desc) {
        $desc = $lang_desc["enname"];
        ?><option value="&(lang);"<?php if ($lang == $view_lang) echo " selected"; ?>>&(desc);</option><?php
    }
?>
  </select><br />
  
  <select class="dropdown" name="f_component">
    <option value="midcom">MidCOM Core</option>
<?php
    foreach ($components as $path) 
    {
    	$_MIDCOM->componentloader->manifests[$path]->get_name_translated();
        $name = "{$_MIDCOM->componentloader->manifests[$path]->name}: {$_MIDCOM->componentloader->manifests[$path]->name_translated}";
        ?><option value="&(path);"<?php if ($path == $view_component) echo " selected"; ?>>&(name);</option><?php
    }
?>
  </select><br />
  <input type="submit" value="Go" />
</div>

</form>
