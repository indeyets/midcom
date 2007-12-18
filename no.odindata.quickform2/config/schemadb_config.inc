'config' => array
(
    'name'        => 'config',
    'description' => 'Default Configuration Schema', /* This is a topic */
    'l10n'        => 'no.odindata.quickform2',
    'fields'      => array
    (
        /* for the day when qform may also save the values ...  */
        'breadcrumb' => array
        (
            'title' => 'breadcrumb text',
            'type' => 'text',
            'helptext' => 'Set the breadcrumb to this text',
            'hidden'   => false,
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'breadcrumb',
            ),
            'widget' => 'text',
            'default'  => "",
        ),
        'mail_address_to' => array
        (
            'title' => 'email address to send the submitted form to',
            'type' => 'text',
            'helptext'      => 'email address where the form will be sent',
            'required'      => true,
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'mail_address_to',
            ),
            'widget' => 'text',
            'start_fieldgroup' => array
            (
                'title' => 'form email settings',
            ),
        ),
        'mail_subject' => array
        (
            'title' => 'mail subject',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'mail_subject',
            ),
            'type' => 'text',
            'widget' => 'text',
        ),
        'mail_subject_reciept' => array
        (
            'title' => 'subject in mail to the form committer',
            'type' => 'text',
            'widget' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'mail_subject_reciept',
            ),
        ),


        'mail_address_from' => array
        (
            'title' => 'from address of the email',
            'type' => 'text',
            'widget' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'mail_address_from',
            ),
        ),
        'mail_reciept' => array
        (
            'title' => 'should a copy of the email be sent to the submitter of the form',
            'type' => 'boolean',
            'widget' => 'checkbox',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'mail_reciept',
            ),
        ),

        'mail_reciept_message' => array 
        (
            'title' => 'message of the mail sent to user',
            'type' => 'text',
            'widget'    => 'textarea',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'mail_reciept_message',
            ),
            'helptext' => 'this message is appended on top of the email sent to user',
        ),
        'mail_reciept_data' => array
        (
            'title' => 'include submitted data in the message sent to user',
            'type' => 'boolean',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'mail_reciept_data',
            ),
            'widget' => 'checkbox',
            'helptext'    => '',
            'end_fieldgroup' => '',
        ),


        'topic_introduction' => array
        (
            'title' => 'text to place over the form',
            'type' => 'text',
            'widget' => 'tinymce',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'form_description',
            ),
            'widget_text_height' => '4em',
            'helptext'  => 'use this field to add a simple text above the form to help the user fill in the form',
            'start_fieldgroup' => Array
            (
                'title' => 'form settings'
            ),
        ),

        'end_message' => array
        (
            'title' => 'message to show user after the form has been submitted',
            'type' => 'text',
            'widget' => 'tinymce',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'end_message',
            ),
            'widget_text_height' => '4em',
            'default' => '',
            'helptext'  => 'this message is displayed after the user has submitted the form',
        ),
        'schemadb' => array
        (
            'title' => 'schema database',
            'type' => 'text',
            'widget' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'schemadb',
            ),
            'hidden'   => false,
            'helptext' => 'Path to schemadefinition', 
            'end_fieldgroup' => '',
        ),

        
        'email_class' => array
        (
            'title' => 'the class to use for formatting the email to send',
            'type' => 'text',
            'widget' => 'text',
            'storage' => array
            (
                'location' => 'configuration',
                'domain' => 'no.odindata.quickform2',
                'name' => 'email_class',
            ),
            'hidden' => true,
        ),
    ),
),
