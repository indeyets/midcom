/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */

'config' => array (
	'name'        => 'config',
	'description' => 'Default Configuration Schema', /* This is a topic */
	'locktimeout' => 0, /* Don't lock this, we always display the edit view */
	'fields'      => array
	(
        'date_form_show_months' => array
        (
            'description' => 'how many months to show in date selector',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'net.nemein.lastupdates',
            'config_key' => 'date_form_show_months',
        ),
	),
),
