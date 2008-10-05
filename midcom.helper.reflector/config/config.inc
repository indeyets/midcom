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
    'org_openpsa_calendar_event' => 'org_openpsa_event',
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
    /* members should not be root classes by MgSchema, workaround a bug */
    'midgard_member',
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
    /* Persons and groups as toplevel is mostly harmful as there can be really many entries */
    'midgard_person',
    'org_openpsa_person',
    'midgard_group',
    'org_openpsa_organization',
    /* Replicator subscriptions have their own specific admin UI */
    'midcom_helper_replicator_subscription',
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
// keyed by icon, values are arrays of classes (__default__ marking the default icon) names, used to determine icon to use for object
'object_icon_magic' => array
(
    'document.png' => array
    (
        '__default__',
    ),
    'stock_folder.png' => array
    (
        'midgard_topic',
        'midgard_snippetdir',
        'midgard_style',
        'midgard_page',
        'net_nemein_discussion_thread',
        'org_openpsa_products_product_group',
    ),
    'stock_people.png' => array
    (
        'net_nehmer_buddylist_entry_db',
        'org_openpsa_campaign',
    ),
    'stock_news.png' => array
    (
        'org_openpsa_notifications_notification',
        'net_nemein_rss_feed',
    ),
    'locale.png' => array
    (
        'org_routamc_positioning_country',
    ),
    'geoclue.png' => array
    (
        'org_routamc_positioning_location',
    ),
    'stock-discussion.png' => array
    (
        'net_nemein_discussion_post',
    ),
    'package.png' => array
    (
        'org_openpsa_products_product',
    ),
    'camera.png' => array
    (
        'org_routamc_photostream_photo',
    ),
),
// This works just like object_icon_magic
'create_type_magic' => array
(
    'new-text.png' => array
    (
        '__default__',
    ),
    'new-dir.png' => array
    (
        'midgard_topic',
        'midgard_page',
        'midgard_snippetdir',
        'midgard_style',
        'net_nemein_discussion_thread',
    ),
    'stock_people-new.png' => array
    (
        'org_openpsa_campaign',
    ),
),

