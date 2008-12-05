<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: textarea.php 14808 2008-02-08 12:36:34Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 simple textarea widget
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports all types which have a simple, plain text representation accessible
 * though their <i>value</i> member.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>int maxlength:</i> The maximum length of the string allowed for this field.
 *   This includes any newlines, which account as at most two characters, depending
 *   on the OS. If you specify a 0, no maximum length is set. If you specify a -1,
 *   maximum length is inherited from the type, if applicable or unlimited otherwise.
 *   If a maximum length is set, an appropriate validation rule is created implicitly.
 *   A -1 setting is processed during startup and has no effect at a later time.
 * - <i>int width:</i> The number of columns of the textarea, this defaults to 50.
 *   Note that this value might be overridden by CSS.
 * - <i>int height:</i> The number of rows of the textearea, this defaults to 6.
 *   Note that this value might be overridden by CSS.
 * - <i>string wrap:</i> Controls the textbox wrapping, defaults to 'virtual' text is
 *   wrapped by the browser, but the automatic wraps are not sent to the server. You
 *   can set this to 'off' or 'physical'. If you set this to an empty string, the
 *   attribute is omitted.
 * - <i>boolean expand</i> If set, then the form will include a link so the user can
 *   expand the textarea.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_textarea extends midcom_helper_datamanager_widget
{
    /**
     * Maximum length of the string encapsulated by this type. 0 means no limit.
     * -1 tries to bind to the types maxlength member, if available.
     *
     * @var int
     * @access public
     */
    var $maxlength = -1;

    /**
     * Width of the textbox.
     *
     * @var int
     * @access public
     */
    var $width = 50;

    /**
     * Height of the textbox.
     *
     * @var int
     * @access public
     */
    var $height = 6;

    /**
     * Wrapping mode of the textbox.
     *
     * @var string
     * @access public
     */
    var $wrap = 'virtual';

    /**
     * Add expand link to textbox?
     * @access public
     * @var boolean
     */
    var $expand = false;
    
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return boolean Indicating Success
     */
    function _on_initialize()
    {
        if (   ! array_key_exists('value', $this->_type)
            || is_array($this->_type->value)
            || is_object($this->_type->value))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have a value member or it is an array or object, you cannot use the text widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if ($this->maxlength == -1)
        {
            if (array_key_exists('maxlength', $this->_type))
            {
                $this->maxlength = $this->_type->maxlength;
            }
        }
        if ($this->maxlength < 0)
        {
            $this->maxlength = 0;
        }
        return true;
    }

    function sync_widget2type($result)
    {
        $this->type->value = $result;
    }
    
    /**
     * Renders the form controls (if not frozen) or read-only view (if frozen)
     * of the widget as html
     */
    public function render_html()
    {
        $output =  "<label for=\"{$this->namespace}_{$this->main_input_name}\"><span>{$this->field['title']}</span>\n";
        $output .= "    <textarea class=\"textarea\" id=\"{$this->namespace}_{$this->main_input_name}\" name=\"{$this->namespace}_{$this->main_input_name}\" size=\"{$this->size}\"";
        if ($this->maxlenght > 0)
        {
            $output .= " maxlenght=\"{$maxlenght}\"";
        }
        if ($this->frozen)
        {
            $output .= ' disabled="disabled"';
        }
        $output .= '>' . midcom_helper_xsspreventer_helper::escape_element('textarea', $this->type->value) . "</textarea>\n";
        $output .= "</label>\n";
        return $output;
    }
}

?>