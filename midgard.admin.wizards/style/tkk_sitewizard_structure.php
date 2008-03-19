<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('select structure'); ?></h1>

<form method="post" name="tkk_sitewizard_structure">
<!--
      <fieldset>
        <label for="tkk_sitewizard_structure_select_template_none);" class="action_select">
        <input type="radio" name="tkk_sitewizard_structure_select_template" value="none"
        id="tkk_sitewizard_structure_select_template_none);"/><?php echo $data['l10n']->get('no structure'); ?>
        </label>
      </fieldset>
-->
  <?php
    
  foreach($data['structure_templates'] as $key => $structure)
  {
  ?>
      <fieldset>
        <label for="tkk_sitewizard_structure_select_template_&(structure['name']);" class="action_select">
        <input type="radio" name="tkk_sitewizard_structure_select_template" value="&(structure['name']);"
        id="tkk_sitewizard_structure_select_template_&(structure['name']);"/>&(structure['title']);
        </label>
      </fieldset>
  <?php
  }        
  ?>
        
  <input type="submit" name="tkk_sitewizard_structure_submit" value="<?php echo $data['l10n']->get('next'); ?>">
</form>