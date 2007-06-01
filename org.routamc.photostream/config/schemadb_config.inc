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
        'schemadb' => array
        (
            'description' => 'schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'org.routamc.photostream',
            'config_key' => 'schemadb',
            'start_fieldgroup' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
            'end_fieldgroup' => '',
        )
    )
)
