<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$preview = $data['preview'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(data['view_title']:h);</h1>
<div class="rcs_navigation">
<?php
echo $data['rcs_toolbar']->render();
?>
</div>
<div class="rcs_navigation">
<?php
if ($data['previous_revision'])
{
    echo "&lt;&lt;\n";
    echo "<a href=\"{$prefix}__mfa/asgard/object/rcs/preview/{$data['guid']}/{$data['previous_revision']}\">". sprintf($data['l10n']->get('version %s'), $data['previous_revision']) ."</a>\n";
    echo "(<em><a href=\"{$prefix}__mfa/asgard/object/rcs/diff/{$data['guid']}/{$data['previous_revision']}/{$data['latest_revision']}/\">{$data['l10n']->get('show differences')}</a></em>)\n";
}

if (   $data['previous_revision']
    && $data['next_revision'])
{
    echo " | ";
}

if ($data['next_revision'])
{
    echo "<a href=\"{$prefix}__mfa/asgard/object/rcs/preview/{$data['guid']}/{$data['latest_revision']}\">". sprintf($data['l10n']->get('version %s'), $data['latest_revision']) ."</a>\n";
    echo "(<em><a href=\"{$prefix}__mfa/asgard/object/rcs/diff/{$data['guid']}/{$data['latest_revision']}/{$data['next_revision']}/\">{$data['l10n']->get('show differences')}</a></em>)\n";
    echo "&gt;&gt;\n";
}
?>
</div>
<dl>
<?php
foreach ($preview as $attribute => $value) 
{
    if ($value == '')
    {
        continue;
    }
    
    if ($value == '0000-00-00')
    {
        continue;
    }
    
    if (!midgard_admin_asgard_handler_object_rcs::is_field_showable($attribute))
    {
        continue;
    }
    
    if (is_array($value))
    {
        continue;
    }
    
    // Three fold fallback in localization
    echo "<dt>". $data['l10n_midcom']->get($data['l10n']->get($attribute)) ."</dt>\n";
    echo "    <dd>" . nl2br($value) . "</dd>\n";
}
?>
</dl>
