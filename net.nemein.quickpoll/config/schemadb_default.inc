"default" => array (
    "name"        => "default",
    "description" => "Poll",
    "fields"      => array (
    
        /* Start of component required fields */
        "name" => midcom_helper_datamanager_urlname_field(),
        "title" => array (
            "description" => "Title",
            "datatype"    => "text",
            "location"    => "title", 
            "required" => true,
        ),
        /* End of component required fields */
        
        "content" => array (
            "description" => "Text",
            "datatype"    => "text",
            "location"    => "content",
            "widget"      => "html"
        )
    )
),