<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['datamanager']->get_content_html();
?>
    <li>
        <p>
            <a href="&(prefix);vehicle/<?php echo $data['vehicle']->guid ?>/">&(view['name']:h); (&(view['regno']:h);)</a>
        </p>
    </li>
