<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:misc.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This helper function searche for a snippet either in the Filesystem
 * or in the database and returns its content or code-field, respecitvly.
 *
 * Prefix the snippet Path with 'file:' for retrival of a file relative to
 * MIDCOM_ROOT; omit it to get the code field of a Snippet.
 *
 * Any error (files not found) will return null. If you want to trigger an error,
 * look for midcom_get_snippet_content.
 *
 * @param string $path  The URL to the snippet.
 * @return string       The content of the snippet/file.
 */
function midcom_get_snippet_content_graceful($path)
{
    if (substr($path, 0, 5) == 'file:')
    {
        $filename = MIDCOM_ROOT . substr($path, 5);
        if (! file_exists($filename))
        {
            return null;
        }
        $data = file_get_contents($filename);
    }
    else
    {
        if (! mgd_snippet_exists($path))
        {
            return null;
        }
        $snippet = mgd_get_snippet_by_path ($path);
        $data = $snippet->code;
    }
    return $data;
}

/**
 * This helper function searche for a snippet either in the Filesystem
 * or in the database and returns its content or code-field, respecitvly.
 *
 * Prefix the snippet Path with 'file:' for retrival of a file relative to
 * MIDCOM_ROOT; omit it to get the code field of a Snippet.
 *
 * Any error (files not found) will raise an MidCOM Error. If you want a more
 * graceful behavoir, look for midcom_get_snippet_content_graceful
 *
 * @param string $path	The URL to the snippet.
 * @return string		The content of the snippet/file.
 */
function midcom_get_snippet_content($path)
{
    if (substr($path, 0, 5) == 'file:')
    {
        $filename = MIDCOM_ROOT . substr($path, 5);
        if (! file_exists($filename))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Could not load the contents of the file {$filename}: File not found.");
            // This will exit.
        }
        $data = file_get_contents($filename);
    }
    else
    {
        $snippet = new midcom_baseclasses_database_snippet();
        $snippet->get_by_path($path);
        if (!$snippet->guid)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Could not load the contents of the snippet {$path}: Snippet does not exist.");
            // This will exit.
        }
        $data = $snippet->code;
    }
    return $data;
}

/**
 * Probes for the installed NemeinRCS root
 *
 * @return string	RCS root or false on failure.
 */
function midcom_probe_nemein_rcs() {
    if (!class_exists('no_bergfald_rcs_aegirrcs'))
    {
        $_MIDCOM->load_library('no.bergfald.rcs');
    }
}

/**
 * MidCOM Interface against NemeinRCS.
 *
 * Updates the Nemein RCS information for $object.
 *
 * @param MidgardObject $object	The object that should be version controlled.
 * @param string $message Change log message
 * @return bool Indicating success.
 */
function midcom_update_nemein_rcs($object, $message = null) {

    // Ensure we have RCS library loaded
    midcom_probe_nemein_rcs();

    // Load the object into RCS handler
    $rcs_handler = new no_bergfald_rcs_aegirrcs($object->guid);

    return $rcs_handler->save_object($object, $message);
}

/**
 * Helper function to check wether a given group is a subgroup
 * of another one.
 *
 * @param int $id		Group to query.
 * @param int $rootid	Root group.
 * @return bool			True if it is a subgroup, false otherwise.
 */
function mgd_is_group_in_group_tree($id, $rootid) {
    if ($id == $rootid)
        return true;
    $grp = mgd_get_group($id);
    do {
        if ($grp->id == $rootid) return true;
        if ($grp->owner == 0) return false;
        $grp = mgd_get_group($grp->owner);
    } while ($id != $grp->id);
    die("mgd_is_group_in_group_tree: We should not get to this line...");
}

/**
 * List all members in a group.
 *
 * @param int $group	The id of the group whose members are queried.
 * @return Array		ID-Array of all group members
 */
function mgd_list_persons_in_group_all($group) {
    return false;

    $result = array();

    $tmp = mgd_memberships_to_uid(mgd_list_members ($group));
    if (!$tmp)
        return false;
    else {
        $resulttmp = array_merge($tmp, $result);
        $result = $resulttmp;
    }
}

/**
 * Transforms a fetchable into an ID-array
 *
 * @param MidgardFetchable $fetchable	Any result traversable by fetch()
 * @return Arraay	An Array of all object id's in the fetchable.
 */
function mgd_fetch_to_array($fetchable) {
    if (!$fetchable)
        return false;
    $result = array();
    while ($fetchable->fetch())
        $result[] = $fetchable->id;
    return $result;
}

/**
 * Transforms a membership fetchable to an Person-ID Array
 *
 * @param MidgardFetchable $fetchable	Any result traversable by fetch()
 * @return Arraay	An Array of all person id's in the fetchable.
 */
function mgd_memberships_to_uid($fetchable) {
    if (!$fetchable)
        return false;
    $result = array();
    while ($fetchable->fetch())
        $result[] = $fetchable->uid;
    return $result;
}

/**
 * Transforms a membership fetchable to an Group-ID Array
 *
 * @param MidgardFetchable $fetchable	Any result traversable by fetch()
 * @return Arraay	An Array of all group id's in the fetchable.
 */
