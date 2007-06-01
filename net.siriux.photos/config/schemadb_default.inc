/* This is a compatibility schema collection, which is fully compatible to 
 * the old, pre-dm photo gallery.
 */

"picture" => array (
 "name"        => "picture",
 "description" => "Photo",
 "fields"      => array (
  
  /**** General stuff ****/
  
  "name" => array (
   /* COMPONENT REQUIRED */
   "description" => "name",
   "location" => "name",
   "datatype" => "text",
   "readonly" => true,
   "start_fieldgroup" => Array ( "title" => "image data" ),
  ),
  
  "title" => array (
   /* COMPONENT REQUIRED */
   "description" => "title",
   "location" => "title",
   "datatype" => "text",
  ),
  
  "abstract" => array (
   "description" => "abstract",
   "location" => "abstract",
   "datatype" => "text",
   "widget_text_inputstyle" => "longtext",
  ),
  
  "description" => array (
   /* COMPONENT REQUIRED */
   "description" => "description",
   "location" => "content",
   "datatype" => "text",
   "widget_text_inputstyle" => "longtext",
   "end_fieldgroup" => "",   
  ),
    
  /**** EXIF stuff ****
   *
   * Note, that the following fields are currently automatically
   * populated by the default exif mapping scheme. You don't have
   * to include them though, you will have a couple of unused
   * parameters in case you omit them.
   */
  
  "fnumber" => array (
   "description" => "exif_fnumber",
   "location" => "parameter",
   "datatype" => "text",
   "readonly" => true,
   "start_fieldgroup" => Array ( "title" => "exif data" ),
   "hidden" => true,
  ),
  
  "iso" => array (
   "description" => "exif_iso",
   "location" => "parameter",
   "datatype" => "text",
   "readonly" => true,
   "hidden" => true,
  ),
  
  "exposure" => array (
   "description" => "exif_exposure",
   "location" => "parameter",
   "datatype" => "text",
   "readonly" => true,
   "hidden" => true,
  ),
  
  "make" => array (
   "description" => "exif_make",
   "location" => "parameter",
   "datatype" => "text",
   "hidden" => true,
   "readonly" => true,
  ),
  
  "model" => array (
   "description" => "exif_model",
   "location" => "parameter",
   "datatype" => "text",
   "readonly" => true,
   "hidden" => true,
   "end_fieldgroup" => "",
  ),
  
  
  /**** Meta stuff ****/

  "photographer" => array (
   "description" => "photographer",
   "location" => "extra1",
   "datatype" => "text",
   "start_fieldgroup" => Array ( "title" => "meta data" ),      
  ),
  
  "taken" => array ( 
   /* COMPONENT REQUIRED */
   "description" => "taken",
   "location" => "url",
   "datatype" => "unixdate",
   "date_withtime" => true,
   "update_article_created" => true,
  ),  

  "keywords" => array (
   /* COMPONENT REQUIRED */
   "description" => "keywords",
   "location" => "extra3",
   "datatype" => "text",
   "end_fieldgroup" => "",
  ),

  "approved" => array ( 
   /* COMPONENT REQUIRED */
   "description" => "approved",
   "location" => "approved",
   "datatype" => "unixdate",
   "date_withtime" => true,
   "readonly" => true,
   "hidden" => true,
  ),
  
  "score" => array (
   /* COMPONENT RECOMMENDED */
   "description" => "score",
   "location" => "score",
   "hidden" => true,
   "datatype" => "text",
  ),
  
  /**** Internal Stuff ****/

  "type" => array ( 
   /* COMPONENT REQUIRED */
   "description" => "Type",
   "location" => "type",
   "datatype" => "text",
   "hidden" => true,
  ),
  
  "scales" => array ( 
   /* COMPONENT REQUIRED */
   "description" => "Scales",
   "location" => "extra2",
   "datatype" => "text",
   "hidden" => true,
  ),
  
  
 )
),

"upload" => array (
 "name"        => "upload",
 "description" => "Upload Schema",
 "fields"      => array (
  
  "title" => array (
   /* COMPONENT REQUIRED */
   "description" => "title",
   "location" => "title",
   "datatype" => "text",
  ),
    
  "description" => array (
   /* COMPONENT REQUIRED */
   "description" => "description",
   "location" => "content",
   "datatype" => "text",
   "widget_text_inputstyle" => "longtext",
   "hidden" => true,
  ),
  
  "photographer" => array (
   "description" => "photographer",
   "location" => "extra1",
   "datatype" => "text",
  ),
  
  "keywords" => array (
   /* COMPONENT RECOMMENDED */
   "description" => "keywords",
   "location" => "extra3",
   "datatype" => "text",
  ),
  
  "upload_file" => array (
   /* COMPONENT REQUIRED */
   "description" => "upload file",
   "datatype" => "blob",
   "widget_blob_simple" => true,
  ),
  
  "create_subgallery" => array (
   /* COMPONENT RECOMMENDED */
   "description" => "create subgallery",
   "location" => "parameter",
   "datatype" => "boolean",
  ),
  
 )
),

