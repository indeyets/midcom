<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
// Available request data: type_list, mode, result_url, search_data, type

if ($data['type']) { ?>
<h2><?php echo $data['l10n']->get("search {$data['mode']}s") . ": {$data['type_list'][$data['type']]['title']}"; ?></h2>
<?php } else { ?>
<h2><?php $data['l10n']->show("search {$data['mode']}s"); ?></h2>
<?php } ?>

<form action="&(data['result_url']);" method="POST">
