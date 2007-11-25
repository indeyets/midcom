<?php

class midcom_helper_datamanager_datatype_collection extends midcom_helper_datamanager_datatype 
{
    
    var $_elements;
    
    function _constructor (&$datamanager, &$storage, $field) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $field["location"] = "attachment";
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "collection";
        }
        if (!array_key_exists("element_datatype", $field))
        {
            $field["element_datatype"] = "blob";
        }
        if (!array_key_exists("element_widget", $field))
        {
            $field["element_widget"] = "blob";
        }
        
        // Override central default constructor completely, as the isnull handling of the
        // storage object is quite different here.
        
        $this->_datamanager =& $datamanager;
        $this->_storage =& $storage;
        $this->_field = $field;
        
        if (! $this->load_from_storage()) 
        {
            debug_add("Load from storage failed");
            $GLOBALS["midcom_errstr"] = "Could not load data from storage. See Debug Log";
            debug_add("Leaving");
            debug_pop();
            return false;
            $x =& $this;
            $x = false;
        } 
        
        $classname = "midcom_helper_datamanager_widget_{$this->_field['widget']}";
        debug_add("We have to instantiate a widget of type {$classname} for field {$this->_field['name']}."); 
        $this->_widget = new $classname($this->_datamanager, $this->_field, $this->_get_widget_default_value());
                
        debug_pop();
    }


    function load_from_storage () 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
            
        $this->_elements = Array();
        
        if (is_null ($this->_storage)) 
        {
            debug_add("The storage object is null, can't do anything except adding an empty field.", MIDCOM_LOG_WARN);
        } 
        else 
        {
            // Collect all matching attachments and create their corresponding datatypes
            // For now: ignore failed attachments as a fail-save operation mechansim
            
            if ($list = $this->_storage->listattachments())
            {
                while ($list->fetch ()) 
                {
                    $att = mgd_get_attachment ($list->id);
                    if ($att->parameter("midcom.helper.datamanager.datatype.collection", "fieldname") == $this->_field["name"])
                    {
                        $this->_add_element($att);
                    }
                }
            }
            
            ksort($this->_elements);
        }
        
        // Add a single empty element after the last one
        // First, search the highest counter id
        
        end($this->_elements);
        $counter = key($this->_elements) + 1;
        $field = $this->_field;
        $field["name"] .= "_" . $counter;
        $field["datatype"] = $this->_field["element_datatype"];
        $field["widget"] = $this->_field["element_widget"];
        unset ($field["element_datatype"]);
        unset ($field["element_widget"]);
        
        debug_print_r ("Adding empty field with ID {$counter}:", $field);
        
        $classname = "midcom_helper_datamanager_datatype_" . $field["datatype"];
        $this->_elements[$counter] = new $classname ($this->_datamanager, $this->_storage, $field);        
        
        if (!array_key_exists("midcom_helper_datamanager_datatype_collection_elements",$GLOBALS))
        {
            $GLOBALS["midcom_helper_datamanager_datatype_collection_elements"] = Array();
        }
        
        $GLOBALS["midcom_helper_datamanager_datatype_collection_elements"][$this->_field["name"]] =& $this->_elements;
        
        debug_pop();
        return true;
    }

    function save_to_storage () 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $result = MIDCOM_DATAMGR_SAVED;
        
        if (is_null ($this->_storage)) 
        {
            debug_add("The storage object is null, can't do anything.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }        
        
        foreach (array_keys($this->_elements) as $key) 
        {
            if ($this->_elements[$key]->save_to_storage() == MIDCOM_DATAMGR_FAILED)
            {
                $result = MIDCOM_DATAMGR_FAILED;
            }
            $attdata = $this->_elements[$key]->get_value();
            if (!is_null($attdata)) 
            {
                $att = mgd_get_attachment($attdata["id"]);
                $att->parameter("midcom.helper.datamanager.datatype.collection", "fieldname", $this->_field["name"]);
                $att->parameter("midcom.helper.datamanager.datatype.collection", "id", $key);
            }
        }
        
        debug_pop();
        return $result;
    }

    function get_value () 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $result = Array();
        
        foreach (array_keys($this->_elements) as $key) 
        {
            $data = $this->_elements[$key]->get_value();
            if (!is_null($data))
            {
                $result[$key] = $data;
            }
        }
        
        debug_pop();
        return $result;
    }

    function get_csv_data () 
    {
        $result = "";
        $first = true;
        
        foreach (array_keys($this->_elements) as $key) 
        {
            if (is_null($this->_elements[$key]->get_value()))
            {
                continue;
            }
            
            if ($first) 
            {
                $result .= $this->_elements[$key]->get_csv_data();
                $first = false;
            } 
            else 
            {
                $result .= "; " . $this->_elements[$key]->get_csv_data();
            }
        }
        
        if ($result != "")
        {
            return "Data Collection: " . $result;
        }
        else
        {
            return "";
        }
    }

    function _get_widget_default_value () 
    {
        return null;
    }
    
    function _get_empty_value() 
    {
        return null;
    }
    
    function _datamanager_set_storage (&$storage) 
    {
        $this->_storage =& $storage;
        foreach (array_keys($this->_elements) as $key)
        {
            $this->_elements[$key]->_datamanager_set_storage($storage);
        }
    }
    
    function sync_widget_with_data () 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        foreach (array_keys($this->_elements) as $key)
        {
            $this->_elements[$key]->sync_widget_with_data();
        }
        
        debug_pop();
    }
    
    function sync_data_with_widget () 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        foreach (array_keys($this->_elements) as $key)
        {
            $this->_elements[$key]->sync_data_with_widget();
        }
        
        debug_pop();
    }
    
    function _add_element($att) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // This function creates the datatype for the attachment $att.
                
        $counter = $att->parameter("midcom.helper.datamanager.datatype.collection", "id");
        if ($counter === false || array_key_exists($counter, $this->_elements)) 
        {
            debug_add ("Counter {$counter} is invalid, aborting.");
            return false;
        }
        $field = $this->_field;
        $field["name"] .= "_" . $counter;
        $field["datatype"] = $this->_field["element_datatype"];
        $field["widget"] = $this->_field["element_widget"];
        unset ($field["element_datatype"]);
        unset ($field["element_widget"]);
        
        debug_print_r("Adding Field {$counter} to the elements:", $field);
        
        $classname = "midcom_helper_datamanager_datatype_" . $field["datatype"];
        $this->_elements[$counter] = new $classname ($this->_datamanager, $this->_storage, $field);
        
        if ($this->_elements[$counter] === false) 
        {
            unset ($this->_elements[$counter]);
            debug_add("Construction of Field $counter failed");
            return false;
        }
        
        debug_pop();
        return true;
    }
    
    function is_empty() 
    {
        return (count($this->_elements) == 0);
    }
    
    /**
     * Relays the autoindex call if we have blobs or subtypes thereof.
     */
    function autoindex()
    {
        if ($this->is_empty())
        {
            return;
        }
        foreach ($this->_elements as $key => $element)
        {
            if (is_a($element, 'midcom_helper_datamanager_datatype_blob'))
            {
                $this->_elements[$key]->autoindex();
            }
        }
    }
    
    /**
     * Relays the destroy call to all elements.
     */
    function destroy()
    {
        unset ($GLOBALS["midcom_helper_datamanager_datatype_collection_elements"][$this->_field["name"]]);
        foreach ($this->_elements as $key => $copy)
        {
            $this->_elements[$key]->destroy();
            unset($this->_elements[$key]);
        }
        $this->_elements = Array();
        
        parent::destroy();
    }
}

?>