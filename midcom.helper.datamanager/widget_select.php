<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle image variable-height select lists.
 *
 * This widget can be used with text-based types, usually limiting it to
 * datatype_text therefore. Creative usage is possible though ;-).
 *
 * <b>Configuration parameters:</b>
 *
 * <b>widget_select_choices:</b> This option is mandatory and has to contain the
 * selection list. The keys of the arrays are stored into the field, while the
 * value is shown to the user in the forms. Note, that it is a good practice to
 * have the default empty-key ('') object in the dropdown too, so that new objects
 * have a "nicer" look.
 *
 * Note, that the various select helper frunctions from helper_select_lists.php can
 * be used here.
 *
 * <b>widget_select_size:</b> This is the height of the widget, in lines. If omitted,
 * the default 1 is used, which essentially makes it a dropdown instead of a list
 * control.
 *
 * <b>widget_select_display_key:</b> A boolean, that controls whether the key should be shown to the
 * user, or not, which would be the default if omitted.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "select" => array (
 *     "description" => "Select Dropdown",
 *     "datatype" => "text",
 *     "widget" => "select",
 *     "widget_select_choices" => Array (
 *         "" => "Default value",
 *         "opt1" => "Option 1",
 *         "opt2" => "Option 2"
 *     ),
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The select widget will be either of select.dropdown or select.list depending on the
 * height configured for the widget.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_select extends midcom_helper_datamanager_widget {

    /**
     * The associative array with the choices available.
     *
     * @var Array
     * @access protected
     */
    var $_choices;

    /**
     * The height of the widget
     *
     * @var int
     * @access protected
     */
    var $_size;

    /**
     * This is true, if we need to display the key to the user.
     *
     * @var boolean 
     * @access protected
     */
    var $_display_key;

    function _constructor (&$datamanager, $field, $defaultvalue) {
 	    parent::_constructor ($datamanager, $field, $defaultvalue);

        if (!array_key_exists("widget_select_choices", $this->_field))
        {
            $this->_field["widget_select_choices"] = Array();
        }
        if (!array_key_exists("widget_select_size", $this->_field))
        {
            $this->_field["widget_select_size"] = 1;
        }
        if (!array_key_exists("widget_select_display_key", $this->_field))
        {
            $this->_field["widget_select_display_key"] = false;
        }

        $this->_choices = Array();
        foreach ($field['widget_select_choices'] as $key => $value)
        {
            $this->_choices[$key] = $datamanager->translate_schema_string($value);
        }
        $this->_size = $this->_field["widget_select_size"];
        $this->_display_key = $this->_field["widget_select_display_key"];
    }

    function draw_view () {
        ?><div class="form_select"><?php
        if ($this->_display_key)
        {
            echo $this->_value . ": ";
        }
        if (array_key_exists($this->_value, $this->_choices))
        {
            /* Keep nbsp's intact */
	        $value = htmlspecialchars($this->_choices[$this->_value]);
	        $value = str_replace ("&amp;nbsp;", "&nbsp;", $value);
        }
        else
        {
            /* Fallback in case we have an unknown key */
	        $value = $this->_value;
        }
        echo htmlspecialchars($value) . '</div>';
    }

    function draw_widget () {
        $class = ($this->_size == 1) ? "dropdown" : "list";
        echo "<select class='{$class}' name='{$this->_fieldname}' id='{$this->_fieldname}' size='{$this->_size}'>\n";

        foreach ($this->_choices as $key => $value)
        {
            /* Keep nbsp's intact */
            $value = htmlspecialchars($value);
            $value = str_replace ("&amp;nbsp;", "&nbsp;", $value);
            $key = htmlspecialchars($key);
            if ($this->_display_key)
            {
                $value = "{$key}: {$value}";
            }
            $selected = ($key == $this->_value) ? ' selected' : '';
            echo "  <option value='{$key}'{$selected}>{$value}</option>\n";
        }

        echo "</select>\n";
    }

}


?>