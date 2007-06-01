<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['datamanager']->get_content_html();
?>
    <li>
        <a href="&(prefix);area/<?php echo $data['area']->guid ?>/">&(view['name']:h);</a>
        <?php
        if (   array_key_exists('routes', $data)
            && count($data['routes']) > 0)
        {
            echo "            <ul>\n";
            foreach ($data['routes'] as $route)
            {
                echo "                <li><a href=\"{$prefix}route/{$route->guid}/\">{$route->name}</a></li>\n";
            }
            echo "            </ul>\n";
        }
        ?>
    </li>
