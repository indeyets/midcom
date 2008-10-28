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
 * or in the database and returns its content or code-field, respectively.
 *
 * Prefix the snippet Path with 'file:' for retrieval of a file relative to
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
    static $cached_snippets = array();
    if (array_key_exists($path, $cached_snippets))
    {
        return $cached_snippets[$path];
    }

    if (substr($path, 0, 5) == 'file:')
    {
        $filename = MIDCOM_ROOT . substr($path, 5);
        if (! file_exists($filename))
        {
            $cached_snippets[$path] = null;
            return null;
        }
        $data = file_get_contents($filename);
    }
    else
    {
        $snippet = new midgard_snippet();
        try
        {
            $snippet->get_by_path($path);
        }
        catch (Exception $e)
        {
            $cached_snippets[$path] = null;
            return null;
        }
        $_MIDCOM->cache->content->register($snippet->guid);
        $data = $snippet->code;
    }
    $cached_snippets[$path] = $data;
    return $data;
}

/**
 * This helper function searche for a snippet either in the Filesystem
 * or in the database and returns its content or code-field, respectively.
 *
 * Prefix the snippet Path with 'file:' for retrieval of a file relative to
 * MIDCOM_ROOT; omit it to get the code field of a Snippet.
 *
 * Any error (files not found) will raise a MidCOM Error. If you want a more
 * graceful behavior, look for midcom_get_snippet_content_graceful
 *
 * @param string $path    The URL to the snippet.
 * @return string        The content of the snippet/file.
 */
function midcom_get_snippet_content($path)
{
    $data = midcom_get_snippet_content_graceful($path);
    if (is_null($data))
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not load the contents of the snippet {$path}: Snippet does not exist.");
        // This will exit.
    }
    return $data;
}

/**
 * PHP-level implementation of the Midgard Preparser language
 * construct mgd_include_snippet. Same semantics, but probably a little bit
 * slower.
 *
 * @param string $path    The path of the snippet that should be included.
 * @return boolean Returns false if the snippet could not be loaded or true, if it was evaluated successfully.
 */
// This function is there as a backup in case you are not running within the
// Midgard Parser; it will run the snippet code through mgd_preparse manually.
function mgd_include_snippet_php($path)
{
    $code = midcom_get_snippet_content_graceful($path);
    if (empty($code))
    {
        debug_add("mgd_include_snippet_php: Could not find snippet {$path}: " . $e->getMessage(), MIDCOM_LOG_ERROR);
        return false;
    }
    debug_add("mgd_include_snippet_php: Evaluating snippet {$path}.");
    eval ('?>' . mgd_preparse($code));
    return true;
}

/**
 * Helper function for generating "clean" URL names from titles, etc.
 *
 * @param string $string    String to edit.
 * @param string $replacer    The replacement for invalid characters.
 * @return string            Normalized name.
 */
function midcom_generate_urlname_from_string($string, $replacer = "-")
{
    // TODO: sanity-check $replacer ?
    $orig_string = $string;
    // Try to transliterate non-latin strings to URL-safe format
    require_once(MIDCOM_ROOT. '/midcom/helper/utf8_to_ascii.php');
    $string = utf8_to_ascii($string, $replacer);
    $string = trim(str_replace('[?]', '', $string));

    // Ultimate fall-back, if we couldn't get anything out of the transliteration we use the UTF-8 character hexes as the name string to have *something*
    if (   empty($string)
        || preg_match("/^{$replacer}+$/", $string))
    {
        $i = 0;
        // make sure this is not mb_strlen (ie mb automatic overloading off)
        $len = strlen($orig_string);
        $string = '';
        while ($i < $len)
        {
            $byte = $orig_string[$i];
            $string .= str_pad(dechex(ord($byte)), '0', STR_PAD_LEFT);
            $i++;
        }
    }

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
 * Helper function for finding MIME type image for a document
 *
 * Used in midcom.admin.styleeditor, midcom.helper.imagepopup, midgard.admin.asgard and org.openpsa.documents.
 *
 * @param string $mimetype  Document MIME type
 * @return string    Path to the icon
 */
function midcom_helper_get_mime_icon($mimetype, $fallback = '')
{
    $mime_fspath = MIDCOM_STATIC_ROOT . '/stock-icons/mime';
    $mime_urlpath = MIDCOM_STATIC_URL . '/stock-icons/mime';
    $mimetype_filename = str_replace('/', '-', $mimetype);
    if (!is_readable($mime_fspath))
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Couldn't read directory {$mime_fspath}", MIDCOM_LOG_WARN);
        debug_pop();
    }
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
 * @return string    Prettified file size
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

    // Otherwise, go with QB
    $qb = midcom_db_topic::new_query_builder();
    $qb->add_constraint('component', '=', $component);
    $qb->add_constraint('name', '<>', '');
    $qb->add_constraint('up', 'INTREE', $node_id);
    $qb->set_limit(1);
    $topics = $qb->execute();
    
    if (count($topics) == 0)
    {
        return null;
    }
    
    $node = $nap->get_node($topics[0]);
    return $node;
}

function midcom_show_element($name) 
{ 
    eval('?>' . mgd_preparse(mgd_template($name))); 
} 
?>