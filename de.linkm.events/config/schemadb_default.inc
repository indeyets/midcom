"events" => array (    
    "description" => "Event calendar",
    "fields"      => array (
        "name" => array (
            "description" => "URL name",
            "helptext"    => "Lowercase, no special characters or spaces",
            "datatype"    => "text",
            "location"    => "name",
            "required"    => true,
        ),
        "title" => array (
            "description" => "Title",
            "datatype"    => "text",
            "location"    => "title",
            "required"    => true,
        ),
        "startdate" => array (
            "description" => "Start date",
            "datatype"    => "unixdate",
            "location"    => "parameter",
        ),
        "date" => array (
            "description" => "End date",
            "helptext"    => "Leave empty to display always",
            "datatype"    => "unixdate",
            "location"    => "extra2"
        ),
        "description" => array (
            "description" => "Description",
            "datatype"    => "text",
            "location"    => "content",
            "widget"      => "html"
        ),
        "location" => array (
            "description" => "Location",
            "datatype"    => "text",
            "location"    => "parameter"
        )
    )
),
