<?php

debug_add('---exec-midcom-org.maemo.calendar-layers START---');

debug_print_r('_POST',$_POST);
debug_print_r('_GET',$_GET);

switch ($_GET['action'])
{
    case 'show_update':
        handler_show_update($_GET['layer_id']);
        break;
    case 'update':
        handler_update(&$_POST);
        break;
    case 'show_update_tag':
        handler_show_update_tag($_GET['layer_id'], $_GET['tag_id']);
        break;
    case 'update_tag':
        handler_update_tag(&$_POST);
        break;
}

function handler_update(&$data)
{
    echo 'updated';
}

function handler_show_update($layer_id)
{
    $html = '';
    
    $html .= _render_modal_win_header('Edit calendar layer');
    
    $html .= 'change color';
    
    $html .= _render_modal_win_footer();    
    
    echo $html;
}

function handler_update_tag(&$data)
{
    echo 'updated';
}

function handler_show_update_tag($layer_id, $tag_id)
{
    $html = '';
    
    $html .= _render_modal_win_header('Edit tag');

    $html .= 'change color, rename';

    $html .= _render_modal_win_footer();
        
    echo $html;
}

function _render_modal_win_header($title)
{
    $html = '';

    $html .= "<div class=\"calendar-modal-window-content\">\n";
    $html .= "    <h1>{$title}</h1>\n";
    $html .= "    <div onclick=\"close_modal_window();\">Close</div>\n";
    
    return $html;
}

function _render_modal_win_footer()
{
    $html = '';

    $html .= "</div>\n";
    
    return $html;
}

debug_add('---exec-midcom-org.maemo.calendar-layers END---');
debug_pop();
$_MIDCOM->finish();
exit();

?>