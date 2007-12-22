<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle a variable-length collection of other types.
 *
 * This widget should only be used with the collection datatype.
 *
 * It will show all child widgets directly. Note, that this is currently only
 * really usable with the blob and image types. See the datatype for more
 * information.
 *
 * All logic is placed within the type, this widget does only proxy the display
 * calls accordingly.
 *
 * <b>Configuration parameters:</b>
 *
 * None.
 *
 * <b>Sample configuration</b>
 *
 * <pre>
 * "collection" => array (
 *     "description" => "Download collection",
 *     "datatype" => "collection"
 * ),
 * </pre>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The fieldset is set to fieldset.image. The input fields are input.shorttext for all
 * text fields and input.fileupload for the actual upload field. The deletion checkbox
 * is of the class input.checkbox.
 *
 * Note, that while the field is empty, the fieldset is only used, when the full-scale
 * widget is used. The simple widget does not use it. While editing an existing image,
 * both have the same UI.
 *
 * The format selection dropdowns are of select.dropdown.
 *
 * The download link is a simple paragraph within the enclosing fieldset.
 *
 * The preview link is a nested div: div.image_preview surrounds the entire preview area,
 * div.image_frame surrounds any displayed image.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_collection extends midcom_helper_datamanager_widget {

    var $_elements;

    function _constructor(&$datamanager, $field, $defaultvalue) {
        parent::_constructor ($datamanager, $field, $defaultvalue);
    }

    function _read_formdata () {
        $elements =& $GLOBALS["midcom_helper_datamanager_datatype_collection_elements"][$this->_field["name"]];
        foreach (array_keys($elements) as $key) {
            $widget =& $elements[$key]->get_widget();
            $widget->_read_formdata();
        }
    }

    function get_value () {
        $result = Array();
        $elements =& $GLOBALS["midcom_helper_datamanager_datatype_collection_elements"][$this->_field["name"]];
        foreach (array_keys($elements) as $key) {
            $widget =& $elements[$key]->get_widget();
            $result[] = $widget->get_value();
        }
    }

    function set_value ($value) {
        // The collection Widget cannot directly set a value.
    }

    function draw_view () {
        $output = false;
        $elements =& $GLOBALS["midcom_helper_datamanager_datatype_collection_elements"][$this->_field["name"]];
        ?><div class="form_collection"><?php
        foreach (array_keys($elements) as $key) {
            $widget =& $elements[$key]->get_widget();
            if (! is_null ($elements[$key]->get_value())) {
                $widget->draw_view();
                $output = true;
            }
        }
        if (!$output) {
            ?><div class="form_shorttext"><?php echo $this->_l10n->get("no files"); ?></div><?php
        }
        ?></div><?php
    }

    /**
     * The collection widget will use a field group to put all contained
     * fields together.
     */
    function draw_widget_start()
    {
        $css = $this->get_css_classes_required();
        if ($css != '')
        {
            $css = " class='$css'";
        }
        echo "<fieldset class='collection' id='{$this->_fieldname}_fieldset'>\n";
        echo "  <legend{$css}>\n";
        echo '    ' . htmlspecialchars($this->_field['description']);
        $this->draw_helptext();
        echo "\n";
        echo "  </legend>\n";
    }

    /**
     * The collection widget will use a field group to put all contained
     * fields together.
     */
    function draw_widget_end()
    {
        echo "</fieldset>\n";
    }

    /**
     * Call the draw widget functions of all contained widgets.
     */
    function draw_widget () {
        $elements =& $GLOBALS["midcom_helper_datamanager_datatype_collection_elements"][$this->_field["name"]];
        foreach (array_keys($elements) as $key) {
            $widget =& $elements[$key]->get_widget();
            $widget->draw_widget_start();
            $widget->draw_widget();
            $widget->draw_widget_end();
        }
    }

}


?>