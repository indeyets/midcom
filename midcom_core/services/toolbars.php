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
interface midcom_core_services_toolbars
{
    /**
     * @param &$configuration Configuration for the current toolbar type
     */
    public function __construct(&$configuration = array());
    
    public function add_item($section, $key, $item);
    
    public function remove_item($section, $key);
    
    public function get_item($section, $key);
    
    /**
     * Returns a reference to the wanted toolbar section of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $section_id The toolbar block to retrieve, this
     *     defaults to node.
     */
    public function get_section($section);
    
    public function can_view($user = null);
    
    public function render();
}

/**
 * Toolbars core class
 *
 * @package midcom_core
 */
abstract class midcom_core_services_toolbars_baseclass implements midcom_core_services_toolbars
{
    protected $configuration = array();
    protected $sections = array();
    protected $icon_pack = '/midcom_core/stock-icons/16x16/';
    
    public function __construct(&$configuration = array())
    {   
        $this->configuration = $configuration;
    }
    
    private function normalize_item($key, $item)
    {
        if (!isset($item['route_id']))
        {
            throw new Exception("Route ID not defined in toolbar item {$key}");
        }
        
        if (!isset($item['route_arguments']))
        {
            $item['route_arguments'] = array();
        }
        
        $item['url'] = $_MIDCOM->dispatcher->generate_url($item['route_id'], $item['route_arguments']);
        
        if (!isset($item['icon']))
        {
            $item['icon'] = 'properties';
        }
        
        if (!isset($item['is_post']))
        {
            $item['is_post'] = false;
        }
        
        if (!isset($item['enabled']))
        {
            $item['enabled'] = true;
        }
        
        $item['icon_url'] = MIDCOM_STATIC_URL . "{$this->icon_pack}{$item['icon']}.png";

        return $item;
    }

    public function add_item($section, $key, $item)
    {
        if (!isset($this->sections[$section]))
        {
            $this->sections[$section] = array
            (
                'title' => $section,
                'items' => array(),
            );
        }
        
        $this->sections[$section]['items'][$key] = $this->normalize_item($key, $item);
    }
    
    public function remove_item($section, $key)
    {
        if (!isset($this->sections[$section]))
        {
            throw new OutOfBoundsException("Toolbar section {$section} not found.");
        }
        
        if (!isset($this->sections[$section]['items'][$key]))
        {
            throw new OutOfBoundsException("Toolbar item {$key} in section {$section} not found.");
        }
        
        unset($this->sections[$section][$key]);
    }
    
    public function get_item($section, $key)
    {
        if (!isset($this->sections['section']))
        {
            throw new OutOfBoundsException("Toolbar section {$section} not found.");
        }

        if (!isset($this->sections[$section]['items'][$key]))
        {
            throw new OutOfBoundsException("Toolbar item {$key} in section {$section} not found.");
        }
        
        return $this->sections[$section]['items'][$key];
    }

    /**
     * Returns a reference to the wanted toolbar section. The toolbars
     * will be created if this is the first request.
     *
     * @param int $section_id The toolbar block to retrieve, this
     *     defaults to node.
     */    
    public function get_section($section)
    {
        if (!isset($this->sections[$section]))
        {
            throw new OutOfBoundsException("Toolbar section {$section} not found.");
        }

        return $this->sections[$section];
    }
    
    public function can_view($user = null)
    {
        return true;
    }
    
    public function render()
    {
    }
}

?>