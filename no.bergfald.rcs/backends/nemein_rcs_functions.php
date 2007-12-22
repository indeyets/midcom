<?php
/**
 * @package no.bergfald.rcs
 *
 * GUID Based RCS functions for Midgard 1.4.1 (PHP4) and up
 * by Eero af Heurlin (eero.afheurlin@iki.fi), will
 * propably work with PHP3 as well but is not tested.
 *
 * This set uses the same XML format as NAdmin RCS, if you do not intend to
 * use NAdmin I suggest you use the simpler function set. Also unless you
 * absolutely need it I suggest you keep the $links2guids_default zero,
 * it causes major overhead and is not normally necessary.
 *
 * By reading the comments for the main functions and checking the
 * variables they take as parameters one should be able to use the
 * most basic RCS functions. For quickstart the main functions:
 *
 * rcs_create ($object, [links2guids], [$type], [$description], [$guid])
 * rcs_update ($object, [links2guids], [$type], [$message], [$guid])
 * rcs_get_version ($guid, $version)
 * rcs_delete ($guid)
 * rcs_diff ($guid, $version1, [$version2])
 * rcs_log ($guid, [$options])
 * rcs_check_version ($guid)
 *
 *
 * Note: 1.4.1 release and CVS versions older than 2001.06.11 have
 * a bug that causes $page_element->guid(); to return an empthy set,
 * if you intend to use these functions with page elements and cannot
 * update to newer version you need to figure a way to get the GUIDs
 * directly from MySQL and give them as parameters to the rcs_create and
 * rcs_update functions, rest of the functions require GUID as parameter
 * anyways.
 */

// *** Variables ***
/**
 * Checks if $rcsroot is set, if not defaults to /var/rcs, note that
 * the apache user/group must have read and write rights to this
 * directory (it's best to have it as owner of the directory)
 * TODO: Fix this to it is within MIDCOM_ROOT or something.
 */
if (!array_key_exists('rcsroot', $GLOBALS)){
     $rcsroot = "/var/rcs";
    }else{
     $rcsroot = $GLOBALS['rcsroot'];
    }
/**
 * Check if $links2guids_default is set, if not sets it to zero
 */
if (!array_key_exists('links2guids_default', $GLOBALS)){
     $links2guids_default = 0;
    }else{
     $links2guids_default = $GLOBALS['links2guids_default'];
    }


// Uncomment to enable a LOT of debug messages
// $debug=1;
// *** Main Functions ***

/**
 * This function takes an object and adds it to RCS, it should be
 * called just after $object->create(). Remember that you first need
 * to mgd_get the object since $object->create() returns only the id,
 * one way of doing this is:
 *
 * $id=$object->create();
 * $object=mgd_get_XXX($id); (here XXX is of course the object type)
 *
 *
 * If the type parameter is omitted the function will use GUID to
 * determine the type, this makes an extra DB query.
 */
function rcs_create ($object, $links2guids = "", $type = "", $description = "Midgard RCS data", $guid = ""){
     global $rcsroot;
    global $debug;
    unset ($output);
    unset($status);
    global $links2guids_default;
     if (!$links2guids) $links2guids = $links2guids_default;
     if (!($guid <> "")){
         if ($debug == 1) echo "rcs_create debug: GUID not given, fetching<br>";
         $guid = $object -> guid();
         }
     if ($debug == 1) echo "rcs_create debug: GUID is: $guid<br>";
     if (!($guid <> "")) return 3;
     if ($type == ""){
        $obj = mgd_get_object_by_guid($guid);
        $type = $obj -> __table__;
    }
     $data = rcs_object2data2($object, $type, $links2guids);
     if ($debug == 1) echo "rcs_create debug: data to be written<br><pre>$data</pre>";
     rcs_writefile($guid, $data);
     $command = "ci -i -t-'" . $description . "' " . $rcsroot . "/" . $guid;
     if ($debug == 1) echo "rcs_create debug: command=$command<br>";
     exec ($command, $output, $status);
     if ($debug == 1){
         echo "rcs_create debug: command exit status=$status<br>";
         echo "rcs_create debug: command output follows:<br><pre>";
         print_r ($output);
        echo "</pre>";
         }
     $filename = $rcsroot . "/" . $guid . ",v";
     if ($debug == 1) echo "rcs_create debug: setting file mode to 0770 to file: $filename<br>";
     chmod ($filename, 0770);
     return $status;
    }

