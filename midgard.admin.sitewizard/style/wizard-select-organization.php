<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_form_prefix = 'midgard_admin_sitewizard_';
?>
<script type="text/javascript">
function handleQuota() {
    if (!document.getElementById('&(view_form_prefix);quota_enable').disabled && document.getElementById('&(view_form_prefix);quota_enable').checked) {
        document.getElementById('&(view_form_prefix);sitegroup_quota').disabled = false;
    } else {
        document.getElementById('&(view_form_prefix);sitegroup_quota').disabled = true;    
    }
}
function enableCreate() {
    document.getElementById('&(view_form_prefix);sitegroup_name').disabled = false;
    document.getElementById('&(view_form_prefix);sitegroup_admin').disabled = false;
    document.getElementById('&(view_form_prefix);sitegroup_password').disabled = false;
    <?php
    if ($data['enable_quota'])
    {
        ?>
        document.getElementById('&(view_form_prefix);quota_enable').disabled = false;
        handleQuota();
        <?php
    }
    ?>
}
function disableCreate() {
    document.getElementById('&(view_form_prefix);sitegroup_name').disabled = true;
    document.getElementById('&(view_form_prefix);sitegroup_admin').disabled = true;
    document.getElementById('&(view_form_prefix);sitegroup_password').disabled = true;
    <?php
    if ($data['enable_quota'])
    {
        ?>
        document.getElementById('&(view_form_prefix);quota_enable').disabled = true;
        handleQuota();
        <?php
    }
    ?>
}
function enableExisting() {
    document.getElementById('&(view_form_prefix);sitegroup_id').disabled = false;
}
function disableExisting() {
    document.getElementById('&(view_form_prefix);sitegroup_id').disabled = true;
}
</script>
<form method="post" name="&(view_form_prefix);sitegroup_select" action="<?php echo $_MIDGARD['uri']; ?>">
<?php
if ($data['17_compatibility'])
{
    // This is hack for 1.7 setsitegroup() issues
    ?>
    <fieldset>
        <div class="explanation">
          <?php echo $data['l10n']->get("you need to authenticate"); ?>
        </div>
    
        <label for="&(view_form_prefix);sg0_admin"><?php echo $data['l10n_midcom']->get("username"); ?></label>
        <input type="text" class="shorttext" name="&(view_form_prefix);sg0_admin" id="&(view_form_prefix);sg0_admin"/>
        
        <label for="&(view_form_prefix);sg0_password"><?php echo $data['l10n_midcom']->get("password"); ?></label>
        <input type="password" class="shorttext" name="&(view_form_prefix);sg0_password" id="&(view_form_prefix)sg0_password" />
    </fieldset>
    <?php
}
?>
  <fieldset>
    <div class="explanation">
      <?php echo $data['l10n']->get("midgard uses sitegroups for organizing data"); ?>
    </div>
    <label for="&(view_form_prefix);create_new" class="action_select">
    <input type="radio" name="&(view_form_prefix);sitegroup_action" value="create_new"
    id="&(view_form_prefix);create_new" checked="checked"
    onclick="enableCreate(); disableExisting();"  />
    <?php echo $data['l10n']->get("create organization"); ?></label>

    <label for="&(view_form_prefix);sitegroup_name"><?php echo $data['l10n']->get("organization name"); ?></label>
    <input type="text" class="shorttext" name="&(view_form_prefix);sitegroup_name" id="&(view_form_prefix);sitegroup_name" />
    
    <label for="&(view_form_prefix);sitegroup_admin"><?php echo $data['l10n_midcom']->get("username"); ?></label>
    <input type="text" class="shorttext" name="&(view_form_prefix);sitegroup_admin" id="&(view_form_prefix);sitegroup_admin"/>
    
    <label for="&(view_form_prefix);sitegroup_password"><?php echo $data['l10n_midcom']->get("password"); ?></label>
    <input type="password" class="shorttext" name="&(view_form_prefix);sitegroup_password" id="&(view_form_prefix);sitegroup_password" />

    <?php
    // Display this option only if quota is enabled in Midgard Core
    if ($data['enable_quota'])
    {
        ?>
        <label for="&(view_form_prefix);quota_enable"><input type="checkbox" 
          name="&(view_form_prefix);quota_enable" id="&(view_form_prefix);quota_enable" onclick="handleQuota();"  
          /><?php echo $data['l10n']->get("organization quota"); ?></label>
          <label for="&(view_form_prefix);sitegroup_quota"><input type="text" class="shorttext short inline" name="&(view_form_prefix);sitegroup_quota" id="&(view_form_prefix);sitegroup_quota" 
          disabled="disabled" /><?php echo $data['l10n']->get("Mb"); ?></label>
        <?php
    }
    ?>
  </fieldset>
<?php 
// Don't show organization selection list if no SGs exist
if (count($data['sitegroups']) > 0) 
{ 
    ?>
    <fieldset>
        <div class="explanation">
            <?php echo $data['l10n']->get("every sitegroup can have multiple sites"); ?>
        </div>
        <label for="&(view_form_prefix);use_existing" class="action_select">
            <input type="radio" name="&(view_form_prefix);sitegroup_action" value="use_existing"
                id="&(view_form_prefix);use_existing" 
                onclick="disableCreate(); enableExisting();" />
            <?php echo $data['l10n']->get("use existing organization"); ?>
        </label>
        <select name="&(view_form_prefix);sitegroup_id" id="&(view_form_prefix);sitegroup_id" disabled="disabled">
            <?php
            foreach ($data['sitegroups'] as $sitegroup) 
            {
                echo "<option value=\"{$sitegroup->id}\">{$sitegroup->name}</option>\n";
            }
            ?>
        </select>
    </fieldset>
    <?php 
} 
?>
<input type="submit" name="&(view_form_prefix);process" class="&(view_form_prefix);next" value="<?php echo $data['l10n_midcom']->get("next"); ?> &raquo;" />
</form>
