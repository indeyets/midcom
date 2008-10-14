'newquery' => array (
    'name'        => 'newquery',
    'description' => 'Query settings for Projects module reports',
    'save_text'   => 'Generate report',
    'fields'      => array (
            //Hidden field for orgOpenpsaObtype
            'type' => array(
                //'hidden'        => true,
                'description'   => 'Save report for future',
                'default'       => ORG_OPENPSA_OBTYPE_REPORT_TEMPORARY,
                'location'      => 'orgOpenpsaObtype',
                'datatype'      => 'integer',
                'widget'        => 'select',
                'widget_select_choices' => array(
                        ORG_OPENPSA_OBTYPE_REPORT_TEMPORARY => 'no',
                        ORG_OPENPSA_OBTYPE_REPORT => 'yes',
                ),
                'start_fieldgroup'  => array (
                    'title'     => 'Metadata',
                    'css_group' => 'area hidden',
                ),
            ),
            'component' => array(
                //'hidden'        => true,
                'description'   => 'Component this report is related to',
                'datatype'      => 'text',
                'location'      => 'relatedcomponent',
                'default'       => 'org.openpsa.projects',
            ),
            'mimetype' => array(
                //'hidden'        => true,
                'description'   => 'Report content-type',
                'datatype'      => 'text',
                'location'      => 'mimetype',
                'default'       => 'text/html',
            ),
            'extension' => array(
                //'hidden'        => true,
                'description'   => 'Report file extension',
                'datatype'      => 'text',
                'location'      => 'extension',
                'default'       => '.html',
                'end_fieldgroup'  => '',
            ),
            'style' => array(
                'description'   => 'Report style',
                'datatype'      => 'text',
                'location'      => 'style',
                'default'       => 'builtin:basic',
                'widget'        => 'select',
                'widget_select_choices' => array(
                    'builtin:deliverable'     => 'deliverable report',
                ),
                'start_fieldgroup'  => array (
                    'title'     => 'report style',
                    'css_group' => 'area',
                ),
                'end_fieldgroup'  => '',
            ),
            /*
            'grouping' => array(
                'description'   => 'Report grouping',
                'datatype'      => 'text',
                'location'      => 'parameter',
                'default'       => 'date',
                'widget'        => 'select',
                'widget_select_choices' => array(
                    'date'      => 'date',
                    'person'    => 'person',
                ),
                'end_fieldgroup'  => '',
            ),
            */
            'start' => array (
                'description'   => 'Start time',
                /* 'default'       => mktime(0,0,1,date('n')-1,1,date('Y')), */
                'default'       => mktime(0,0,1,date('n'),1,date('Y')),
                'datatype'      => 'unixdate',
                'location'      => 'start',
                'date_withtime' => false,
                'widget_date_enable_outside_ais' => true,
                'start_fieldgroup'  => array (
                    'title'     => 'Timeframe',
                    'css_group' => 'area',
                ),
            ),
            'end' => array (
                'description'   => 'End time',
                /* 'default'       => mktime(0,0,1,date('n'),0,date('Y')), */
                'default'       => mktime(0,0,1,date('n')+1,0,date('Y')),
                'datatype'      => 'unixdate',
                'location'      => 'end',
                'date_withtime' => false,
                'widget_date_enable_outside_ais' => true,
                'end_fieldgroup'    => '',
            ),
            'resource' => array (
                'description'   => 'Workgroup/Person',
                'location'      => 'parameter',
                'datatype'      => 'text',
                'widget'        => 'select',
                'widget_select_choices' => array_merge(array('all' => 'all'), org_openpsa_helpers_list::workgroups('first', true)),
                'start_fieldgroup'  => array (
                    'title'     => 'Scope',
                    'css_group' => 'area',
                ),
                'end_fieldgroup'    => '',
            ),
    ),
),
'default' => array (
    'name'        => 'default',
    'description' => 'Query settings for Projects module reports',
    'fields'      => array (
            'type' => array(
                'description'   => 'Save query for future',
                'default'       => ORG_OPENPSA_OBTYPE_REPORT,
                'location'      => 'orgOpenpsaObtype',
                'datatype'      => 'integer',
                'widget'        => 'select',
                'widget_select_choices' => array(
                        ORG_OPENPSA_OBTYPE_REPORT => 'yes',
                        ORG_OPENPSA_OBTYPE_REPORT_TEMPORARY => 'no',
                ),
                'start_fieldgroup'  => array (
                    'title'     => 'Metadata',
                    'css_group' => 'area',
                ),
            ),
            'component' => array(
                //'hidden'        => true,
                'description'   => 'Component this report is related to',
                'datatype'      => 'text',
                'location'      => 'relatedcomponent',
                'default'       => 'org.openpsa.projects',
            ),
            'mimetype' => array(
                //'hidden'        => true,
                'description'   => 'Report content-type',
                'datatype'      => 'text',
                'location'      => 'mimetype',
                'default'       => 'text/html',
            ),
            'extension' => array(
                //'hidden'        => true,
                'description'   => 'Report file extension',
                'datatype'      => 'text',
                'location'      => 'extension',
                'default'       => '.html',
                'end_fieldgroup'  => '',
            ),
            'style' => array(
                'description'   => 'Report style',
                'datatype'      => 'text',
                'location'      => 'style',
                'default'       => 'builtin:basic',
                'widget'        => 'select',
                'widget_select_choices' => array(
                    'builtin:deliverable'     => 'deliverable report',
                ),
                'start_fieldgroup'  => array (
                    'title'     => 'report style',
                    'css_group' => 'area',
                ),
                'end_fieldgroup'  => '',
            ),
            /*
            'grouping' => array(
                'description'   => 'Report grouping',
                'datatype'      => 'text',
                'location'      => 'parameter',
                'default'       => 'date',
                'widget'        => 'select',
                'widget_select_choices' => array(
                    'date'      => 'date',
                    'person'    => 'person',
                ),
                'end_fieldgroup'  => '',
            ),
            */
            'start' => array (
                'description'   => 'Start time',
                /* 'default'       => mktime(0,0,1,date('n')-1,1,date('Y')), */
                'default'       => mktime(0,0,1,date('n'),1,date('Y')),
                'datatype'      => 'unixdate',
                'location'      => 'start',
                'date_withtime' => false,
                'widget_date_enable_outside_ais' => true,
                'start_fieldgroup'  => array (
                    'title'     => 'Timeframe',
                    'css_group' => 'area',
                ),
            ),
            'end' => array (
                'description'   => 'End time',
                /* 'default'       => mktime(0,0,1,date('n'),0,date('Y')), */
                'default'       => mktime(0,0,1,date('n')+1,0,date('Y')),
                'datatype'      => 'unixdate',
                'location'      => 'end',
                'date_withtime' => false,
                'widget_date_enable_outside_ais' => true,
                'end_fieldgroup'    => '',
            ),
            'resource' => array (
                'description'   => 'Workgroup/Person',
                'location'      => 'parameter',
                'datatype'      => 'text',
                'widget'        => 'select',
                'widget_select_choices' => array_merge(array('all' => 'all'), org_openpsa_helpers_list::workgroups('first', true)),
                'start_fieldgroup'  => array (
                    'title'     => 'Scope',
                    'css_group' => 'area',
                ),
                'end_fieldgroup'    => '',
            ),
    ),
),