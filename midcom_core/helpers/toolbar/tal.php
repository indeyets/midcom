<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAL toolbar helper
 *
 * @package midcom_core
 */
class midcom_core_helpers_toolbar_tal extends midcom_core_helpers_toolbar
{
    protected $template = '';
    
    protected function initialize()
    {
        $html = "<a href=\"#\">\n";
        $html .= "    <img src=\"".MIDCOM_STATIC_URL."/midcom_core/services/toolbars/midgard-logo.png\" width=\"16\" height=\"16\"/ alt=\"Midgard\">\n";
        $html .= "</a>\n";
        
        $data = array(
            'css_class' => $this->css_class,
            'holder_attributes' => $this->holder_attributes,
        );
        
        $this->template = $_MIDCOM->templating->get_template_content('midcom_core', 'midcom_helpers_toolbar_tal', &$data);
    }
    
    public function render(&$toolbar)
    {
        if (!class_exists('PHPTAL'))
        {
            require('PHPTAL.php');
        }
        
        $tal = new PHPTAL();        

        // $tal->MIDCOM = $_MIDCOM;
        $tal->toolbar = $toolbar;
        
        $tal->setSource($this->template);

        $html = $tal->execute();
        
        return $html;
    }
}

?>