<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a widget to handle radio-box style select lists, intended for configuration
 * UIs.
 *
 * This widget should only be used with the text-based types. Key comparison is
 * done through strcmp, to be able to distinguish "" and "0" safely.
 *
 * This widget is otherwise fully equivalent its base class, the regular radiobox.
 * See there for details.
 *
 * <b>Sample configuration</b>
 *
 * <code>
 * "radiobox" => array (
 *     "description" => "Radiobox Widget",
 *     "datatype" => "text",
 *     "widget" => "config_radiobox",
 *     "widget_radiobox_choices" => Array (
 *         "" => "Default value",
 *         "opt1" => "Option 1",
 *         "opt2" => "Option 2"
 *     ),
 * ),
 * </code>
 *
 * <b>CSS Styles in use by the Widget</b>
 *
 * The fieldset is set to fieldset.radiobox. The input fields are input.radiobutton.
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_widget_config_radiobox extends midcom_helper_datamanager_widget_radiobox {

    function _key_comparer ($key1, $key2)
    {
        return (strcmp($key1, $key2) == 0);
    }
}


?>