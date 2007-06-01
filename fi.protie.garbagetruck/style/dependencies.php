<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['page_title']:h);</h1>
<p>
<?php
    echo $data['l10n']->get('resource has dependencies and cannot be removed');
?>
</p>