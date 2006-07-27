"default" => array (
    "name" => "default",
    "description" => "Example schema for default records", /* This is an article */
    "save_text" => "Send",
    "cancel_text" => "Cancel",
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
        "extra1" => array (
            "description" =>  "email",
            "datatype" => "text",
            "location" => "extra1",
            "required" => true,
            'validation' => array (
                'email' => 
                array(
                    'message' => 'You have to use a valid email adress',
                )
            )
        ) , 
        
        "abstract" => array (
            "description" => "comment",
            "helptext" => "",
            "datatype" => "text",
            "widget_text_inputstyle" => "longtext",
            "location" => "abstract",
            "required" => false,
        ),

    ),
),


