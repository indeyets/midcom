<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_form_prefix = 'midgard_admin_sitewizard_';
?>
<script type="text/javascript">
function enableAdvanced() {
    document.getElementById('&(view_form_prefix);host_port').disabled = false; 
    document.getElementById('&(view_form_prefix);host_prefix').disabled = false;
    document.getElementById('&(view_form_prefix);host_component').disabled = false;
}
function disableAdvanced() {
    document.getElementById('&(view_form_prefix);host_port').disabled = true; 
    document.getElementById('&(view_form_prefix);host_prefix').disabled = true;
    document.getElementById('&(view_form_prefix);host_component').disabled = true;
}
function handleAdvanced() {
    selector = document.getElementById('&(view_form_prefix);advanced');
    if (selector.checked) {
        enableAdvanced();
    } else {
        disableAdvanced();
    }
}
</script>
<form method="post" name="&(view_form_prefix);sitegroup_select" action="&(_MIDGARD['uri']);">

  <fieldset>
    <label for="&(view_form_prefix);host_name"><?php echo $data['l10n']->get("host name"); ?></label>
    <input type="text" class="shorttext" name="&(view_form_prefix);host_name" id="&(view_form_prefix);host_name" value="<?php echo $data['current_host']->name; ?>" />
  </fieldset>

  <fieldset>
    <label for="&(view_form_prefix);advanced" class="action_select">
    <input type="checkbox" name="&(view_form_prefix);advanced" id="&(view_form_prefix);advanced"
    onclick="handleAdvanced();"  />
    <?php echo $data['l10n']->get("advanced options"); ?></label>

    <label for="&(view_form_prefix);host_port"><?php echo $data['l10n']->get("host port"); ?></label>
    <input type="text" class="shorttext short" name="&(view_form_prefix);host_port" id="&(view_form_prefix);host_port" value="80" disabled="disabled" />
    
    <label for="&(view_form_prefix);host_prefix"><?php echo $data['l10n']->get("host prefix"); ?></label>
    <input type="text" class="shorttext" name="&(view_form_prefix);host_prefix" id="&(view_form_prefix);host_prefix" disabled="disabled" />
    
    <label for="&(view_form_prefix);host_component"><?php echo $data['l10n']->get("host template"); ?></label>
    <select name="&(view_form_prefix);host_component" id="&(view_form_prefix);host_component" disabled="disabled">
    <?php
    foreach ($data['components'] as $name => $label) 
    {
        $selected = '';
        if ($name == $data['config']->get('default_component'))
        {
            $selected = ' selected="selected"';
        }
        echo "<option value=\"{$name}\"{$selected}>{$label}</option>\n";
    }
    ?>
    </select>
  </fieldset>
    
  <input type="submit" name="&(view_form_prefix);process" class="&(view_form_prefix);next" value="<?php echo $data['l10n_midcom']->get("next"); ?> &raquo;" />
</form>