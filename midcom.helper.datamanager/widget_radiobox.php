<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle radio-box style select lists.
 *
 * This widget should only be used with the text-based types. Key comparison is
 * done using the PHP type-insensitive equality operator. See widget_config_radiobox
 * for an alternative to this.
 *
 * <b>Configuration parameters:</b>
 *
 * <b>widget_radiobox_choices:</b> This option is mandatory and has to contain the
 * selection list. The keys of the arrays are stored into the field, while the
 * value is shown to the user in the forms. Note, that it is a good practice to
 * have the default empty-key ('') object in the list too, so that new objects
 * have a "nicer" look.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "radiobox" => array (
 *     "description" => "Radiobox Widget",
 *     "datatype" => "text",
 *     "widget" => "radiobox",
 *     "widget_radiobox_choices" => Array (
 *         "" => "Default value",
 *         "opt1" => "Option 1",
 *         "opt2" => "Option 2"
 *     ),
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The fieldset is set to fieldset.radiobox. The input fields are input.radiobutton.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_radiobox extends midcom_helper_datamanager_widget {

    /**
     * The associative array with the choices available.
     *
     * @var Array
     * @access private
     */
    var $_choices;

    function _constructor (&$datamanager, $field, $defaultvalue) {
        parent::_constructor ($datamanager, $field, $defaultvalue);

        if (!array_key_exists("widget_radiobox_choices", $this->_field))
            $this->_field["widget_radiobox_choices"] = Array();

        $this->_choices = Array();
        foreach ($field['widget_radiobox_choices'] as $key => $value)
        {
            $this->_choices[$key] = $datamanager->translate_schema_string($value);
        }
    }

    /**
     * Helper function that does compare two keys for equality.
     *
     * @param mixed $key1 The first key.
     * @param mixed $key2 The second key.
     * @return bool True, if equal, false otherwise.
     * @access private
     */
    function _key_comparer ($key1, $key2)
    {
        return ($key1 == $key2);
    }

    function draw_view () {
        ?><div class="form_radiobox"><?echo htmlspecialchars($this->_value);?></div><?php
    }

    /**
     * The widget will use a field group to put all related fields together.
     */
    function draw_widget_start()
    {
        $css = $this->get_css_classes_required();
        if ($css != '')
        {
            $css = " class='$css'";
        }
        echo "<fieldset class='radiobox' id='{$this->_fieldname}_fieldset'>\n";
        echo "  <legend{$css}>\n";
        echo '    ' . htmlspecialchars($this->_field['description']);
        $this->draw_helptext();
        echo "\n";
        echo "  </legend>\n";
    }

    /**
     * The widget will use a field group to put all related fields together,.
     */
    function draw_widget_end()
    {
        echo "</fieldset>\n";
    }

    function draw_widget () {
        foreach ($this->_choices as $key => $value)
        {
            $key = htmlspecialchars($key);
            $value = htmlspecialchars($value);
            $id = "{$this->_fieldname}_{$key}";
            $checked = '';
            if ($this->_key_comparer($key, $this->_value))
            {
                $checked = ' checked';
            }
            echo "  <label for='{$id}' id='{$id}_label'>\n";
            echo "    <input type='radio' class='radiobutton' name='{$this->_fieldname}' id='{$id}' value='{$key}'{$checked} />\n";
            echo "    {$value}";
            echo "  </label>\n";
        }
    }

}


?>