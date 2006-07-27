'schema' => 'person',
'schemadb' => 'file:/net/nemein/personnel/config/schemadb_default.inc',
'schemadbs' => Array (
    'file:/net/nemein/personnel/config/schemadb_default.inc' => 'Personnel Default Schema Database',
),

// The GUID of the group to use
'group' => null,

// If this is defined and a valid user GUID, that user is shown first in
// the person index. In case your boss wants publicity ;-).
'preferred_person' => null,

// Persons columns in the index view
'persons_in_row' => 3,

// Index view ordering sequence. This is a list of midcom_core_querybuidler::add_order
// compatible field names. Use the "reverse" prefix for decending order.
// Note, that these orders are relative to the *membership* table, not to the *person*
// table.
'index_order' => Array('uid.lastname', 'uid.firstname'),

// Set this to true to enable the alphabetically ordered index by default.
// Note that the preferred person setting is only taken into account on the
// corresponding alphabetic page. The unqualified root page will show the 'A'
// index page.
//
// Note that the original alphabetic index pages using GET parameters are not
// supported by this new implementation.
'enable_alphabetical' => false,

/*
 * These options are currently not implemented
 *
'enable_foaf' => true,
'enable_vcard' => true,

'enable_filtering' => true,
'filter_default' => true,
 */