<?php
// Bind the view data, remember the reference assignment:

//$data =& $_MIDCOM->get_custom_context_data('request_data');

     echo $data['l10n']->get('Time').';';
     echo $data['fields'][$data['fields_for_search'][$data['sort_key_1']]].';';
     echo $data['fields'][$data['fields_for_search'][$data['sort_key_2']]].';';
foreach($data['fields'] as $field_key => $field_title)
{
    if(!($data['fields_for_search'][$data['sort_key_1']] == $field_key ||  $data['fields_for_search'][$data['sort_key_2']] == $field_key))
    {
        echo $field_title.';';
    }
}
echo "\n";
foreach($data['articles'] as $article_key => $article)
{
    echo strftime('%x %X',$article->name).';';
    echo $article->$data['sort_key_1'].';';
    echo $article->$data['sort_key_2'].';';
    foreach($data['fields'] as $field_key => $field_title)
    {
        if(!($data['fields_for_search'][$data['sort_key_1']] == $field_key ||  $data['fields_for_search'][$data['sort_key_2']] == $field_key))
        {
            if($data['schema_content']['fields'][$field_key]['location'] == 'parameter')
            {
                echo $article->parameter('midcom.helper.datamanager2', $field_key).';';
            }
            else
            {
                echo $article->$data['schema_content']['fields'][$field_key]['location'].';';
            }
        }
    }
    echo "\n";
}
?>