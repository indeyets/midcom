<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager simple checkbox widget
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
 * - <i>int size:</i> The size of the input box. Defaulting to 40. Note that this value
 *   might be overridden by CSS.
 * - <i>boolean hideinput:</i> Set this to true if you want to hide the input in the widget,
 *   this usually means that a password HTML element will be used, instead of a regular
 *   text input widget. Defaults to false.
 *
 * @package midcom_helper_datamanager
 */
class com_rohea_account_datamanager_widget_checkbox extends midcom_helper_datamanager_widget
{
    /**
     * The initialization event handler post-processes the maxlength setting.
     *
     * @return boolean Indicating Success
     */
    protected function on_initialize()
    {
        if (   ! array_key_exists('value', $this->type)
            || is_array($this->type->value)
            || is_object($this->type->value))
        {
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} does not have a value member or it is an array or object, you cannot use the text widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            */
            return false;
        }
        return true;
    }

    public function sync_widget2type($result)
    {
        $this->type->value = $result;
    }

    /**
     * Renders the form controls (if not frozen) or read-only view (if frozen)
     * of the widget as html
     */
    public function render_html()
    {
        $output =  "<label class=\"checkbox\" for=\"{$this->namespace}_{$this->main_input_name}\"><span>{$this->field['title']}</span>\n";
        $output .= "    <input type='checkbox' id=\"{$this->namespace}_{$this->main_input_name}\" name=\"{$this->namespace}_{$this->main_input_name}\" ";
        if ($this->frozen)
        {
            $output .= ' disabled="disabled"';
        }
        
        if ($this->type->value == true)
        {
            $output .= ' checked="checked" ';
        }
        
//        $output .= ' value=' . midcom_helper_xsspreventer_helper::escape_attribute($this->type->value) . " />\n";
        $output .= " value='1' />\n";        
        $output .= "</label>\n";
        return $output;
    }
    public function validate()
    {
        return true;
    }
}

?>