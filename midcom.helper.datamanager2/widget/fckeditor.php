<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: tinymce.php 12007 2007-09-04 13:47:39Z w_i $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** Base class */
require_once('textarea.php');

/**
 * Datamanager 2 FCKeditor driven textarea widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports all types which have a simple, plain text representation accessible
 * though their <i>value</i> member. The class will put HTML into those base types.
 *
 * This type extends the regular textarea type, as this is the fallback for all cases where
 * FCKeditor doesn't run (on Opera f.x.).
 *
 * <b>Available configuration options:</b>
 *
 * - All of the textarea baseclass. The height default has been changed to 25, the width default
 *   to 80.
 * - <i>string fck_config_snippet:</i> Indicates the name of the snippet which holds the base
 *   configuration. This is looked up in the DM2 directory in SG-Config. This defaults to
 *   '$GLOBALS['midcom_config']['midcom_sgconfig_basedir']/midcom.helper.datamanager2/fckeditor'.
 *   Any valid option for midcom_get_snippet_content() is allowed at this point.
 * - <i>string local_config:</i> Local configuration options which should overwrite the defaults
 *   from the config snippet. This defaults to an empty string.
 * - <i>theme</i> Valid values: default, office and silver. The systemwide default
 *   for this value can be set in the fck_default_theme DM2 configuration option.
 * - <i>boolean use_midcom_imagedialog</i> Use the midcom version of the image dialog. Defaults to yes.
 * - <i>toolbar_set</i> Valid predefined values: Default and Basic. The systemwide default
 *   for this value can be set in the fck_default_toolbar_set DM2 configuration option.
 *
 * <b>Notes about FCKeditor configuration:</b>
 *
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_fckeditor extends midcom_helper_datamanager2_widget_textarea
{
    /**
     * The FCK configuration snippet to use. Argument must be applicable to use with
     * midcom_get_snippet_content.
     *
     * @var string
     */
    var $fck_config_snippet = null;

    /**
     * Local configuration to be added to the config snippet.
     *
     * @var string
     */
    var $local_config = '';

    /**
     * Which theme will the editor use to render itself
     *
     * @var string : valid values: default, office and silver
     */
    var $theme = null;
    
    /**
     * Define some simple configuration without having to create a configs.
     *
     * @var string : valid values: Default or Basic
     */
    var $toolbar_set = "Default";
    
    /**
     * Should the midcom image dialog be used instead of the normal?
     * @var boolean defaults to true.
     */
    var $use_midcom_imagedialog = true;
    
    var $editor;
    
    var $configuration = array();
    
    var $fckeditor_path = null;
    
    /**
     * Adds the external HTML dependencies, both JS and CSS. A static flag prevents
     * multiple insertions of these dependencies.
     *
     * @access private
     */
    function _add_external_html_elements()
    {
        static $executed = false;

        if ($executed)
        {
            return;
        }

        $executed = true;
    }

    /**
     * This helper will construct the FCKeditor initscript based on the specified configuration.
     */
    function _add_initscript()
    {

        $this->configuration['fckconfig'] = midcom_get_snippet_content_graceful($this->fck_config_snippet);
        
        if (! $this->configuration['fckconfig'])
        {
            $this->_get_configuration();
        }
		
        $language = $_MIDCOM->i18n->get_current_language();
        // fix to use the correct langcode for norwegian.
        if ($language == 'no')
        {
             $language = 'nb';
        }
    }
    
    /**
     * Returns the configuration theme based on the local_config_toolbar_set.
     * @return string
     * @access private
     */
    function _get_configuration()
    {   
        $valid_sets = array(
            'Default'   => true,
            'Basic' => true,
        );
        
        if (array_key_exists($this->toolbar_set, $valid_sets))
        {
            $this->configuration['toolbar_set'] = $this->toolbar_set;
        }
        else
        {
            $this->configuration['toolbar_set'] = 'Default';
        }
        
        if ($this->fck_config_snippet != '') 
        {
            $this->configuration['toolbar_set'] = 'Custom';
            $this->configuration['toolbar_set_content'] = $this->fck_config_snippet;
        }

        $this->height = 450;            
        $this->width = 800;
        
        $this->configuration['width'] = $this->width;
        $this->configuration['height'] = $this->height;
        
        $this->configuration['fckconfig'] = array();
    }

    /**
     * Returns the Default configuraiton:
     *
     * @return string The default configuration
     * @access private
     */
    function _get_default_configuration()
    {
        $this->configuration['toolbar_set'] = 'Default';
    }

    /**
     * Returns the "basic" configuration
     */
    function _get_basic_configuration ()
    {
        $this->configuration['toolbar_set'] = 'Basic';
    }
    
    /**
     * This changes the defaults for the textarea size to something more usable for a
     * WYSIWYG editor.
     *
     * The systemwide defaults for the theme and the fck config snippet will be loaded
     * from the config file at this point.
     *
     * @todo make overrideable.
     * @access private
     */
    function _on_configuring()
    {
        parent::_on_configuring();
                
        $this->theme = $this->_config->get('fck_default_theme');
        $this->toolbar_set = $this->_config->get('fck_default_toolbar_set');
        $this->fck_config_snippet = $this->_config->get('fck_default_config_snippet');
        
        $this->fckeditor_path = MIDCOM_ROOT . '/midcom/helper/datamanager2/static/fckeditor/';
        $this->configuration['basepath'] = MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/fckeditor/';
    }

    /**
     * This is called during intialization.
     * @return boolean always true
     */
    function _on_initialize ()
    {
        if (!$this->toolbar_set)
        {
            $this->toolbar_set = 'Default';
        }
        
        if ($this->_initialize_dependencies)
        {
            $this->_add_external_html_elements();
            $this->_add_initscript();
        }

        return true;
    }

    /**
     *
     */
    function add_elements_to_form()
    {
        $this->_form->registerElementType(
            'fckeditor',
            $this->fckeditor_path . 'HTML_Quickform_fckeditor.php',
            'HTML_Quickform_fckeditor'
        );
        
        if (!$this->_initialize_dependencies)
        {
            $this->_add_external_html_elements();
            $this->_add_initscript();
        }
        
        $attributes = Array
        (
            'class' => 'fckeditor',
            'id'    => "{$this->_namespace}{$this->name}",
        );

        $this->editor = $this->_form->addElement(
            'fckeditor',
            $this->name,
            $this->_translate($this->_field['title']),
            $attributes
        );

        $this->editor->setFCKProps(
            $this->configuration['basepath'],
            $this->configuration['toolbar_set'],
            $this->configuration['width'],
            $this->configuration['height'],
            $this->configuration['fckconfig']
        );
        
        $this->_form->applyFilter($this->name, 'trim');

        if ($this->maxlength > 0)
        {
            $errormsg = sprintf($this->_l10n->get('type text: value is longer then %d characters'), $this->maxlength);
            $this->_form->addRule($this->name, $errormsg, 'maxlength', $this->maxlength);
        }

    }

}

?>
