"config" => array (
    "name"        => "config",
    "description" => "Default Configuration Schema", /* This is a topic */
    "locktimeout" => 0, /* Don"t lock this, we display the edit view always */
    "fields"      => array (
        /* for the day when qform may also save the values ...  */
        "mail_new_item" => array (
            "description" => "mail_new_item",
            "datatype" => "boolean",
            "helptext" => "mail new item",
            "hidden"   => true,
            "default"  => true,
        ),
        "mail_address_to" => array (
            "description" => "Mailadress to send the contents of the form to",
            "datatype" => "text",
            "helptext" => "Who should get the mail?",
            "location"    => "config",
            'required' => true,
            "config_key"  => "mail_address_to",
            "config_domain" => "no.odindata.quickform",
            "start_fieldgroup" => Array ( "title" => "Mailsettings" ),
        ),
        "mail_subject" => array (
            "description" => "Email subject",
            "datatype" => "text",
            "helptext" => "The subject of the email",
            "location"    => "config",
            "config_key"  => "mail_subject",
            "config_domain" => "no.odindata.quickform",
        ),

        "mail_address_from" => array (
            "description" => "Which mailaddress should the mail come from?",
            "datatype" => "text",
            "helptext" => "Which mailaddress should the mail come from?",
            "location"    => "config",
            "config_key"  => "mail_address_from",
            "config_domain" => "no.odindata.quickform",
        ),
        "mail_reciept" => array (
            "description" => "Send mail to submitter? (BIG NOTE: YOU MUST HAVE A 'email' attribute in the form schema for this to work!",
            "datatype"    => "boolean",
            "location"    => "config",
            "config_key"  => "mail_reciept",
            "config_domain" => "no.odindata.quickform",
            "helptext"    => "Do you want to send a recept to the submitter of the form?",
        ),

        "mail_reciept_message" => array (
            "description" => "Message to send to user",
            "datatype"  => "text",
            "widget"    => "text",
            "location" => "config",
            "config_key"  => "mail_reciept_message",
            "config_domain" => "no.odindata.quickform",
            "widget_text_height" => "4em",
            "widget_text_inputstyle" => "longtext",
            "helptext" => "If you want to write a note to the user, write it here.",
        ),
        "mail_reciept_data" => array (
            "description" => "Include submitted data in reciept?",
            "datatype"    => "boolean",
            "location"    => "config",
            "config_key"  => "mail_reciept_data",
            "config_domain" => "no.odindata.quickform",
            "helptext"    => "",
            "end_fieldgroup" => "",
        ),


        "topic_introduction" => array (
            "description" => "Message over the form",
            "datatype" => "text",
            "location" => "config",
            "widget" => "html",
            "config_key" => "form_description",
            "config_domain" => "no.odindata.quickform",
            "widget_text_height" => "4em",
            "helptext"  => "topic_introduction_help",
        ),

        "end_message" => array (
            "description" => "Thank you message to the user",
            "datatype" => "text",
            "location" => "config",
            "widget" => "html",
            "config_key" => "end_message",
            "config_domain" => "no.odindata.quickform",
            "widget_text_height" => "4em",
            "default" => "",
            "helptext"  => "This message is shown to the user after the form has been submitted.",
        ),
        "schemadb" => array (
            "description" => "Schema database",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "no.odindata.quickform",
            "hidden"   => false,
            "config_key" => "schemadb",
            "helptext" => "Path to schemadefinition", 
        ),

        
        "form_user" => array (
            "description" => "public posting user",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "no.odindata.quickform",
            "config_key" => "form_user",
            "hidden" => true,
        ),
        "form_pass" => array (
            "description" => "public posting password",
            "datatype" => "text",
            "location" => "config",
            "config_domain" => "no.odindata.quickform",
            "config_key" => "form_pass",
            "widget_text_inputstyle" => "password",
            "end_fieldgroup" => "",
            "hidden" => true,
        ),

        
        /* some hidden topic fields come below here, edit topic will show them,
         * but this will make accessing them on-site easier.
         */

        "topic_urlname" => array (
            "description" => "topic_urlname",
            "datatype" => "text",
            "location" => "name",
            "hidden" => true,
        ),
        "topic_title" => array (
            "description" => "topic_title",
            "datatype" => "text",
            "location" => "name",
            "hidden" => true,
        ),

    )
),