function mgd_memberships_to_gid ($fetchable) {
    if (!$fetchable)
        return false;
    $result = array();
    while ($fetchable->fetch())
        $result[] = $fetchable->gid;
    return $result;
}

/**
 * Save a number of variables into parameters on an arbitary Midgard object.
 *
 * This function will save
 * the Member-Variables specified in $array of the Midgard Object $object in
 * the Parameter Domain $domain. It will checkt wether all Fields are short enough
 * (Margin: 255 characters) to fit into a parameter, if not, it will abort.
 * It will return TRUE if successful, FALSE on failure.
 *
 * @param Array $array			The data to store (keys will be preserved).
 * @param MidgardObject $object	The object where to store the information.
 * @param string $domain		The domain where to save the information.
 * @return bool					Indicating success.
 */
function mgd_save_custom_fields_param($array, &$object, $domain) {
    foreach ($array as $var) {
        eval("\$result = strlen(\$object->$var);");
        if ($result > 255)
            return false;
    }

    foreach ($array as $var) {
        eval("\$var_data = \$object->$var;");
        if (strlen($var_data) > 0)
            if (! $object->parameter($domain,$var,$var_data))
                return false;
    }
    return true;
}

/**
 * Load a number of variables into parameters and attach them to the Midgard object.
 *
 * This function will load
 * the Member-Variables specified in $array of the Midgard Object $object in
 * the Parameter Domain $domain. It will assign them as members to the passed
 * object
 *
 * <b>Note:</b> Midgard currently loads all parameters and assigns them to members
 * automatically. So this function should no longer be needed.
 *
 * @param Array $array			The variable names to load.
 * @param MidgardObject $object	The object where to load the information.
 * @param string $domain		The domain where to load the information.
 */
function mgd_load_custom_fields_param($array, &$object, $domain) {
    foreach ($array as $var) {
        $result = $object->parameter($domain, $var);
        if ($result)
            eval("\$object->$var = \$result;");
        else
            eval("\$object->$var = \"\";");
    }
    return true;
}

/**
 * Get the constructor string for an object's class.
 *
 * Returns either a mgd_get_... string or a new ... string.
 *
 * @param string $object_type The typename of the object ("MidgardObject" or "MyClass").
 * @return string The string required to create an object of that type.
 */
function mgd_get_createstr($object_type) {
    if (substr($object_type,0,7) == "Midgard")
        return ("mgd_get_" . strtolower(substr($object_type,7)));
    else
        return("new $object_type");
}

/**
 * Sort an Array of object IDs
 *
 * <b>Note:</b> This function is quite slow. It does everything on  a PHP level.
 *
 * @param Array $array		The ID-Array to sort.
 * @param string $sortkey	The name of the member that should be sorted (prepend "reverse" for a reverse sorting).
 * @param string $object_type	The type of the object referenced by the IDs
 * @param int $sorting		The PHP sorting mechanism to use.
 */
function mgd_sort_id_array(&$array, $sortkey, $object_type, $sorting = SORT_REGULAR) {
    $createstr = mgd_get_createstr($object_type);
    if (count($array) == 0)
        return array();
    if (!is_array($array))
        return array();
    $sortkey = trim ($sortkey);
    $sortkey_word = explode(" ", $sortkey);

    switch (count ($sortkey_word)) {
        case 1:
            $reverse = false;
            break;
        case 2:
            if ($sortkey_word[0] != "reverse")
                die("mgd_sort_id_array. First Parameter of \$sortkey ($sortkey_word[0]) invalid. aborting");
            $sortkey = $sortkey_word[1];
            $reverse = true;
            break;
        default:
            die("mgd_sort_id_array: Parameter count in \$sortkeyey ($sortkey) wrong, aborting.");
            break;
    }

    $sortarray = array();
    $result = array();

    foreach ($array as $elementid) {
        eval("\$tmp = $createstr($elementid);");
        $sortarray[$tmp->id] = $tmp->$sortkey;
    }

    asort($sortarray, $sorting);
    reset($sortarray);

    while(list($key, $value) = each ($sortarray))
        $result[] = $key;

    $array = ($reverse) ? array_reverse($result) : $result;
}


/**
 * Sort an Array of objects
 *
 * Works with a reference array (doesn't return anything)
 *
 * @param Array $array		The object-array to sort (used via reference).
 * @param string $sortkey	The name of the member that should be sorted (prepend "reverse" for a reverse sorting).
 * @param int $sorting		The PHP sorting mechanism to use.
 */
function mgd_sort_object_array (&$array, $sortkey, $sorting=SORT_REGULAR)
{
    if (count($array) === 0)
    {
        return array();
    }
    if (!is_array($array))
    {
        return array();
    }
    $sortkey = trim($sortkey);
    $sortkey_word = explode(" ",$sortkey);
    switch (count($sortkey_word))
    {
        case 1:
            $reverse = false;
            break;

        case 2:
            if ($sortkey_word[0] !== "reverse")
            {
                die("mgd_sort_id_array. First Parameter of \$sortkey ($sortkey_word[0]) invalid. aborting");
            }
            $sortkey = $sortkey_word[1];
            $reverse = true;
            break;

        default:
            die("mgd_sort_id_array: Parameter count in \$sortkeyey ($sortkey) wrong, aborting.");
            break;
    }
    $sortarray = array();
    $result = array();
    foreach ($array as $key => $obj)
    {
        if ($obj->$sortkey)
        {
            $sortarray[$key] = $obj->$sortkey;
        }
        else
        {
            $sortarray[$key] = $obj->parameter("midcom.helper.datamanager", "data_$sortkey");
        }
    }
    asort($sortarray, $sorting);
    reset($sortarray);
    foreach ($sortarray as $key => $value)
    {
        $result[] = $array[$key];
    }
    $array = ($reverse) ? array_reverse($result) : $result;
}


