<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 simple checkbox
 *
 * As with all subclasses, the actual initialization is done in the initialize() function,
 * not in the constructor, to allow for error handling.
 *
 * This widget supports the boolean type only.
 *
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_widget_checkbox extends midcom_helper_datamanager2_widget
{
    /**
     * The initialization event handler validates the base type.
     *
     * @return bool Indicating Success
     */
    function _on_initialize()
    {
        if (! is_a($this->_type, 'midcom_helper_datamanager2_type_boolean'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Warning, the field {$this->name} is not of type boolean.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        return true;
    }

    /**
     * Adds a simple checkbox form element.
     */
    function add_elements_to_form()
    {
        $this->_form->addElement('checkbox', $this->name, $this->_translate($this->_field['title']), '', Array('class' => 'checkbox'));
    }

    function get_default()
    {
        if ($this->_type->value)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function sync_type_with_widget($results)
    {
        $element =& $this->_form->getElement($this->name);
        $this->_type->value = $element->getChecked();
    }

}

?>