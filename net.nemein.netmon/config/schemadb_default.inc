'host' => array
(
    'name'        => 'host',
    'description' => 'host object',
    'fields'      => array
    (
        'name' => array
        (
            'storage'   => 'name',
            'title'     => '"short" name',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'title' => array
        (
            'storage'   => 'title',
            'title'     => 'title',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'description' => array
        (
            'storage'   => 'description',
            'title'     => 'description',
            'type'      => 'text',
            'widget'    => 'textarea',
            'required'  => false,
        ),
        'ipaddress' => array
        (
            'storage'   => 'ipaddress',
            'title'     => 'ip address',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'dnsname' => array
        (
            'storage'   => 'dnsname',
            'title'     => 'dns name',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => false,
        ),
        'parent' => array
        (
            'storage'   => 'parent',
            'title'     => 'parent host',
            'type'      => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    0 => 'no parent',
                ),
                'require_corresponding_option' => false,
                'allow_other' => true,
            ),
            'widget'    => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'net_nemein_netmon_host_dba',
                'component' => 'net.nemein.netmon',
                'idfield' => 'id',
                'titlefield' => 'title',
                'searchfields' => array
                (
                    'title',
                    'name',
                ),
                'auto_wildcards' => 'both',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'sitegroup',
                        'op'    => '=',
                        'value' => $_MIDGARD['sitegroup'],
                    ),
                ),
                'orders' => array
                (
                    array('title' => 'ASC'),
                    array('name' => 'ASC'),
                ),
                'static_options' => array
                (
                    0 => 'no parent',
                ),
            ),
            'required'  => false,
        ),
        'contactgroup' => array
        (
            'storage'   => 'contactgroup',
            'title'     => 'contact group',
            'type'      => 'select',
            'type_config' => array
            (
                'options' => array(),
                'require_corresponding_option' => false,
                'allow_other' => true,
            ),
            'widget'    => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'org_openpsa_contacts_group',
                'component' => 'org.openpsa.contacts',
                'idfield' => 'id',
                'titlefield' => 'official',
                'searchfields' => array
                (
                    'official',
                    'name',
                    'email',
                ),
                'auto_wildcards' => 'both',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'sitegroup',
                        'op'    => '=',
                        'value' => $_MIDGARD['sitegroup'],
                    ),
                ),
                'orders' => array
                (
                    array('official' => 'ASC'),
                    array('name' => 'ASC'),
                ),
            ),
            'required'  => true,
        ),
        'nagiosextra' => array
        (
            'storage'   => 'nagiosextra',
            'title'     => 'extra Nagios definitions',
            'type'      => 'text',
            'widget'    => 'textarea',
            'required'  => false,
        ),
        'hostgroups' => array
        (
            'title'     => 'belongs to hostgroups',
            'type'      => 'mnrelation',
            'type_config' => array
            (
                'allow_multiple' => true,
                'options' => net_nemein_netmon_hostgroup_options(),
                'master_is_id' => true,
                'master_fieldname' => 'host',
                'member_fieldname' => 'hostgroup',
                'mapping_class_name' => 'net_nemein_netmon_hostgroup_member_dba',
            ),
            'widget'    => 'select',
        ),
    ),
), // host
'hostgroup' => array
(
    'name'        => 'hostgroup',
    'description' => 'hostgroup object',
    'fields'      => array
    (
        'name' => array
        (
            'storage'   => 'name',
            'title'     => '"short" name',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'title' => array
        (
            'storage'   => 'title',
            'title'     => 'title',
            'type'      => 'text',
            'widget'    => 'text',
            'required'  => true,
        ),
        'description' => array
        (
            'storage'   => 'description',
            'title'     => 'description',
            'type'      => 'text',
            'widget'    => 'textarea',
            'required'  => false,
        ),
        'nagiosextra' => array
        (
            'storage'   => 'nagiosextra',
            'title'     => 'extra Nagios definitions',
            'type'      => 'text',
            'widget'    => 'textarea',
            'required'  => false,
        ),
        /* using universalchooser here might not work
        'hosts' => array
        (
            'title'     => 'member hosts',
            'type'      => 'mnrelation',
            'type_config' => array
            (
                'allow_multiple' => true,
                'options' => array();
                'require_corresponding_option' => false,
                'allow_other' => true,
                'master_is_id' => true,
                'master_fieldname' => 'hostgroup',
                'member_fieldname' => 'host',
                'mapping_class_name' => 'net_nemein_netmon_hostgroup_member_dba',
            ),
            'widget'    => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'net_nemein_netmon_host_dba',
                'component' => 'net.nemein.netmon',
                'idfield' => 'id',
                'titlefield' => 'title',
                'searchfields' => array
                (
                    'title',
                    'name',
                ),
                'auto_wildcards' => 'both',
                'constraints' => array
                (
                    array
                    (
                        'field' => 'sitegroup',
                        'op'    => '=',
                        'value' => $_MIDGARD['sitegroup'],
                    ),
                ),
                'orders' => array
                (
                    array('title' => 'ASC'),
                    array('name' => 'ASC'),
                ),
            ),
        ),
        */
    ),
), // hostgroup
