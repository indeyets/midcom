<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Head includes helper for MidCOM 3
 *
 *
 * @package midcom_core
 */
class midcom_core_helpers_snippet
{
    public function __construct() {}
    
    static function get($path, $graceful=false)
    {        
        // TODO: Implement using http://spyc.sourceforge.net/ if syck is not available
        if ($graceful)
        {
            $content = midcom_core_helpers_snippet::get_contents_graceful($path);
        }
        else
        {
            $content = midcom_core_helpers_snippet::get_contents($path);
        }
        
        return syck_load($content);
    }
    
    /**
     * This helper function searches for a snippet either in the Filesystem
     * or in the database and returns its content or code-field, respectively.
     *
     * Prefix the snippet Path with 'file:' for retrieval of a file relative to
     * MIDCOM_ROOT; omit it to get the code field of a Snippet.
     *
     * Any error (files not found) will raise a OutOfBoundsException. If you want a more
     * graceful behavior, look for get_content_graceful
     *
     * @param string $path    The URL to the snippet.
     * @return string        The content of the snippet/file.
     */
    static function get_contents($path)
    {        
        if (substr($path, 0, 5) == 'file:')
        {
            $filename = MIDCOM_ROOT . substr($path, 5);

            if (! file_exists($filename))
            {
                throw new OutOfBoundsException("Could not load the contents of the file {$filename}: File not found.");
            }
            $data = file_get_contents($filename);
        }
        else
        {
            try
            {
                $snippet = new midgard_snippet();
                $snippet->get_by_path($path);
            }
            catch (Exception $e)
            {
                throw new OutOfBoundsException("Could not load the contents of the snippet {$path}: Snippet does not exist.");
            }
            
            $data = $snippet->code;
        }
        
        return $data;        
    }
    
    /**
     * This helper function searches for a snippet either in the Filesystem
     * or in the database and returns its content or code-field, respectively.
     *
     * Prefix the snippet Path with 'file:' for retrieval of a file relative to
     * MIDCOM_ROOT; omit it to get the code field of a Snippet.
     *
     * @param string $path    The URL to the snippet.
     * @return string        The content of the snippet/file.
     */
    static function get_contents_graceful($path)
    {
        if (substr($path, 0, 5) == 'file:')
        {
            $filename = MIDCOM_ROOT . substr($path, 5);
            if (! file_exists($filename))
            {
                return '';
            }
            $data = file_get_contents($filename);
        }
        else
        {
            try
            {
                $snippet = new midgard_snippet();
                $snippet->get_by_path($path);
            }
            catch (Exception $e)
            {
                return '';
            }
            
            $data = $snippet->code;
        }
        return $data;        
    }
}

?>