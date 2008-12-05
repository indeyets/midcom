<?php
/**
 * @package midcom_tests
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

if (! defined('MIDCOM_TESTS_ENABLE_OUTPUT')) {
    define('MIDCOM_TESTS_ENABLE_OUTPUT', true);
}

/**
 * @package midcom_tests
 */
class midcom_core_tests_helpers
{
    public static function get_tests($root_file, $root_class, $add_skip = null)
    {
        $tests = array();
        
        if (   !isset($root_file)
            || !isset($root_class))
        {
            return $tests;
        }
        
        $skip = array( '.', '..', 'all.php' );
        if (is_array($add_skip))
        {
            $skip = array_merge( $skip, $add_skip );
        }
        $skip = array_flip($skip);
        
        $path_parts = pathinfo($root_file);
        $tests_dir = dir($path_parts['dirname']);
        $prefix = str_replace('_all', '', $root_class);

        while(($testfile = $tests_dir->read()) !== false)
        {
            if (   array_key_exists($testfile, $skip)
                || substr($testfile, 0, 1) == '.') 
            {
                continue;
            }
            
            $path_parts = pathinfo($testfile);
            $test_name = str_replace('.php', '', $path_parts['filename']);
            $test_name = str_replace('Test', '', $test_name);

            if ($test_name != '')
            {
                require_once(realpath(dirname($root_file)) . "/{$testfile}");
                $tests[] = "{$prefix}_{$test_name}";
            }
        }
        
        return $tests;
    }
}

?>