/**
 * Sort and group an Array of object IDs
 *
 * <b>Note:</b> This function is quite slow. It does everything on  a PHP level.
 *
 * @param Array $array		The ID-Array to sort and group.
 * @param string $groupkey	The key after which to group the results.
 * @param string $element_type	The type of the element after which to group.
 * @param int $elementsortkey	The key after which to sort the elements.
 * @param string $groupsortkey	The name of the member after which the groups should be sorted (prepend "reverse" for a reverse sorting).
 * @param string $group_type	The type of the object referenced by the groups
 * @param int $elementsorting		The PHP sorting mechanism to use.
 * @param int $groupsorting		The PHP sorting mechanism to use.
 * @return Array	Two-Level array with the grouped and sorted IDs.
 */
function mgd_group_id_array ($array, $groupkey, $element_type, $group_type, $elementsortkey = "unsorted", $groupsortkey = "unsorted", $elementsorting = SORT_REGULAR, $groupsorting = SORT_REGULAR ) {
    $element_createstr = mgd_get_createstr($element_type);

    // 1. Group together by groupkeys

    $result = array();
    $found_keys = array();

    foreach ($array as $element)  {
        eval("\$tmp = $element_createstr($element);");
        if (in_array($tmp->$groupkey, $found_keys)) {
            $result[$tmp->$groupkey][] = $tmp->id;
        } else {
            $found_keys[] = $tmp->$groupkey;
            $result[$tmp->$groupkey] = array($tmp->id);
        }
    }

    // 2. Now sort it

    mgd_sort_group_id_array($result, $element_type, $group_type, $elementsortkey, $groupsortkey, $elementsorting, $groupsorting);

    return $result;
}

/* Helper function for mgd_group_id_array */
/**
 * @ignore
 */
function mgd_sort_group_id_array(&$array, $element_type, $group_type, $elementsortkey = "unsorted", $groupsortkey = "unsorted", $elementsorting = SORT_REGULAR, $groupsorting = SORT_REGULAR ) {
    // Sort the groups
    if ($groupsortkey != "unsorted") {
        $found_keys = array();

        foreach ($array as $key => $value)
            $found_keys[] = $key;

        mgd_sort_id_array($found_keys, $groupsortkey, $group_type, $groupsorting);

        $newresult = array();
        foreach ($found_keys as $key)
            $newresult[$key] = $array[$key];

        $array = $newresult;
    }

    // Now sort the subarrays

    if ($elementsortkey != "unsorted") {
        foreach ($array as $k => $elements)
            mgd_sort_id_array($array[$k], $elementsortkey, $element_type, $elementsorting);
    }
}

/**
 * Save a variable as attachment to an Midgard object. Type is preserved
 * through serialization.
 *
 * @param MidgardObject $object	The object at which to save the data.
 * @param mixed $var			The variable that should be saved.
 * @param string $name			The identifier to use for storage.
 * @return bool	Indicating succes.
 */
function mgd_save_var_as_attachment($object, &$var, $name) {
    $att = $object->getattachment($name);

    if (!$att)
    {
        $att = $object->createattachment($name, "mgd_save_var of $name", "application/octet-stream");
        if (!$att)
        {
            debug_add("Failed to create attachment '{$name}': " . mgd_errstr(), MIDCOM_LOG_ERROR);
            return false;
        }
    }

    $h_att = $object->openattachment($name);

    if (!$h_att)
    {
        debug_add("Could not open attachment {$name} for writing: " . mgd_errstr(), MIDCOM_LOG_ERROR);
        return false;
    }

    $result = fwrite($h_att, serialize($var));

    if ($result == -1 || ! fclose($h_att))
    {
        debug_add("Failed to write to attachment {$name}, result was {$result}.", MIDCOM_LOG_ERROR);
        return false;
    }

    // Hack for Repligard Bug: Update core object to propagate changes
    // See also #154.
    // No errorchecking, we fail silently anyway, and sometimes $att seems
    // not to be populated, for whatever reason.
    $object->update();

    return true;
}

/**
 * Load a variable form an attachment to a Midgard object.
 *
 * @param MidgardObject $object	The object at which to save the data.
 * @param string $name			The identifier to use for storage.
 * @return mixed The retrieved variable.
 */
function mgd_load_var_from_attachment($object, $name) {
    $att = $object->getattachment($name);
    if (!$att)
    {
        return false;
    }

    $stats = mgd_stat_attachment ($att->id);
    if ($stats[7] == 0)
    {
        return false;
    }

    $h_att = $object->openattachment($name, "r");
    if (!$h_att)
    {
        return false;
    }
    $content = fread($h_att, $stats[7]);
    $result = @unserialize($content);
    if ($result === false)
    {
        debug_add("Possible Failure to unserialize the attachment {$name}, unserialize returned false.", MIDCOM_LOG_INFO);
        debug_add("PHP Eror Message was: {$php_errormsg}", MIDCOM_LOG_INFO);
        debug_print_r("Content Object:", $object);
        debug_print_r("Attachment {$name}:", $att);
        debug_print_r("First 1.000 Bytes of the content:", substr($content, 0, 1000));
    }
    ini_restore('track_errors');
    fclose($h_att);

    return $result;
}

