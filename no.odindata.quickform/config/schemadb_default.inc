"default" => array (
    "name" => "default",
    "description" => "Example schema for default records", /* This is an article */
    "save_text" => "send",
    "cancel_text" => "cancel",
    "fields" => array(
    /*
        "title" => array (
            "description" => "",
            "datatype" => "text",
            "location" => "title",
            "required" => true,
        ),
     */
        "name" => array(
            "description" => "Name",
            "datatype" => "text",
            "location" => "name",
            "required" => true,
        ), 
        /*
         * This is an example how to include file attachments in the form
        "file" => array(
            "description" => "File",
            "datatype" => "blob",
            "location" => "blob",
            "widget_blob_simple" => true,
        ), 
        */
        "extra1" => array (
            "description" =>  "email",
            "datatype" => "text",
            "location" => "extra1",
            "required" => true,
            'validation' => array (
                'email' => 
                array(
                    'message' => 'You have to use a valid email address',
                )
            )
        ) , 
        
        "abstract" => array (
            "description" => "message",
            "helptext" => "",
            "datatype" => "text",
            "widget_text_inputstyle" => "longtext",
            "widget_text_height" => "12em",
            "location" => "abstract",
            "required" => false,
        ),

    ),
),


