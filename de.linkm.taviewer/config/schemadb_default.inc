"default" => array (
    "name"        => "default",
    "description" => "Default Article",
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
        ),
        "image" => array (
            "description" => "Image",
            "datatype"    => "image",
            "widget_image_simple" => true,
        )
    )
), // default

