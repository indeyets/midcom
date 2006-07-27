
"article" =>   array (
	"name"        => "article",
	"description" => "Default Article", 
	"fields"      => array (

        "name" => array (
                    "description" => "Article name",
                    "datatype"    => "text",
                    "location"    => "name",
                    "start_fieldgroup" => array (
                        "title" => "Details" ) ,
                        ),
        /*
        "topic" => array (
                    "description" => "",
                    "datatype"    => "text",
                    "location"    => "topic",
                    "widget"      => "text"), 
        */ 

         "title" => array (
                    "description" => "Title",
                    "datatype"    => "text",
                    "location"    => "title", 
                    "required" => true,
                ),
                
        "abstract" => array (
                    "description" => "Article abstract",
                    "datatype"    => "text",
                    "location"    => "abstract",
                    "widget"      => "text",
                    "widget_text_inputstyle" => "longtext",
                    "widget_text_height" => "5em",
                    "end_fieldgroup" => "",
                    ), 
                /* Start of component required fields */	

		"id" => array (
		            "description" => "Article id",
		            "datatype"    => "text",
		            "location"    => "id",
		            "widget"      => "text",
		            "readonly"   => true,
		            "start_fieldgroup" => array (
                       	"title" => "Advanced" ) ,
		            ),
                    
        "sitegroup" => array (
                    "description" => "Sitegroup",
                    "datatype"    => "text",
                    "location"    => "sitegroup",
                    "widget"      => "text",
                    "hidden"      => "true"
                    ),
           
		"guid" => array (
		            "description" => "Global ID (GuID)",
		            "datatype"    => "text",
		            "location"    => "guid",
                    "readonly"   => true,
		            "widget"      => "text"
		            ),		            

		"type" => array (
		            "description" => "Type",
		            "datatype"    => "text", //todo: should be integer.
		            "location"    => "type",
		            "widget"      => "text"), 
		"url" => array (
		            "description" => "URL",
		            "datatype"    => "text",
		            "location"    => "url",
		            "widget"      => "text"),
		"realm" => array (
		            "description" => "Realm",
		            "datatype"    => "text",
		            "location"    => "realm",
		            "widget"      => "text"), 
		             
		
		"action" => array (
		            "description" => "Action",
		            "datatype"    => "text",
		            "location"    => "action",
		            "widget"      => "text"), 
		
		
		"calstart" => array (
		            "description" => "Calstart",
		            "datatype"    => "text",
		            "location"    => "calstart",
		            "widget"      => "text"), 
		
		"caldays" => array (
		            "description" => "Caldays",
		            "datatype"    => "text",
		            "location"    => "caldays",
		            "widget"      => "text"), 
		
		"icon" => array (
		            "description" => "Icon",
		            "datatype"    => "text",
		            "location"    => "icon",
		            "widget"      => "text"), 
		
		"view" => array (
		            "description" => "View",
		            "datatype"    => "text",
		            "location"    => "view",
		            "widget"      => "text"), 
		
		"print" => array (
		            "description" => "Print",
		            "datatype"    => "text",
		            "location"    => "print",
		            "widget"      => "text"), 
		
		"extra1" => array (
		            "description" => "Extra 1",
		            "datatype"    => "text",
		            "location"    => "extra1",
		            "widget"      => "text"), 
		
		"extra2" => array (
		            "description" => "Extra 2",
		            "datatype"    => "text",
		            "location"    => "extra2",
		            "widget"      => "text"), 
		
		"extra3" => array (
		            "description" => "Extra 3",
		            "datatype"    => "text",
		            "location"    => "extra3",
		            "widget"      => "text"), 
		
		    
		
		"score" => array (
		            "description" => "Score",
		            "datatype"    => "text",
		            "location"    => "score",
		            "widget"      => "text",
					"end_fieldgroup" => "",
		            "hidden" 	  => false),
		
		"content" => array (
		            "description" => "Content",
		            "datatype"    => "text",
		            "location"    => "content",
		            "start_fieldgroup" => array (
                        "title" => "Content") ,
		            "end_fieldgroup" => "",
		            "widget"      => "html"), 
		
		/* todo : remove into metadata */
		"revised" => array (
		            "description" => "Revised",
		            "datatype"    => "text",
		            "location"    => "revised",
					"start_fieldgroup" => array (
                        "title" => "metadata"), 
		            "widget"      => "text"),
		            
		
		"lang" => array (
		            "description" => "Language",
		            "datatype"    => "text",
		            "location"    => "lang",
		            "widget"      => "text"), 
		
		"sid" => array (
		            "description" => "Sid",
		            "datatype"    => "text",
		            "location"    => "sid",
		            "widget"      => "text"), 
		
		"contentauthor" => array (
		            "description" => "Content Author",
		            "datatype"    => "text",
		            "location"    => "contentauthor",
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
		
		"approved" => array (
		            "description" => "Approved",
		            "datatype"    => "text",
		            "location"    => "approved",
		            "widget"      => "text"), 
		
		"locked" => array (
		            "description" => "Locked",
		            "datatype"    => "text",
		            "location"    => "locked",
		            "widget"      => "text"), 
		
		"creator" => array (
		            "description" => "Creator",
		            "datatype"    => "text",
		            "location"    => "creator",
		            "widget"      => "text"), 
		
		"revisor" => array (
		            "description" => "Revisor",
		            "datatype"    => "text",
		            "location"    => "revisor",
		            "widget"      => "text"), 
		
		"approver" => array (
		            "description" => "Approver",
		            "datatype"    => "text",
		            "location"    => "approver",
		            "widget"      => "text"), 
		
		"locker" => array (
		            "description" => "Locker",
		            "datatype"    => "text",
		            "location"    => "locker",
					"end_fieldgroup" => "",
		            "widget"      => "text"), 
		
		/*
		"up" => array (
		            "description" => "Up",
		            "datatype"    => "text",
		            "location"    => "up",
		            "widget"      => "text"), 
		*/
		// Metadata
		/*
        'calendar' => array (
            'description' => 'enable calendar',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'start_fieldgroup' => array (
                'title' => 'Application modules',
            ),
        ),

        'projects' => array (
            'description' => 'enable project creation',
            'datatype'    => 'privilege',
            'privilege'   => 'midgard:create',
            'privilege_classname' => 'org_openpsa_projects_project',
            'end_fieldgroup' => '',
        ),
		*/
	) 
),
"old_article" => array (
    "name"        => "old_article",
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
