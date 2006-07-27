'name' => 'net.nehmer.account',
'version' => '1.0.1',
'state' => 'stable',
'package.xml' => Array
(
    'summary' => 'Account management and registration',
    'description' => 'Allows users to manage their accounts. New users can register for new accounts as well.',
    'maintainers' => Array
    (
        'torben' => Array
        (
            'name' => 'Torben Nehmer',
            'email' => 'torben@nehmer.net',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array
    (
        'midcom.helper.datamanager2' => Array(),
    ),
),

'customdata' => Array
(
    'midcom.services.cron' => Array
    (
        Array
        (
            'handler' => 'net_nehmer_account_cron_cleanup',
            'recurrence' => MIDCOM_CRON_DAY,
        )
    ),
),