/**
 * @ignore
 */
function mgd_get_style_by_name2 ($id, $name) {
    $_styles = mgd_list_styles($id);
    if (isset($_styles))
        while ($_styles->fetch ())
            if ($_styles->name == $name)
                return mgd_get_style($_styles->id);
    return false;
}


/**
 * Delete all extensions (parameters and attachments) to a
 * Midgard object
 *
 * @param MidgardObject $object	The object that should be cleared.
 * @return bool Indicating success.
 */
function mgd_delete_extensions(&$object) {
    // List and remove parameters
    // TODO: Deprecate this as DBA should be used in favor of this always (DBA has this built-in)
    $domainlist = $object->listparameters();
    if ($domainlist) while ($domainlist->fetch()) {
        $paramlist = $object->listparameters($domainlist->domain);
        if ($paramlist) while ($paramlist->fetch()) {
            $ret = $object->parameter($domainlist->domain,
              $paramlist->name, "");
            // Cancel on error; a detailed message comes via mgd_errstr()
            if (! $ret) { debug_add ("Failed deleting parameter {$domainlist->domain}/{$paramlist->name}"); return($ret); }
        }
    }

    // List and remove attachments, including their parameters
    // Use DBA API here

    $list = $object->list_attachments();
    foreach ($list as $attachment)
    {
        if (! $attachment->delete())
        {
            debug_add("Failed to delete attachment ID {$attachment->id}");
            return false;
        }
    }

    return(TRUE);
}


// This function will be available in Midgard 1.6.0
if (!function_exists('mgd_get_snippet_by_path')) 
{
    /**
     * @ignore
     */
    function mgd_get_snippet_by_path($path) 
    {
        $snippet = new midcom_baseclasses_database_snippet();
        $snippet->get_by_path($path);
        if (!$snippet->guid)
        {
            return false;
        }
        return $snippet;
    }
}

/**
 * PHP-level implementation of the Midgard Preparser language
 * construct mgd_include_snippet. Same semantics, but probably a littlebit
 * slower.
 *
 * @param string $path	The path of the snippet that should be included.
 * @return bool Returns false if the snippet could not be loaded or true, if it was evaluated successfully.
 */
// This function is there as a backup in case you are not running within the
// Midgard Parser; it will run the snippet code through mgd_preparse manually.
function mgd_include_snippet_php ($path)
{
    $snippet = mgd_get_snippet_by_path($path);
    if (! $snippet)
    {
        debug_add("mgd_include_snippet_php: Could not find snippet {$path}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
        return false;
    }
    debug_add("mgd_include_snippet_php: Evaluating snippet {$path}.");
    eval ('?>' . mgd_preparse($snippet->code));
    return true;
}

/**
 * Bicubic resampling of an Image, 256 color version
 *
 * Gives an image with a higher quality then the built-in gdlib functions.
 * It is recommended to use imagemagick in favor of this function.
 *
 * @param int $dst_img	Destination Image Handle.
 * @param int $src_img	Source image Handle.
 * @param int $dst_x	X-offest of the destination.
 * @param int $dst_y	Y-offest of the destination.
 * @param int $src_x	X-offest of the source.
 * @param int $src_y	Y-offest of the source.
 * @param int $dst_w	Width of the destination.
 * @param int $dst_h	Height of the destination.
 * @param int $src_w	Width of the source.
 * @param int $src_h	Height of the source.
 */
function ImageCopyResampleBicubicPalette(&$dst_img, &$src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
    /*
    port to PHP by John Jensen July 10 2001 (updated June 13, 2002 by tim@smoothdeity.com) --
    original code (in C, for the PHP GD Module) by jernberg@fairytale.se
    Taken out of http://www.php.net/manual/en/function.imagecopyresized.php
    */

    $palsize = ImageColorsTotal ($src_img);
    for ($i = 0; $i < $palsize; $i++) {
        // get palette.
        $colors = ImageColorsForIndex ($src_img, $i);
        ImageColorAllocate ($dst_img, $colors['red'], $colors['green'], $colors['blue']);
    }

    $scaleX = ($src_w - 1) / $dst_w;
    $scaleY = ($src_h - 1) / $dst_h;

    $scaleX2 = (int) ($scaleX / 2);
    $scaleY2 = (int) ($scaleY / 2);

    $dstSizeX = imagesx( $dst_img );
    $dstSizeY = imagesy( $dst_img );
    $srcSizeX = imagesx( $src_img );
    $srcSizeY = imagesy( $src_img );

    for ($j = 0; $j < ($dst_h - $dst_y); $j++) {
        $sY = (int) ($j * $scaleY) + $src_y;
        $y13 = $sY + $scaleY2;

        $dY = $j + $dst_y;

        if (($sY > $srcSizeY) or ($dY > $dstSizeY))
            break 1;

        for ($i = 0; $i < ($dst_w - $dst_x); $i++) {
            $sX = (int) ($i * $scaleX) + $src_x;
            $x34 = $sX + $scaleX2;

            $dX = $i + $dst_x;

            if (($sX > $srcSizeX) or ($dX > $dstSizeX))
                break 1;

            $color1 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX, $y13));
            $color2 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX, $sY));
            $color3 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34, $y13));
            $color4 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34, $sY));

            $red = ($color1['red'] + $color2['red'] + $color3['red'] + $color4['red']) / 4;
            $green = ($color1['green'] + $color2['green'] + $color3['green'] + $color4['green']) / 4;
            $blue = ($color1['blue'] + $color2['blue'] + $color3['blue'] + $color4['blue']) / 4;

            ImageSetPixel ($dst_img, $dX, $dY, ImageColorClosest ($dst_img, $red, $green, $blue));
        }
    }
}

