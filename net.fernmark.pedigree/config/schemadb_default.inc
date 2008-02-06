'dog' => array (
    'name'        => 'dog',
    'description' => 'dog object',
    'fields'      => array
    (
        'breed' => Array
        (
            'title' => 'breed',
            'storage' => 'breed',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            // PONDER: make select with allow_other ??
        ),
        'sex' => array
        (
            'title' => 'sex',
            'storage' => 'sex',
            'required' => true,
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    NET_FERMARK_PEDIGREE_SEX_MALE => 'male',
                    NET_FERMARK_PEDIGREE_SEX_FEMALE => 'female',
                ),
            ),
            'widget' => 'select',
        ),
        'name' => Array
        (
            'title' => 'name',
            'storage' => 'name',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'titles' => Array
        (
            'title' => 'titles',
            'storage' => 'titles',
            'required' => false,
            'type' => 'text',
            'widget' => 'text',
        ),
        'regno' => Array
        (
            'title' => 'registration number',
            'storage' => 'regno',
            'required' => false,
            'type' => 'text',
            'widget' => 'text',
        ),
        'sire' => array
        (
            'title' => 'sire',
            'storage' => 'sire',
            'required' => false,
            'type' => 'select',
            'type_config' => net_fernmark_pedigree_widget_universalchooser_type_config(),
            'widget' => 'universalchooser',
            'widget_config' => net_fernmark_pedigree_widget_universalchooser_config_dog(NET_FERMARK_PEDIGREE_SEX_MALE),
        ),
        'dam' => array
        (
            'title' => 'dam',
            'storage' => 'dam',
            'required' => false,
            'type' => 'select',
            'type_config' => net_fernmark_pedigree_widget_universalchooser_type_config(),
            'widget' => 'universalchooser',
            'widget_config' => net_fernmark_pedigree_widget_universalchooser_config_dog(NET_FERMARK_PEDIGREE_SEX_FEMALE),
        ),
        'dob' => Array
        (
            'title' => 'date of birth',
            'storage' => 'dob',
            'required' => false,
            'type' => 'date',
            // PONDER: use only ISO_DATE as storage format ?
            'widget' => 'jsdate',
            'widget_config' => array
            (
                'show_time' => false,
            ),
        ),
        'tod' => Array
        (
            'title' => 'date of death',
            'storage' => 'tod',
            'required' => false,
            'type' => 'date',
            // PONDER: use only ISO_DATE as storage format ?
            'widget' => 'jsdate',
            'widget_config' => array
            (
                'show_time' => false,
            ),
        ),
        'owner' => array
        (
            'title' => 'owner',
            'storage' => 'owner',
            'required' => false,
            'type' => 'select',
            'type_config' => net_fernmark_pedigree_widget_universalchooser_type_config(),
            'widget' => 'universalchooser',
            'widget_config' => net_fernmark_pedigree_widget_universalchooser_config_person(),
        ),
        'breeder' => array
        (
            'title' => 'breeder',
            'storage' => 'breeder',
            'required' => false,
            'type' => 'select',
            'type_config' => net_fernmark_pedigree_widget_universalchooser_type_config(),
            'widget' => 'universalchooser',
            'widget_config' => net_fernmark_pedigree_widget_universalchooser_config_person(),
        ),
        'kennel' => array
        (
            'title' => 'kennel',
            'storage' => 'kennel',
            'required' => false,
            'type' => 'select',
            'type_config' => net_fernmark_pedigree_widget_universalchooser_type_config(),
            'widget' => 'universalchooser',
            'widget_config' => net_fernmark_pedigree_widget_universalchooser_config_group(),
        ),
        'tags' => Array
        (
            'title' => 'tags',
            'storage' => null,
            'type' => 'tags',
            'widget' => 'text',
        ),
        'notes' => Array
        (
            'title' => 'notes',
            'storage' => 'notes',
            'required' => false,
            'type' => 'text',
            'type_config' => Array 
            ( 
                'output_mode' => 'html' 
            ),
            'widget' => 'tinymce',
        ),
    ),
), // dog
'result' => array (
    'name'        => 'result',
    'description' => 'result for dog',
    'fields'      => array
    (
        'dog' => array
        (
            'title' => 'dog this result is for',
            'storage' => 'dog',
            'required' => true,
            'type' => 'select',
            'type_config' => net_fernmark_pedigree_widget_universalchooser_type_config(),
            'widget' => 'universalchooser',
            'widget_config' => net_fernmark_pedigree_widget_universalchooser_config_dog(false, 'id', false),
        ),
        'date' => Array
        (
            'title' => 'date',
            'storage' => 'date',
            'required' => true,
            'type' => 'date',
            // PONDER: use only ISO_DATE as storage format ?
            'widget' => 'jsdate',
            'widget_config' => array
            (
                'show_time' => false,
            ),
        ),
        'eventname' => Array
        (
            'title' => 'event',
            'storage' => 'eventname',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'judge' => array
        (
            'title' => 'judge',
            'storage' => 'judge',
            'required' => true,
            'type' => 'select',
            'type_config' => net_fernmark_pedigree_widget_universalchooser_type_config(),
            'widget' => 'universalchooser',
            'widget_config' => net_fernmark_pedigree_widget_universalchooser_config_person(),
        ),
        'result' => Array
        (
            'title' => 'result',
            'storage' => 'result',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        /* do we need this here ?
        'tags' => Array
        (
            'title' => 'tags',
            'storage' => null,
            'type' => 'tags',
            'widget' => 'text',
        ),
        */
        'critique' => Array
        (
            'title' => 'critique',
            'storage' => 'critique',
            'required' => false,
            'type' => 'text',
            'type_config' => Array 
            ( 
                'output_mode' => 'html' 
            ),
            'widget' => 'tinymce',
        ),
    ),
), // result
