<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: select.php 14808 2008-02-08 12:36:34Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager simple select widget.
 *
 * It can only be bound to a select type (or subclass thereof), and inherits the confguration
 * from there as far as possible.
 *
 * <b>Available configuration options:</b>
 *
 * - <i>int height:</i> The height of the select box, applies only for multiselect enabled
 *   boxes, the value is ignored in all other cases. Defaults to 6.
 * - <i>string othertext:</i> The text that is used to separate the main from the
 *   other form element. They are usually displayed in the same line. The value is passed
 *   through the standard schema localization chain.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_widget_select extends midcom_helper_datamanager_widget
{
    /**
     * The height of the multi-select box, ignored if no multiple selection is allowed.
     *
     * @var int
     */
    public $height = 6;

    /**
     * l10n string id or direct text to use to separate the others input field from
     * the main select. Applies only for types which have allow_other set.
     *
     * @var string
     */
    public $othertext = 'widget select: other value';


    var $main_input_name = 'selection';
    /**
     * The initialization event handler verifies the correct type.
     *
     * @return boolean Indicating Success
     */
    function _on_initialize()
    {
        if (! $this->type instanceof midcom_helper_datamanager_type_select)
        {
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not a select type or subclass thereof, you cannot use the select widget with it.",
                MIDCOM_LOG_WARN);
            debug_pop();
            */
            return false;
        }
        return true;
    }

    /**
     * Renders the form controls (if not frozen) or read-only view (if frozen)
     * of the widget as html
     */
    public function render_html()
    {
        $output =  "<label for=\"{$this->namespace}_{$this->main_input_name}\"><span>{$this->field['title']}</span>\n";
        $output .= "    <select id=\"{$this->namespace}_{$this->main_input_name}\" name=\"{$this->namespace}_{$this->main_input_name}\" size=\"{$this->height}\"";
        if ($this->type->allow_multiple)
        {
            $output .= ' multiple';
        }
        if ($this->frozen)
        {
            $output .= ' disabled="disabled"';
        }
        $output .= " >\n";
        // FIXME: for read-only etc mode load only the needed option
        $options = $this->type->list_all();
        foreach ($options as $value => $label)
        {
            $output .= "        <option value=" . midcom_helper_xsspreventer_helper::escape_attribute($value) . '>' . midcom_helper_xsspreventer_helper::escape_element('option', $label) . "</option>\n"; 
        }

        $output .= "    </select>\n";
        $output .= "</label>\n";

        if ($this->type->allow_other)
        {
            // FIXME: load info for other
            $othervalue = '';
            $output =  "<label for=\"{$this->namespace}_other\"><span>{$this->field['title']}</span>\n";
            $output .= "    <input id=\"{$this->namespace}_other\" name=\"{$this->namespace}_other\" ";
            if ($this->frozen)
            {
                $output .= ' disabled="disabled"';
            }
            $output .= ' value=' . midcom_helper_xsspreventer_helper::escape_attribute($othervalue) . " />\n";
            $output .= "</label>\n";
        }

        return $output;
    }


    /**
     * The current selection is compatible to the widget value only for multiselects.
     * We need minor typecasting otherwise.
     */
    function sync_widget2type($result)
    {
        $this->type->value = $result;
        // TODO: handle allow_other
        /*
        $other_key = "{$this->namespace}_other";
        */
    }

    

    function render_content()
    {
        if ($this->type->allow_multiple)
        {
            echo '<ul>';
            if (count($this->type->selection) == 0)
            {
                echo '<li>' . $this->_translate('type select: no selection') . '</li>';
            }
            else
            {
                foreach ($this->type->selection as $key)
                {
                    echo '<li>' . $this->_translate($this->type->get_name_for_key($key)) . '</li>';
                }
            }
            echo '</ul>';
        }
        else
        {
            if (count($this->type->selection) == 0)
            {
                echo $this->_translate('type select: no selection');
            }
            else
            {
                echo $this->_translate($this->type->get_name_for_key($this->type->selection[0]));
            }
        }

        if ($this->type->allow_other)
        {
            if (! $this->type->allow_multiple)
            {
                echo '; ';
            }
            echo $this->_translate($this->othertext) . ': ';
            echo implode(',', $this->type->others);
        }

    }
}
?>