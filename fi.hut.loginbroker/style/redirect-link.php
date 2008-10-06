<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$link_html = "<a id='fi_hut_loginbroker_redirect_link' href='{$data['redirect_to']}'>${data['redirect_to']}</a>";
$l_string1 = sprintf($data['l10n']->get('you should have been redirected to %s'), $link_html);
$l_string2 = $data['l10n']->get('please click on the link to continue');
?>
<p>
    &(l_string1:h);, &(l_string2:h);
</p>
<script language="javascript">
    document.getElementById('fi_hut_loginbroker_redirect_link').click();
</script>
