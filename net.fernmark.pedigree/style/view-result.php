<?php
// Available request keys: controller, indexmode, schema, schemadb

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_result =& $data['view_result'];
?>

<h2>&(view_result['date']:h);: &(view_result['eventname']:h);</h2>
<p class="judge">&(view_result['judge']:h);</p>
<h3 class="result">&(view_result['result']:h);</h3>
<div class="critique">
    &(view_result['critique']:h);
</div>
