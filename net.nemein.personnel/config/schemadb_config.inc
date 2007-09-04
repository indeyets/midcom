'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array (

        /**** Display settings ****/
        'group' => array
        (
            'description' => 'group',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'group',
            'widget'   => 'select',
            /* TODO: This can be quite slow */
            'widget_select_choices' => midcom_helper_datamanager_selectlist_allgroups(),
            'start_fieldgroup' => array
            (
                'title' => 'list settings',
            ),
            'end_fieldgroup' => '',
        ),

         /**** Schema settings ****/
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'schemadb',
			'widget' => 'select',
   			'widget_select_choices' => $GLOBALS['net_nemein_personnel_schemadbs'],
            'start_fieldgroup' => array
            (
                'title' => 'advanced schema and data settings'
            ),
            'end_fieldgroup' => '',
        ),

        
        'sort_order' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'sorting order',
            ),
            'description' => 'sorting order',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'sort_order',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                'alphabetic' => 'alphabetic',
                'sorted' => 'manually sorted',
                'sorted and grouped' => 'manually sorted and grouped',
            ),
        ),
/* Deprecated
        'manual_order' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'sorting order',
            ),
            'description' => 'sort personnel manually',
            'helptext' => 'overrides alphabetical order',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'manual_order',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
/* */
        'enable_alphabetical' => array
        (
            'description' => 'enable alphabetical ordering',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'enable_alphabetical',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
        'persons_in_row' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'accessibility',
            ),
            'description' => 'how many persons should be displayed in a row',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'persons_in_row',
        ),
        'preferred_person' => array
        (
            'description' => 'preferred person',
            'helptext' => 'this person will be displayed first',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'preferred_person',
            'widget' => 'select',
            'widget_select_choices' => $GLOBALS['net_nemein_personnel_members'],
        ),
        'display_in_navigation' => array
        (
            'description' => 'display in navigation',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'display_in_navigation',
            'widget' => 'select',
            'widget_select_choices' => array
            (
                ''          => 'default setting',
                'empty'     => 'display nothing',
                'personnel' => 'personnel',
                'groups'    => 'groups',
            ),
        ),
/* Not yet implemented
        'enable_foaf' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'miscellaneous settings'
            ),
            'description' => 'enable foaf',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'enable_foaf',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),

        'enable_vcard' => array
        (
            'description' => 'enable vcard',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'enable_vcard',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),

        'enable_filtering' => array
        (
            'description' => 'enable filtering',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'enable_filtering',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
        ),
        'filter_default' => array
        (
            'description' => 'filter default',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.personnel',
            'config_key' => 'filter_default',
            'widget' => 'config_radiobox',
            'widget_radiobox_choices' => array
            (
                '' => 'default setting',
                '1' => 'yes',
                '0' => 'no',
            ),
            'end_fieldgroup' => '',
        ),
/* */
    ),
),