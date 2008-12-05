<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

if (MIDCOM_TEST_RUN)
{
    $path_parts = pathinfo(__FILE__);
    require_once("{$path_parts['dirname']}/../toolbars.php");
}

/**
 * float toolbar
 *
 * @package midcom_core
 */
include MIDCOM_ROOT . "/midcom_core/services/toolbars.php";
class midcom_core_services_toolbars_float extends midcom_core_services_toolbars_baseclass implements midcom_core_services_toolbars 
{
    protected $configuration = array();
    private $jsconfiguration = '{}';
    
    private $toolbars = array();
    
    private $helper;
    
    public $has_logos = false;

    public $logos = array();
    public $sections = array();
    
    public function __construct(&$configuration = array())
    {
        $this->set_configuration($configuration);
        
        $this->helper = new midcom_core_helpers_toolbar_tal
        (
            $this->configuration['className'],
            'style="display: none;"'
        );

        $this->create_toolbar($_MIDCOM->context->get_current_context());
        
        $_MIDCOM->head->enable_jsmidcom();
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/services/toolbars/javascript.js");
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/services/toolbars/float.js");
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/jQuery/jquery.dimensions-1.1.2.js");
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/jQuery/jquery.easydrag-1.4.js");
        
        $_MIDCOM->head->add_link_head
        (
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL . '/midcom_core/services/toolbars/float.css',
            )
        );
        $_MIDCOM->head->add_link_head
        (
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
        
        if (   array_key_exists('js', $this->configuration)
            && is_array($this->configuration['js']))
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
    
    public function can_view($user = null)
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
        $logo = array
        (
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
        $this->toolbars[$context_id] = $this->sections;
    }
}
?>