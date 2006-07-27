'schemadb' => '',
'account_topic' => null,
'default_root_category' => '',
'category_list_page_size' => 10,
'index_to' => null,

'allow_add_to_all' => true, // false,

'type_config' => Array (),
'additional_fields' => Array
(
    // Default is required (may be an empty Array though)
    'default' => Array(),
),
'field_mapping' => Array (),

/****************************************************************************
 * Slightly more complex example
 *
'type_config' => Array
(
    'account' => Array
    (
        'readonly_list' => Array('lastname'),
        'hidden_list' => Array(),
        'field_order' => Array('lastname', 'city'),
        'default_view' => 'alpha',
    ),

    // with callback:
    'account' => Array
    (
        'readonly_list' => Array('lastname'),
        'hidden_list' => Array(),
        'field_order' => Array('lastname', 'city'),
        'default_view' => 'customsearch',
        'customsearch' => Array
        (
            'src' => 'file:/...', // '/path/to/snippet/' or null
            'class' => 'some_class_name',
            'config' => 'constructor arg'
        ),
    ),
),

'additional_fields' => Array
(
    // Default is required (may be an empty Array though)
    'default' => Array
    (
        'image_logo' => Array
        (
            'title' => 'Logo',
            'description' => '',
            'helptext' => '',

            'storage' => null,
            'type' => 'image',
            'type_config' => Array
            (
                'filter_chain' => 'resize(150,134);',
                'auto_thumbnail' => Array(50,44),
            ),
            'widget' => 'image',
        ),
    ), // end of default section
),

'field_mapping' => Array
(
    // unknown account default fieldname => branchenbuch schema fieldname
    'firmafirstname' => 'firstname',
    'firmalastname' => 'lastname',
),
*/