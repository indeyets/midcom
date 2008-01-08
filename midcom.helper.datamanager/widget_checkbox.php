<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget for simple boolean fields.
 *
 * This widget should be used with the boolean type. The widget's value is
 * either true or false, depending on the state of the checkbox in the form.
 * (The PHP "on" value gets converted automagically.)
 *
 * <b>Configuration parameters:</b>
 *
 * <b>widget_checkbox_textafter<b>: Set this to true to display the text after the
 * checkbox instead of before the checkbox.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "checkbox" => array (
 *     "description" => "Checkbox",
 *     "datatype" => "boolean",
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The checkbox is of the type input.checkbox.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_checkbox extends midcom_helper_datamanager_widget {

    /**
     * check if the text should be shown before or after the checkbox.
     * @access private
     * @var boolean
     */
    var $_textafter = true;

    /**
     * check if the HTML should be allowed in a description
     * @access private
     * @var boolean
     */
    var $_allow_html = false;

    function _constructor (&$datamanager, $field, $defaultvalue)
    {

        if (! array_key_exists('widget_checkbox_textafter', $field))
        {
            $field['widget_checkbox_textafter'] = true;
        }
        $this->_textafter = $field['widget_checkbox_textafter'];

        if (! array_key_exists('widget_checkbox_allow_html', $field))
        {
            $field['widget_checkbox_allow_html'] = false;
        }
        $this->_allow_html = $field['widget_checkbox_allow_html'];

        parent::_constructor ($datamanager, $field, $defaultvalue);

    }

    function _read_formdata ()
    {
        //debug_push_class(__CLASS__, __FUNCTION__);
        // Read form data only if we have submitted
        if (array_key_exists("midcom_helper_datamanager_submit", $_REQUEST))
        {
            //debug_add('submit key found in _REQUEST, evaluating value');
            if (   array_key_exists($this->_fieldname, $_REQUEST)
                && (   strtolower($_REQUEST[$this->_fieldname]) == 'on'
                    || strtolower($_REQUEST[$this->_fieldname]) == 'yes'
                    || (string)$_REQUEST[$this->_fieldname] === '1'
                    )
                )
            {
                //debug_add('evaluated to true');
                $this->_value = true;
            }
            else
            {
                //debug_add('evaluated to false');
                $this->_value = false;
            }
        }
        //debug_pop();
    }

   /**
     * This is the default widget "introduction" code rendered before the actual
     * field code. It will open a <label> tag and display the heading.
     *
     * @see midcom_helper_datamanager_widget::draw_widget()
     * @see midcom_helper_datamanager_widget::draw_widget_end()
     * @see midcom_helper_datamanager_widget::draw_helptext()
     */
    function draw_widget_start()
    {
        echo "<fieldset class='checkbox_area' id='{$this->_fieldname}_fieldset'>\n";
        if (!$this->_textafter) {
            $css = $this->get_css_classes_required();
            $css .= " checkbox";
            if ($css = '')
            {
                $css = "class='$css' ";
            }

            echo "<label {$css}for='{$this->_fieldname}' id='{$this->_fieldname}_label'>\n";
            if ($this->_allow_html)
            {
                $title = $this->_field['description'];
            }
            else
            {
                $title = htmlspecialchars($this->_field['description']);
            }
            echo "<span class='field_text'>";
            echo "  {$title}";
            echo "</span>";

            $this->draw_helptext();
            echo "\n";
        }
    }

    /**
     * The widget will use a field group to put all related fields together,.
     */
    function draw_widget_end()
    {

        if ($this->_textafter) {
            $css = $this->get_css_classes_required();
            $css .= " checkbox";
            if ($css != '')
            {
                $css = "class='$css' ";
            }
            echo "<label {$css}for='{$this->_fieldname}' id='{$this->_fieldname}_label'>\n";
            if ($this->_allow_html)
            {
                $title = $this->_field['description'];
            }
            else
            {
                $title = htmlspecialchars($this->_field['description']);
            }
            echo "<span class='field_text_after'>";
            echo "  {$title}";
            echo "</span>";

            $this->draw_helptext();
            echo "\n";
        }
        echo "</label>\n";
        echo "</fieldset>\n";
    }


    function draw_view ()
    {
        if ($this->_value == true)
        {
            ?><div class="checkbox"><?php echo $this->_l10n->get('yes'); ?></div><?php
        }
        else
        {
            ?><div class="checkbox"><?php echo $this->_l10n->get('no'); ?></div><?php
        }
    }

    function draw_widget ()
    {
        $checked = '';
        if ($this->_value == true)
        {
            $checked = ' checked="checked"';

        }
        echo "  <input type='checkbox' class='checkbox' name='{$this->_fieldname}' id='{$this->_fieldname}'{$checked} />\n";
    }
}

?>
