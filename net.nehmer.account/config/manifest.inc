'name' => 'net.nehmer.account',
'icon' => 'stock-icons/16x16/stock_person.png',
'version' => '1.2.0beta1',
'state' => 'beta',
'class_definitions' => array
(
    'midcom_dba_classes.inc',
),
'package.xml' => array
(
    'summary' => 'Account management and registration',
    'description' => 'Allows users to manage their accounts. New users can register for new accounts as well.',
    'maintainers' => array
    (
        'torben' => array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
        ),
        'juhana' => array
        (
            'name' => 'Juhana Hirvilahti',
            'email' => 'juhana@nemein.com',
            'role' => 'developer',
        ),
        'adrenalin' => array
        (
            'name' => 'Arttu Manninen',
            'email' => 'arttu@kaktus.cc',
            'role' => 'lead',
        ),
        'w_i' => array
        (
            'name' => 'Jerry Jalava',
            'email' => 'jerry.jalava@gmail.com',
            'role' => 'developer',
        ),
    ),
    'dependencies' => array
    (
        'midcom.helper.datamanager2' => array(),
        'midcom' => array(),
        'org.openpsa.mail' => array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'net_nehmer_account_cron_cleanup',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
        array
        (
            'handler' => 'net_nehmer_account_cron_karma',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),
