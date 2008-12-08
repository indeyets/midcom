<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Navigation helper for MidCOM 3
 *
 *
 * @package midcom_core
 */
class midcom_core_helpers_navigation
{
    public $tree = array();
    public $active = 0;
    protected $_root = 0;
    
    public function __construct($root_id=null)
    {
        if (! is_null($root_id))
        {
            $this->_root = $root_id;
        }
        else
        {
            $this->_root = $_MIDGARD['page'];
        }
        
        $this->active = $_MIDGARD['page'];
        
        // echo "this->_root: {$this->_root}\n";
        // echo "this->_active: {$this->_active}\n";
        
        $this->tree = $this->_get_children(0);
    }
    
    protected function _get_children($parent_id)
    {
        $prefix = "{$_MIDGARD['sitegroup']}-{$_MIDGARD['host']}-{$_MIDGARD['page']}"; // FIXME: Take account midgard configuration as it's possible
        if (class_exists('Memcache'))
        {   
            $memcache = new Memcache;
            $memcache->connect('localhost');
            if (!$childs = $memcache->get($prefix . 'childs-' . $parent_id))
            {
            
            }
            else
            {
                return $childs;
            }
        }
        $mc = midgard_page::new_collector('up', $parent_id);
        $mc->set_key_property('id');
        $mc->add_value_property('id');
        $mc->add_value_property('name');
        $mc->add_value_property('title');
        $mc->add_value_property('component');
        $mc->add_order('metadata.score');
        $mc->execute();

        $child_ids = $mc->list_keys();
        
        $children = array();
        foreach ($child_ids as $cid => $data)
        {
            $children[] = $this->_prepare_page_data($mc->get_subkey($cid, 'id'), $mc->get_subkey($cid, 'name'), $mc->get_subkey($cid, 'title'), $mc->get_subkey($cid, 'component'));
        }
        if (class_exists('Memcache'))
        {
            $memcache->set($prefix . 'childs-' . $parent_id, $children, false, 36000);
        }
        return $children;
    }
    
    protected function _prepare_page_data($id, $name, $title, $component)
    {        
        $navigation_item = new midcom_core_helpers_navigation_item($id, $name, $title, $component);
        $navigation_item->add_children($this->_get_children($id));
        
        if ($id == $this->active)
        {
            $navigation_item->is_active = true;
        }
        
        return $navigation_item;
    }
}

/**
 * @package midcom_core
 */
class midcom_core_helpers_navigation_item
{
    public $id;
    public $name;
    public $title;
    public $component;
    public $is_active = false;
    public $has_children = false;
    public $children = array();
    
    public function __construct($id, $name, $title, $component)
    {
        $this->id = $id;
        $this->name = $name;
        $this->title = $title;
        $this->component = $component;
    }
    
    public function add_children(array $childs)
    {
        if (! empty($childs))
        {
            $this->has_children = true;
        }        
        $this->children = $childs;
    }

}
?>
