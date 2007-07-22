<?php

debug_add('---exec-midcom-org.maemo.calendar-shelf START---');

debug_print_r('_POST',$_POST);
debug_print_r('_GET',$_GET);

switch ($_GET['action'])
{
    case 'load':
        handler_load();
        break;
    case 'save':
        handler_save(&$_POST['data']);
        break;
    case 'update_list':
        if (isset($_POST['data']))
        {
            handler_update_list(&$_POST['data']);            
        }
        else
        {
            handler_update_list();
        }
        break;        
    case 'empty':
        handler_empty();
        break;
}

function load_shelf_contents()
{
    $shelf_items = array();
    
    $session =& new midcom_service_session('org.maemo.calendarpanel');
    if ($session->exists('shelf_contents'))
    {
        $shelf_items = json_decode($session->get('shelf_contents'));
    }
    unset($session);
    
    return $shelf_items;
}

function save_shelf_contents(&$data)
{
    $session =& new midcom_service_session('org.maemo.calendarpanel');
    $session->set('shelf_contents',json_encode($data));
    unset($session);
}

function handler_load()
{
    debug_add('handler_load');
    
    $shelf_items = load_shelf_contents();
    
    if (! is_array($shelf_items))
    {
        $shelf_items = array();
    }    
    
    debug_print_r('shelf_items',$shelf_items);
    
    echo json_encode($shelf_items);
}

function handler_save(&$data)
{
    debug_add('handler_save');
    
    $decoded_data = json_decode($data);
    
    if (! is_array($decoded_data))
    {
        $decoded_data = array();
    }
    
    save_shelf_contents($decoded_data);
    
    echo 'saved';
}

function handler_update_list(&$data=false)
{
    echo org_maemo_calendarpanel_shelf_leaf::regenerate_list($data);
}

function handler_empty()
{
    $session =& new midcom_service_session('org.maemo.calendarpanel');
    if ($session->exists('shelf_contents'))
    {
        $session->remove('shelf_contents');
        echo 'emptied';
    }
    else
    {
        echo 'already empty';        
    }
    
    unset($session);
}

debug_add('---exec-midcom-org.maemo.calendar-shelf END---');

debug_pop();

?>