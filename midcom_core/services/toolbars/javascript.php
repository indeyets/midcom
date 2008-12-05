<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Javascript toolbar
 *
 * @package midcom_core
 */
class midcom_core_services_toolbars_javascript implements midcom_core_services_toolbar
{
    private $configuration = array();
    private $jsconfiguration = '{}';
    
    private $toolbars = array();
    
    public function __construct(&$configuration=array())
    {
        $this->set_configuration($configuration);
        
        $_MIDCOM->head->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/services/toolbars/javascript.js");
    }
    
    public function set_configuration($configuration)
    {
        $this->configuration = $configuration;
        
        if (! array_key_exists('className', $this->configuration))
        {
            $this->configuration['className'] = 'midcom_services_toolbars_javascript';
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
    
    public function get_item($key)
    {
        return;
    }
    
    /**
     * Returns a reference to the wanted toolbar of the specified context. The toolbars
     * will be created if this is the first request.
     *
     * @param int $block_id The toolbar block to retrieve, this
     *     defaults to node.
     * @param int $context_id The context to retrieve the toolbar block for, this
     *     defaults to the current context.
     */
    public function get_item_block($block_id=MIDCOM_TOOLBAR_NODE, $context_id = null)
    {
        if ($context_id === null)
        {
            $context_id = $_MIDCOM->context->get_current_context();
        }

        if (! array_key_exists($context_id, $this->_toolbars))
        {
            $this->create_toolbars($context_id);
        }

        return $this->toolbars[$context_id][$block_id];
    }
    
    public function can_view($user=null)
    {
        return true;
    }
    
    public function render()
    {
        $html = "<div class=\"{$this->configuration['className']}\" style=\"display: none;\">\n";
        $html .= "    <div class=\"logos\">\n";
        
        $html .= $this->generate_logos();
        
        $html .= "    </div>\n";
        $html .= "    <div class=\"items\">\n";
        $html .= $this->generate_items();
        $html .= "    </div>\n";
        $html .= "    <div class=\"dragbar\"></div>\n";
        $html .= "</div>\n";
        
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "    jQuery('.{$this->configuration['className']}').midcom_services_toolbars({$this->jsconfiguration});\n";
        $html .= "</script>\n";
        
        echo $html;
    }
    
    private function generate_logos()
    {
        $html = "<a href=\"#\">\n";
        $html .= "    <img src=\"".MIDCOM_STATIC_URL."/midcom_core/services/toolbars/midgard-logo.png\" width=\"16\" height=\"16\"/ alt=\"Midgard\">\n";
        $html .= "</a>\n";
        
        return $html;
    }
    
    private function generate_items()
    {
        $html = '';
        return $html;
    }
    
    private function generate_block($name)
    {
        
        //         <div id="midcom_services_toolbars_topic-host" class="item">
        //             <span class="midcom_services_toolbars_topic_title host">Website</span>
        // <ul class='midcom_toolbar host_toolbar'>
        // 
        //   <li class='enabled'>
        //     <a href='/midcom-logout-' title='Ctrl-L' class="accesskey " accesskey='l' >
        //       <img src='/midcom-static/stock-icons/16x16/exit.png' alt='' />&nbsp;<span class="toolbar_label"><span style="text-decoration: underline;">L</span>ogout</span>
        //     </a>
        //   </li>
        //   <li class='enabled'>
        //     <a href='/__mfa/asgard/' title='Ctrl-A' class="accesskey " accesskey='a' >
        //       <img src='/midcom-static/midgard.admin.asgard/asgard2-16.png' alt='' />&nbsp;<span class="toolbar_label">Midgard&nbsp;<span style="text-decoration: underline;">A</span>dministration&nbsp;UI</span>
        // 
        //     </a>
        //   </li>
        //   <li class='enabled'>
        //     <a href='/midcom-cache-invalidate' title=''>
        //       <img src='/midcom-static/stock-icons/16x16/stock_refresh.png' alt='' />&nbsp;<span class="toolbar_label">Invalidate MidCOM's cache</span>
        //     </a>
        //   </li>
        //   <li class='last_item enabled'>
        // 
        //     <a href='/midcom-exec-midcom/config-test.php' title=''>
        //       <img src='/midcom-static/stock-icons/16x16/start-here.png' alt='' />&nbsp;<span class="toolbar_label">Test settings</span>
        //     </a>
        //   </li>
        // </ul>        </div>
    }
}

?>