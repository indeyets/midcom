'name' => 'org.routamc.gallery',
'purecode' => false,
'version' => '1.2.0beta1',
'state' => 'beta',
'privileges' => array(),
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Photo gallery',
    'description' => 'Collection of photos in photostream, based on different configurable criteria',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@nemein.com',
            'role' => 'lead',
        ),
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
            'active' => 'no',
        ),
        'rambo' => array
        (
            'name' => 'Eero af Heurlin',
            'email' => 'eero.afheurlin@nemein.com',
            'role' => 'lead',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'lead',
        ),
    ),
    'dependencies' => array
    (
        'org.routamc.photostream' => array(),
        'midcom.helper.datamanager2' => array(),
        'net.nemein.tag' => array(),
        'org.openpsa.qbpager' => array(),
        'midcom' => array(),
    ),
),
'watches' => array
(
    array
    (
        'classes' => array
        (
            'org_routamc_photostream_photo_dba',
        ),
        'operations' => 'MIDCOM_OPERATION_DBA_DELETE',
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'org_routamc_gallery_cron_gallery_sync',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),
