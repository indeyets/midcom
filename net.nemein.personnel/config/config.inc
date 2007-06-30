'schema' => 'person',
'schemadb' => 'file:/net/nemein/personnel/config/schemadb_default.inc',
'schemadbs' => array (
    'file:/net/nemein/personnel/config/schemadb_default.inc' => 'Personnel Default Schema Database',
),

// Schema for groups
'schema_group' => 'group',

// The GUID of the group to use
'group' => null,

// If this is defined and a valid user GUID, that user is shown first in
// the person index. In case your boss wants publicity ;-).
'preferred_person' => null,

// Persons columns in the index view
'persons_in_row' => 3,

// Sort personnel manually
'sort_order' => 'alphabetic',

// Show unsorted, manually arranged persons
'show_unsorted' => true,

// Show the person in multiple groups
'allow_multiple_memberships' => false,

// Navigation configuration
'display_in_navigation' => 'empty',

// Index view ordering sequence. This is a list of midcom_core_querybuilder::add_order
// compatible field names. Use the "reverse" prefix for decending order.
// Note, that these orders are relative to the *membership* table, not to the *person*
// table.
'index_order' => array('uid.lastname', 'uid.firstname'),

// Set this to true to enable the alphabetically ordered index by default.
// Note that the preferred person setting is only taken into account on the
// corresponding alphabetic page. The unqualified root page will show the 'A'
// index page.
//
// Note that the original alphabetic index pages using GET parameters are not
// supported by this new implementation.
'enable_alphabetical' => false,

// Emailing the user account details
'email_from' => "WWW-data <www-data@{$_SERVER['SERVER_NAME']}>",
'email_subject' => "{$_SERVER['SERVER_NAME']}: your user account details",
'email_content' => "
Your user account to http://{$_SERVER['SERVER_NAME']}{$_MIDGARD['self']} has been either created or updated.

Username:    [USERNAME]
Password:    [PASSWORD]

This is an automatically generated message, do not reply!


-- 
WWW-data <www-data@{$_SERVER['SERVER_NAME']}
[TIMESTAMP]
",

/*
 * These options are currently not implemented
 *
'enable_foaf' => true,
'enable_vcard' => true,

'enable_filtering' => true,
'filter_default' => true,
 */
'csv_export_separator' => ';',
'csv_export_quote' => '"',
'csv_export_decimal' => '.', // NOTE: Must *not* be same as csv_export_separator (ie comma on both is forbidden)
'csv_export_newline' => "\n",
'csv_export_charset' => "ISO-8859-1",
'mb_detect_encoding_list' => 'ASCII,JIS,UTF-8,ISO-8859-1,EUC-JP,SJIS',
'iconv_append_target' => '//TRANSLIT', //Transliterate by default (other valid options '//IGNORE' or null)
'csv_export_content_type' => 'appplication/csv',