/**
 * This function takes an object and updates it to RCS, it should be
 * called just before $object->update(), if the type parameter is omitted
 * the function will use GUID to determine the type, this makes an
 * extra DB query.
 */
function rcs_update ($rcsroot, $object, $links2guids = "", $type = "", $message = "Changed via Midgard", $guid = ""){
     //$rcsroot = $GLOBALS['rcsroot'];
     $debug = $GLOBALS['debug'];
     $output = null;
     //unset ($output);
     //unset($status);
     $status = null;
     if (!$links2guids) {
        $links2guids = $GLOBALS['links2guids_default'];
     }

     if (!($guid <> "")){
         if ($debug == 1) {
             echo "rcs_update debug: GUID not given, fetching<br>";
         }
         $guid = $object->guid;
     }

     if (!($guid <> "")) return 3;

     if ($type == "" ) {
        $obj = mgd_get_object_by_guid($guid);
        $type = $obj -> __table__;
     }
     $rcsfilename = $rcsroot . "/" . $guid . ",v";

     if (!file_exists($rcsfilename)){
         if ($debug == 1) echo "rcs_update debug: RCS file not found, calling rcs_create()<br>";
         rcs_create($object, $links2guids, $type, $message, $guid);
         return 2;
         }
     //if ($debug == 1) echo "rcs_update debug: Checking out latest version<br>";
     $command = "co -l " . $rcsroot . "/" . $guid;
     //if ($debug == 1) echo "rcs_update debug: command=$command<br>";
     exec ($command, $output, $status);
     /*
     if ($debug == 1){
         echo "rcs_update debug: command exit status=$status<br>";
         echo "rcs_update debug: command output follows:<br><pre>";
         print_r ($output);
        echo "</pre>";
         echo "rcs_update debug: Generating new data<br>";
         }
     */
     $data = rcs_object2data2($object, $type, $links2guids);

     //if ($debug == 1) echo "rcs_update debug: data to be written<br><pre>$data</pre>";
     rcs_writefile($guid, $data);

     //if ($debug == 1) echo "rcs_update debug: Checking in new data<br>";
     $command = "ci -m'" . $message . "' " . $rcsroot . "/" . $guid;
     //if ($debug == 1) echo "rcs_update debug: command=$command<br>";
     unset ($output);
    unset ($status);
     exec ($command, $output, $status);
     /*
     if ($debug == 1){
         echo "rcs_update debug: command exit status=$status<br>";
         echo "rcs_update debug: command output follows:<br><pre>";
         print_r ($output);
        echo "</pre>";
         }
         */
     $filename = $rcsroot . "/" . $guid . ",v";
     // if ($debug == 1) echo "rcs_update debug: setting file mode to 0770 to file: $filename<br>";
     chmod ($filename, 0770);
     return $status;
    }

/**
 * This function takes a GUID and deletes the corresponding RCS
 * entry, should be called just before $object->delete
 */
function rcs_delete ($guid){
     global $rcsroot;
     global $debug;
     $command = "rm -f " . $rcsroot . "/" . $guid . "*";
     if ($debug == 1) echo "rcs_delete debug: command=$command<br>";
     unset ($output);
    unset ($status);
     exec ($command, $output, $status);
     if ($debug == 1){
         echo "rcs_delete debug: command exit status=$status<br>";
         echo "rcs_delete debug: command output follows:<br><pre>";
         print_r ($output);
        echo "</pre>";
         }
     return $status;
    }

/**
 * This function takes a GUID and RCS version number, it returns
 * the corresponding object
 */
function rcs_get_version ($guid, $version)
{
     global $rcsroot;
     global $debug;
     settype ($version, "double");
     $command = "co -r" . $version . " " . $rcsroot . "/" . $guid;
     unset ($output);
     unset ($status);
     exec ($command, $output, $status);
     $data = rcs_readfile($guid);
     $object = rcs_data2object($data, $guid);
     $command = "rm -f " . $rcsroot . "/" . $guid;
     unset ($output);
    unset ($status);
     exec ($command, $output, $status);
     return $object;
}


