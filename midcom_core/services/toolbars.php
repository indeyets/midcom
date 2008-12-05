<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

 /**
  * Toolbar interface
  *
  * @package midcom_core
  */
interface midcom_core_services_toolbar
{
    /**
     * @param &$configuration Configuration for the current toolbar type
     */
    public function __construct(&$configuration=array());
    
    public function add_item($data);
    
    public function remove_item($key);
    
    public function get_item($key);
    
    /**
     * Returns a reference to the wanted toolbar of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $block_id The toolbar block to retrieve, this
     *     defaults to node.
     * @param int $context_id The context to retrieve the toolbar block for, this
     *     defaults to the current context.
     */
    public function get_item_block($block_id=MIDCOM_TOOLBAR_NODE, $context_id = null);
    
    public function can_view($user=null);
    
    public function render();
}

/**
 * Toolbars core class
 *
 * @package midcom_core
 * extends midcom_core_component_baseclass
 */
class midcom_core_services_toolbars
{
    public $type = 'javascript';
    public $implementation = null;
    
    public function __construct(&$configuration)
    {
        $this->set_definitions();
        
        if (array_key_exists('type', $configuration))
        {
            $this->type = $configuration['type'];
        }
        
        $classname = "midcom_core_services_toolbars_{$this->type}";
        $this->implementation = new $classname($configuration);
    }
    
    private function set_definitions()
    {
        /**
         * Identifier for a node toolbar for a request context.
         *
         */
        if (! defined('MIDCOM_TOOLBAR_NODE'))
        {
            define ('MIDCOM_TOOLBAR_NODE', 100);            
        }

        /**
         * Identifier for a view toolbar for a request context.
         *
         */
        if (! defined('MIDCOM_TOOLBAR_VIEW'))
        {
            define ('MIDCOM_TOOLBAR_VIEW', 101);
        }

        /**
         * Identifier for a host toolbar for a request context.
         *
         */
        if (! defined('MIDCOM_TOOLBAR_HOST'))
        {
            define ('MIDCOM_TOOLBAR_HOST', 104);            
        }

        /**
         * Identifier for a help toolbar for a request context.
         *
         */
        if (! defined('MIDCOM_TOOLBAR_HELP'))
        {
            define ('MIDCOM_TOOLBAR_HELP', 105);
        }
    }
    
    public function get_implementation()
    {
        if ($this->implementation == null)
        {
            return false;
        }
        
        return $this->implementation;
    }
}

?>