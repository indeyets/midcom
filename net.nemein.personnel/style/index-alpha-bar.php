<?php
// Available Request keys: persons, alpha_filter

$data =& $_MIDCOM->get_custom_context_data('request_data');

if ($data['config']->get('enable_alphabetical'))
{
    $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'alpha/';
    ?>
<p>
<?php
    for ($char = 'A', $i = 0; $i < 26; $i++, $char++)
    {
        if ($char == $data['alpha_filter'])
        {
?>
  &(char);
<?php } else { ?>
  <a href="&(prefix);&(char);.html">&(char);</a>
<?php } } ?>
</p>
<?php } ?>
