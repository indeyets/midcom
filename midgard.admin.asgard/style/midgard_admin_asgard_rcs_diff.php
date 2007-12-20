<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$diff   = $data['diff'];
$latest = $data['latest_revision'];
$comment= $data['comment'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(data['view_title']);</h1>
<div class="rcs_navigation">
<?php
echo $data['rcs_toolbar']->render();
?>
</div>
<dl class="midgard_admin_asgard_rcs_diff">
<?php
$changes = false;
foreach ($diff as $attribute => $values)  
{
    if (!array_key_exists('diff', $values)) 
    {
        continue;
    }

    if (!midgard_admin_asgard_handler_object_rcs::is_field_showable($attribute))
    {
        continue;
    }
    
    if (is_array($values['diff']))
    {
        continue;
    }
    
    $changes = true;

    // Three fold fallback in localization
    echo "<dt>". $data['l10n_midcom']->get($data['l10n']->get($attribute)) ."</dt>\n";
    echo "    <dd>\n";
    echo nl2br($values['diff']);
    echo "    </dd>\n";
}

if (!$changes)
{
    echo "<dt>". $data['l10n']->get('no changes in content') ."</dt>\n";
}
?>
</dl>