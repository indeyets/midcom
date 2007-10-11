'object_preferred_fields' => array
(
    'name',
    'title',
    'official',
    'customerId',
    'username',
    'firstname',
    'lastname',
    'prefix',
    'port',
    'code',
    'domain',
),
'object_timerange_fields' => array
(
    'start',
    'end',
    'calstart',
    'caldays',
    'openregistration',
    'closeregistration',
),
'object_address_fields' => array
(
    'street',
    'postcode',
    'city',
    'country',
    'postalStreet',
    'postalPostcode',
    'postalCity',
    'postalCountry',
    'invoiceStreet',
    'invoicePostcode',
    'invoiceCity',
    'invoiceCountry',
),
'object_phone_fields' => array
(
    'phone',
    'handphone',
    'workphone',
    'homephone',
    'fax',
),
'object_location_fields' => array
(
    'latitude',
    'longitude',
    'altitude',
    'bearing',
    'accuracy',
),
'object_skip_fields' => array
(
    'guid',
    'id',
    'sid',
    // Legacy 1.7 metadata fields
    'author',
    'contentauthor',
    'approved',
    'approver',
    'revisor',
    'revised',
    'revision',
    'creator',
    'created',
    'locker',
    'locked',
    'score',
    'contentcreated',
    // Legacy 1.7 parameter fields
    'oid',
    'tablename',
    // Legacy 1.7 attachment fields
    'pid',
    'ptable',
),
'schemadb_parameters' => 'file:/midgard/admin/asgard/config/schemadb_parameters.inc',
'schemadb_permissions' => 'file:/midgard/admin/asgard/config/schemadb_permissions.inc',

/**
 * reflector configuration 
 *
 * All classes must be defined as MgdSchema level names
 */
/*
 Used to replace one classname with another, for example org_openpsa_event
 extends midgard_event, so we wish to display only the more complete type.
 
 Keyed by the type we wish to replace, value is the type we wish to replace to
*/
'class_extends' => array
(
    'midgard_event' => 'org_openpsa_event',
    'midcom_org_openpsa_event' => 'org_openpsa_event',
    'net_nemein_calendar_event_db' => 'org_openpsa_event',
    'midgard_eventmember' => 'org_openpsa_eventmember',
    'midgard_person' => 'org_openpsa_person',
    'midgard_group' => 'org_openpsa_organization',
),
// Classes that never should be listed as root class
'root_class_exceptions_notroot' => array
(
    /* These are core special cases */
    'midgard_parameter',
    'midgard_attachment',
    'midgard_language',
    'midgard_quota',
    /* These use GUID to link to many parents */
    'net_nemein_tag_link',
    'net_nehmer_comments_comment_db',
    'org_openpsa_relatedto',
    'org_routamc_positioning_location',
    /* These we just don't anybody mucking about with */
    'midcom_core_login_session_db',
    'midcom_core_temporary_object_db',
    'midcom_core_privilege_db',
    'midcom_services_at_entry_db',
    /* And then some objects that just don't make sense to show as roots */
    'net_nemein_tag',
    'org_openpsa_query',
),
// Classes that should always be listed as root class (mainly workaround for schema bugs)
'root_class_exceptions_forceroot' => array
(
),

// Always search these fields (used by get_search_properties), keyed by MgdSchema class name
'always_search_fields' => array
(
    'midgard_person' => array('email'),
    'org_openpsa_person' => array('email'),
),
// Never search these fields (used by get_search_properties), keyed by MgdSchema class name
'never_search_fields' => array
(
),

'experimental_root_class_detection' => false,