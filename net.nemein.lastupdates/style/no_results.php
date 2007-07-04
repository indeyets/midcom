<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
if ($data['query_failure'])
{
    $localized = $data['l10n']->get('indexer returned error, see debug log for details');
}
else
{
    // succefull query but no results
    $localized = sprintf($data['l10n']->get('could not find any documents modified since %s'), strftime('%x', $data['edited_since']));
}
?>
<p>&(localized);</p>
