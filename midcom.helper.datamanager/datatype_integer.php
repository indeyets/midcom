<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Integer datatype.
 * 
 * This type will store integrers. Values 
 * 
 * <b>Default Parameters</b>
 * 
 * - <i>Location</i>: parameter
 * - <i>widget</i>: text
 *
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_datatype_integer extends midcom_helper_datamanager_datatype 
{
    
    /**
     * Constructor with default configuration.
     */
    function midcom_helper_datamanager_datatype_integer (&$datamanager, &$storage, $field) 
    {
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "parameter";
        }
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "text";
        }
                        
        parent::_constructor ($datamanager, $storage, $field);
    }
    
    function load_from_storage() 
    {
        if (! parent::load_from_storage()) 
        {
            return false;
        }
        
        $this->_value = (int) $this->_value;
        return true;
    }
    
    function save_to_storage ()
    {
        $numeric_value = $this->_value;
        $this->_value = (int) $this->_value;
        $result = parent::save_to_storage();
        $this->_value = $numeric_value;
        return $result;
    }
    
    function sync_widget_with_data() 
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        
        $widget =& $this->get_widget();
        $value = (int) $this->_value;
        $widget->set_value($value);
        debug_add("Set valueof the widget to {$value}");
        
        debug_pop();
    }
    
    function _get_widget_default_value () 
    {
        return (int) $this->_value;
    }
    
    function sync_data_with_widget () 
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        
        $widget =& $this->get_widget();
        $string = $widget->get_value();
        if (trim($string) == '') 
        {
            /* Check for empty string, if yes, set the value to 0 */
            $this->_value = 0;
            
            debug_add("Widget was empty, setting to 0.");
            debug_pop();
            return;
        }

        $this->_value = (int) $string;
        $this->sync_widget_with_data();
    }
    
    function _get_empty_value() 
    {
        return 0;
    }
    
}

?>