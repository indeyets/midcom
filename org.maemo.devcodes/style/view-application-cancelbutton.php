<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
if (   isset($_MIDGARD['user'])
    && !empty($_MIDGARD['user'])
    && $data['application']->applicant == $_MIDGARD['user']
    && $data['application']->can_do('midgard:delete')
    && !$data['application']->has_dependencies())
{
    $delete_url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "application/delete/{$data['application']->guid}.html";
    $delete_text = $data['l10n']->get('cancel application');
?>
<form method="get" action="&(delete_url);">
    <input type="submit" value="&(delete_text);" />
</form>
<?php
}
?>