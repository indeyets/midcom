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
     * Whether to allow loading of the widget_html outside AIS
     *
     * @var bool
     * @access private
     */
    var $_enable_outside_ais;

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

        if (!array_key_exists("widget_tinymce_enable_outside_ais", $field))
        {
            $field["widget_tinymce_enable_outside_ais"] = false;
        }
        $this->_enable_outside_ais = $field["widget_tinymce_enable_outside_ais"];

        $this->_read_formdata();

        // Ensure that AIS is running
        if (   $this->_enable_outside_ais
            || array_key_exists("view_contentmgr", $GLOBALS))
        {
            $midgard = $_MIDCOM->get_midgard();

            // Language negotiation ($i18n->_current_language and $i18n->_fallback_language)
            $i18n =& $_MIDCOM->get_service("i18n");

            /* TODO: do somethign with $i18n->get_current_language() */

            $this->_add_external_html_elements();
            $this->_add_initscript();
        }
    }

    /**
     * Adds the external HTML dependencies, both JS and CSS. A static flag prevents
     * multiple insertions of these dependencies.
     *
     * Copied from DM2 widget tinymce
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

        $prefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager2/tinymce';
        $_MIDCOM->add_jsfile("{$prefix}/tiny_mce.js", true);
    }

    /**
     * This helper will construct the TinyMCE initscript based on the specified configuration.
     * Copied from DM2 widget tinymce
     */
    function _add_initscript($mode = 'exact')
    {
        $language = $_MIDCOM->i18n->get_current_language();
        // fix to use the correct langcode for norwegian.
        if ($language == 'no')
        {
             $language = 'nb';
        }

        // Compute the final script:
        $script = <<<EOT
tinyMCE.init({
mode : "{$mode}",
convert_urls : false,
relative_urls : false,
remove_script_host : true,
elements : "{$this->_fieldname}",
language : "{$language}",
docs_language : "{$language}",
theme_advanced_toolbar_align : "left",
theme_advanced_toolbar_location : "top",
paste_create_linebreaks : false
});
EOT;

        $_MIDCOM->add_jscript($script);
    }

    function draw_view ()
    {
        echo "<div class='form_htmleditor'>{$this->_value}</div>\n";
    }

    function draw_widget ()
    {
        echo "<textarea class='htmleditor' id='{$this->_fieldname}' name='{$this->_fieldname}'>{$this->_value}</textarea>\n";
    }

}


?>
