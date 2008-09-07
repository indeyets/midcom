'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'fields'      => array (

        'group' => array
        (
            'title' => 'group',
            'type' => 'select',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.personnel',
                'name' => 'group',
            ),
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array(),
                'allow_other' => true,
                'allow_multiple' => false,
            ),
            'widget' => 'chooser',
            'widget_config' => array
            (
                'class' => 'midcom_db_group',
                'component'   => 'net.nemein.personnel',
                'titlefield' => 'name',
                'id_field' => 'guid',
                'searchfields' => array
                (
                    array('name'=>'ASC'),
                    array('official'=>'ASC'),
                ),
                'result_headers' => array
                (
                    array
                    (
                        'name' => 'name',
                        'title' => 'name',
                    ),
                ),

                'orders' => array(),
                'creation_mode_enabled' => false,
            ),

            'start_fieldgroup' => array
            (
                'title' => 'list settings',
            ),
        ),

        'preferred_person' => array
        (
            'title' => 'preferred person',
            'helptext' => 'this person will be displayed first',
            'type' => 'select',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.personnel',
                'name' => 'preferred_person',
            ),
            'widget' => 'select',
            'type_config' => array
            (
                'options' => $GLOBALS['net_nemein_personnel_members'],
            ),
        ),

        'persons_in_row' => array
        (
            'title' => 'how many persons should be displayed in a row',
            'type' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.personnel',
                'name' => 'persons_in_row',
            ),
            'widget' => 'text',
            'end_fieldgroup' => '',

        ),
        'sort_order' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'sorting order',
            ),
            'title' => 'sorting order',
            'type' => 'select',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.personnel',
                'name' => 'sort_order',
            ),
            'widget' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'alphabetic' => 'alphabetic',
                    'sorted' => 'manually sorted',
                    'sorted and grouped' => 'manually sorted and grouped',
                ),
            ),
        ),

        'enable_alphabetical' => array
        (
            'title' => 'enable alphabetical ordering',
            'type' => 'select',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.personnel',
                'name' => 'enable_alphabetical',
            ),
            'widget' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'end_fieldgroup' => '',
        ),



        'display_in_navigation' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'accessibility',
            ),

            'title' => 'display in navigation',
            'type' => 'select',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.personnel',
                'name' => 'display_in_navigation',
            ),
            'widget' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    ''          => 'default setting',
                    'empty'     => 'display nothing',
                    'personnel' => 'personnel',
                    'groups'    => 'groups',
                ),
            ),
            'end_fieldgroup' => '',
        ),

        'schemadb' => array
        (
            'title' => 'schema database',
            'type' => 'select',
            'widget' => 'select',
            'type_config' => array
            (
                'options' => $GLOBALS['net_nemein_personnel_schemadbs'],
            ),
            'start_fieldgroup' => array
            (
                'title' => 'advanced schema and data settings'
            ),
            'end_fieldgroup' => '',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'net.nemein.personnel',
                'name' => 'schemadb',
            ),

        ),

        

    ),
),