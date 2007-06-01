/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */
'config' => array
(
    'description' => 'configuration',
    'fields'      => array
    (

        'dbaddr' => array
        (
            'title'   => 'database uri',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'dbaddr',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),


        'cache' => array
        (
            'title'   => 'search cache',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'cache',
            ),
            'type' => 'boolean',
            'widget' => 'checkbox',

        ),

        'detectclones' =>array
        (
            'title'   => 'detect clones',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'detectclones',
            ),
            'type' => 'boolean',
            'widget' => 'checkbox',

        ),
 
        'trackquery' => array
        (
            'title'   => 'query tracking',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'trackquery',
            ),
            'type' => 'boolean',
            'widget' => 'checkbox',

        ),


        'crosswords' => array
        (
            'title'   => 'crosswords',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'crosswords',
            ),
            'type' => 'boolean',
            'widget' => 'checkbox',

        ),


        'pageresults' => array
        (
            'title'   => 'results per page',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'pageresults',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),


        'hlbeg' => array
        (
            'title'   => 'begin mark',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'hlbeg',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),

        'hlend' => array
        (
            'title'   => 'end mark',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'hlend',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),

        'excerptsize' => array
        (
            'title'   => 'excerpt size',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'excerptsize',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),

        'excerptpadding' => array
        (
            'title'   => 'excerpt padding',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'excerptpadding',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),

        'dateformat' => array
        (
            'title'   => 'date format',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'dateformat',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),

        'resultslimit' =>array
        (
            'title'   => 'max number of results',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'pl.olga.mnogosearch',
                'name'     => 'resultslimit',
            ),
            'type' => 'text',
            'widget' => 'text',

        ),

    ),

),
