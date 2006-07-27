"config" => array (
 "name"        => "config",
 "description" => "Default Configuration Schema", /* This is a topic */
 "locktimeout" => 0, /* Don't lock this, we display the edit view always */
 "fields"      => array (


  /* Anonymous posting settings */

  "allow_anonymous" => array (
   "description" => $GLOBALS["view_l10n"]->get("allow anonymous voting"),
   "datatype" => "boolean",
   "location" => "config",
   "config_domain" => "net.nemein.quickpoll",
   "config_key" => "allow_anonymous",
   "start_fieldgroup" => Array ( "title" => $GLOBALS["view_l10n"]->get("anonymous voting") ),
  ),
  "anonymous_username" => array (
   "description" => $GLOBALS["view_l10n"]->get("anonymous username"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "net.nemein.quickpoll",
   "config_key" => "anonymous_username",
  ),
  "anonymous_password" => array (
   "description" => $GLOBALS["view_l10n"]->get("anonymous password"),
   "datatype" => "text",
   "location" => "config",
   "config_domain" => "net.nemein.quickpoll",
   "config_key" => "anonymous_password",
   "end_fieldgroup" => "",
  ),
  "allow_multiple_votes" => array (
   "description" => $GLOBALS["view_l10n"]->get("allow multiple votes for ip/username"),
   "datatype" => "boolean",
   "location" => "config",
   "config_domain" => "net.nemein.quickpoll",
   "config_key" => "allow_multiple_votes",
   "start_fieldgroup" => Array ( "title" => $GLOBALS["view_l10n"]->get("vote limits") ),
   "end_fieldgroup" => "",
  ),

 ),
),