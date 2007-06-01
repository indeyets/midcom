</ul>
<div style="clear: left;"></div>
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

if (   isset($data['qb'])
    && is_object($data['qb'])
    && method_exists($data['qb'], 'show_pages'))
{
    $data['qb']->show_pages();
}
?>
</div>