/**
 * Bicubic resampling of an Image, truecolor version
 *
 * Gives an image with a higher quality then the built-in gdlib functions.
 * It is recommended to use imagemagick in favor of this function.
 *
 * @param int $dst_img	Destination Image Handle.
 * @param int $src_img	Source image Handle.
 * @param int $dst_x	X-offest of the destination.
 * @param int $dst_y	Y-offest of the destination.
 * @param int $src_x	X-offest of the source.
 * @param int $src_y	Y-offest of the source.
 * @param int $dst_w	Width of the destination.
 * @param int $dst_h	Height of the destination.
 * @param int $src_w	Width of the source.
 * @param int $src_h	Height of the source.
 */
function ImageCopyResampleBicubic(&$dst_img, &$src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
    /*
    port to PHP by John Jensen July 10 2001 (updated June 13, 2002 by tim@smoothdeity.com) --
    original code (in C, for the PHP GD Module) by jernberg@fairytale.se
    Taken out of http://www.php.net/manual/en/function.imagecopyresized.php
    */

    $scaleX = ($src_w - 1) / $dst_w;
    $scaleY = ($src_h - 1) / $dst_h;

    $scaleX2 = (int) ($scaleX / 2);
    $scaleY2 = (int) ($scaleY / 2);

    $dstSizeX = imagesx( $dst_img );
    $dstSizeY = imagesy( $dst_img );
    $srcSizeX = imagesx( $src_img );
    $srcSizeY = imagesy( $src_img );

    for ($j = 0; $j < ($dst_h - $dst_y); $j++) {
        $sY = (int) ($j * $scaleY) + $src_y;
        $y13 = $sY + $scaleY2;

        $dY = $j + $dst_y;

        if (($sY > $srcSizeY) or ($dY > $dstSizeY))
            break 1;

        for ($i = 0; $i < ($dst_w - $dst_x); $i++) {
            $sX = (int) ($i * $scaleX) + $src_x;
            $x34 = $sX + $scaleX2;

            $dX = $i + $dst_x;

            if (($sX > $srcSizeX) or ($dX > $dstSizeX))
                break 1;

            $color1 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX, $y13));
            $color2 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX, $sY));
            $color3 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34, $y13));
            $color4 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34, $sY));

            $red = ($color1['red'] + $color2['red'] + $color3['red'] + $color4['red']) / 4;
            $green = ($color1['green'] + $color2['green'] + $color3['green'] + $color4['green']) / 4;
            $blue = ($color1['blue'] + $color2['blue'] + $color3['blue'] + $color4['blue']) / 4;

            ImageSetPixel ($dst_img, $dX, $dY, ImageColorClosest ($dst_img, $red, $green, $blue));
        }
    }
}

/**
 * Helper function for generating "clean" URL names from titles, etc.
 *
 * @param string $string	String to edit.
 * @param string $replacer	The replacement for invalid characters.
 * @return string			Normalized name.
 */
