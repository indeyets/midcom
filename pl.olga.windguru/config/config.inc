'id_user' => 52388, // YOUR WINDGURU USER ID (IF YOU DO NOT REMEMBER YOUR ID, SEE YOUR SETTINGS PAGE ON WINDGURU.CZ)

'lang' => 'en', // DEFAULT LANGUAGE VERSION, possible values: http://www.windguru.cz/int/help_index.php?sec=distr

'encoding' => '', // if not set then UTF-8 is used, if you only use english version then you will probably never need to set this variable...

'cache_type' => 'file', // CACHE TYPE SETTING - supported values: 'DBA' or 'file'

// Depending on the type you set in 'cache_type' you must also edit following options:

// if you use 'file':
'cache_dir'                 => '/var/local/cache/midgard/midcom/phpscripts', 

'schemadb' => 'file:/pl/olga/windguru/config/schemadb_default.inc',

'status' => array
(
	WG_STATUS_GFS,
	WG_STATUS_NWW3,
	WG_STATUS_NONE,
),