<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: tinymce.php 12007 2007-09-04 13:47:39Z w_i $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager FCKeditor driven textarea widget
 *
 * This widget implements currently very simple support for fckeditor. 
 * It simply replaces textarea to an editor. 
 * 
 * If browser does not support javascript or fckeditor this widget deprecates
 * to a siple textarea
 * 
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_fckeditor extends midcom_helper_datamanager_widget_textarea
{
    /**
     * Width of the editor.
     *
     * @var int
     * @access public
     */
    public $width = null; // FIXME: As soon as configuration works, switch to null
    
    /**
     * Height of the editor.
     *
     * @var int
     * @access public
     */
    public $height = null; // FIXME: As soon as configuration works, switch to null
    
    /**
     * The FCK configuration snippet to use. Argument must be applicable to use with
     * midcom_get_snippet_content.
     *
     * @var string
     */
    public $fck_config_snippet = null;
    
    public $toolbarset = 'Midgard';

    /**
     * Configuration array
     * @var array
     * @access public
     */
    public $configuration = array(
        
    );
    
    public $fckeditor_path = null;
    
    /**
     * This is called during intialization.
     * @return boolean always true
     */
    function on_initialize()
    {
        // FIXME: Lots of hardcodings
        $this->width = '80%';
        $this->height = 460;
        $this->configuration['basepath'] = MIDCOM_STATIC_URL . '/midcom_helper_datamanager/fckeditor/';
        
        // FIXME: As soon as language support is up, remove hardcoding
        $language = 'en';
        
        //$language = $_MIDCOM->i18n->get_current_language();
        // fix to use the correct langcode for norwegian.
        if ($language == 'no')
        {
            $language = 'nb';
        }
        
        return true;
    }
    
  public function render_html()
    {
        $output = "<script src=\"{$this->configuration['basepath']}fckeditor.js\"></script>";
        
        if ($this->frozen)
        {
            // Include the utility functions for disabling FCKeditor
            $output .= "<script src=\"{$this->configuration['basepath']}toggleFCKeditor.js\"></script>";        
        }
        
        $output .=  "<label for=\"{$this->namespace}_{$this->main_input_name}\"><span>{$this->field['title']}</span>\n";
        $output .= "    <textarea class=\"fckeditor\" style=\"width: {$this->width}; height: {$this->height}px;\" id=\"{$this->namespace}_{$this->main_input_name}\" name=\"{$this->namespace}_{$this->main_input_name}\"";
        if ($this->frozen)
        {
            $output .= ' disabled="disabled"';
        }
        // TODO: Escape to be safe
        $output .= ">{$this->type->value}";
        $output .= "</textarea>";
        $output .= "<script>\n";
        $output .= "   jQuery(document).ready(function(){\n";
        $output .= "       var oFCKeditor{$this->namespace}_{$this->main_input_name} = new FCKeditor(\"{$this->namespace}_{$this->main_input_name}\");\n";
        
        // Set configuration for the editor
        $output .= "       oFCKeditor{$this->namespace}_{$this->main_input_name}.BasePath = \"".$this->configuration['basepath']."\";\n";
        $output .= "       oFCKeditor{$this->namespace}_{$this->main_input_name}.Height = {$this->height};\n";
        $output .= "       oFCKeditor{$this->namespace}_{$this->main_input_name}.Width = \"{$this->width}\";\n";
        $output .= "       oFCKeditor{$this->namespace}_{$this->main_input_name}.ToolbarSet = \"{$this->toolbarset}\";\n";
        $output .= "       oFCKeditor{$this->namespace}_{$this->main_input_name}.Config.LinkBrowserURL = oFCKeditor{$this->namespace}_{$this->main_input_name}.BasePath + 'editor/filemanager/browser/default/browser.html?Connector={$_MIDCOM->context->prefix}mgd:fckeditor/';\n";
        
        // Start the editor
        $output .= "       oFCKeditor{$this->namespace}_{$this->main_input_name}.ReplaceTextarea();\n";
        $output .= "   });\n";
                
        if ($this->frozen)
        {
            $output .= "    function FCKeditor_OnComplete(editorInstance)\n";
            $output .= "    {\n";
            $output .= "        toggleFCKeditor(editorInstance);\n";
            $output .= "    }\n";
        }
        

        $output .= "</script>\n";
        $output .= "</label>\n";
        return $output;
    }
    

}

?>