function midcom_generate_urlname_from_string($string, $replacer = "-")
{
    // TODO: sanity-check $replacer ?
    echo "{$string}: ";

    // Try to transliterate non-latin strings to URL-safe format
    require_once('utf8_to_ascii.php');
    $string = utf8_to_ascii($string, '_');
    echo "{$string}: ";

    // Replacement map for most common characters
    /**
     * Use hex codes for characters to avoid issues with editors using charset other than UTF-8
     * Add also uppercase codes, strotolower as default is not multibyte aware and you can't trust that
     *   1. it is overloaded with mb_strtolower (if even available)
     *   2. mb_strolower works in all cases as you suppose it would
     */
    $replace_characters = array(
        // UTF-8, scandinavian lowercase
        "\xC3\xA4" => "a",
        "\xC3\xB6" => "o",
        "\xC3\xA5" => "a",
        "\xC3\xBC" => "u",
        "\xC3\x9F" => "ss",

        // UTF-8, scandinavian uppercase
        "\xC3\x84" => "A",
        "\xC3\x96" => "O",
        "\xC3\x85" => "A",
        "\xC3\x9C" => "U",

        //UTF-8, Polish lowercase
        "\xC4\x85" => "a",
        "\xC4\x87" => "c",
        "\xC4\x99" => "e",
        "\xC5\x82" => "l",
        "\xC5\x84" => "n",
        "\xC3\xB3" => "o",
        "\xC5\x9B" => "s",
        "\xC5\xBA" => "z",
        "\xC5\xBC" => "z",

        // Latin-1, see http://en.wikipedia.org/wiki/ISO_8859-1
        "\xE4" => "a",
        "\xF6" => "o",
        "\xE5" => "a",
        "\xFC" => "u",
        "\xDF" => "ss",

        // Latin-1 uppercase
        "\xC4" => "A",
        "\xD6" => "O",
        "\xC5" => "A",
        "\xDC" => "U",

        // Latin-2 lowercase
        "\xB1" => "a",
        "\xE6" => "c",
        "\xEA" => "e",
        "\xB3" => "l",
        "\xF1" => "n",
        "\xF3" => "o",
        "\xB6" => "s",
        "\xBC" => "z",
        "\xBF" => "z",

        // Latin-2 uppercase
        "\xA1" => "A",
        "\xC6" => "C",
        "\xCA" => "E",
        "\xA3" => "L",
        "\xD1" => "N",
        "\xD3" => "O",
        "\xA6" => "S",
        "\xAC" => "Z",
        "\xAF" => "Z",

    );

    // Replace the characters in the map
    static $search_arr = array();
    static $replace_arr = array();
    if (   empty($search_arr)
        || empty($replace_arr))
    {
        foreach ($replace_characters as $search => $replace)
        {
            $search_arr[] = $search;
            $replace_arr[] = $replace;
        }
    }
    // this should be faster than calling the fuction for each search/replace pair separately
    $string = str_replace($search_arr, $replace_arr, $string);

    // Try to transliterate characters not in map with iconv if we can
    if (   function_exists('iconv')
        && function_exists('mb_detect_encoding'))
    {
        /**
         * NOTE: the order of character sets is important and some sets look very similar to each other leading to possible
         * mis-identifications
         * Therefore: Do not touch unless you actually know how mb_detect_encoding actually works and how the character sets work
         *
         * Luckily nowadays all Midgard installs *should* use UTF-8 only, making our life easier.
         **/
        $encoding = mb_detect_encoding($string, 'ASCII,JIS,UTF-8,ISO-8859-1,EUC-JP,SJIS');
        if (   !empty($encoding)
            && $encoding != 'ASCII')
        {
            // silence in case we have misdetected the encoding and iconv complains
            $stat = @iconv($encoding, 'ASCII//TRANSLIT', $string);
            if (!empty($stat))
            {
                $string = $stat;
            }
        }
    }

    // Spaces around a dash to just dash
    $string = preg_replace('/\s+-\s+/', '-', $string);
    // Rest of spaces to underscores
    $string = preg_replace('/\s+/', '_', $string);

    // Regular expression for characters to replace (the ^ means an inverted character class, ie characters *not* in this class are replaced)
    $regexp = '/[^a-zA-Z0-9_-]/';
    // Replace the unsafe characters with the given replacer (which is supposed to be safe...)
    $safe = preg_replace($regexp, $replacer, $string);

    // Strip trailing {$replacer}s and underscores from start and end of string
    $safe = preg_replace("/^[{$replacer}_]+|[{$replacer}_]+$/", '', $safe);

    // Clean underscores around $replacer
    $safe = preg_replace("/_{$replacer}|{$replacer}_/", $replacer, $safe);

    // Any other cleanup routines ?

    // We're done here, return $string lowercased
    return strtolower($safe);
}

/**
 * Helper function for really removing an object
 *
 * @param guid $guid	The GUID of the object or the object itself
 * @param bool $removeattachments	Remove attachments too? This parameter is ignored in MidCOM 2.5 upwards
 * @return bool	Indicating success.
 * @deprecated This function is no longer required, use the DBA delete method instead.
 */
function midcom_helper_purge_object($guid, $removeattachments=true)
{
    if (is_object($guid))
    {
        $object = $_MIDCOM->dbfactory->convert_midgard_to_midcom($guid);
        if (! $object)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to cast object to a DBA instance.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
    }
    else
    {
        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
        if (! $object)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to resolve the guid {$guid}.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
    }
    return $object->delete();
}

/**
 * Check if a file exists in the include path
 *
 * @version      1.2.0
 * @author       Aidan Lister <aidan@php.net>
 * @param        string     $file       Name of the file to look for
 * @return       bool       TRUE if the file exists, FALSE if it does not
 **/
function midcom_file_exists_incpath ($file)
{
    $paths = explode(PATH_SEPARATOR, get_include_path());

    foreach ($paths as $path)
    {
        // Formulate the absolute path
        $fullpath = $path . DIRECTORY_SEPARATOR . $file;
        // Check it
        if (file_exists($fullpath))
        {
            return true;
        }
    }
    return false;
}

function dump_mem($text)
{
    static $lastmem = 0;
    $curmem = memory_get_usage();
    $delta = $curmem - $lastmem;
    $lastmem = $curmem;

    $curmem = str_pad(number_format($curmem), 10, " ", STR_PAD_LEFT);
    $delta = str_pad(number_format($delta), 10, " ", STR_PAD_LEFT);
    echo "{$curmem} (delta {$delta}): {$text}\n";
}

/**
 * Helper function for finding MIME type image for a document
 *
 * @param string $mimetype  Document MIME type
 * @return string	Path to the icon
 */
