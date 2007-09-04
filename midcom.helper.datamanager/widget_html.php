<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a WYSIWYG HTML widget, built on HTMLArea.
 *
 * This widget should only be used with the text type configured to an unlimited-length
 * field.
 *
 * <b>Configuration parameters:</b>
 *
 * <b>widget_html_stripmstags:</b> This option will activate a couple of regular
 * expressions that filter out a lot of the crap that M$ products like Word insert
 * into the stuff they copy into the clipborard. If you leave this deactivated, any
 * content copy/pasted from Word will most probably override your site style in
 * almost any thinkable way. As this modifies content, this is disabled by default.
 *
 * <b>widget_html_css_url:</b> The CSS file in this URL is used by the HTMLArea to
 * render the content within the HTMLArea to have a better preview what the content
 * will look like on-site. Note, that with strongly CSS formatted content, this is
 * not always a good idea.
 *
 * <b>widget_html_enable_outside_ais:</b> Set this to true to allow the JS code to
 * be added even if we are not within AIS. This is disabled by default to keep on-site
 * performance up.
 *
 * <b>widget_htmlarea_customconfig:</b> The most powerful and complex part of the
 * HTMLArea configuration. It is inserted into the HTMLArea startup code and allows
 * you to adapt the widget to your personal surroundings. The example below gives
 * you an insight what can be possible, albeit it is far from complete. Refer to the
 * HTMLArea documentation for details about this.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "html" => array (
 *     "description" => "WYSIWYG HTML",
 *     "datatype" => "text",
 *     "location" => "attachment",
 *     "widget" => "html",
 *     "widget_html_stripmstags" => true,
 *     "widget_html_css_url" => "http://my.host.com/css/htmlarea_iframe.css",
 *     "widget_html_customconfig" => <<<EOF
 * config.formatblock = {
 *     "H2": "h2",
 *     "H3": "h3",
 *     "H4": "h4",
 *     "H5": "h5",
 *     "Paragraph": "p",
 *     "Preformatted": "pre",
 *     "Block Quote": "blockquote",
 *     "Address": "address"
 * };
 * config.toolbar = [
 *         [ "formatblock", "space",
 *           "bold", "italic", "underline", "strikethrough", "subscript", "superscript", "separator",
 *           "copy", "cut", "paste", "space", "undo", "redo" ],
 *         [ "insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
 *           "inserthorizontalrule", "createlink", "insertimage", "inserttable", "htmlmode", "separator",
 *           "popupeditor", "separator", "showhelp", "about" ]
 * ];
 * EOF
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The select widget will be both of select.list and select.multiple.
 *
 * <b>HTMLArea licence information</b>
 *
 * Portions (c) dynarch.com, 2003-2004
 *
 * A free WYSIWYG editor replacement for <textarea> fields.
 * For full source code and docs, visit http://www.interactivetools.com/
 * or http://www.htmlarea.com/
 *
 * Version 3.0 developed by Mihai Bazon. http://dynarch.com/mishoo
 *
 * @todo Add a section about configuration of HTMLArea or at least add a corresponding docuemnt to the distribution.
 * @package midcom.helper.datamanager
 */

class midcom_helper_datamanager_widget_html extends midcom_helper_datamanager_widget {

    /**
     * The URL of the CSS file to use inside of the HTMLArea.
     *
     * @var string
     * @access private
     */
    var $_css_url;

    /**
     * Custom JScript configuration block
     *
     * @var string
     * @access private
     */
    var $_customconfig;

    /**
     * Flag to enable the M$ HTML crap filter(tm).
     *
     * @var bool
     * @access private
     */
    var $_stripmstags;

    /**
     * Flag to enable HTML Tidy cleaning
     * Requires the PECL Tidy extension
     *
     * @var bool
     * @access private
     */
    var $_tidy;

    /**
     * Whether to allow loading of the widget_html outside AIS
     *
     * @var bool
     * @access private
     */
    var $_enable_outside_ais;

    /**
     * The constructor will add the corresponding Javascript code only if we are
     * running AIS or enable outside AIS
     */
    function _constructor (&$datamanager, $field, $defaultvalue) {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('constructor called');
        $this->_datamanager =& $datamanager;
        $this->_field = $field;
        $this->_fieldname = $this->_datamanager->form_prefix . "field_" . $field["name"];
        $this->_value = $defaultvalue;

        if (!array_key_exists("widget_html_css_url", $this->_field))
        {
            $this->_field["widget_html_css_url"] = null;
        }
        if (!array_key_exists("widget_html_customconfig", $this->_field))
        {
            $this->_field["widget_html_customconfig"] = "";
        }
        if (!array_key_exists("widget_html_stripmstags", $this->_field))
        {
            $this->_field["widget_html_stripmstags"] = false;
        }
        if (!array_key_exists("widget_html_enable_outside_ais", $field))
        {
            $field["widget_html_enable_outside_ais"] = false;
        }
        if (!array_key_exists("widget_html_enable_tidy", $field))
        {
            $field["widget_html_enable_tidy"] = true;
        }

        $this->_css_url = $this->_field["widget_html_css_url"];
        $this->_customconfig = $this->_field["widget_html_customconfig"];
        $this->_stripmstags = $this->_field["widget_html_stripmstags"];
        $this->_enable_outside_ais = $field["widget_html_enable_outside_ais"];
        $this->_tidy = $field["widget_html_enable_tidy"];

        $this->_read_formdata();

        // Ensure that AIS is running
        if (   $this->_enable_outside_ais
            || $this->_datamanager->_show_js )
        {

            // Language negotiation ($i18n->_current_language and $i18n->_fallback_language)
            $i18n =& $_MIDCOM->get_service("i18n");
            $langprefix = MIDCOM_STATIC_ROOT . '/midcom.helper.datamanager/htmlarea/lang/';

            if (file_exists($langprefix . $i18n->get_current_language() . '.js'))
            {
                $htmlarea_lang = $i18n->get_current_language();
            }
            else if (file_exists($langprefix . $i18n->get_fallback_language() . '.js'))
            {
                $htmlarea_lang = $i18n->get_fallback_language();
            }
            else
            {
                $htmlarea_lang = 'en';
            }
            $urlprefix = MIDCOM_STATIC_URL . '/midcom.helper.datamanager/htmlarea/';

            debug_add('adding language code');
            // Disable HTMLArea language selection
            // The translations are incomplete, and missing strings break at least
            // the insert link popup due to jscript errors.
            $_MIDCOM->add_jscript("
_editor_url = '{$urlprefix}';
_editor_lang = '{$htmlarea_lang}';\n" .
        "var xinha_editors = Array();\n");
            debug_add('adding main JS file');
            $_MIDCOM->add_jsfile($urlprefix. 'htmlarea.js');

            // Add here all Plugins you wish to use with your HTMLArea.
            // The Context Menu Plugin is disabled, as it makes trouble with
            // default Mozilla (scripts may not copy/paste by default anymore).
            // TODO: Make this configurable.
            debug_add('adding editor plugins');
            $_MIDCOM->add_jscript("

HTMLArea.loadPlugin('TableOperations');
HTMLArea.loadPlugin('ContextMenu');\n");

            $_MIDCOM->add_jscript($this->_get_config_js());
            debug_add('adding onload');
            $_MIDCOM->add_jsonload($this->_fieldname . "_init()");
            // fix so that HTMLArea doesn't break on slow lines.
            // Commented out due to caching issues.
            //$this->_preloadImages();
        }
        debug_pop();
    }

    function draw_view ()
    {
        echo "<div class='form_htmleditor'>{$this->_value}</div>\n";
    }

    function draw_widget ()
    {
        echo "<textarea class='htmleditor' id='{$this->_fieldname}' name='{$this->_fieldname}'>{$this->_value}</textarea>\n";
    }

    /**
     * Overridden to allow for the post-processing of the HTMLArea generated content.
     *
     * Two things happen:
     *
     * - First, a newline will be added after all closing td, tr, table,
     * p and div tags for better readability of the source
     * - Second, if enabled, the M$ HTML crap filter (tm) is invoked.
     *
     * <b>The M$ HTML crap filter(tm):</b>
     *
     * It will remove about this, and is incomplete yet:
     *
     * - mso, navigointipaa1 and windowtext tags within p, b, div and span tags
     * - all font tags
     * - all msnormal class tags
     *
     * This might be replaced by HTMLTidy on the long run.
     */
    function _read_formdata ()
    {
        if (array_key_exists($this->_fieldname, $_REQUEST))
        {
            $this->_value = $_REQUEST[$this->_fieldname];

            // bring some linbreaks into tables and after p/div to make it more readable.
            $this->_value = preg_replace("'</((td|tr|table|p|div)[^>]*)><'im", "</\\1>\n<", $this->_value);

            // Run HTML Tidy if available
            // This requires libtidy and the PECL Tidy extension to be installed
            if (   $this->_tidy
                && function_exists('tidy_repair_string')
                && function_exists('tidy_setopt'))
            {
                // Default options for Tidy
                // See http://public.planetmirror.com/pub/tidy for docs
                // TODO: Enable custom options in DM field config
                tidy_setopt('show-body-only', TRUE);
                tidy_setopt('output-xhtml', TRUE);
                tidy_setopt('enclose-block-text', TRUE);
                tidy_setopt('drop-empty-paras', TRUE);

                // Don't make a mess out of UTF-8 characters
                // TODO: Check encoding from MidCOM i18n service
                tidy_setopt('char-encoding', 'utf8');
                tidy_setopt('input-encoding', 'utf8');
                tidy_setopt('output-encoding', 'utf8');
                tidy_set_encoding('utf8');

                // Proper indentation will make RCS diffs more useful
                tidy_setopt('indent', TRUE);
                tidy_setopt('indent-spaces', 4);
                tidy_setopt('break-before-br', TRUE);

                // TODO: Make these depend on stripmstags?
                tidy_setopt('drop-font-tags', TRUE);
                tidy_setopt('drop-proprietary-attributes', TRUE);
                tidy_setopt('bare', TRUE);

                $this->_value = tidy_repair_string($this->_value);
            }

            // If configured accordingly, strip all nasty word spans/p's/divs to something usable
            if ($this->_stripmstags)
            {
                $search = Array (
                    "'<([PB]|DIV|SPAN)[^>]*(mso|navigointipaa1|windowtext)+[^>]*>'im",
                    "'<FONT[^>]*>'i",
                    "'</FONT>'i",
                    '$class="msonormal" $i'
                );
                $replace = Array (
                    "<\\1>",
                    "",
                    "",
                    ""
                );
                $this->_value = preg_replace ($search, $replace, $this->_value);
            }
        }
    }

    /**
     * Prepares the HTMLArea configuration scriptlet. It will search the templates in this order:
     *
     * - /$GLOBALS['midcom_config']['midcom_sgconfig_basedir']/midcom.helper.datamanager.widget_html/config
     * - file:/midcom/helper/datamanager/config/midcom.helper.datamanager.widget_html
     * - local fallback default configuration
     *
     * Look at the source of this function how your template should look like.
     *
     * @return string JS configuration code for the current HTMLArea
     */
    function _get_config_js ()
    {
        // Check for sitegroup-config
        if (mgd_snippet_exists("/{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/midcom.helper.datamanager.widget_html/config"))
        {
            $snippet = mgd_get_snippet_by_path("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/midcom.helper.datamanager.widget_html/config");
            if (! $snippet)
            {
                return false;
            }
            eval ('?>' . mgd_preparse($snippet->code));
        }
        else if ( file_exists(MIDCOM_ROOT . '/midcom/helper/datamanager/config/midcom.helper.datamanager.widget_html') )
        {
            $code = file_get_contents(MIDCOM_ROOT . '/midcom/helper/datamanager/config/midcom.helper.datamanager.widget_html');
            if ($code)
            {
                eval ('?>' . $code );
            }
        }
        else
        {
            $file = MIDCOM_ROOT . 'lib/midcom/helper/datamanager/config/midcom.helper.datamanager.widget_html';
            $result = <<<EOF
// FIELD {$this->_fieldname} HTMLAREA init script START

var {$this->_fieldname} = null;
function {$this->_fieldname}_init() {

    if (!document.getElementById("{$this->_fieldname}")) {
        return;
    }
    config = new HTMLArea.Config();
    config.bodyStyle = "background-color: #fff;";

    config.formatblock = {
        "Normal": "p",
        "Heading 2": "h2",
        "Heading 3": "h3",
        "Heading 4": "h4",
        "Formatted": "pre",
        "Address": "address",
        "Quote": "blockquote"
    };

    config.fontsize = {
        "1 (small)":  "1",
        "2 (footnote)": "2",
        "3 (regular)": "3",
        "4 (large)": "4",
        "5 (big)": "5"
    };

    // "fontname" and "fontsize" disabled by default, add if required
    config.toolbar = [
        ["popupeditor"],
    ["separator","formatblock","fontname","fontsize","bold","italic","underline","strikethrough"],
    ["separator","forecolor","hilitecolor","textindicator"],
    ["separator","subscript","superscript"],
    ["linebreak","separator","justifyleft","justifycenter","justifyright","justifyfull"],
    ["separator","insertorderedlist","insertunorderedlist","outdent","indent"],
    ["separator","inserthorizontalrule","createlink","insertimage","inserttable"],
    ["separator","undo","redo","selectall","print"], (HTMLArea.is_gecko ? [] : ["cut","copy","paste","overwrite","saveas"]),
    ["separator","killword","clearfonts","removeformat","toggleborders","splitblock","lefttoright", "righttoleft"],
    ["separator","htmlmode","showhelp","about"]

    ];
    /*
     * [ "formatblock", "space",
          "bold", "italic", "separator",
          "strikethrough", "subscript", "superscript", "separator",
          "copy", "cut", "paste", "space", "undo", "redo" ],
        [ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
          "insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
          "inserthorizontalrule", "createlink", "insertimage", "inserttable", "htmlmode", "separator",
          "popupeditor", "separator", "showhelp", "about" ]
     * */

    {$this->_customconfig}
    //{$this->_fieldname} = new HTMLArea("{$this->_fieldname}",config);
   // {$this->_fieldname}.registerPlugin("TableOperations");
   // {$this->_fieldname}.registerPlugin("ContextMenu");
   // {$this->_fieldname}.generate();

    xinha_editors[xinha_editors.length] = new HTMLArea("{$this->_fieldname}",config);
    xinha_editors[xinha_editors.length-1].registerPlugin("TableOperations");
    xinha_editors[xinha_editors.length-1].registerPlugin("ContextMenu");
    xinha_editors[xinha_editors.length-1].generate();

};
// FIELD {$this->_fieldname} HTMLAREA init script END
EOF;
        }

        return $result;
    }

    /**
     * Preload images used by htmlarea.
     *
     * This makes HTMLArea more stable in IE.
     *
     * @access private
     */
    function _preloadImages() {
        $images = array (
                'ed_about.gif',
                'ed_align_center.gif',
                'ed_align_justify.gif',
                'ed_align_left.gif',
                'ed_align_right.gif',
                'ed_blank.gif',
                'ed_charmap.gif',
                'ed_color_bg.gif',
                'ed_color_fg.gif',
                'ed_copy.gif',
                'ed_custom.gif',
                'ed_cut.gif',
                'ed_delete.gif',
                'ed_format_bold.gif',
                'ed_format_italic.gif',
                'ed_format_strike.gif',
                'ed_format_sub.gif',
                'ed_format_sup.gif',
                'ed_format_underline.gif',
                'ed_help.gif',
                'ed_hr.gif',
                'ed_html.gif',
                'ed_image.gif',
                'ed_indent_less.gif',
                'ed_indent_more.gif',
                'ed_left_to_right.gif',
                'ed_link.gif',
                'ed_list_bullet.gif',
                'ed_list_num.gif',
                'ed_paste.gif',
                'ed_redo.gif',
                'ed_right_to_left.gif',
                'ed_save.gif',
                'ed_show_border.gif',
                'ed_splitcel.gif',
                'ed_undo.gif',
                    );
        foreach ($images as $image) {
          $_MIDCOM->add_object_head('',array('data' =>  MIDCOM_STATIC_ROOT . '/midcom.helper.datamanager/htmlarea/images/' . $image));
          }
    }

}


?>
