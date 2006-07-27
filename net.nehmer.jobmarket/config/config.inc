'result_list_page_size' => 10,
'welcome_offer_count' => 5,
'welcome_application_count' => 5,
'welcome_teasers_checkprivs' => false,
'index_to' => null,

'expiration_days' => 45,
'account_topic' => null,

'schemadb' => 'file:/net/nehmer/jobmarket/config/schemadb_default.inc',
'sector_list' => Array('example sector 1', 'example sector 2', 'example sector 3', 'example sector 4', 'example sector 5'),
'location_list' => Array('example location 1', 'example location 2', 'example location 3'),
'type_config' => Array
(
    'jobs' => Array
    (
        'title' => 'Jobs',
        'offer_schema' => 'jobs_example',
        'offer_anonymous_read' => true,
        'offer_anonymous_write' => false,
        'offer_mapping' => Array
        (
            'contact' => Array('firstname', 'lastname'),
            'mobilephone' => 'handphone',
        ),
        'offer_mapping_content' => Array (),
        'application_schema' => 'jobs_example',
        'application_anonymous_read' => true,
        'application_anonymous_write' => false,
        'application_mapping' => Array
        (
            'contact' => Array('firstname', 'lastname'),
            'mobilephone' => 'handphone',
        ),
        'application_mapping_content' => Array (),
        'show_in_search_all' => true,
        'search_by_sectors' => true,
        'search_by_locations' => true,
    ),
),