function midcom_helper_get_mime_icon($mimetype, $fallback = '')
{
    $mime_fspath = MIDCOM_STATIC_ROOT . '/stock-icons/mime';
    $mime_urlpath = MIDCOM_STATIC_URL . '/stock-icons/mime';
    $mimetype_filename = str_replace('/', '-', $mimetype);

    $check_files = Array();
    $check_files[] = "{$mimetype_filename}.png";
    $check_files[] = "gnome-{$mimetype_filename}.png";
    // Default icon if there is none for the MIME type
    $check_files[] = 'gnome-unknown.png';
    //TODO: handle other than PNG files ?

    //Return first match
    foreach($check_files as $filename)
    {
        //echo "DEBUG: checking path: ".$mime_fspath.'/'.$filename."<br>\n";
        if (is_readable("{$mime_fspath}/{$filename}"))
        {
            return "{$mime_urlpath}/{$filename}";
        }
    }

    return $fallback;
}

/**
 * Helper function for pretty printing file sizes
 * Original from http://www.theukwebdesigncompany.com/articles/php-file-manager.php
 *
 * @param int $size  File size in bytes
 * @return string	Prettified file size
 */
function midcom_helper_filesize_to_string($size)
{
    if ($size > 104876)
    {
        // More than a meg
        return $return_size=sprintf("%01.2f",$size / 1048576)." Mb";
    }
    elseif ($size > 1024)
    {
        // More than a kilo
        return $return_size=sprintf("%01.2f",$size / 1024)." Kb";
    }
    else
    {
        return $return_size=$size." Bytes";
    }
}

/**
 * This helper function returns the first instance of a given component on
 * the MidCOM site.
 *
 * Note from torben, Seems to return null on failure. Not sure though.
 *
 * @todo for Bergie: Check return value in case of failure.
 * @return array NAP array of the first component instance found
 */
function midcom_helper_find_node_by_component($component, $node_id = null, $nap = null)
{
    if (is_null($nap))
    {
        $nap = new midcom_helper_nav();
    }

    if (is_null($node_id))
    {
        $node_id = $nap->get_root_node();

        $root_node = $nap->get_node($node_id);
        if ($root_node[MIDCOM_NAV_COMPONENT] == $component)
        {
            return $root_node;
        }
    }

    $component_node = null;
    $nodes = $nap->list_nodes($node_id);
    if ($nodes)
    {
        foreach ($nodes as $nodes_id)
        {
            $node = $nap->get_node($nodes_id);
            if ($node[MIDCOM_NAV_COMPONENT] == $component)
            {
                $component_node = $node;
                return $node;
            }
            else
            {
                $returned_node = midcom_helper_find_node_by_component($component, $node[MIDCOM_NAV_ID], $nap);
                if (!is_null($returned_node))
                {
                    return $returned_node;
                }
            }
        }
    }
    return $component_node;
}

if (!function_exists('midcom_helper_toc_formatter'))
{
    /**
     * This function parses HTML content and looks for header tags, making index of them.
     *
     * What exactly it does is looks for all H<num> tags and converts them to named
     * anchors, and prepends a list of links to them to the start of HTML.
     *
     * TODO: Parse the heading structure to create OL subtrees based on their relative levels
     */
    function midcom_helper_toc_formatter_prefix($level)
    {
        $prefix = '';
        for ($i=0; $i < $level; $i++)
        {
            $prefix .= '    ';
        }
        return $prefix;
    }
    function midcom_helper_toc_formatter($data)
    {
        if (!preg_match_all("/(<(h([1-9][0-9]*))[^>]*?>)(.*?)(<\/\\2>)/i", $data, $headings))
        {
            echo mgd_format($data, 'h');
            return;
        }
    
        $current_tag_level = false;
        $current_list_level = 1;
        echo "\n<ol class=\"midcom_helper_toc_formatter level_{$current_list_level}\">\n";
        foreach ($headings[4] as $key => $heading)
        {
            $anchor = md5($heading);
            $tag_level =& $headings[3][$key];
            $heading_code =& $headings[0][$key];
            $heading_tag =& $headings[2][$key];
            $heading_new_code = "<a name='{$anchor}'></a>{$heading_code}";
            $data = str_replace($heading_code, $heading_new_code, $data);
            $prefix = midcom_helper_toc_formatter_prefix($current_list_level);
            if ($current_tag_level === false)
            {
                $current_tag_level = $tag_level;
            }
            if ($tag_level > $current_tag_level)
            {
                for ($i = $current_tag_level; $i < $tag_level; $i++)
                {
                    $current_tag_level = $tag_level;
                    $current_list_level++;
                    echo "{$prefix}<ol class=\"level_{$current_list_level}\">\n";
                    $prefix .= '    ';
                }
            }
            if ($tag_level < $current_tag_level)
            {
                for ($i = $current_tag_level; $i > $tag_level; $i--)
                {
                    $current_tag_level = $tag_level;
                    if ($current_list_level > 1)
                    {
                        $current_list_level--;
                        $prefix = midcom_helper_toc_formatter_prefix($current_list_level);
                        echo "{$prefix}</ol>\n";
                    }
                }
            }
            echo "{$prefix}<li class='{$heading_tag}'><a href='#{$anchor}'>" . strip_tags($heading) .  "</a></li>\n";
        }
        for ($i = $current_list_level; $i > 0; $i--)
        {
            $prefix = midcom_helper_toc_formatter_prefix($i-1);
            echo "{$prefix}</ol>\n";
        }
    
        echo mgd_format($data, 'h');
    }
    
    /**
     * Register the formatter as "toc", meaning that &(variable:xtoc); will filter through it
     */
    mgd_register_filter('toc', 'midcom_helper_toc_formatter');
}

