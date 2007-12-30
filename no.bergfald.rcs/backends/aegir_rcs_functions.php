<?php
/**
 * @author tarjei huse
 * @package no.bergfald.rcs
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This code stores (in rcs)
 * and generates repligard compatible xml files
 */
// mgd_include_snippet("/NemeinRCS/xml_rcs_functions");
$xmlformat = array(
    "person" => array(
        "username" => "CDATA",
         "firstname" => "CDATA",
         "lastname" => "CDATA",
         "birthdate" => "CDATA",
         "street" => "CDATA",
         "postcode" => "CDATA",
         "city" => "CDATA",
         "homephone" => "CDATA",
         "handphone" => "CDATA",
         "workphone" => "CDATA",
         "homepage" => "CDATA",
         "email" => "CDATA",
         "extra" => "CDATA",
         "img" => "blobs",
         "info" => "CDATA",
         "topic" => "topic",
         "subtopic " => "topic",
         "department " => "grp",
         "office" => "STR",
         "pgpkey" => "CDATA",
         "created" => "STR",
         "creator" => "person",
         "sitegroup" => "sitegroup"),
     "grp" => array(
        "city" => "CDATA",
         "name" => "CDATA",
         "official" => "CDATA",
         "postcode" => "CDATA",
         "city" => "CDATA",
         "homepage" => "CDATA",
         "email" => "CDATA",
         "extra" => "CDATA",
         "owner" => "grp",
         "sitegroup" => "sitegroup"),
     "member" => array(
        "extra" => "CDATA",
         "info" => "CDATA",
         "uid" => "person",
         "gid" => "grp",
         "sitegroup" => "sitegroup"),
     "host" => array(
        "name" => "CDATA",
         "info" => "CDATA",
         "port" => "STR",
         "online" => "STR",
         "prefix" => "CDATA",
         "owner" => "grp",
         "root" => "page",
         "style" => "style",
         "sitegroup" => "sitegroup"),
     "snippet" => array (
        "up" => "snippetdir",
         "name" => "CDATA",
         "code" => "CDATA",
         "doc" => "CDATA",
         "author" => "CDATA",
         "creator" => "person",
         "created" => "STR",
         "revisor" => "person",
         "revised" => "STR",
         "revision" => "STR",
         "sitegroup" => "sitegroup"),
     "snippetdir" => array(
        "up " => "snippetdir",
         "name" => "CDATA",
         "description" => "CDATA",
         "owner" => "grp",
         "sitegroup" => "sitegroup"),
     "page" => array(
        "up" => "page",
         "style" => "style",
         "name" => "CDATA",
         "title" => "CDATA",
         "content" => "CDATA",
         "author" => "person",
         "info" => "STR",
         "changed" => "STR",
         "sitegroup" => "sitegroup"),
     "page_element" => array(
        "page" => "page",
         "name" => "CDATA",
         "value" => "CDATA",
         "info" => "STR",
         "sitegroup" => "sitegroup"),
     "pageelement" => array(
        "page" => "page",
         "name" => "CDATA",
         "value" => "CDATA",
         "info" => "STR",
         "sitegroup" => "sitegroup"),
     "style" => array(
        "up" => "style",
         "name" => "CDATA",
         "owner" => "grp",
         "sitegroup" => "sitegroup"),
     "element" => array(
        "style" => "style",
         "name" => "CDATA",
         "value" => "CDATA",
         "sitegroup" => "sitegroup"),
     "topic" => array(
        "up" => "topic",
         "name" => "CDATA",
         "extra" => "CDATA",
         "owner" => "grp",
         "score" => "STR",
         "description" => "CDATA",
         "revised" => "STR",
         "created" => "STR",
         "revisor" => "person",
         "creator" => "person",
         "revision" => "STR",
         "code" => "CDATA",
         "sitegroup" => "sitegroup"),
     "article" => array(
        "up" => "article",
         "topic" => "topic",
         "title " => "CDATA",
         "abstract" => "CDATA",
         "content" => "CDATA",
         "author" => "person",
         "created" => "STR",
         "url" => "CDATA",
         "calstart" => "STR",
         "caldays" => "STR",
         "icon" => "STR",
         "view" => "STR",
         "print" => "STR",
         "extra1" => "CDATA",
         "extra2" => "CDATA",
         "extra3" => "CDATA",
         "name " => "CDATA",
         "creator" => "person",
         "revisor" => "person",
         "revision" => "STR",
         "approver" => "person",
         "revised" => "STR",
         "approved" => "STR",
         "score" => "STR",
         "type" => "STR",
         "locked" => "STR",
         "locker" => "person",
         "sitegroup" => "sitegroup"),
     "blobs" => array(
        "title" => "CDATA",
         "score" => "STR",
         "mimetype" => "CDATA",
         "location" => "BLOB",
         "ptable" => "STR",
         "author" => "person",
         "name" => "CDATA",
         "created" => "STR",
         "pid" => "PID" ,
         "sitegroup" => "sitegroup"),
     "record_extension" => array(
        "tablename" => "PTABLE",
         "domain" => "CDATA",
         "value" => "CDATA",
         "oid" => "OID",
         "name" => "CDATA",
         "sitegroup" => "PSITEGROUP"),
     "sitegroup" => array(
        "name" => "CDATA",
         "realm" => "CDATA",
         "admingroup" => "grp")
    );



