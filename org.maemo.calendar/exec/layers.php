<?php

debug_add('---exec-midcom-org.maemo.calendar-layers START---');

debug_print_r('_POST',$_POST);
debug_print_r('_GET',$_GET);

switch ($_GET['action'])
{
    case 'show_update_layer':
        handler_show_update_layer($_GET['layer_id']);
        break;
    case 'update_layer':
        handler_update_layer($_GET['layer_id'], &$_POST);
        break;
    case 'show_update_tag':
        handler_show_update_tag($_GET['layer_id'], $_GET['tag_id']);
        break;
    case 'update_tag':
        handler_update_tag($_GET['layer_id'], $_GET['tag_id'], &$_POST);
        break;
}

function handler_update_layer($layer_id, &$data)
{
    $success = false;
    
    if (isset($data['color']))
    {
        $new_color = str_replace("#", "", $data['color']);
        $success = org_maemo_calendar_common::update_user_calendar_color($new_color, $layer_id);        
    }
    
    if ($success)
    {
        echo 'updated';        
    }
    else
    {
        echo 'not_updated';        
    }
}

function handler_show_update_layer($layer_id)
{
    $html = '';
    
    $form_type = 'layer';
    $form_name = "update-{$form_type}-form";
    
    $current_color = '#' . org_maemo_calendar_common::fetch_user_calendar_color();
    
    $html .= _render_modal_win_header('Edit calendar layer');

    $html .= "<form name=\"{$form_name}\" id=\"{$form_name}\">\n";

    $html .= _render_color_picker($layer_id, $form_type, $current_color);

    $html .= "   <input type=\"submit\" name=\"submit\" value=\"Submit\" />";
    $html .= "   <input type=\"submit\" name=\"cancel\" value=\"Cancel\" />\n";
    $html .= "</form>\n";
  
    $html .= "<script>";
    $html .= "enable_layer_update_form('{$layer_id}');";
    $html .= "</script>\n";
        
    $html .= _render_modal_win_footer();    
    
    echo $html;
}

function handler_update_tag($layer_id, $tag_id, &$data)
{
    $success = false;
    
    if (! empty($data))
    {
        $data['color'] = str_replace("#", "", $data['color']);
        $success = org_maemo_calendar_common::save_user_tag($tag_id, $data, $layer_id);        
    }
    
    if ($success)
    {
        echo 'updated';        
    }
    else
    {
        echo 'not_updated';        
    }
}

function handler_show_update_tag($layer_id, $tag_id)
{
    $users_tags = org_maemo_calendar_common::fetch_available_user_tags();
    
    $current_tag = array();
    foreach ($users_tags as $tag)
    {
        if ($tag['id'] == $tag_id)
        {
            $current_tag = $tag;
        }
    }
    
    $html = '';

    $form_type = 'layer_tag';
    $form_name = "update-{$form_type}-form";
    $type_id = "{$layer_id}-{$tag_id}";
    
    $current_color = '#' . $current_tag['color'];
    $current_name = $current_tag['name'];
    
    $public_yes_status = '';
    $public_no_status = 'checked="checked"';
    if ($current_tag['is_public'])
    {
        $public_yes_status = 'checked="checked"';
        $public_no_status = '';
    }
    
    $html .= _render_modal_win_header('Edit tag');

    $html .= "<form name=\"{$form_name}\" id=\"{$form_name}\">\n";

    $html .= _render_color_picker($type_id, $form_type, $current_color);
    
    $html .= "   <label for=\"{$form_type}-name-{$type_id}\">Name</label>\n";
    $html .= "   <input type=\"text\" name=\"name\" id=\"{$form_type}-name-{$type_id}\" value=\"{$current_name}\" /><br />\n";
    $html .= "   <label for=\"{$form_type}-ispublic-{$type_id}\">Is public?</label>\n";
    $html .= "   <input type=\"radio\" name=\"ispublic\" value=\"1\" {$public_yes_status}/> Yes\n";
    $html .= "   <input type=\"radio\" name=\"ispublic\" value=\"0\" {$public_no_status}/> No\n";
    $html .= "   <br /><br />\n";
    $html .= "   <input type=\"submit\" name=\"submit\" value=\"Submit\" />";
    $html .= "   <input type=\"submit\" name=\"cancel\" value=\"Cancel\" />\n";
    $html .= "</form>\n";
  
    $html .= "<script>";
    $html .= "enable_layer_update_form('{$layer_id}','{$tag_id}');";
    $html .= "</script>\n";

    $html .= _render_modal_win_footer();
        
    echo $html;
}

function _render_modal_win_header($title)
{
    $html = '';

    $html .= "\n<div class=\"calendar-modal-window-content\">\n";
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

function _render_color_picker($type_id, $form_type, $color)
{
    $html = '';
    
    $html .= "<div id=\"color-change-form\">";
    $html .= "   <label for=\"{$form_type}-color-{$type_id}\">Color</label><br />\n";
    $html .= "   <input type=\"text\" name=\"color\" value=\"{$color}\" id=\"{$form_type}-color-{$type_id}\" />\n";
    $html .= "   <div class=\"color-picker-toggle\" onclick=\"jQuery('#{$form_type}-color-{$type_id}-picker').toggle();\">&nbsp;</div>\n";
    $html .= "   <div id=\"{$form_type}-color-{$type_id}-picker\" class=\"color-picker\" style=\"display: none;\">&nbsp;</div>\n";
    $html .= "   <br />";
    $html .= "</div>\n";    

    $html .= "<script>"; 
    $html .= "jQuery('#{$form_type}-color-{$type_id}-picker').farbtastic('#{$form_type}-color-{$type_id}');";
    $html .= "</script>\n";
    
    return $html;
}

debug_add('---exec-midcom-org.maemo.calendar-layers END---');
debug_pop();
$_MIDCOM->finish();
exit();

?>