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
    
    public function get_item($key, $section_id=MIDCOM_TOOLBAR_NODE);
    
    /**
     * Returns a reference to the wanted toolbar section of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $section_id The toolbar block to retrieve, this
     *     defaults to node.
     * @param int $context_id The context to retrieve the toolbar block for, this
     *     defaults to the current context.
     */
    public function get_section($section_id=MIDCOM_TOOLBAR_NODE, $context_id = null);
    
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
    public $type = 'float';
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
         * Element URL
         */
        define ('MIDCOM_TOOLBAR_URL', 'url');

        /**
         * Element Label
         */
        define ('MIDCOM_TOOLBAR_LABEL', 'label');

        /**
         * Element html label
         */
        define ('MIDCOM_TOOLBAR_HTMLLABEL', 'htmllabel');

        /**
         * Element Helptext
         */
        define ('MIDCOM_TOOLBAR_HELPTEXT', 2);

        /**
         * Element Icon (Relative URL to MIDCOM_STATIC_URL root),
         * e.g. '/stock-icons/16x16/attach.png'.
         */
        define ('MIDCOM_TOOLBAR_ICON', 'icon');

        /**
         * Element Icon (Including MIDCOM_STATIC_URL),
         * e.g. '/midcom-static/stock-icons/16x16/attach.png'.
         */
        define ('MIDCOM_TOOLBAR_ICONURL', 'iconurl');

        /**
         * Element Enabled state
         */
        define ('MIDCOM_TOOLBAR_ENABLED', 'enabled');

        /**
         * Original element URL as defined by the callee.
         */
        define ('MIDCOM_TOOLBAR__ORIGINAL_URL', 'originalurl');

        /**
         * Options array.
         */
        define ('MIDCOM_TOOLBAR_OPTIONS', 'options');

        /**
         * Set this to true if you just want to hide this element
         * from the output.
         */
        define ('MIDCOM_TOOLBAR_HIDDEN', 'hidden');
        
        /**
         * Use an HTTP POST form request if this is true. The default is not to do so.
         */
        define ('MIDCOM_TOOLBAR_POST', 'is_post');
        
        /**
         * Optional arguments for a POST request.
         */
        define ('MIDCOM_TOOLBAR_POST_HIDDENARGS', 'hiddenargs');

        /**
         * Item css class name
         */
        define ('MIDCOM_TOOLBAR_CLASSNAME', 'css_class');

        /**
         * The accesskey for section item
         */
        define ('MIDCOM_TOOLBAR_ACCESSKEY', 'accesskey');
        
        /**
         * Identifier for a node toolbar for a request context.
         *
         */
        define ('MIDCOM_TOOLBAR_NODE', 'node');

        /**
         * Identifier for a view toolbar for a request context.
         *
         */
        define ('MIDCOM_TOOLBAR_VIEW', 'view');

        /**
         * Identifier for a host toolbar for a request context.
         *
         */
        define ('MIDCOM_TOOLBAR_HOST', 'host');

        /**
         * Identifier for a help toolbar for a request context.
         *
         */
        define ('MIDCOM_TOOLBAR_HELP', 'help');
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