function checkmail ($email)
{
     if (eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-wyz][a-z](g|l|m|pa|t|u|v)?$", $email, $check)){
         if (checkdnsrr(substr(strstr($check[0], '@'), 1), "ANY")){
             return TRUE;
             }
         }
     return FALSE;
    }

function rcs_check_ci_file_exists()
{
     if (file_exists ("/tmp/nadmin_ci_exists")){
         return 1;
         }elseif (file_exists("/usr/bin/ci")){
         @touch("/tmp/nadmin_ci_exists");
         return 1;
         }elseif (file_exists("/usr/sbin/ci")){
         @touch("/tmp/nadmin_ci_exists");
         return 1;
         }elseif (file_exists("/usr/local/bin/ci")){
         @touch("/tmp/nadmin_ci_exists");
         return 1;
         }elseif (file_exists("/usr/local/sbin/ci")){
         @touch("/tmp/nadmin_ci_exists");
         return 1;
         }else{
         return 0;
         }
    }

function rcs_check_co_file_exists()
{
     if (file_exists("/tmp/nadmin_co_exists")){
         return 1;
         }elseif (file_exists("/usr/bin/co")){
         @touch("/tmp/nadmin_co_exists");
         return 1;
         }elseif (file_exists("/usr/sbin/co")){
         @touch("/tmp/nadmin_co_exists");
         return 1;
         }elseif (file_exists("/usr/local/bin/co")){
         @touch("/tmp/nadmin_co_exists");
         return 1;
         }elseif (file_exists("/usr/local/sbin/co")){
         @touch("/tmp/nadmin_co_exists");
         return 1;
         }else{
         return 0;
         }
    }


function rcs_exec($command)
{
     $fh = popen($command, "r");
     $ret = "";
     while ($reta = fgets($fh, 1024))
     $ret .= $reta;
     pclose($fh);

     return $ret;
    }
/**
 * Get a list of the objects history
 */
function rcs_gethistory($what)
{
     // global $argv;
    $history = rcs_exec('rlog "' . $what . ',v"');
     $revisions = array();
     $lines = explode("\n", $history);

     for ($i = 0; $i < count($lines); $i++){
         if (substr($lines[$i], 0, 9) == "revision "){
             $currentrev = substr($lines[$i], 9);

             $i += 2;

             while ($i < count($lines) && substr($lines[$i], 0, 4) != '----' && substr($lines[$i], 0, 5) != '====='){
                 if (array_key_exists($currentrev, $revisions)){
                     $revisions[$currentrev] .= $lines[$i] . " ";
                 }else{
                     $revisions[$currentrev] = $lines[$i] . " ";
                 }
                 $i++;
             }
         }
    }
    // print_r($revisions);
    return $revisions;
}

function rcs_checkout_nolock($what)
{
     rcs_exec('rcs -u "' . $what . ',v" 2>/dev/null');
     return rcs_exec('co -U -p "' . $what . ',v" 2>/dev/null');
    }

function rcs_checkout_lock($what)
{
     rcs_exec("rcs -u \"" . $what . ",v\" 2>/dev/null");

     if (file_exists($what . ",v")){
         rcs_exec("co -l -p \"" . $what . ",v\" 2>/dev/null");
         }else{
         return 0;
         }
    }

