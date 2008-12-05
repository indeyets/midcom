<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Midgard-based templating interface for MidCOM 3
 *
 * @package midcom_core
 */
class midcom_core_services_templating_midgard implements midcom_core_services_templating
{
    /**
     * Include the template based on either global or controller-specific template entry point.
     */    
    public function template()
    {
        $template_entry_point = $_MIDCOM->context->get_item('template_entry_point');

        $component = $_MIDCOM->context->get_item('component');
        if (   !mgd_is_element_loaded($template_entry_point)
            && $component)
        {        
            // Load element from component templates
            echo $_MIDCOM->componentloader->load_template($component, $template_entry_point);
        }
        else
        {
            eval('?>' . mgd_preparse(mgd_template($template_entry_point)));
        }
    }
    
    /**
     * Include the content template based on either global or controller-specific template entry point.
     */
    public function content()
    {
        $content_entry_point = $_MIDCOM->context->get_item('content_entry_point');

        $page_data = $_MIDCOM->context->get_item('page');

        $component = $_MIDCOM->context->get_item('component');

        if (   !mgd_is_element_loaded($content_entry_point)
            && $component)
        {        
            // Load element from component templates
            echo $_MIDCOM->componentloader->load_template($component, $content_entry_point);
        }
        else
        {
            eval('?>' . mgd_preparse(mgd_template($content_entry_point)));
        }
    }
    
    /**
     * Show the loaded contents using the template engine
     *
     * @param string $content Content to display
     */
    public function display($content)
    {
        $data = $_MIDCOM->context->get();
        switch ($data['template_engine'])
        {
            case 'tal':
                require('PHPTAL.php');
                include('TAL/modifiers.php');
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-require');
                }
                
                $tal = new PHPTAL();
                $tal->setSource($content);

                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-source');
                }
                
                $tal->navigation = $_MIDCOM->navigation;
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-set-navigation');
                }
                
                $tal->MIDCOM = $_MIDCOM;

                foreach ($data as $key => $value)
                {
                    $tal->$key = $value;
                    
                    if ($_MIDCOM->timer)
                    {
                        $_MIDCOM->timer->setMarker("post-set-{$key}");
                    }
                }
                
                $content = $tal->execute();
                
                if ($_MIDCOM->timer)
                {
                    $_MIDCOM->timer->setMarker('post-execute');
                }
                break;
            default:
                break;
        }

        echo $content;
        
        if ($_MIDCOM->timer)
        {
            $_MIDCOM->timer->display();
        }
    }
}
?>