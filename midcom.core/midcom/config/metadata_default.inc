/* Attention to all developers:
 * Changes to this schema must be documented in midcom_helper_metadata's
 * class introduction
 */
'metadata' => array
(
    'description' => 'metadata',
    'l10n_db'     => 'midcom.admin.folder',
    'fields' => array
    (
        'schedulestart' => array
        (
            'hidden' => ! $GLOBALS['midcom_config']['metadata_scheduling'],
            'start_fieldgroup' => array
            (
                'title' => 'visibility settings',
            ),
            'title' => 'schedule start',
            'type' => 'date',
            'type_config' => array
            (
                // MidCOM DBA converts dates to/from unixtimes to MgdSchema's ISO format
                'storage_type' => 'UNIXTIME',
            ),
            'storage' => 'metadata',
            'widget' => 'jsdate',
        ),
        'scheduleend' => array
        (
            'hidden' => ! $GLOBALS['midcom_config']['metadata_scheduling'],
            'title' => 'schedule end',
            'type' => 'date',
            'type_config' => array
            (
                // MidCOM DBA converts dates to/from unixtimes to MgdSchema's ISO format
                'storage_type' => 'UNIXTIME',
            ),
            'storage' => 'metadata',
            'widget' => 'jsdate',
        ),
        'navnoentry' => array
        (
            'title' => 'hide from navigation',
            'helptext' => 'objects with no navigation entry are still accessible on-site if the url is known',
            'type' => 'boolean',
            'storage' => 'metadata',
            'widget' => 'checkbox',
        ),
        'score' => array
        (
            'title' => 'arrangement score',
            'storage' => 'metadata',
            'type' => 'number',
            'widget' => 'text',
            'hidden' => version_compare(mgd_version(), '1.8.3', '<'),
        ),
        'hidden' => array
        (
            'title' => 'prevent entering',
            'type' => 'boolean',
            'hidden' => true,
            'storage' => 'metadata',
            'widget' => 'checkbox',
            'end_fieldgroup' => '',
        ),
        'description' => array
        (
            'title' => 'description',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'midcom.helper.metadata',
                'name'     => 'description',
            ),
            'type' => 'text',
            'widget' => 'textarea',
        ),
        'keywords' => array
        (
            'title' => 'keywords',
            'storage' => array
            (
                'location' => 'configuration',
                'domain'   => 'midcom.helper.metadata',
                'name'     => 'keywords',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'tags' => Array
        (
            'title' => 'tags',
            'type' => 'tags',
            'widget' => 'text',
        ),
        'published' => array
        (
            'title' => 'published',
            'storage' => 'metadata',
            'type' => 'date',
            'type_config' => array
            (
                // MidCOM DBA converts dates to/from unixtimes to MgdSchema's ISO format
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
        ),
        'authors' => array
        (
            'title' => 'authors',
            'storage' => 'metadata',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_multiple' => true,
                'multiple_storagemode' => 'imploded_wrapped',
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'name',
                'idfield' => 'guid',
                'constraints' => array
                (
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                ),
                'orders' => array(array('lastname', 'ASC')),
            ),
        ),
        'owner' => array
        (
            'title' => 'owner',
            'storage' => 'metadata',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'name',
                'idfield' => 'guid',
                'constraints' => array
                (
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                ),
                'orders' => array(array('lastname', 'ASC')),
            ),
        ),
        'created' => array
        (
            'title' => 'created',
            'storage' => 'metadata',
            'type' => 'date',
            'type_config' => array
            (
                // MidCOM DBA converts dates to/from unixtimes to MgdSchema's ISO format
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
            'readonly' => true,
        ),
        'creator' => array
        (
            'title' => 'creator',
            'storage' => 'metadata',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'name',
                'idfield' => 'guid',
                'constraints' => array
                (
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                ),
                'orders' => array(array('lastname', 'ASC')),
            ),
            'readonly' => true,
        ),
        'revised' => array
        (
            'title' => 'revised',
            'storage' => 'metadata',
            'type' => 'date',
            'type_config' => array
            (
                // MidCOM DBA converts dates to/from unixtimes to MgdSchema's ISO format
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
            'readonly' => true,
        ),
        'revisor' => array
        (
            // Universal chooser
            'title' => 'revisor',
            'storage' => 'metadata',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'name',
                'idfield' => 'guid',
                'constraints' => array
                (
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                ),
                'orders' => array(array('lastname', 'ASC')),
            ),
            'readonly' => true,
        ),
        'revision' => array
        (
            'title' => 'revision',
            'stogare' => 'metadata',
            'type' => 'number',
            'widget' => 'text',
            'end_fieldgroup' => '',
            'readonly' => true,
        ),
        
        // Approvals
        'approved' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'approvals',
            ),
            'title' => 'approved',
            'storage' => 'metadata',
            'type' => 'date',
            'type_config' => array
            (
                // MidCOM DBA converts dates to/from unixtimes to MgdSchema's ISO format
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
            'readonly' => true,
            'hidden' => ! $GLOBALS['midcom_config']['metadata_approval'],
        ),
        'approver' => array
        (
            'hidden' => ! $GLOBALS['midcom_config']['metadata_approval'],
            'title' => 'approver',
            'storage' => 'metadata',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'name',
                'idfield' => 'guid',
                'constraints' => array
                (
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                ),
                'orders' => array(array('lastname', 'ASC')),
            ),
            'readonly' => true,
            'end_fieldgroup' => '',
        ),
        
        // Editing status
        'locked' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'editing status',
            ),
            'title' => 'locked',
            'storage' => 'metadata',
            'type' => 'date',
            'type_config' => array
            (
                // MidCOM DBA converts dates to/from unixtimes to MgdSchema's ISO format
                'storage_type' => 'UNIXTIME',
            ),
            'widget' => 'jsdate',
            'readonly' => true,
            'hidden' => true,
        ),
        'locker' => array
        (
            'title' => 'locker',
            'storage' => 'metadata',
            'type' => 'select',
            'type_config' => array
            (
                'require_corresponding_option' => false,
                'options' => array (),
                'allow_other' => true,
            ),
            'widget' => 'universalchooser',
            'widget_config' => array
            (
                'class' => 'midcom_db_person',
                'component' => 'net.nemein.personnel',
                'titlefield' => 'name',
                'idfield' => 'guid',
                'constraints' => array
                (
                ),
                'searchfields' => array
                (
                    'firstname',
                    'lastname',
                ),
                'orders' => array(array('lastname', 'ASC')),
            ),
            'readonly' => true,
            'hidden' => true,
            'end_fieldgroup' => '',
        ),
        'size' => array
        (
            'start_fieldgroup' => array
            (
                'title' => 'internal metadata',
            ),
            'title' => 'size',
            'storage' => 'metadata',
            'type' => 'number',
            'widget' => 'text',
            'readonly' => true,
        ),
    ),
),
