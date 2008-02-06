/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */

'config' => array 
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we always display the edit view */
    'fields'      => array 
    (
        'current_release' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.downloads',
            'config_key' => 'current_release',
            'widget' => 'select',
            'widget_select_choices' => net_nemein_downloads_viewer::list_releases(),
            'start_fieldgroup' => array
            ( 
                'title' => 'display settings',
            ),
            'end_fieldgroup' => '',
        ),
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.downloads',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
            'end_fieldgroup' => '',
        )
    )
)
