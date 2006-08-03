'name' => 'midcom.helper.datamanager2',
'version' => '0.8.13',
'state'   => 'beta',
'purecode' => true,
'package.xml' => Array(
    'summary' => 'Data abstraction and form rendering library',
    'description' => 'midcom.helper.datamanager2 is a MidCOM purecode component providing database abstraction and form processing features to MidCOM components.',
    'maintainers' => Array(
        'midgard' => Array(
            'name' => 'The Midgard Project',
            'email' => 'dev@midgard-project.org',
            'role' => 'lead',
        ),
    ),
    'dependencies' => Array(    
        'net.nehmer.markdown'           => Array(),
        'HTML_QuickForm'                => Array
        (
            'channel'  => 'pear.php.net',
        ),
        'Text_CAPTCHA'                  => Array
        (
            'channel'  => 'pear.php.net',
        ),        
        'JavaScript_Prototype'          => Array(
            'channel' => 'pearified.com',
        ),        
    ), 
),
