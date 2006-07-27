'enable_weblog_pings' => FALSE,
'components_to_ping' => Array(
    'de.linkm.newsticker',
    'net.nemein.wiki',
    'net.siriux.photos',
),
'weblog_ping_servers' => Array(
    'Weblogs.Com' => Array(
        'server' => 'rpc.weblogs.com',
        'port'   => 80,
        'path'   => '/RPC2',
    ),
    'Blo.gs' => Array(
        'server' => 'ping.blo.gs',
        'port'   => 80,
        'path'   => '/',
    ),
    'Ping-o-Matic' => Array(
        'server' => 'rpc.pingomatic.com',
        'port'   => 80,
        'path'   => '/RPC2',
    ),
    'Technorati' => Array(
        'server' => 'rpc.technorati.com',
        'port'   => 80,
        'path'   => '/rpc/ping',
    ),
),
