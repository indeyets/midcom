// Payment handlers, override this in your sitegroup configuration
// Handlers are indexed by arbitrary values which are not used for further
// processing.
//
// The default will use the pay by invoice handler only.

'handlers' => Array
(
    'invoice' => Array
    (
        '_handler' => 'invoice',
    ),
),

/*
// Example:
'handlers' => Array
(
    'nordea-test' => Array
    (
        '_handler' => 'nordea',
        'SOLOPMT_RCV_ID' => '12345678',
        'MAC_SEED' => 'LEHTI',
        // 'SOLOPMT_RCV_ACCOUNT' => '29501800008512',
        // 'SOLOPMT_RCV_NAME' => 'Testing',
        // 'SOLOPMT_LANGUAGE' => 3,
        // 'SOLOPMT_REF_SUFFIX' => '15',
    ),
),
*/