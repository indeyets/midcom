'name' => 'net.nemein.approvenotifier',
'icon' => 'stock-icons/16x16/stock_mark.png',
'purecode' => true,
'version' => '0.1.0beta1',
'state' => 'beta',
'package.xml' => array
(
    'summary' => 'Approval notifier',
    'description' => 'Approval notifier',
    'maintainers' => array
    (
        'bergie' => array
        (
            'name' => 'Henri Bergius',
            'email' => 'henri.bergius@iki.fi',
            'role' => 'lead',
            'active' => 'no',
        ),
    ),
    'dependencies' => array
    (
        'org.openpsa.notifications' => array(),
        'midcom' => array(),
    ),
),
'customdata' => array
(
    'midcom.services.cron' => array
    (
        array
        (
            'handler' => 'net_nemein_approvenotifier_cron_checkexpiries',
            'recurrence' => MIDCOM_CRON_DAY,
        ),
    ),
),
