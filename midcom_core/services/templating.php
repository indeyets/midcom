<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Templating interface for MidCOM 3
 *
 * @package midcom_core
 */
interface midcom_core_services_templating
{
    /**
     * Include the template based on either global or controller-specific template entry point.
     */ 
    public function template();
    
    /**
     * Include the content template based on either global or controller-specific template entry point.
     */
    public function content();
    
    /**
     * Show the loaded contents using the template engine
     *
     * @param string $content Content to display
     */
    public function display($content);
}
?>