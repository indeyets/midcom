<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div id="cc_kaktus_exhibitions_sorter">
    <h1><?php echo sprintf($data['l10n']->get('%s for event %s'), $data['l10n']->get($data['leaf_type']), $data['event']->title); ?></h1>
    <p>
        <?php echo $data['l10n']->get('drag and drop to sort'); ?>
    </p>
    <form method="post" class="datamanager datamanager2" action="&(_MIDGARD['uri']:h);">
        <ul class="sortable &(data['leaf_type']:h);" id="cc_kaktus_exhibitions_leaf_list">
