'enable_weblog_pings' => true,
'components_to_ping' => array
(
    'de.linkm.newsticker',
    'net.nemein.wiki',
    'net.siriux.photos',
    'net.nehmer.blog',
),
'weblog_ping_servers' => array
(
    'Weblogs.Com' => array
    (
        'server' => 'rpc.weblogs.com',
        'port'   => 80,
        'path'   => '/RPC2',
        'protocol' => 'ping',
    ),
    'Blo.gs' => array
    (
        'server' => 'ping.blo.gs',
        'port'   => 80,
        'path'   => '/',
        'protocol' => 'ping',
    ),
    'Ping-o-Matic' => array
    (
        'server' => 'rpc.pingomatic.com',
        'port'   => 80,
        'path'   => '/RPC2',
        'protocol' => 'ping',
    ),
    'Technorati' => array
    (
        'server' => 'rpc.technorati.com',
        'port'   => 80,
        'path'   => '/rpc/ping',
        'protocol' => 'ping',
    ),
    'Google' => array
    (
        'server' => 'blogsearch.google.com',
        'port'   => 80,
        'path'   => '/ping/RPC2',
        'protocol' => 'extendedPing',
    ),
),