/**
 * Returns a diff between two versions of a GUID. If version2 is omitted
 * it's supposed to be the latest version. Parsing the diff output to
 * preferred look'n'feel is left as an exercise for the user.
 */
function rcs_diff ($guid, $version1, $version2 = "", $options = "-q"){
     global $rcsroot;
    global $debug;
     if ($version2 == "") $version2 = rcs_check_version($guid);
     settype ($version1, "double");
    settype ($version2, "double");
     $command = "rcsdiff " . $options . " -r" . $version1 . " -r" . $version2 . " " . $rcsroot . "/" . $guid;
     if ($debug == 1) echo "rcs_diff debug: command value=$command<br>";
     unset ($output);
    unset ($status);
     exec ($command, $output, $status);
     if ($debug == 1) echo "rcs_diff debug: command exit status=$status<br>";
     $diff = implode ("\n", $output);
     return $diff;
    }

/**
 * Returns rlog for a GUID, parsing the output is left to the user.
 */
function rcs_log ($guid, $options = ""){
     global $rcsroot;
    global $debug;
     $command = "rlog " . $options . " " . $rcsroot . "/" . $guid;
     if ($debug == 1) echo "rcs_log debug: command value=$command<br>";
     unset ($output);
    unset ($status);
     exec ($command, $output, $status);
     if ($debug == 1) echo "rcs_log debug: command exit status=$status<br>";
     $rlog = implode ("\n", $output);
     return $rlog;
    }

/**
 * This function returns the current RCS version number of GUID
 */
function rcs_check_version ($guid){
    global $rcsroot;
    global $debug;
    unset ($output);
    unset ($status);
    $command = "rlog -h " . $rcsroot . "/" . $guid;
    exec ($command, $output, $status);
    if ($debug = 1){
         echo "rcs_check_version debug: command=$command<br>";
         echo "rcs_check_version debug: command exit status=$status<br>";
         echo "rcs_check_version debug: command output follows:<br><pre>";
         print_r ($output);
        echo "</pre>";
        }
    $version = substr($output[3], 5);
    settype ($version, "double");
    return $version;
    }


/**
 * rcs_data2object2
 * @param string xmldata
 * @return array of attribute=> value pairs.
 */
function rcs_data2object2 ($data,$guid)
{
    require_once 'XML/Unserializer.php';

    $unserializer = new XML_Unserializer();
    $result = $unserializer->unserialize($data);
    if ($result) {
        return array_shift($unserializer->getUnserializedData());
    }
    return array();

}

/**
 * Make xml out of an object.
 */
function rcs_object2data2($object) {

    require_once 'XML/Serializer.php';

    $serializer = new XML_Serializer();
    $fields = get_object_vars($object);

    // remove private fields
    foreach ($fields as $key => $field ) {
        if (preg_match("/^__/",$key)) {
            unset($fields[$key]);
        }
    }

    $this->setOption(XML_SERIALIZER_OPTION_ROOT_NAME, $object->__table__);
    $obj = array ( $fields);
    $result = $serializer->serialize($obj);
    if ($result) {
        return $serializer->getSerializedData();
    }
    return array();
}


// *** Supporting Functions ***
/**
 * Makes file data out of an object. NOTE: This function does not
 * save the data to file, it needs to be done separately.
 */
