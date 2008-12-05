<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * float toolbar
 *
 * @package midcom_core
 */
class midcom_core_services_toolbars_float implements midcom_core_services_toolbar
{
    private $configuration = array();
    private $jsconfiguration = '{}';
    
    private $toolbars = array();
    
    private $helper;
    
    public $has_logos = false;

    public $logos = array();
    public $sections = array();
    
    public function __construct(&$configuration=array())
    {
        $this->set_configuration($configuration);
        
        $this->helper = new midcom_core_helpers_toolbar_tal(
            $this->configuration['className'],
            'style="display: none;"'
        );

        $this->create_toolbar($_MIDCOM->context->get_current_context());
        
        $_MIDCOM->head->enable_jsmidcom();
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/services/toolbars/javascript.js");
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/services/toolbars/float.js");
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/jQuery/jquery.dimensions-1.1.2.js");
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/jQuery/jquery.easydrag-1.4.js");
        
        $_MIDCOM->head->add_link_head(
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL . '/midcom_core/services/toolbars/float.css',
            )
        );
        $_MIDCOM->head->add_link_head(
            array
            (
                'condition' => 'eq IE',
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL . '/midcom_core/services/toolbars/float-ie.css',
            )
        );
    }
    
    public function set_configuration($configuration)
    {
        $this->configuration = $configuration;
        
        if (! array_key_exists('className', $this->configuration))
        {
            $this->configuration['className'] = 'midcom_services_toolbars_float';
        }
        
        if (array_key_exists('js', $this->configuration))
        {
            $jsconfig = '{';
            
            $config_length = count($this->configuration['js']);
            $curr_key_i = 1;
            foreach ($this->configuration['js'] as $key => $value)
            {
                $jsconfig .= "{$key}: {$value}";
                if ($curr_key_i < $config_length)
                {
                    $jsconfig .= ", ";
                }                
                $curr_key_i += 1;      
            }
            
            $jsconfig .= '}';
            
            $this->jsconfiguration = $jsconfig;
        }
    }
    
    public function add_item($data)
    {
        return true;
    }
    
    public function remove_item($key)
    {
        return false;
    }
    
    public function get_item($key, $section_id=MIDCOM_TOOLBAR_NODE)
    {
        return;
    }
    
    /**
     * Returns a reference to the wanted toolbar section of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $section_id The toolbar block to retrieve, this
     *     defaults to node.
     * @param int $context_id The context to retrieve the toolbar block for, this
     *     defaults to the current context.
     */
    public function get_section($section_id=MIDCOM_TOOLBAR_NODE, $context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->context->get_current_context();
        }

        if (! array_key_exists($context_id, $this->toolbars))
        {
            $this->create_toolbar($context_id);
        }

        return $this->toolbars[$context_id][$block_id];
    }
    
    public function can_view($user=null)
    {
        if ($_MIDCOM->context->mimetype == 'text/html')
        {
            return true;            
        }
        
        return false;
    }
    
    public function render()
    {
        $html = $this->helper->render(&$this);
        
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "    jQuery(document).ready(function() {\n";
        $html .= "        jQuery('.{$this->configuration['className']}').midcom_services_toolbars('float', {$this->jsconfiguration});\n";
        $html .= "    });\n";
        $html .= "</script>\n";
        
        echo $html;
    }
    
    private function generate_logo($title, $link, $image_path)
    {        
        $logo = array(
            'title' => $title,
            'url' => $link,
            'path' => MIDCOM_STATIC_URL . $image_path,
        );
        
        if (! $this->has_logos)
        {
            $this->has_logos = true;
        }
        
        return $logo;
    }
    
    /**
     * Creates the default toolbar sections for a given context ID.
     * Creates logos defined in configuration.
     *
     * @param int $context_id The context ID for which the toolbars should be created.
     */
    private function create_toolbar($context_id)
    {
        if (   array_key_exists('logos', $this->configuration)
            && !empty($this->configuration['logos']))
        {
            foreach ($this->configuration['logos'] as $key => $logo)
            {
                $this->logos[] = $this->generate_logo($logo['title'], $logo['url'], $logo['image']);
            }
        }
        
        $this->sections[MIDCOM_TOOLBAR_NODE] = array(
            'name' => 'section_' . MIDCOM_TOOLBAR_NODE,
            'title' => 'Node',
            'css_class' => "{$this->configuration['className']}_section_" . MIDCOM_TOOLBAR_NODE,
            'items' => array(),
        );
        $this->sections[MIDCOM_TOOLBAR_VIEW] = array(
            'name' => 'section_' . MIDCOM_TOOLBAR_VIEW,
            'title' => 'View',
            'css_class' => "{$this->configuration['className']}_section_" . MIDCOM_TOOLBAR_VIEW,
            'items' => array(),
        );
        $this->sections[MIDCOM_TOOLBAR_HOST] = array(
            'name' => 'section_' . MIDCOM_TOOLBAR_HOST,
            'title' => 'Host',
            'css_class' => "{$this->configuration['className']}_section_" . MIDCOM_TOOLBAR_HOST,
            'items' => array(),
        );
        $this->sections[MIDCOM_TOOLBAR_HELP] = array(
            'name' => 'section_' . MIDCOM_TOOLBAR_HELP,
            'title' => 'Help',
            'css_class' => "{$this->configuration['className']}_section_" . MIDCOM_TOOLBAR_HELP,
            'items' => array(),
        );
        
        $this->add_node_management_commands(&$this->sections[MIDCOM_TOOLBAR_NODE]['items'], $context_id);
        // $this->add_host_management_commands(&$this->sections[MIDCOM_TOOLBAR_HOST]['items'], $context_id);
        // $this->add_help_management_commands(&$this->sections[MIDCOM_TOOLBAR_HELP]['items'], $context_id);

        $this->toolbars[$context_id] = $this->sections;
    }
    
    /**
     * Adds the node management commands to the specified section.
     *
     * @param array $items A reference to the sections items to be filled.
     * @param int $context_id The context to use (the topic is drawn from there). This defaults
     *     to the currently active context.
     */
    function add_node_management_commands(&$items, $context_id = null)
    {
        // if ($context_id === null)
        // {
        //     $topic = $_MIDCOM->context->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        // }
        // else
        // {
        //     $topic = $_MIDCOM->context->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC, $context_id);
        // }
        // 
        // if (! $topic)
        // {
        //     return false;
        // }
        // 
        // if (! is_a($topic, 'midcom_baseclasses_database_topic'))
        // {
        //     // Force-Cast to DBA object
        //     $topic = new midcom_db_topic($topic->id);
        // }
                
        $this->helper->add_item(MIDCOM_TOOLBAR_NODE,
            array
            (
                MIDCOM_TOOLBAR_URL => "/__midcom/edit/",
                MIDCOM_TOOLBAR_LABEL => 'edit node',
                MIDCOM_TOOLBAR_ICON => 'midcom_core/stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',
            )
        );
        
        $items =& $this->helper->get_section_items(MIDCOM_TOOLBAR_NODE);
    }
}

?>