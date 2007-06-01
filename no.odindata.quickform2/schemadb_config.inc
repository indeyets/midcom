'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'locktimeout' => 0, /* Don't lock this, we display the edit view always */
    'fields'      => array (
        /* for the day when qform may also save the values ...  */
        'mail_new_item' => array
        (
            'description' => 'send an email to someone for each new item',
            'datatype' => 'boolean',
            'helptext' => 'mail new item',
            'hidden'   => true,
            'default'  => true,
        ),
        'mail_address_to' => array
        (
            'description'   => 'email address to send the submitted form to',
            'datatype'      => 'text',
            'helptext'      => 'email address where the form will be sent',
            'location'      => 'config',
            'required'      => true,
            'config_key'    => 'mail_address_to',
            'config_domain' => 'no.odindata.quickform',
            'start_fieldgroup' => array
            (
                'title' => 'form email settings'
            ),
        ),
        'mail_subject' => array
        (
            'description' => 'mail subject',
            'datatype' => 'text',
            'location'    => 'config',
            'config_key'  => 'mail_subject',
            'config_domain' => 'no.odindata.quickform',
        ),

        'mail_address_from' => array
        (
            'description' => 'from address of the email',
            'datatype' => 'text',
            'location'    => 'config',
            'config_key'  => 'mail_address_from',
            'config_domain' => 'no.odindata.quickform',
        ),
        'mail_reciept' => array
        (
            'description' => 'should a copy of the email be sent to the submitter of the form',
            'datatype'    => 'boolean',
            'location'    => 'config',
            'config_key'  => 'mail_reciept',
            'config_domain' => 'no.odindata.quickform',
        ),

        'mail_reciept_message' => array 
        (
            'description' => 'message of the mail sent to user',
            'datatype'  => 'text',
            'widget'    => 'text',
            'location' => 'config',
            'config_key'  => 'mail_reciept_message',
            'config_domain' => 'no.odindata.quickform',
            'widget_text_height' => '4em',
            'widget_text_inputstyle' => 'longtext',
            'helptext' => 'this message is appended on top of the email sent to user',
        ),
        'mail_reciept_data' => array
        (
            'description' => 'include submitted data in the message sent to user',
            'datatype'    => 'boolean',
            'location'    => 'config',
            'config_key'  => 'mail_reciept_data',
            'config_domain' => 'no.odindata.quickform',
            'helptext'    => '',
            'end_fieldgroup' => '',
        ),


        'topic_introduction' => array
        (
            'description' => 'text to place over the form',
            'datatype' => 'text',
            'location' => 'config',
            'widget' => 'html',
            'config_key' => 'form_description',
            'config_domain' => 'no.odindata.quickform',
            'widget_text_height' => '4em',
            'helptext'  => 'use this field to add a simple text above the form to help the user fill in the form',
            'start_fieldgroup' => Array
            (
                'title' => 'form settings'
            ),
        ),

        'end_message' => array
        (
            'description' => 'message to show user after the form has been submitted',
            'datatype' => 'text',
            'location' => 'config',
            'widget' => 'html',
            'config_key' => 'end_message',
            'config_domain' => 'no.odindata.quickform',
            'widget_text_height' => '4em',
            'default' => '',
            'helptext'  => 'this message is displayed after the user has submitted the form',
        ),
        'schemadb' => array
        (
            'description' => 'Schema database',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'no.odindata.quickform',
            'hidden'   => false,
            'config_key' => 'schemadb',
            'helptext' => 'Path to schemadefinition', 
            'end_fieldgroup' => '',
        ),

        
        'form_user' => array
        (
            'description' => 'public posting user',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'no.odindata.quickform',
            'config_key' => 'form_user',
            'hidden' => true,
        ),
        'form_pass' => array
        (
            'description' => 'public posting password',
            'datatype' => 'text',
            'location' => 'config',
            'config_domain' => 'no.odindata.quickform',
            'config_key' => 'form_pass',
            'widget_text_inputstyle' => 'password',
            //'end_fieldgroup' => '',
            'hidden' => true,
        ),
    ),
),
