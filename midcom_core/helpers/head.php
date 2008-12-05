<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Head includes helper for MidCOM 3
 *
 *
 * @package midcom_core
 */
class midcom_core_helpers_head
{
    private $link_head = array();
    private $meta_head = array();

    private $js_head = array();
    
    private $prepend_script_head = array();
    private $script_head = array();
    
    private $prepend_css_head = array();
    private $css_head = array();
    private $append_css_head = array();
    
    private $jquery_enabled = false;
    private $enable_jquery_noconflict = false;
    private $jquery_inits = "";
    private $jquery_statuses_prepend = array();
    private $jquery_statuses = array();
    private $jquery_statuses_append = array();
    
    private $jsmidcom_enabled = false;
    
    public function __construct($enable_jquery=false, $enable_jsmidcom=false)
    {
        if ($enable_jquery)
        {
            $this->enable_jquery();
        }
        
        if ($enable_jsmidcom)
        {
            $this->enable_jsmidcom();
        }
    }
    
    public function enable_jquery($version="1.2.3")
    {
        if ($this->jquery_enabled)
        {
            return;
        }
        
        $url = MIDCOM_STATIC_URL . "/midcom_core/jQuery/jquery-{$version}.js";
        $this->jquery_inits = "<script type=\"text/javascript\" src=\"{$url}\"></script>\n";        
        
        $script = 'var $j = jQuery.noConflict();'."\n";

        $this->jquery_inits .= "<script type=\"text/javascript\">\n";
        $this->jquery_inits .= trim($script) . "\n";
        $this->jquery_inits .= "</script>\n";
        
        $this->jquery_enabled = true;
    }
    
    public function enable_jsmidcom()
    {
        if ($this->jsmidcom_enabled)
        {
            return;
        }
        
        $this->add_jsfile(MIDCOM_STATIC_URL . "/midcom_core/midcom.js", true);
        
        $script = "jQuery.midcom.init({\n";
        $script .= "    MIDCOM_STATIC_URL: '" . MIDCOM_STATIC_URL . "',\n";
        $script .= "    MIDCOM_PAGE_PREFIX: '/'\n"; //$_MIDCOM->get_page_prefix()
        $script .= "});\n";
        
        $this->add_script($script, true);
        
        $this->jsmidcom_enabled = true;
    }
    
    function add_jsfile($url, $prepend = false)
    {
        // Adds a URL for a <script type="text/javascript" src="tinymce.js"></script>
        // like call. $url is inserted into src. Duplicates are omitted.
        if (! in_array($url, $this->js_head))
        {
            $js_call = "<script type=\"text/javascript\" src=\"{$url}\"></script>\n";
            if ($prepend)
            {
                // Add the javascript include to the beginning, not the end of array
                array_unshift($this->js_head, $js_call);
            }
            else
            {
                $this->js_head[] = $js_call;
            }
        }
    }
    
    function add_script($script, $prepend = false, $type = 'text/javascript', $defer = '')
    {
        $js_call = "<script type=\"{$type}\"{$defer}>\n";
        $js_call .= trim($script) . "\n";
        $js_call .= "</script>\n";
        
        if ($prepend)
        {
            $this->prepend_script_head[] = $js_call;
        }
        else
        {
            $this->script_head[] = $js_call;
        }
    }
    
    /**
     * Echo the head elements added.
     * This function echos the elements added by the add_(css|meta|link|js(file|script)|jquery)
     * methods.
     *
     * Place the method within the <head> section of your page.
     *
     * This allows MidCOM components to register HEAD elements
     * during page processing. The site style code can then query this queued-up code
     * at anytime it likes. The queue-up SHOULD be done during the code-init phase,
     * while the print_elements output SHOULD be included in the HTML HEAD area and
     * the HTTP onload attribute returned by print_jsonload SHOULD be included in the
     * BODY-tag. Note, that these suggestions are not enforced, if you want a JScript
     * clean site, just omit the print calls and you should be fine in almost all
     * cases.
     *
     * @see add_link
     * @see add_css
     * @see add_meta
     * @see add_jsfile()
     * @see add_script()
     */
    function print_elements()
    {
        if ($this->jquery_enabled)
        {
            echo $this->jquery_inits;
        }
        
        foreach ($this->js_head as $js_call)
        {
            echo $js_call;
        }
        
        if (!empty($this->prepend_script_head))
        {
            foreach ($this->prepend_script_head as $js_call)
            {
                echo $js_call;
            }
        }
    }
}

?>