function rcs_object2data ($object, $type, $links2guids){
    global $debug;
    $fields = rcs_get_object_fields($type);
    if ($links2guids >= 1){
         if ($debug == 1) echo "rcs_object2data debug: Saving links as GUIDs when possible (links2guids=$links2guids)<br>";
         if ($object -> guid()){
             $data = "<" . $object -> __table__ . " id=\"" . $object -> guid() . "\" changed=\"" . date("YmdHis") . "\">\n";
             }else{
             $data = "<" . $object -> __table__ . " id=\"" . $object -> id . "\" changed=\"" . date("YmdHis") . "\">\n";
             }
        }else{
         $data = "<" . $object -> __table__ . " id=\"" . $object -> id . "\" changed=\"" . date("YmdHis") . "\">\n";
        }
    reset ($fields);
    while (list($k, $v) = each($fields)){
        if ($debug == 1) echo "rcs_object2data debug: processing field $k<br>";
         switch ($v){
         case "STR":
             if ($object -> $k){
                 $data .= "<$k>" . $object -> $k . "</$k>\n";
                 }else{
                 $data .= "<$k/>\n";
                 }
             break;
         case "CDATA":
             if ($object -> $k){
                 $data .= "<$k><!" . "[CDATA[" . $object -> $k . "]" . "]></$k>\n";
                 }else{
                 $data .= "<$k/>\n";
                 }
             break;
         case "LINK-ptable":
             if ($links2guids >= 1){
                 $func = "mgd_get_" . $object -> ptable;
                 $linkobj = $func($object -> pid);
                 $linkguid = $linkobj -> guid();
                 if ($linkguid){
                     $data .= "<$k>" . $linkguid . "</$k>\n";
                     }else{
                    $data .= "<$k>" . $linkobj -> __table__ . "_" . $linkobj -> id . "</$k>\n";
                }
                 }else{
                $data .= "<$k>" . $object -> $k . "</$k>\n";
            }
             break;
         default:
             if (!$object -> $k){
                 $data .= "<$k/>\n";
                 }else{
                 if ($links2guids >= 1){
                     $func = "mgd_get_$v";
                     $link = $func($object -> $k);
                     $linkguid = $link -> guid();
                     if ($linkguid){
                         $data .= "<$k>" . $link -> guid() . "</$k>\n";
                         }else{
                         $data .= "<$k>" . $link -> __table__ . "_" . $link -> id . "</$k>\n";
                         }
                     }else{
                    $data .= "<$k>" . $object -> k . "</$k>\n";
                }
                 }
             break;
             }
        }
    $data .= "</" . $object -> __table__ . ">\n";
    return $data;
    }


/**
 * Makes object out of file data, the function first loads the object
 * by GUID, then fills/replaces data from file and returns the object.
 */
function rcs_data2object ($data, $guid){
    global $debug;
    $debug = 1;
    if ($guid == "") return "";
    $object = mgd_get_object_by_guid($guid);

    /**
     * Much of this is copied from NAdmin RCS parser, comments and some prosessing
     * added (plus debug...)
     *
     */

    /*
     * todo: use a propper parser!
     * */
     $curname = "";
     unset($dataarray);
     $tempdata = explode("\n", $data);
     // NOW READ THE simple XML format!
    $status[block] = "";
     $status[level] = 0;
     $status[table] = "";

     for ($i = 0;$i < count($tempdata);$i++){
         // is it the start or end of a block
        if ($args) unset($args);
         switch ($status[level]){
         case 0: // then I only expect "<object>";
            if (eregi("^[ ]*<([a-z_]+) id=\"[a-z0-9-]+\" [a-z]+=\"[0-9]+\">", $tempdata[$i], $args)){
                 $status[table] = $args[1];
                 $dataformat = rcs_get_object_fields($status[table]);
                 if ($debug == 1) echo "rcs_data2object debug: object type is $status[table]<br>";
                 $status[level] = 1;
                 }
             break;
         case 1:
             if (eregi("^[ ]*<([a-z]+)><!\[CDATA\[(.*)$", $tempdata[$i], $args)){
                 // begining CDATA!
                $status[block] = $args[1];
                 $status[level] = 2;
                 $dataarray[$status[block]] = $args[2] . "\n";
                 // option for single line data!
                if (ereg("\]\]></" . $status[block] . ">$", $args[2])){
                     $tmp = ereg_replace("\]\]></" . $status[block] . ">$", "", $args[2]);
                     $status[level] = 1;
                     $dataarray[$status[block]] = $tmp;
                     $status[block] = "";
                     }
                 }elseif (eregi("^[ ]*<([a-z]+)>([^<]+)</([a-z]+)>[ ]*$", $tempdata[$i], $args)){
                    if ($dataformat[$args[1]] == "STR"){
                        $dataarray[$args[1]] = $args[2];

                    }elseif (mgd_is_guid($args[2])){
                        $subobj = mgd_get_object_by_guid($args[2]);
                        $dataarray[$args[1]] = $subobj -> id;
                    }elseif (ereg("_", $args[2])) {
                        list ($stp, $sid) = explode ("_", $args[2]);
                        $dataarray[$args[1]] = $sid;
                    }else{
                        $dataarray[$args[1]] = $args[2];
                    }

                 }elseif (eregi("^[ ]*<([a-z]+)></([a-z]+)>[ ]*$", $tempdata[$i], $args) ||
                     eregi("^[ ]*<([a-z]+)/>[ ]*$", $tempdata[$i], $args)
                    ){
                    $dataarray[$args[1]] = 0;
                    if (ereg("^(CDATA|STR)$", $dataformat[$args[1]])) {
                        $dataarray[$args[1]] = "";
                    } else {
                        unset($dataarray[$args[1]] );
                    }
                 }elseif (eregi("^[ ]*</" . $status[table] . ">[ ]*$", $tempdata[$i])){
                     $status[level] = 0;
                 } // other wise unrecognized!!!!!
             break;
         case 2:
             if (ereg("(.*)\]\]></" . $status[block] . ">[ ]*$", $tempdata[$i], $args)){
                 $dataarray[$status[block]] .= $args[1];
                 $status[block] == "";
                 $status[level] = 1;
                 }else{
                 $dataarray[$status[block]] .= $tempdata[$i] . "\n";
                 }
             break;
             }
         } // end for loop


    // *** End the copied part
    if ($debug == 1) echo "rcs_data2object debug: placing parsed data to object<br>";
    while (list($k, $v) = each($dataarray)){
         if ($debug == 1) echo "rcs_data2object debug: object->$k = $v<br>";
         $object -> $k = $v;
        }

    return $object;
    }

