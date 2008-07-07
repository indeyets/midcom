'queue_root_dir' => '/var/spool/midgard/replicator_queue/',
'watcher_skip_classes' => array // Classes never to replicate via DBA watcher
(
    'midcom_helper_replicator_subscription',
    'midcom_services_at_entry',
    'midcom_core_login_session_db',
    'midcom_core_temporary_object_db',
),
'exporter_staging2live_check_approvals_for' => array
(
    'midgard_topic',
    'midgard_article',
    'midgard_style',
    'midgard_element', // Style-elements
    'midgard_snippetdir',
    'midgard_snippet',
),
'exporter_staging2live_typefilter_pass_types' => array
(
    0,
),
'exporter_staging2live_typefilter_check_types_for' => array
(
    'midgard_article',
),
