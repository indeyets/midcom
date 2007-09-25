/**
 * This is the configuration file for the module.
 * This is where you set default values for different configuration variables
 */
/* The sort order to use */
'sort_order' => 'score',

/* The schema database to use */
'schemadb' => 'file:/net/nemein/teams/config/schemadb_default.inc',

/* The schema in the database to use to edit articles 
 * note the name here and in schemadb_default.inc
 * This is used when having created the complete crud component - not just the basic one. 
 * */
'schema_team_create' => 'team',

'teams_root_guid' => 'bd153978547111dcbbc6cf47b1b3dd3fdd3f',
'create_team_home' => true,
'create_team_home_plugin' => 'create_team_home',
'display_teams_per_page' => 1,
'display_pages' => 10, 
'max_players_per_team' => 10,
// Should net.nehmer.mail be user to private massage team manager
'pm_manager' => true,
// Link text in the pm
'private_pendings_link' => 'approve/decline',

'on_create_profile' => null,
// 'on_create_profile' => Array
// (
//     'callback' => 'callback_function_name',
//     'autoload_snippet' => 'snippet_name', // optional
//     'autoload_file' => 'filename', // optional
// ),

/**
 * Plugins and other miscellaneous configuration keys
 */
// see the docs of the viewer/admin classes for details about the plugin handlers
/*
'plugins' => array
(
    'create_team_home' => Array
    (
        'class' => 'midgard_admin_sitewizard_plugin',
        'src' => 'file:/net/nemein/teams/plugins/midgard_admin_sitewizard_plugin.php',
        'name' => 'Team home creation',
        'config' => Array
        (
            'sitewizard_path' => '/usr/share/php/midcom/static/data/midgard_admin_sitewizard.php',
            'host_guid' => '983e66725acd11db845a197adaa843af43af',
            'creation_root_topic_guid' => '6d3af3384be911dcb7b0b3bf4b275d0d5d0d',
            'creation_root_topic_parent_guid' => '6d3af3384be911dcb7b0b3bf4b275d0d5d0d',
            'creation_root_topic_component' => 'net.nehmer.static',
            'creation_root_topic_parameters' => array(),
            'creation_root_group_guid' => '94f058364f0f11dc93f803ebc4b67c0c7c0c',
            'creation_root_group_parent_guid' => '',
            'creation_root_group_name' => '',
            'verbose' => true,
            'structure_config_path' => '/usr/share/midgard/svn/midcom-trunk/net.nemein.teams/plugins/leet_team_home_structure.inc',
        ),
    ),
),
*/