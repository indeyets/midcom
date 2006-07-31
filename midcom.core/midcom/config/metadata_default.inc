/* Attention to all developers:
 * Changes to this schema must be documented in midcom_helper_metadata's
 * class introduction
 */
'metadata' => array (
    'name'        => 'config',
    'description' => 'Default Metadata Schema',
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array 
    (
  
        'schedule_start' => array
        (
            'description' => 'schedule_start',
            'datatype' => 'unixdate',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'schedule_start',
            'date_withtime' => true,
            'widget_date_enable_outside_ais' => true,
            'hidden' => ! $GLOBALS['midcom_config']['metadata_scheduling'],
            'start_fieldgroup' => array 
            ( 
                'title' => 'visibility settings' 
            ),
        ),
        'schedule_end' => array 
        (
            'description' => 'schedule_end',
            'datatype' => 'unixdate',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'schedule_end',
            'date_withtime' => true,
            'widget_date_enable_outside_ais' => true,
            'hidden' => ! $GLOBALS['midcom_config']['metadata_scheduling'],
        ),
        'nav_noentry' => array 
        (
            'description' => 'nav_noentry',
            'helptext' => 'nav_noentry_helptext',
            'datatype' => 'boolean',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'nav_noentry',
        ),
        'hide' => array 
        (
            'description' => 'hide',
            'datatype' => 'boolean',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'hide',
            'hidden' => $GLOBALS['midcom_config']['show_hidden_objects'],
            'end_fieldgroup' => '',
        ),
  
        'keywords' => array 
        (
            'description' => 'keywords',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'keywords',
            'start_fieldgroup' => array 
            ( 
                'title' => 'metadata' 
            ),
        ),
        'description' => array 
        (
            'description' => 'description',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'description',
        ),
        'robots' => array 
        (
            'description' => 'robots',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'robots',
            'widget' => 'select',
            'widget_select_choices' => Array 
            (
                '' => 'robots-not-specified',
                'noindex' => 'robots-noindex',
                'index' => 'robots-index',
                'follow' => 'robots-follow',
                'nofollow' => 'robots-nofollow'
            ),
            'end_fieldgroup' => '',   
        ),
  
        'created' => array 
        (
            'description' => 'created',
            'datatype' => 'unixdate',
            'location' => 'created',
            'readonly' => true,
            'date_withtime' => true,
            'start_fieldgroup' => array
            ( 
                'title' => 'internal metadata' 
            ),
        ),
        /* On sites with large number of persons you will not want to use these */
        'creator' => array 
        (
            'description' => 'creator',
            'datatype' => 'text',
            'location' => 'creator',
            'widget' => 'select',
            'widget_select_choices' => midcom_helper_datamanager_selectlist_allpersons_id(),
            'readonly' => true,
        ),
  
        'edited' => array 
        (
            'description' => 'edited',
            'datatype' => 'unixdate',
            'location' => 'revised',
            'readonly' => true,
            'date_withtime' => true,
        ),
        'editor' => array 
        (
            'description' => 'editor',
            'datatype' => 'text',
            'location' => 'revisor',
            'widget' => 'select',
            'widget_select_choices' => midcom_helper_datamanager_selectlist_allpersons_id(),
            'readonly' => true,
        ),
        'approved' => array 
        (
            'description' => 'approved',
            'datatype' => 'unixdate',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'approved',
            'readonly' => true,
            'hidden' => ! $GLOBALS['midcom_config']['metadata_approval'],
            'date_withtime' => true,
        ),
        'approver' => array 
        (
            'description' => 'approver',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'midcom.helper.metadata',
            'config_key' => 'approver',
            'widget' => 'select',
            'widget_select_choices' => midcom_helper_datamanager_selectlist_allpersons(),
            'readonly' => true,
            'hidden' => ! $GLOBALS['midcom_config']['metadata_approval'],
            'end_fieldgroup' => '',
        ),
    )
),