<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
</ul>

<p><?php echo sprintf($data['l10n']->get('found %d entries.'), $data['total']); ?></p>
