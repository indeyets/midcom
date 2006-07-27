"1" => array (

    "name"        => "1",
    "description" => "Shoplifting",
    "fields"      => array (
        "timestamp" => array (
            "description" => "Date of incident",
            "datatype" => "unixdate",
            "location" => "extra",
            "date_withtime" => true,
            "index_method" => "title"
        ),
        "department" => array (
            "description" => "Department",
            "datatype"    => "text",
            "location"    => "parameter"
        ),
        "caughtby"=> array (
            "description" => "Caught By",
            "datatype"    => "text",
            "location"    => "parameter"
        ),
        "shoplifter" => array (
            "description" => "Shoplifter",
            "datatype"    => "text",
            "location"    => "parameter"
        ),
        "reportedto" => array (
            "description" => "Reported to",
            "datatype"    => "text",
            "location"    => "parameter"
        ),
        "amount" => array (
            "description" => "Amount in EUR",
            "datatype"    => "text",
            "location"    => "parameter"
        ),
    )
),
"2" => array (

    "name"        => "2",
    "description" => "Training",
    "fields"      => array (
        "timestamp" => array (
            "description" => "Date of incident",
            "datatype" => "unixdate",
            "location" => "extra",
            "date_withtime" => true
        ),
        "type" => array (
            "description" => "Type",
            "datatype"    => "text",
            "location"    => "parameter"
        ),
        "participants"=> array (
            "description" => "Number of Participants",
            "datatype"    => "text",
            "location"    => "parameter"
        ),
        "description" => array (
            "description" => "Description",
            "datatype"    => "text",
            "location"    => "parameter",
            "index_method"   => "title"
        ),
    )
),
