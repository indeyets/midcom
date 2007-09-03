// 2DO: Add l10n strings

/* This is an example article, taken from the de.linkm.taviewer default
 * schema.
 *
 * When overriding this schema database with component specific data using
 * a snippet that is usually put into the snippetdir 
 * /sitegroup-config/net.nemein.team/
 * and configured using the configuration key "schemadb" all fields that are
 * within the component required section must be copied verbatim (you may
 * change the description field though).
 */

'team' => array (
    'name'        => 'team',
    'description' => 'team',
    'fields'      => array (
    
        'team_name' => Array
        (
	    'title' => 'team name',
	    'storage' => 'name',
	    'type' => 'text',
	    'widget' => 'text',
        ),
	'team_logo' => Array
	(
	    'title' => 'team logo',
	    'storage' => null,
	    'type' => 'image',
	    'type_config' => Array
	    (
	        'filter_chain' => 'resize(800,600)',
		'auto_thumbnail' => Array(100,100),
		'keep_original' => true,
	    ),
	    'widget' => 'image',
	    'hidden' => false,
	),
        'team_description' => Array
        (
	    'title' => 'team introduction text',
	    'storage' => 'parameter',
	    'type' => 'text',
	    'widget' => 'textarea',
        ),
        'team_location' => Array
        (
	    'title' => 'team location',
	    'storage' => 'parameter',
	    'type' => 'text',
	    'widget' => 'text',
        ),

    )
), // team

