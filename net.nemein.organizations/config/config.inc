'schema' => 'group',
'schemadb' => 'file:/net/nemein/organizations/config/schemadb_default.inc',
'schemadbs' => Array (
    'file:/net/nemein/organizations/config/schemadb_default.inc' => 'Organzations Default Schema Database',
),

// The GUID of the group to use
'group' => null,

// If this is defined and a valid user GUID, that user is shown first in
// the group index. In case your boss wants publicity ;-).
'preferred_group' => null,

// Groups columns in the index view
'groups_in_row' => 3,

'show_nearest' => 3,
'show_nearest_geocoder' => 'city',
'show_nearest_geocoder_redirect' => false,
'show_nearest_geocoder_redirect_url' => '',

// Index view ordering sequence. This is a list of midcom_core_querybuidler::add_order
// compatible field names. Use the "reverse" prefix for descending order.
// Note, that these orders are relative to the *membership* table, not to the *group*
// table.
'index_order' => Array('official', 'city'),

// Set this to true to enable the alphabetically ordered index by default.
// Note that the preferred group setting is only taken into account on the
// corresponding alphabetic page. The unqualified root page will show the 'A'
// index page.
//
// Note that the original alphabetic index pages using GET parameters are not
// supported by this new implementation.
'enable_alphabetical' => false,

'show_only_groups_under_root_group' => true,