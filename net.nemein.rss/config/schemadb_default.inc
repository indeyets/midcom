'default' => array
(
    'description' => 'feed',
    'l10n_db'     => 'net.nemein.rss',
    'fields'      => array
    (
        'title' => Array
        (
            'title' => 'feed title',
            'storage' => 'title',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
            'index_method' => 'title',
        ),
        'url' => Array
        (
            'title' => 'feed url',
            'storage' => 'url',
            'required' => true,
            'type' => 'text',
            'widget' => 'text',
        ),
        'keepremoved' => Array
        (
            'title'   => 'keep removed items',
            'storage' => 'keepremoved',
            'type'    => 'boolean',
            'widget'  => 'checkbox',
        ),
        'autoapprove' => Array
        (
            'title'   => 'approve new items automatically',
            'storage' => 'autoapprove',
            'type'    => 'boolean',
            'widget'  => 'checkbox',
            'hidden' => !$GLOBALS['midcom_config']['metadata_approval'],
        ),
    	'defaultauthor' => array
        (
            'title' => 'default author for items',
            'storage' => 'defaultauthor',
            'type' => 'select',
            'type_config' => array
            (
                 'require_corresponding_option' => false,
                 'options' => array(),
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class'       => 'midcom_db_person',
                'component'   => 'org.openpsa.contacts',
                'titlefield'  => 'name',
                'idfield'     => 'id',
                'constraints' => array
                (
                ),
                'searchfields'  => array
                (
                    'lastname',
                    'firstname',
                    'username'
                ),
                'orders'        => array
                (
                    array('lastname'    => 'ASC'), 
                    array('firstname'  => 'ASC'), 
                ),
            ),            
        ),
    )
),

