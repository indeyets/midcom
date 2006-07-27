
'parameters' => Array
(
    'description' => 'Parameters',
    'fields' => Array
    (
        'parameters' => Array
        (
            'title' => 'url name',
            'description' => 'The ',
            'helptext' => '',
            'storage' => Array
             (
                    'location' => null,
             ),
            
            
            'type' => 'parameters',
            'widget' => 'table',
            'type_config' => array (
                'headers' => array ('Domain', 'Name', 'Value', 'Delete')
            ) ,
            'widget_config' => array (
                'widths' => array( '15em', '10em', '10em' , '1em'),
                'types'  => array ('text','text','text','checkbox')
                
            ) ,
        ),
 
    ), // fields
),