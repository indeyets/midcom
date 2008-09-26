<?php
$view = $data['view_host'];

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
    <li>
        <a href="&(prefix);edit/<?php echo $data['host']->guid; ?>/">&(view['name']:h);&(view['prefix']:h);</a>
    </li>
