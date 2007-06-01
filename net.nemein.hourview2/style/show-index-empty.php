<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$node = $data['node'];
?>
<h1>&(node.extra);</h1>

<p>
    <?php 
    echo $data['l10n']->get('no hour reports');
    ?>
</p>