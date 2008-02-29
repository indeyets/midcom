/**
 * This schema is used to show the settings form on
 * for the folder of your component
 */

'config' => array 
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we always display the edit view */
    'fields'      => array 
    (
        'enable_ajax_editing' => array
        (
            'start_fieldset' => array
            ( 
                'title' => 'user interface settings',
            ),
            'title' => 'enable ajax editing',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'enable_ajax_editing',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
        'entry_page' => array
        (
            'title' => 'entry page operating model',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'entry_page',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    'lists' => 'select the user or tags',
                    'straight' => 'go straight to the all photos view',
                ),
            ),
            'widget' => 'select',
        ),
        'navigate_with_context' => array
        (
            'title' => 'navigate with sticky context (e.g. tags, timespan or photographer)',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'navigate_with_context',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
            'end_fieldset' => 1,
        ),
        'moderate_uploaded_photos' => array
        (
            'start_fieldset' => array
            (
                'title' => 'moderation settings',
            ),
            'title' => 'moderate uploaded photos',
            'static_append' => 'if this is on, photos require moderation before they will be displayed to the public',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'moderate_uploaded_photos',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
        'moderator_email' => array
        (
            'start_fieldset' => array
            (
                'title' => 'related to the moderator',
            ),
            'title' => 'moderator email address',
            'static_prepend' => 'this address will receive the messages notifying of new photos',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'moderator_email',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'moderator_mail_subject' => array
        (
            'title' => 'moderator mail subject',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'moderator_mail_subject',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'moderator_mail_body' => array
        (
            'title' => 'moderator mail message',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'moderator_mail_body',
            ),
            'type' => 'text',
            'widget' => 'textarea',
            'end_fieldset' => 1,
        ),
        'send_statuschange_message' => array
        (
            'start_fieldset' => array
            (
                'title' => 'messages sent to user',
            ),
            'title' => 'send status change messages to the photographer',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'send_statuschange_message',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
        'status_accepted_subject' => array
        (
            'start_fieldset' => array
            (
                'title' => 'message sent after accepting a photo',
            ),
            'title' => 'accepted mail subject',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'status_accepted_subject',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'status_accepted_body' => array
        (
            'title' => 'accepted mail message',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'status_accepted_body',
            ),
            'type' => 'text',
            'widget' => 'textarea',
            'end_fieldset' => 1,
        ),
        'status_rejected_subject' => array
        (
            'start_fieldset' => array
            (
                'title' => 'message sent after rejecting a photo',
            ),
            'title' => 'rejected mail subject',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'status_rejected_subject',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'status_rejected_body' => array
        (
            'title' => 'rejected mail subject',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'status_rejected_body',
            ),
            'type' => 'text',
            'widget' => 'textarea',
            'end_fieldset' => 2,
        ),

        'system_mailer_address' => array
        (
            'title' => 'system email address for outgoing mail',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'system_mailer_address',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        
        'moderate_callback_snippet' => array
        (
            'start_fieldset' => array
            (
                'title' => 'moderate callback options',
                'description' => 'these settings are for system administrators only',
            ),
            'title' => 'moderate callback snippet',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'moderate_callback_snippet',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'moderate_callback_function' => array
        (
            'title' => 'moderate callback function',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'moderate_callback_function',
            ),
            'type' => 'text',
            'widget' => 'text',
            'end_fieldset' => 2,
        ),
        'api_email_enable' => array
        (
            'start_fieldset' => array
            ( 
                'title' => 'API settings',
            ),
            'title' => 'email API enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_email_enable',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
        'api_email_schema' => array
        (
            'title' => 'email API schema',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_email_schema',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'api_email_default_tags' => array
        (
            'title' => 'email API default tags to apply to photos',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_email_default_tags',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        /* not implemented yet
        'api_metaweblog_enable' => array
        (
            'title' => 'metaweblog API enable',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_metaweblog_enable',
            ),
            'type' => 'select',
            'type_config' => array
            (
                'options' => array
                (
                    '' => 'default setting',
                    '1' => 'yes',
                    '0' => 'no',
                ),
            ),
            'widget' => 'select',
        ),
        'api_metaweblog_schema' => array
        (
            'title' => 'metaweblog API schema',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'api_metaweblog_schema',
            ),
            'type' => 'text',
            'widget' => 'text',
            'end_fieldset' => '',
        ),
        */
        'schemadb' => array
        (
            'start_fieldset' => array
            ( 
                'title' => 'advanced schema and data settings',
            ),
            'title' => 'schema database',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'schemadb',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'create_callback_snippet' => array
        (
            'title' => 'create callback snippet',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'create_callback_snippet',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'create_callback_function' => array
        (
            'title' => 'create callback function',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'org.routamc.photostream',
                'name' => 'create_callback_function',
            ),
            'type' => 'text',
            'widget' => 'text',
            'end_fieldset' => 1,
        ),
    ),
),
