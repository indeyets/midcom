'default' => array
(
    'description' => 'bannedwords',
    'fields'      => array
    (
        'language' => Array
        (
          // COMPONENT-REQUIRED
         'title' => 'language',
             'storage' => 'language',
             'type' => 'select',
         'type_config' => array
         (
             'options' => array('fi' => 'Fi', 'en' => 'En'),
             ),
         'widget' => 'select',
        ),
        'word' => Array
        (
          // COMPONENT-REQUIRED
         'title' => 'banned word',
             'storage' => 'bannedWord',
             'type' => 'text',
         'widget' => 'text',
        ),
        'description' => Array
        (
          // COMPONENT-REQUIRED
         'title' => 'description',
             'storage' => 'description',
             'type' => 'text',
         'widget' => 'text',
        ),
    ),
),
