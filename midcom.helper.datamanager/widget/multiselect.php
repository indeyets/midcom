<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle image variable-height multi-selection lists.
 *
 * This widget can only be used with the multiselect type.
 *
 * <b>Configuration parameters:</b>
 *
 * <b>multiselect_selection_list:</b> This option is mandatory and has to contain the
 * selection list. The keys of the arrays are stored into the field, while the
 * value is shown to the user in the forms.
 *
 * Note, that the various select helper functions from helper_select_lists.php can
 * be used here.
 *
 * <b>widget_select_size:</b> This is the height of the widget, in lines. If omitted,
 * the default 5 is used.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "multiselect" => array (
 *     "description" => "Multi-Select list",
 *     "datatype" => "multiselect",
 *     "multiselect_selection_list" => Array (
 *         "opt1" => "Option 1",
 *         "opt2" => "Option 2"
 *     ),
 *     "widget_select_size" => 10,
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The select widget will be both of select.list and select.multiple.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_multiselect extends midcom_helper_datamanager_widget {

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

    function _constructor (&$datamanager, $field, $defaultvalue) {
        if (!array_key_exists("widget_select_size", $field))
        {
            $field["widget_select_size"] = 5;
        }

        $this->_choices = Array();
        foreach ($field['multiselect_selection_list'] as $key => $value)
        {
            $this->_choices[$key] = $datamanager->translate_schema_string($value);
        }
        $this->_size = $field["widget_select_size"];
        $this->_value = Array();

        parent::_constructor ($datamanager, $field, $defaultvalue);
    }

    function draw_view () {
        ?><div class="form_multiselect"><ul><?php
        foreach ($this->_value as $key => $value)
        {
            ?><li><?echo htmlspecialchars($value);?><!-- Key: <?echo htmlspecialchars($key);?> --></li><?php
        }
        ?></ul></div><?php
    }

    function draw_widget () {
        echo "<select multiple class='list multiple' name='{$this->_fieldname}[]' id='{$this->_fieldname}' size='{$this->_size}'>\n";

        foreach ($this->_choices as $key => $value)
        {
            /* Keep nbsp's intact */
            $value = htmlspecialchars($value);
            $value = str_replace ("&amp;nbsp;", "&nbsp;", $value);
            $key = htmlspecialchars($key);
            $selected = (array_key_exists($key, $this->_value)) ? ' selected' : '';
            echo "  <option value='{$key}'{$selected}>{$value}</option>\n";
        }

        echo "</select>\n";
    }

    function _read_formdata () {
        if (array_key_exists($this->_fieldname, $_REQUEST))
        {
            $this->_value = Array();
            foreach ($_REQUEST[$this->_fieldname] as $key)
            {
                $this->_value[$key] = $this->_choices[$key];
            }
        }
    }

    function set_value ($value) {
        if (is_array($value))
        {
            $this->_value = $value;
        }
        else
        {
            $this->_value = Array();
        }
    }

}


?>