function rcs_checkdiff($what, $version, $prevvers)
{
     if (file_exists($what . ",v")){
         $curname = "";

         unset($dataarray);

         rcs_exec("rcs -u \"" . $what . ",v\" 2>/dev/null");

         // $tempdata=explode("\n",rcs_exec("rcsdiff -r$prevvers -r$version --context=3 \"".$what.",v\" 2>/dev/null"));
        $tempdata = explode("\n", rcs_exec('rcsdiff -r' . $prevvers . ' -r' . $version . ' --unified=3 "' . $what . ',v" 2>/dev/null'));

         for ($i = 0; $i < count($tempdata); $i++){
             // if (ereg("^\*\*\*\*\*\*\*\*\*\*\*\*\*",$tempdata[$i])) {
            // $curname="";
            // } elseif (ereg("^\*\*\* ([^\*]*) \*\*\*",$tempdata[$i],$temp)) {
            // $curname="body1";
            // $lines=$temp[1];
            // } elseif (ereg("^\-\-\- ([^\-]*) \-\-\-",$tempdata[$i],$temp)) {
            // $curname="body2";
            // $lines=$temp[1];
            // }
            if (preg_match("/^\@\@ ([^\@]*) \@\@/", $tempdata[$i], $temp)){
                 $curname = "body";
                 $lines = $temp[1];
                 }elseif (preg_match("/^\+\+\+ ([^\*]*$)/", $tempdata[$i], $temp)){
                 $dataarray["head1"] .= $temp[1];
                 $curname = "";
                 }elseif (preg_match("/^\-\-\- ([^\-]*$)/", $tempdata[$i], $temp)){
                 $dataarray["head2"] .= $temp[1];
                 $curname = "";
                 }else{
                 if ($curname){
                     if (strpos($curname, "body") !== false){
                         $dataarray["body"][$lines] .= $tempdata[$i] . "\n";
                         }
                     }
                 }
             }
         return $dataarray;
         }else{
         return 0;
         }
    }

function rcs_checkout($what, $version)
{
     global $xmlformat;

     if (!file_exists($what . ",v"))
         return 0;

     $curname = "";

     if (isset($dataarray))
         unset($dataarray);

     $tempdata = explode("\n", rcs_exec("co -p" . $version . " \"" . $what . ",v\" 2>/dev/null"));
     // NOW READ THE simple XML format!
    $status["block"] = "";
     $status["level"] = 0;
     $status["table"] = "";

     for ($i = 0; $i < count($tempdata); $i++){
         // is it the start or end of a block
        if (isset($args))
             unset($args);

         switch ($status["level"]){
         case 0: // then I only expect "<object>";
            if (eregi("^[ ]*<([a-z_]+) id=\"[a-z0-9-]+\" [a-z]+=\"[0-9]+\">", $tempdata[$i], $args)){
                 $status["table"] = $args[1];
                 $status["level"] = 1;
                 }
             break;
         case 1:
             if (eregi("^[ ]*<([a-z]+)><!\[CDATA\[(.*)$", $tempdata[$i], $args)){
                 // begining CDATA!
                $status["block"] = $args[1];
                 $status["level"] = 2;
                 $dataarray[$status["block"]] = $args[2] . "\n";

                 // option for single line data!
                if (ereg("\]\]></" . $status["block"] . ">$", $args[2])){
                     $tmp = ereg_replace("\]\]></" . $status["block"] . ">$", "", $args[2]);
                     $status["level"] = 1;
                     $dataarray[$status["block"]] = $tmp;
                     $status["block"] = "";
                     }
                 }elseif (eregi("^[ ]*<([a-z]+)>([^<]+)</([a-z]+)>[ ]*$", $tempdata[$i], $args)){
                 if ($xmlformat[$status["table"]][$args[1]] == "STR"){
                     $dataarray[$args[1]] = $args[2];
                     }else{
                     $subobj = mgd_get_object_by_guid($args[2]);
                     $dataarray[$args[1]] = $subobj -> id;
                     }
                 }elseif (eregi("^[ ]*<([a-z]+)></([a-z]+)>[ ]*$", $tempdata[$i], $args) ||
                     eregi("^[ ]*<([a-z]+)/>[ ]*$", $tempdata[$i], $args)
                    ){
                 $dataarray[$args[1]] = 0;

                 if (in_array($xmlformat[$status["table"]][$args[1]], array("CDATA", "STR"))){
                     $dataarray[$args[1]] = "";
                     }
                 }elseif (eregi("^[ ]*</" . $status["table"] . ">[ ]*$", $tempdata[$i])){
                 $status["level"] = 0;
                 }
             break;

         case 2:
             if (ereg("(.*)\]\]></" . $status["block"] . ">[ ]*$", $tempdata[$i], $args)){
                 $dataarray[$status["block"]] .= $args[1];
                 $status["block"] == "";
                 $status["level"] = 1;
                 }else{
                 $dataarray[$status["block"]] .= $tempdata[$i] . "\n";
                 }
             break;
             }
         }

     if ($dataarray)
         return $dataarray;

     return 0;
    }

function rcs_checkin($what, $message, $fileexists)
{
     if ($fileexists){
         return rcs_exec("ci -u -m\"" . $message . "\" \"" . $what . "\"");
         }else{
         return rcs_exec("ci -m\"" . $message . "\" \"" . $what . "\"");
         }
    }