// Checks that $rcsroot exists in the filesystem
function rcs_check_exist(){
     global $rcsroot;
    global $debug;
     if (is_dir($rcsroot)){
        return 1;
    }
    else{
        return 0;
    }
    }

// Checks that we can write to $rcsroot
function rcs_check_writable(){
     global $rcsroot;
    global $debug;
     $ret = @touch ($rcsroot . "/midgard_check_cvs_write");
     return $ret;
    }

// Writes $data to file $guid, does not return anything.
function rcs_writefile ($guid, $data){
     global $rcsroot;
    global $debug;
     $filename = $rcsroot . "/" . $guid;
     if ($debug == 1) echo "rcs_writefile debug: file name=$filename<br>";
     $fp = fopen ($filename, "w");
     fwrite ($fp, $data);
     fclose ($fp);
    }

// Reads data from file $guid and returns it.
function rcs_readfile ($guid){
     global $rcsroot;
    global $debug;
     $filename = $rcsroot . "/" . $guid;
     if ($debug == 1) echo "rcs_readfile debug: file name=$filename<br>";
     $fd = fopen ($filename, "r");
     $data = fread ($fd, filesize ($filename));
     fclose ($fd);
    return $data;
    }



// Return an array of fields/field types to be saved for the object type
function rcs_get_object_fields ($type){
    global $debug;
    unset ($fields);
    if (debug == 1) echo "rcs_get_object_fields debug: type=$type<br>";
    switch ($type){
     case "snippet":
         $fields = array (
            up => "snippetdir",
             name => "CDATA",
             code => "CDATA",
             doc => "CDATA",
             author => "CDATA",
             creator => "person",
             created => "STR",
             revisor => "person",
             revised => "STR",
             revision => "STR",
             sitegroup => "sitegroup"
            );
         break;
     case "snippetdir":
         $fields = array (
            up => "snippetdir",
             name => "CDATA",
             description => "CDATA",
             owner => "person",
             sitegroup => "sitegroup"
            );
         break;
     case "page":
         $fields = array (
            up => "page",
             style => "style",
             name => "CDATA",
             title => "CDATA",
             content => "CDATA",
             author => "person",
             info => "STR",
             changed => "STR",
             sitegroup => "sitegroup"
            );
         break;
     case "pageelement":
         $fields = array (
            page => "page",
             name => "CDATA",
             value => "CDATA",
             info => "STR",
             sitegroup => "sitegroup"
            );
         break;
     case "style":
         $fields = array (
            up => "style",
             name => "CDATA",
             owner => "group",
             sitegroup => "sitegroup"
            );
         break;
     case "element":
         $fields = array (
            style => "style",
             name => "CDATA",
             value => "CDATA",
             sitegroup => "sitegroup"
            );
         break;
     case "topic":
         $fields = array (
            up => "topic",
             name => "CDATA",
             extra => "CDATA",
             owner => "group",
             score => "STR",
             description => "CDATA",
             revised => "STR",
             created => "STR",
             revisor => "person",
             creator => "person",
             revision => "STR",
             code => "CDATA",
             sitegroup => "sitegroup"
            );
         break;
     case "article":
         $fields = array (
            up => "article",
             topic => "topic",
             title => "CDATA",
             abstract => "CDATA",
             content => "CDATA",
             author => "person",
             created => "STR",
             url => "CDATA",
             calstart => "STR",
             caldays => "STR",
             icon => "STR",
             view => "STR",
             "print" => "STR",
             extra1 => "CDATA",
             extra2 => "CDATA",
             extra3 => "CDATA",
             name => "CDATA",
             creator => "person",
             revisor => "person",
             revision => "STR",
             approver => "person",
             revised => "STR",
             approved => "STR",
             score => "STR",
             type => "STR",
             locked => "STR",
             locker => "person",
             sitegroup => "sitegroup"
            );
         break;
     case "attachment":
         $fields = array (
            ptable => "STR",
             pid => "LINK-ptable",
             score => "STR",
             name => "CDATA",
             title => "CDATA",
             location => "CDATA",
             mimetype => "CDATA",
             sitegroup => "sitegroup",
             author => "person",
             created => "STR"
            );
         break;
     case "eventmember":
         $fields = array (
            eid => "event",
             uid => "person",
             count => "STR",
             period => "STR",
             extra => "CDATA",
             sitegroup => "sitegroup"
            );
         break;
     case "event":
         $fields = array (
            up => "event",
             start => "STR",
             end => "STR",
             title => "CDATA",
             description => "CDATA",
             extra => "CDATA",
             type => "STR",
             busy => "STR",
             owner => "group",
             creator => "person",
             created => "STR",
             revisor => "person",
             revised => "STR",
             revision => "STR",
             locker => "person",
             locked => "STR",
             sitegroup => "sitegroup"
            );
         break;
     case "group":
         $fields = array (
            name => "CDATA",
             official => "CDATA",
             street => "CDATA",
             city => "CDATA",
             homepage => "CDATA",
             email => "CDATA",
             extra => "CDATA",
             owner => "group",
             sitegroup => "sitegroup"
            );
         break;
     case "host":
         $fields = array (
            name => "STR",
             root => "page",
             style => "style",
             info => "STR",
             owner => "group",
             port => "STR",
             online => "STR",
             prefix => "STR",
             sitbegroup => "sitegroup"
            );
         break;
     case "member":
         $fields = array (
            uid => "person",
             gid => "group",
             extra => "CDATA",
             sitegroup => "sitegroup"
            );
         break;
     case "pagelink":
         $fields = array (
            up => "page",
             name => "CDATA",
             target => "STR",
             group => "group",
             owner => "group",
             sitegroup => "sitegroup"
            );
         break;
     case "person":
         $fields = array (
            username => "CDATA",
             password => "CDATA",
             firstname => "CDATA",
             lastname => "CDATA",
             birthdate => "STR",
             street => "CDATA",
             postcode => "CDATA",
             city => "CDATA",
             homephone => "CDATA",
             workphone => "CDATA",
             handphone => "CDATA",
             homepage => "CDATA",
             email => "CDATA",
             extra => "CDATA",
             info => "STR",
             topic => "topic",
             subtopic => "topic",
             department => "topic",
             office => "topic",
             sitegroup => "sitegroup"
            );
         break;
     case "preference":
         $fields = array (
            uid => "person",
             domain => "CDATA",
             name => "CDATA",
             value => "CDATA",
             sitegroup => "sitegroup"
            );
         break;
     case "sitegroup":
         $fields = array (
            name => "CDATA",
             admingroup => "group"
            );
         break;
        }
    if ($debug == 1){
     echo "rcs_get_object_fields debug: fields follow<br><pre>";
     print_r ($fields);
    echo "</pre>";
    }
return $fields;
}

/**
 * Dummy function to make sure we get page elements when creating function calls
 * with $func="mgd_get_$object->__table__"; -style calls
 */
function mgd_get_pageelement ($id){
 $object = mgd_get_page_element ($id);
 return $object;
}

?>