if (! function_exists('mgd_is_guid'))
{
    /**
     * This funciton validates wether the argument passed is a syntactically valid GUID.
     * The check is currently very basic (in favor of speed), and does only validate the
     * lentgth and type of the argument.
     *
     * Will only be included if the function doesn't already exist.
     *
     * @param mixed $guid The argument to validate
     * @return bool True, if the argument is a valid guid, false otherwise.
     */
    function mgd_is_guid($guid)
    {
        return (   is_string($guid)
                && strlen($guid) >= 32);
    }
}

if (! function_exists('mgd_show_element'))
{
    /** @ignore, backup implementation */
    function mgd_show_element($name)
    {
        eval('?>' . mgd_preparse(mgd_template($name)));
    }
}

if (! function_exists('mgd_set_errno'))
{
    /** @ignore, empty fallback stub */
    function mgd_set_errno($error)
    {
        debug_push('mgd_set_errno');
        debug_add("Tried to set mgd error to {$error} but Midgard is too old to support this already. Ignoring silently.",
            MIDCOM_LOG_ERROR);
        debug_print_function_stack('We were called from here:');
        debug_pop();
    }
}

// MGD_ERR Constants, only if not yet defined
// TODO: Keep in sync with http://www.midgard-project.org/api-docs/midgard/core/1.8/group__midgard__error.html
if (! defined('MGD_ERR_OK'))
{
    define('MGD_ERR_OK', -0);
    define('MGD_ERR_ERROR', -1);
    define('MGD_ERR_ACCESS_DENIED', -2);
    define('MGD_ERR_SITEGROUP_VIOLATION', -3);
    define('MGD_ERR_NOT_OBJECT', -4);
    define('MGD_ERR_NOT_EXISTS', -5);
    define('MGD_ERR_NO_MEM', -6);
    define('MGD_ERR_INVALID_NAME', -7);
    define('MGD_ERR_DUPLICATE', -8);
    define('MGD_ERR_HAS_DEPENDANTS', -9);
    define('MGD_ERR_RANGE', -10);
    define('MGD_ERR_NOT_CONNECTED', -11);
    define('MGD_ERR_SG_NOTFOUND', -12);
    define('MGD_ERR_INVALID_OBJECT', -13);
    define('MGD_ERR_QUOTA', -14);
    define('MGD_ERR_INTERNAL', -15);
    define('MGD_ERR_OBJECT_NAME_EXISTS', -16);
    define('MGD_ERR_OBJECT_NO_STORAGE', -17);
    define('MGD_ERR_OBJECT_NO_PARENT', -18);
}

if (   (version_compare(phpversion(), '5.0') < 0)
    && !function_exists('clone'))
{
    eval('
    function clone($object) 
    {
        return $object;
    }
    ');
}

/**
 * Helper function for encoding any data into JSON format
 * http://www.json.org/
 *
 * If the PECL (or PHP 5.2) JSON extension is found it will be used. Otherwise
 * the helper attempts to use the PEAR Services_JSON package.
 *
 * It will also set the MIME type of the page correctly as discussed in:
 * http://jibbering.com/blog/?p=514
 *
 * @param mixed 
 * @return string Data in JSON format
 */
function midcom_helper_json_encode($data)
{
    // Set correct MIME type
    $_MIDCOM->cache->content->content_type('application/json');
    $_MIDCOM->header('Content-type: application/json');
    
    if (function_exists('json_encode'))
    {
        // Use the json PHP extension
        return json_encode($data);
    }

    // Use the PEAR Services_JSON package
    @include_once('JSON.php');
    if (!class_exists('Services_JSON'))
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
            'Services_JSON package not found in PEAR.');
    }

    $json = new Services_JSON();
    return $json->encode($data);
}

/**
 * Helper function for decoding any data from JSON format
 * http://www.json.org/
 *
 * If the PECL (or PHP 5.2) JSON extension is found it will be used. Otherwise
 * the helper attempts to use the PEAR Services_JSON package.
 *
 * @param string Data in JSON format
 * @return mixed 
 */
function midcom_helper_json_decode($data)
{   
    if (function_exists('json_decode'))
    {
        // Use the json PHP extension
        return json_decode($data);
    }

    // Use the PEAR Services_JSON package
    @include_once('JSON.php');
    if (!class_exists('Services_JSON'))
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
            'Services_JSON package not found in PEAR.');
    }

    $json = new Services_JSON();
    return $json->decode($data);
}

if (!function_exists('get_ancestors'))
{
    /**
     * Walk back class tree to get list of all parent classes
     *
     * @param string $class The class name the check
     * @return array list of classes
     */
    function get_ancestors ($class)
    {
        $classes = array($class);
        while($class = get_parent_class($class))
        {
            $classes[] = $class;
        }
        return $classes;
    }
}
?>