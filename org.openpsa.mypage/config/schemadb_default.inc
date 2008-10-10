'default' => array
(
    'description' => 'my summary',
    'fields'      => array
    (
        'hours' => Array
        (
            'title' => 'hours',
            'storage' => null,
            'type' => 'composite',
            'type_config' => Array
            (
                'child_class'                 => 'org_openpsa_projects_hour_report',
                'child_schemadb'              => 'file:/org/openpsa/projects/config/schemadb_default_hours.inc',
                'child_foreign_key_fieldname' => 'person',
                'style_element_name'          => 'hours',
                'area_element'                => 'tr',
                'orders'                      => array
                (
                    'date'    => 'ASC',
                    'created' => 'ASC',
                ),
                'defaults'                    => array
                (
                    'date'    => time(),
                    'person'  => $_MIDGARD['user'],
                ),
                'wide_mode'                 => true,
            ),
            'widget' => 'composite',
        ),
    )
),