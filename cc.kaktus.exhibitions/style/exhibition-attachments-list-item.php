<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();
$size = cc_kaktus_exhibitions_viewer::get_image_size($view['image']);

$view['image'] = str_replace('<a', "<a title=\"{$view['title']}\"", $view['image']);
$view['image'] = str_replace('</a>', "<span style=\"display: none;\" class=\"width\">{$size['x']}</span><span style=\"display: none;\" class=\"height\">{$size['y']}</span></a>\n", $view['image']);
?>
    <li>
        &(view['image']:h);
    </li>
