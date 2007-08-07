<?php

debug_add('---exec-midcom-org.maemo.calendar-buddylist START---');

debug_print_r('_POST',$_POST);
debug_print_r('_GET',$_GET);

switch ($_GET['action'])
{
    case 'search':
        handler_search(&$_POST);
        break;
    case 'refresh_list':
        handler_refresh_list(&$_POST);
        break;        
}

function handler_refresh_list()
{
    echo org_maemo_calendarpanel_buddylist_leaf::refresh_buddylist_items();
}

function handler_search(&$form_data)
{
    debug_add('handler_search');
    $json = '{ ';
    
    $header_items = $form_data['result_headers'];
    
    $results = _search_persons(&$form_data);
    
    $json .= "count: '{$results['count']}',";
    
    if ($results['count'] > 0)
    {
        $json .= "header_items: [";
        foreach ($header_items as $key => $value)
        {
            $json .= "'th', { scope: 'col', align: 'left', class: 'header-item-{$key}' }, '{$value}',";
        }
        $json .= "'th', { scope: 'col', align: 'center', width: '25' }, '&nbsp;'";
        $json .= "],";
        
        $json .= "result_items: [";
        foreach ($results['items'] as $rk => $item)
        {
            $json .= "'tr', { id: 'result-item-{$item->guid}' }, [";
            foreach ($header_items as $key => $value)
            {
                if (isset($item->$key))
                {
                    $value = $item->$key;
                    $json .= "'td', { align: 'left' }, '{$value}',";                    
                }
            }
            $json .= "'td', { width: 25, align: 'center' }, ['img', { src: MIDCOM_STATIC_URL + '/org.maemo.calendarpanel/images/icons/contact-new.png', alt: 'Add', onclick: function(){add_person_as_buddy('{$item->guid}')} }, '']]";
            if (($rk-1) < $results['count'])
            {
                $json .= ", ";
            }
        }
        $json .= "],";
    }
    else
    {
        $json .= "message: '{$results['message']}',";
    }
    
    $json .= 'success: true }';
    
    debug_add('response: '.$json);
    
    echo $json;
}

function _search_persons(&$form_data)
{
    debug_add('_search_persons');

    $results = array(
                    'count' => 0,
                    'items' => array(),
                    'message' => 'No results found'
                    );
    
    // Convert tradiotional wildcard to SQL wildcard
    $search = str_replace('*', '%', $form_data['sq']['string']);
    // Make sure we don't have multiple successive wildcards (performance killer)
    $search = preg_replace('/%+/', '%', $search);

    $search_fields = $form_data['search_fields'];
    $result_ordering = $form_data['result_ordering'];
    
    if (preg_match('/^%+$/', $search))
    {
        debug_add('$search is all wildcards, don\'t search!');
        $results['message'] = 'Can\'t search with only wildcards!';
        
        return $results;
    }

    $component = 'org.maemo.calendar';
    $class = 'org_maemo_calendar_eventparticipant';
    
    $constraints = array();
    $constraints[] = array(
        'field' => 'username',
        'op'    => '<>',
        'value' => '',
    );

    if (!class_exists($class))
    {
        $_MIDCOM->componentloader->load_graceful($component);
    }
    
    $qb = call_user_func(array($class, 'new_query_builder'));
                    
    if (is_array($constraints))
    {
        ksort($constraints);
        reset($constraints);
        foreach ($constraints as $key => $data)
        {
            debug_add("Adding constraint: {$data['field']} {$data['op']} '{$data['value']}'");
            $qb->add_constraint($data['field'], $data['op'], $data['value']);
        }
    }
    
    if (is_array($search_fields))
    {
        $qb->begin_group('OR');
        foreach ($search_fields as $field)
        {
            debug_add("adding search constraint (OR): {$field} LIKE '{$search}'");
            $qb->add_constraint($field, 'LIKE', $search);
        }
        $qb->end_group();        
    }
    
    if (is_array($result_ordering))
    {
        ksort($result_ordering);
        reset($result_ordering);
        foreach ($result_ordering as $field => $order)
        {
            debug_add("adding order: {$field}, {$order}");
            $qb->add_order($field, $order);
        }
    }
    $qb_res = $qb->execute();

    if ($qb_res === false)
    {
        return $results; 
    }
    else
    {
        $items = array();

        $_MIDCOM->componentloader->load_graceful('net.nehmer.buddylist');
        $current_user = $_MIDCOM->auth->user->get_storage();

        foreach ($qb_res as $object)
        {               
            if ($object->guid != $current_user->guid)
            {
                $qb = net_nehmer_buddylist_entry::new_query_builder();
                $qb->add_constraint('account', '=', $current_user->guid);
                $qb->add_constraint('buddy', '=', $object->guid);
                //$qb->add_constraint('isapproved', '=', true);
                $buddies = $qb->execute();
                if (count($buddies) == 0)
                {
                    $items[] = $object;
                }                
            }
        }
        $results['count'] = count($items);
        $results['items'] = $items;
        $results['message'] = '';        
    } 
    
    return $results;
}

debug_add('---exec-midcom-org.maemo.calendar-buddylist END---');
debug_pop();

$_MIDCOM->finish();
exit();

?>