function rcs_createfile($object, $parentobject = "")
{
     global $xmlformat;

     $thisdef = $xmlformat[$object -> __table__];

     if (!$thisdef){
         echo "ERROR CREATING RCS FILE FROM OBJECT";
         return;
         }

     if ($object -> guid()){
         $ret = "<" . $object -> __table__ . " id=\"" . $object -> guid() . "\" changed=\"" . date("YmdHis") . "\">\n";
         }else{
         $ret = "<" . $object -> __table__ . " id=\"" . $object -> id . "\" changed=\"" . date("YmdHis") . "\">\n";
         }

     reset($thisdef);

     while (list($k, $v) = each($thisdef)){
         switch ($v){
         case "STR":
             if ($object -> $k){
                 $ret .= "<$k>" . $object -> $k . "</$k>\n";
                 }else{
                 $ret .= "<$k/>\n";
                 }
             break;

         case "CDATA":
             if ($object -> $k){
                 $ret .= "<$k><!" . "[CDATA[" . $object -> $k . "]" . "]></$k>\n";
                 }else{
                 $ret .= "<$k/>\n";
                 }
             break;

         case "PID":
             if ($object -> $k){
                 // get pid
                $pfunc = "mgd_get_" . $object -> ptable;
                 $pobject = $pfunc($object -> $k);
                 $ret .= "<$k>" . $pobject -> guid() . "</$k>\n";
                 }else{
                 $ret .= "<$k/>\n"; // ouch this would be a problem!
                 }
             break;

         case "OID":
             $ret .= "<$k>" . $parentobject -> guid() . "</$k>\n";
             break;

         case "PTABLE":
             $ret .= "<$k>" . $parentobject -> __table__ . "</$k>\n";
             break;

         case "PSITEGROUP":
             if ($psitegroup){
                 $psitegroup = mgd_get_sitegroup($parentobject -> sitegroup);
                 $ret .= "<$k>" . $psitegroup -> guid() . "</$k>\n";
                 }else{
                 $ret .= "<$k/>\n";
                 }
             break;

         case "BLOB":
             $ret .= "<$k locid=\"" . $object -> $k . "\">\n";

             $buffer = "";
             $fh = $filehandle = mgd_open_attachment($object -> id, "r");
             while (!feof($fh))
             $buffer .= fgets($fh, 4096);

             $ret .= chunk_split(base64_encode($buffer));
             $ret .= "</$k>\n";
             break;

         default:
             if (!$object -> $k){
                 $ret .= "<$k/>\n";
                 }else{
                 $func = "mgd_get_$v";

                 if ($v == "grp")
                     $func = "mgd_get_group";
                 $link = $func($object -> $k);

                 if ($link -> guid()){
                     $ret .= "<$k>" . $link -> guid() . "</$k>\n";
                     }else{
                     $ret .= "<$k>" . $link -> __table__ . "_" . $link -> id . "</$k>\n";
                     }
                 }
             break;
             }
         }
     $ret .= "</" . $object -> __table__ . ">\n";
     return $ret;
    }


function view_pwalk($thisid, $type, $oldname)
{
     global $filetree, $filetreeid, $pos, $id;

     if (in_array($type, array("page", "style", "snippetdir", "topic"))){
         $tmpfunc = "mgd_get_" . $type;
         if (!$thisid or !$p = $tmpfunc($thisid)){
             return;
             }
         unset($tmpfunc);
         }


     if ($p -> id == $id && 0){
         $filetree[$pos] = str_replace("+", " ", urlencode($oldname));
         }else{
         $filetree[$pos] = str_replace("+", " ", urlencode($p -> name));
         }

     $pos++;
     view_pwalk($p -> up, $type, $oldname);
    }



function rcs_getfilename($object)
{
     global $rcsroot, $SERVER_NAME, $error_message;

     if (!rcs_check_ci_file_exists()){
         $error_message = "please install rcs for this to work";
         return 0;
         }elseif (!rcs_check_co_file_exists()){
         $error_message = "please install rcs for this to work";
         return 0;
         }

     if (!rcs_check_exist()){
         $error_message = "please configure write access to $rcsroot<br>";
         $error_message .= "e.g. mkdir $rcsroot<br>";
         $error_message .= "chown nobody nobody $rcsroot for Red Hat 6<br>";
         $error_message .= "chown apache apache $rcsroot for Red Hat 7<br>";
         $error_message .= "chown www-data www-data $rcsroot for Debian<br>";
         return 0;
         }elseif (!rcs_check_writable()){
         $error_message = "please configure write access to $rcsroot <br>e.g. mkdir $rcsroot<br>";
         $error_message .= "chown nobody nobody $rcsroot for Red Hat 6<br>";
         $error_message .= "chown apache apache $rcsroot for Red Hat 7<br>";
         $error_message .= "chown www-data www-data $rcsroot for Debian<br>";
         return 0;
         }

     if ($object -> guid())
         return $rcsroot . "/" . $object -> guid();
     else
         return $rcsroot . "/" . $object -> __table__ . "_" . $object -> id;
    }


?>