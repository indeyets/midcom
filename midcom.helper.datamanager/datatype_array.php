<?php

class midcom_helper_datamanager_datatype_array extends midcom_helper_datamanager_datatype
{
    
    var $_current_selection;
    
    function midcom_helper_datamanager_datatype_array (&$datamanager, &$storage, $field) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "attachment";
        }
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "multiselect";
        }
        
        $this->_value = Array();
        
        parent::_constructor ($datamanager, $storage, $field);
        
        debug_pop();
    }
    
    function load_from_storage () 
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        
        // First load the data from the storage
        parent::load_from_storage();
        debug_print_r('Loaded data:', $this->_value);
        
        if (!is_array($this->_value))
        {
            $this->_value = array();
            return false;
        }
                
        debug_pop();
        return true;
    }


    function save_to_storage () 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!is_array($this->_value))
        {
            $this->_value = array();
            return false;
        }
        
        debug_pop();
        return parent::save_to_storage();
    }
    
    function get_value () 
    {
        return $this->_value;
    }
    
    function sync_widget_with_data() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $widget =& $this->get_widget();        
        $widget->set_value($this->_value);
        
        debug_pop();
    }

    function sync_data_with_widget () 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $widget =& $this->get_widget();
        $this->_value = $widget->get_value();
        
        debug_pop();
    }
    
    function is_empty() 
    {
        return (count ($this->_selection_list) == 0);
    }
}

?>