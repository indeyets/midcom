
/*
 * IMPORTANT NOTE: Do not use the extraN fields in your schemas unless you are
 * using newstickers show by date functionality.
 * */

"default" => array (
    "name"        => "default",
    "description" => "News Article",
    "fields"      => array (
        "date" => array (
            "description" => "Date",
            "datatype"    => "unixdate",
            "location"    => "created", 
            "hidden" => true,
        ),
        "name" => midcom_helper_datamanager_urlname_field(),
        "title" => array (
            "description" => "Title",
            "datatype"    => "text",
            "location"    => "title", 
            "required" => true,
        ),
        "abstract" => array (
            "description" => "Abstract",
            "datatype"    => "text",
            "location"    => "abstract",
            "widget_text_inputstyle" => "longtext",
            /* abstracs are usually shorter than the full text, so we set a smaller 
             * size for it
             */
            "widget_text_height" => "8em",
        ),
        "content" => array (
            "description" => "Text",
            "datatype"    => "text",
            "location"    => "content",
            "widget"      => "html"
        ),
        "created" => array (
            "description" => "Date",
            "datatype"    => "unixdate",
            "location"    => "created",
            "date_withtime" => true,
            "update_article_created" => true,
        ),
        "image" => array (
            "description" => "Image",
            "datatype"    => "image",
            "location"    => "attachment",
            "widget_image_simple" => true,
        )
    )
), // default

"guestbook" => array (
    "name"        => "guestbook",
    "description" => "Guestbook entry",
    "fields"      => array (
        "name" => array (
            "description" => "URL name",
            "datatype" => "text",
            "location" => "name",
            "readonly" => true,
            "aisonly" => true,
        ),
        "title" => array (
            "description" => "Title",
            "datatype" => "text",
            "location" => "title", 
            "required" => true,
        ),
        "author" => array (
            "description" => "Author",
            "datatype" => "text",
            "location" => "parameter", 
            "required" => true,
        ),
        "content" => array (
            "description" => "Text",
            "datatype" => "communityhtml",
            "location" => "content",
            "required" => true,
        ),
        "client_ip" => array (
            "description" => "Client IP Address",
            "datatype" => "text",
            "location" => "parameter",
            "aisonly" => true,
            "readonly" => true,
        ),
        "client_ua" => array (
            "description" => "Client User Agent",
            "datatype" => "text",
            "location" => "parameter",
            "aisonly" => true,
            "readonly" => true,
        ),
        "created" => array (
            "description" => "Entry creation time",
            "datatype" => "unixdate",
            "date_withtime" => true,
            "location" => "created",
            "aisonly" => true,
            "readonly" => true,
        ),
        "revised" => array (
            "description" => "Entry last modified time",
            "datatype" => "unixdate",
            "date_withtime" => true,
            "location" => "revised",
            "aisonly" => true,
            "readonly" => true,
        ),
    )
), // guestbook
