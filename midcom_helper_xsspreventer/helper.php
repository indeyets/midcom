<?php
/**
 * @package midcom_helper_xsspreventer
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Static helper functions for the Cross-Site Scripting (XSS) preventer.
 *
 * @package midcom_helper_xsspreventer
 */
class midcom_helper_xsspreventer_helper
{
    /**
     * Escape value of an XML attribute
     *
     * @param string $input Attribute value to escape
     */
    static public function escape_attribute($input)
    {
        $output = str_replace('"', '&quot;', $input);
        return '"' . $output . '"';
    }

    /**
     * Escape contents of an XML element
     *
     * @param string $element XML element to close
     * @param string $input Element content to escape
     */
    static public function escape_element($element, $input)
    {
        return preg_replace_callback
        (
        	"%(<\s*)+(/\s*)+{$element}%i", 
            create_function
            (
            	'$matches',
            	'return htmlentities($matches[0]);'
            ),
            $input
        );
    }
}

?>
