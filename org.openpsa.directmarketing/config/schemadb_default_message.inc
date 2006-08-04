'textemail' => array (
    'name'        => 'textemail',
    'description' => 'text email',
    'org_openpsa_directmarketing_messagetype' => ORG_OPENPSA_MESSAGETYPE_EMAIL_TEXT,
    'fields'      => array (
        // Metadata
        'title' => array (
            'description' => 'title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'metadata',
                'css_group' => 'area meta',
            ),        
            'required'    => true,
        ), 
        'description' => array (
            'description' => 'description',
            'datatype'    => 'text',
            'widget_text_inputstyle' => 'longtext',
            'widget_text_height' => '8em',
            'location'    => 'description',
            'end_fieldgroup' => '',            
        ),
        'subject' => array (
            'description' => 'subject',
            'datatype'    => 'text',
            'location'    => 'parameter',
            'start_fieldgroup' => array (
                'title' => 'email message',
                'css_group' => 'area',
            ),        
        ), 
        'from' => array (
            'description' => 'from address',
            'datatype'    => 'text',
            'location'    => 'parameter',
        ),        
        'content' => array (
            'description' => 'content',
            'datatype'    => 'text',
            "widget_text_inputstyle" => "longtext",
            'location'    => 'parameter',
            'end_fieldgroup' => '',            
        ),
    )
),
'htmlemail' => array (
    'name'        => 'htmlemail',
    'description' => 'html email',
    'org_openpsa_directmarketing_messagetype' => ORG_OPENPSA_MESSAGETYPE_EMAIL_HTML,
    'fields'      => array (
        // Metadata
        'title' => array (
            'description' => 'title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'metadata',
                'css_group' => 'area meta',
            ),        
            'required'    => true,
        ), 
        'description' => array (
            'description' => 'description',
            'datatype'    => 'text',
            'widget_text_inputstyle' => 'longtext',
            'widget_text_height' => '8em',
            'location'    => 'description',
            'end_fieldgroup' => '',            
        ),
        'subject' => array (
            'description' => 'subject',
            'datatype'    => 'text',
            'location'    => 'parameter',
            'start_fieldgroup' => array (
                'title' => 'email message',
                'css_group' => 'area',
            ),        
        ), 
        'from' => array (
            'description' => 'from address',
            'datatype'    => 'text',
            'location'    => 'parameter',
        ),        
        'content' => array (
            'description' => 'content',
            'datatype'    => 'text',
            'widget'      => 'html',
            'widget_html_enable_outside_ais' => true,
            'location'    => 'parameter',
            'end_fieldgroup' => '',            
        ),
    )
),
/* TODO: hide newsletter if midcom_helper_find_node_by_component(de.linkm.newsticker) returns no results */
'htmlnewsletter' => array (
    'name'        => 'htmlnewsletter',
    'description' => 'HTML newsletter email',
    'org_openpsa_directmarketing_messagetype' => ORG_OPENPSA_MESSAGETYPE_EMAIL_HTML,
    'fields'      => array (
        'substyle' => array (
            'datatype' => 'text',
            'hidden' => true,
            'location' => 'parameter',
            'default' => 'builtin:newsletter',
        ),
        // Metadata
        'title' => array (
            'description' => 'Title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'Metadata',
                'css_group' => 'area meta',
            ),        
            'required'    => true,
        ), 
        'description' => array (
            'description' => 'Description',
            'datatype'    => 'text',
            'widget_text_inputstyle' => 'longtext',
            'widget_text_height' => '8em',
            'location'    => 'description',
            'end_fieldgroup' => '',            
        ),
        'subject' => array (
            'description' => 'Subject',
            'datatype'    => 'text',
            'location'    => 'parameter',
            'start_fieldgroup' => array (
                'title' => 'email message',
                'css_group' => 'area',
            ),        
        ), 
        'from' => array (
            'description' => 'From address',
            'datatype'    => 'text',
            'location'    => 'parameter',
        ),        
        'content' => array ( //DM property named 'content' must exist in schema
            'description' => 'Abstract',
            'datatype'    => 'text',
            'widget'      => 'html',
            'widget_html_enable_outside_ais' => true,
            'location'    => 'parameter',          
        ),
        'newsitems' => array (
            'description' => 'Number of news items to load',
            'datatype'    => 'integer',
            'location'    => 'parameter',
            'widget'      => 'select',
            'widget_select_choices' => array(
                1 => '1 latest item',
                2 => '2 latest items',
                3 => '3 latest items',
                4 => '4 latest items',
                5 => '5 latest items',
            ),
            'end_fieldgroup' => '',
        ),
    )
),
'sms' => array (
    'name'        => 'sms',
    'description' => 'sms message',
    'org_openpsa_directmarketing_messagetype' => ORG_OPENPSA_MESSAGETYPE_SMS,    
    'fields'      => array (
        // Metadata
        'title' => array (
            'description' => 'title',
            'datatype'    => 'text',
            'location'    => 'title',
            'start_fieldgroup' => array (
                'title' => 'metadata',
                'css_group' => 'area meta',
            ),        
            'required'    => true,
        ), 
        'description' => array (
            'description' => 'description',
            'datatype'    => 'text',
            'widget_text_inputstyle' => 'longtext',
            'widget_text_height' => '8em',
            'location'    => 'description',
            'end_fieldgroup' => '',            
        ),       
        'from' => array (
            'description' => 'from text/number (text max lenght 11 characters, no special characters)',
            'datatype'    => 'text',
            'location'    => 'parameter',
        ),  
        'content' => array (
            'description' => 'message',
            'datatype'    => 'text',
            "widget_text_inputstyle" => "longtext",
            'location'    => 'parameter',
            'start_fieldgroup' => array (
                'title' => 'sms message',
                'css_group' => 'area',
            ),            
            'end_fieldgroup' => '',            
        ),
    )
),