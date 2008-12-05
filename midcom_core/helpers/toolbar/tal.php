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
    protected $template_name = 'midcom_helpers_toolbar_tal';
    protected $template = '';
    
    protected function initialize()
    {        
        $data = array(
            'css_class' => $this->css_class,
            'holder_attributes' => $this->holder_attributes,
        );
        
        $this->template = $_MIDCOM->templating->get_template_content('midcom_core', $this->template_name, &$data);
    }
    
    public function render(&$toolbar)
    {
        if (!class_exists('PHPTAL'))
        {
            require('PHPTAL.php');
        }
        
        $tal = new PHPTAL();
        $tal->toolbar = $toolbar;
        
        $tal->setSource($this->template);

        $html = $tal->execute();
        
        return $html;
    }
}

?>