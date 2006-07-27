"topic" => array (
    "name"        => "topic",
    "description" => "Default Topic",
    "fields"      => array (
    "guid" => array (
            "description" => "GUID",
            "datatype"    => "text",
            "location"    => "guid",
            "widget"      => "text",
            "readonly"   => true,
            "start_fieldgroup" => array (
            "title" => "Details" ) ,
            ), 

    "id" => array (
            "description" => "ID",
            "datatype"    => "text",
            "location"    => "id",
            "widget"      => "text",
            "readonly"    => true
            ),

    "name" => array (
            "description" => "Name",
            "datatype"    => "text",
            "location"    => "name",
            "widget"      => "text",
                ), 
    "extra" => array (
            "description" => "Extra",
            "datatype"    => "text",
            "location"    => "extra",
            "widget"      => "text"), 

    "score" => array (
            "description" => "Score",
            "datatype"    => "text",
            "location"    => "score",
            "widget"      => "text"), 

    "action" => array (
            "description" => "Action",
            "datatype"    => "text",
            "location"    => "action",
            "widget"      => "text"), 

    "sitegroup" => array (
            "description" => "Sitegroup",
            "datatype"    => "text",
            "location"    => "sitegroup",
            "end_fieldgroup" => '',
            "widget"      => "text"), 

   "description" => array (
            "description" => "Description",
            "datatype"    => "text",
            "location"    => "description",
            "start_fieldgroup" => array (
                    "title" => "Description" ) ,
            "end_fieldgroup" => "", 
            "widget"      => "html"), 

    "code" => array (
            "description" => "Code",
            "datatype"    => "text",
            "location"    => "code",
            "start_fieldgroup" => array (
                    "title" => "Code" ) ,
            "end_fieldgroup" => "",
            "widget"      => "html"
            ), 

    "realm" => array (
            "description" => "Realm",
            "datatype"    => "text",
            "location"    => "realm",
            "widget"      => "text",
            "readonly"    => true,
            "start_fieldgroup" => array (
                    "title" => "Metadata" ) ,
            ), 

    "revised" => array (
            "description" => "Revised",
            "datatype"    => "text",
            "location"    => "revised",
            "widget"      => "text"), 

    "revision" => array (
            "description" => "Revision",
            "datatype"    => "text",
            "location"    => "revision",
            "widget"      => "text"), 

    "created" => array (
            "description" => "Created",
            "datatype"    => "text",
            "location"    => "created",
            "widget"      => "text"), 

    "revisor" => array (
            "description" => "Revisor",
            "datatype"    => "text",
            "location"    => "revisor",
            "widget"      => "text"), 

    "creator" => array (
            "description" => "Creator",
            "datatype"    => "text",
            "location"    => "creator",
            "widget"      => "text",
            "end_fieldgroup" => "",
            ), 
    
    //"up" => array (
    //            "description" => "",
    //            "datatype"    => "text",
    //            "location"    => "up",
    //            "widget"      => "text"), 
    // commented out , but here if you need it :)
    //"__table__" => array (
    //            "description" => "",
    //            "datatype"    => "text",
    //            "location"    => "__table__",
    //            "widget"      => "text"),
     
),
),