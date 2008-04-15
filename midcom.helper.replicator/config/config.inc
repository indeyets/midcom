'queue_root_dir' => '/var/spool/midgard/replicator_queue/',
'log_filename'   => "{$GLOBALS['midcom_config']['log_filename']}.replicator",
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
