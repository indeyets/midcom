<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a WYSIWYG HTML widget that uses the powerful TinyMCE editor
 * 
 * This widget should only be used with the text type configured to an unlimited-length
 * field.
 * 
 * <b>Configuration parameters:</b>
 * 
 * <b>widget_tinymcearea_customconfig:</b> This widget has smart defaults that are 
 * inspired by popular text processors. You can adjust its look and functionality here
 * (e.g. to enhance or strip down functions). 
 * See http://tinymce.moxiecode.com/docs/using.htm how to do this.
 * <b>Important:</b> leave the "mode" setting ("specific_textareas") alone or this thing
 * will begin to do very strange things
 * 
 * <b>Sample configuration</b>
 * 
 * <pre>
 * "html" => array (
 *     "description" => "WYSIWYG HTML",
 *     "datatype" => "text",
 *     "location" => "attachment",
 *     "widget" => "tinymce",
 *     "widget_tinymce_customconfig" => <<<EOF
 *
 * theme: 'advanced',
 * theme_advanced_toolbar_location: 'top',
 * theme_advanced_toolbar_align: 'left',
 * theme_advanced_path_location: 'bottom',
 * theme_advanced_buttons1: 'cut,copy,paste,spearator,undo,redo,separator,  link,unlink,image,table,charmap,separator, forecolor,backcolor,separator,  removeformat,code,cleanup,separator,  help',
 * theme_advanced_buttons2: 'formatselect,styleselect,separator,  bold,italic,underline,strikethrough,separator,  justifyleft,justifycenter,justifyright,justifyfull,separator,  bullist,numlist,outdent,indent',	
 * theme_advanced_buttons3: '',
 * 
 * EOF
 * ),
 * </pre>
 * 
 * <b>Note</b>
 *
 * As an advantage over HTMLarea we are now able to use more than one WYSIWYG editor
 * field per page. But we still can use only one configuration set. So all TinyMCE
 * editors in one page will look the same, only the first configuration is taken into
 * account.
 *
 * 
 * TinyMCE
 * Copyright (c) 2004, Moxiecode Systems AB, All rights reserved
 * 
 * @todo Documentation and links how to configure TinyMCE
 * @abstract WYSIWYG HTML Widget (TinyMCE)
 * @package midcom.helper.datamanager
 */

class midcom_helper_datamanager_widget_tinymce extends midcom_helper_datamanager_widget {
       
    /**
     * Custom JScript configuration block
     * 
     * @var string
     * @access private
     */
    var $_customconfig;
    
    
    /**
     * The constructor will add the corresponding Javascript code only if we are 
     * running AIS. On-Site usage is not yet possible.
     */
    function _constructor (&$datamanager, $field, $defaultvalue) {
        $this->_datamanager =& $datamanager;
        $this->_field = $field;
        $this->_fieldname = $this->_datamanager->form_prefix . "field_" . $field["name"];
        $this->_value = $defaultvalue;
        
        if (!array_key_exists("widget_tinymce_customconfig", $this->_field))
        {
            $this->_field["widget_tinymce_customconfig"] = "";
        }
        $this->_customconfig = $this->_field["widget_tinymce_customconfig"];
        
        $this->_read_formdata();
        
        // Ensure that AIS is running
        if (array_key_exists("view_contentmgr", $GLOBALS)) 
        {
            $midgard = $GLOBALS["midcom"]->get_midgard();
            
            // Language negotiation ($i18n->_current_language and $i18n->_fallback_language)
            $i18n =& $GLOBALS["midcom"]->get_service("i18n");
            
            /* TODO: do somethign with $i18n->get_current_language() */
            
            /*
             * This enables TinyMCE textareas on this page, although we can only use
             * one configuration for all textareas. 
             * TODO: Documentation on this
             * TODO: Only add this code once!
             */
            if (! array_key_exists("midcom_helper_datamanager_tiny_mce__js_added", $GLOBALS)) {
                $GLOBALS["midcom_helper_datamanager_tiny_mce__js_added"] = true;
                $urlprefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager/tiny_mce/';
                $GLOBALS["midcom"]->add_jsfile($urlprefix. 'tiny_mce.js', true);
                $GLOBALS["midcom"]->add_jscript("tinyMCE.init({\nmode: 'specific_textareas',\n\n" . $this->_get_config_js(). " });\n");
            }

        }
    }

    function draw_view () 
    {
        echo "<div class='form_htmleditor'>{$this->_value}</div>\n";
    }
    
    function draw_widget () 
    {
        echo "<textarea class='htmleditor' mce_editable='true' id='{$this->_fieldname}' name='{$this->_fieldname}'>{$this->_value}</textarea>\n";
    }
    
    
    
    /**
     * Get the TinyMCE configuration
     * This will be included in the "tinyMCE.init(...)" call
     *
     * - /$GLOBALS['midcom_config']['midcom_sgconfig_basedir']/midcom.helper.datamanager.widget_tinymce/config
     * - file:/midcom/helper/datamanager/config/midcom.helper.datamanger.widget_tinymce
     * - local fallback default configuration
     *
     * Look at the source of this function how your template should look like.
     * 
     * TODO: this does not work yet!
     *
     * @return string JS configuration code
     */
     
    function _get_config_js ()
    {
        /*
        if (mgd_snippet_exists("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/midcom.helper.datamanager.widget_tinymce/config"))
        {
            $snippet = mgd_get_snippet_by_path("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/midcom.helper.datamanager.widget_tinymce/config");
            if (! $snippet)
            {
                return false;
            }
            eval ('?>' . mgd_preparse($snippet->code));
        }
        else if ( file_exists(MIDCOM_ROOT . '/midcom/helper/datamanager/config/midcom.helper.datamanger.widget_html') )
        {
            $code = file_get_contents(MIDCOM_ROOT . '/midcom/helper/datamanager/config/midcom.helper.datamanger.widget_html');
            if ($code)
            {
                eval ('?>' . $code );
            }
        }
        else
        {
        */
            $result = <<<EOF

// TinyMCE configuration for field '{$this->_fieldname}'

theme: 'advanced',
theme_advanced_toolbar_location: 'top',
theme_advanced_toolbar_align: 'left',
theme_advanced_path_location: 'bottom',
	
theme_advanced_buttons1: 'cut,copy,paste,spearator,undo,redo,separator,  link,unlink,image,table,charmap,separator, forecolor,backcolor,separator,  removeformat,code,cleanup,separator,  help',
	
theme_advanced_buttons2: 'formatselect,styleselect,separator,  bold,italic,underline,strikethrough,separator,  justifyleft,justifycenter,justifyright,justifyfull,separator,  bullist,numlist,outdent,indent',	
	
theme_advanced_buttons3: ''
	
{$this->_customconfig}


EOF;
        // }
        return $result;
    }
	
}


?>
