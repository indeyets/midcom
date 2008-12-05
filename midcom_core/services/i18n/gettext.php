<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * HTTP Basic authentication service for MidCOM
 *
 * @package midcom_core
 */
require_once('PHPTAL.php'); // FIXME: Better place required
require_once 'PHPTAL/GetTextTranslator.php'; // FIXME: Better place required
class midcom_core_services_i18n_gettext implements midcom_core_services_i18n
{
    /**
     * Simple constructor.
     * 
     * @access public
     */
     
    private $tr = array();
    private $language = null;
     
    public function __construct()
    {
        // Adding MidCOM core messages to the translation domains
        bindtextdomain('midcom_core', MIDCOM_ROOT . '/midcom_core/locale/');
        
        try 
        {
            // set language to use for this session (first valid language will 
            // be used)
            $this->language = $_MIDCOM->configuration->get('default_language');
        }
        catch (Exception $e)
        {
            echo $e;
        }
        
        $path = MIDCOM_ROOT . "/midcom_core/locale/";
        $this->tr['midcom_core'] = new PHPTAL_GetTextTranslator();
        $this->tr['midcom_core']->addDomain('midcom_core', $path);
    }
    
    public function get($key, $component = null)
    {
        if (is_null($component))
        {
            return gettext($key);
        }
        else
        {
            return dgettext($component, $key);
        }
    }
    
    public function &set_translation_domain($component_name)
    {
        // If no component name is set, then it's from the core
        // translations are going to get searched.
        if ($component_name == '')
        {
            $component_name = 'midcom_core';
        }
        
         // Checking if TAL translator is already available
        if ( isset($this->tr[$component_name])) 
        { 
            // useDomain must be called. Otherwise gettext context is not changed 
            $this->tr[$component_name]->useDomain($component_name); 
            return $this->tr[$component_name]; 
        } 
    
        try
        {
            $this->tr[$component_name] = new PHPTAL_GetTextTranslator();
            $this->tr[$component_name]->setLanguage($this->language.'.utf8', $this->language);
        }
        catch (Exception $e)
        {
            echo ($e);
        }
        // register gettext domain to use
        $path = MIDCOM_ROOT . "/{$component_name}/locale/";
        $this->tr[$component_name]->addDomain($component_name, $path);

        // specify current domain
        $this->tr[$component_name]->useDomain($component_name);
        return $this->tr[$component_name]; 
    }
    
    //public function set_language(midgard_language $language, $switch_content_language)
    public function set_language($locale, $switch_content_language)
    {
        $this->language = $locale;
        
        foreach($this->tr as $key => $val)
        {
            $this->tr[$key]->setLanguage($this->language.'.utf8', $this->language);
        }

        // $_MIDCOM->context->gettext_translator->setLanguage($lang.'.utf8', $lang);

        if ($switch_content_language)
        {
            $this->set_content_language($language);
        }
    }
    
    public function set_content_language(midgard_language $language)
    {
        die("Not implemented yet");        
    }
    
}
?>
