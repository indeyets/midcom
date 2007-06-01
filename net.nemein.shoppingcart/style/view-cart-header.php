<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['title']);</h1>
<table class="net_nemein_shoppingcart">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('item'); ?></th>
            <th><?php echo $data['l10n']->get('units'); ?></th>
            <th><?php echo $data['l10n']->get('price'); ?></th>
        </tr>
    </thead>
    <tbody>
