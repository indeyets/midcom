<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$abc = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
    . "category/list/alpha/{$data['type']->guid}/" ;
$all_url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
    . "category/list/{$data['type']->guid}.html" ;
$filter = $data['filter'];

?>

<p><?php
if ($filter === null)
{
    $data['l10n_midcom']->show('all'); ?>&nbsp;<?php
}
else
{
    ?><a href="&(all_url);"><?php $data['l10n_midcom']->show('all'); ?></a>&nbsp;<?php
}

for ($i = 0; $i < strlen($abc); $i++)
{
    $letter = $abc{$i};
    $url = "{$prefix}{$letter}.html";
    if ($letter == $data['filter'])
    {
?>&(letter);&nbsp;<?php
    }
    else
    {
?><a href="&(url);">&(letter);</a>&nbsp;<?php
    }
}
?></p>
