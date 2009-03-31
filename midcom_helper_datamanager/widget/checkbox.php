<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager checkbox widget
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_widget_checkbox extends midcom_helper_datamanager_widget
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
        $this->type->value = (boolean) $result;
    }

    /**
     * Renders the form controls (if not frozen) or read-only view (if frozen)
     * of the widget as html
     */
    public function render_html()
    {
        $output =  "<label class=\"text\" for=\"{$this->namespace}_{$this->main_input_name}\"><span>{$this->field['title']}</span>\n";
        $output .= "    input id=\"{$this->namespace}_{$this->main_input_name}\" type=\"checkbox\" name=\"{$this->namespace}_{$this->main_input_name}\"";

        if ($this->type->value)
        {
            $output .= " checked=\"checked\"";
        }
        $output .= " />\n";
        $output .= "</label>\n";
        return $output;
    }
}

?>