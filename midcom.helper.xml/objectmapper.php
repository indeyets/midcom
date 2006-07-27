<?php


/**
 * Created on Jan 12, 2006
 * @author tarjei huse
 * @package midcom.helper.xml
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

/**
 * This is a simple class to move midgard objects to and from 
 * xml. 
 * @package midcom.helper.xml
 * Usage:
 * To get data from xml:
 * $mapper = new midcom_helper_xml_objectmapper;
 * $data = "<midcom_db_topic><id>7</id><name>Test</name></midcom_db_topic>"
 * $object = new midcom_db_topic(7);
 * $object2 = $mapper->data2object($data,$object);
 * echo $object2->name ; // outputs Test
 * 
 * To get data to xml:
 * $object = new midcom_db_topic(7);
 * $mapper = new midcom_helper_xml_objectmapper;
 * $xml = $mapper->object2data($object);
 * echo $xml ; // outputs: "<midcom_db_topic><id>7</id><name>Test</name></midcom_db_topic>"
 * 
 */
class midcom_helper_xml_objectmapper
{

    /**
     * The classname of the last read object
     * @var string classname
     * @access public
     */
    var $classname = "";
    /**
     * The errorstring 
     * @access public
     * @var string
     */
    var $errstr = "";

    /**
     * Take xml and move it into an object  
     * @param string xmldata 
     * @param the object in question.
     * @return the updated object (not saved)
     */
    function data2object($data, $object)
    {

        if (!is_string($data) || !is_object($object))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            if (!is_string($data))
            {
                debug_add("Missing data cannot unserialize");
            }
            if (!is_object($object))
            {
                debug_add("Missing object, cannot unserialize");
            }
            debug_pop();
            return false;
        }
        
        $object_values = $this->data2array($data);
        
        if (!$object_values) 
        {
            // the debugging info was added earlier in data2array;
            return false;
        }            
        
        // set the objects values to the ones from xml.
        foreach (get_object_vars($object) as $field_name => $val)
        {
            
            // skip private or read_only fields.
            if (substr($field_name, 0,2) != '__' && ( $field_name != 'guid' && $field_name != 'id'))
            {
                
                if (array_key_exists($field_name, $object_values) ) 
                    
                {
                    $object->{$field_name} = $object_values[$field_name];
                }
                else
                {
                    // unset any other value that was there before.
                    $object->{$field_name} = null;
                }
            }
        }
        return $object;
    }
    
    /**
     * Make an array out of some xml.
     * 
     * Note, the function expects xml like this:
     * <objecttype><attribute>attribute_value</attribute></objecttype>
     * But it will not return the objecttype. 
     * @param xml
     * @return array with attribute => key values.
     */
    function data2array ($data) {
        
        
        if (!is_string($data))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            if (!is_string($data))
            {
                debug_add("Missing data cannot unserialize");
            }
            debug_pop();
            return false;
        }

        $parser = new midcom_helper_xml_toarray();

        $array = $parser->parse($data);
        
        if (!$array)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Error on parsing XML:  ".$parser->errstr);
            debug_add("Data: $data");
            debug_pop();
            return false;
        }
        /* the xml is prefixed with either the old midcom class or the new one. We solve this
         * by just jumping over it as we allready got the object.
         */
        $this->classname = key($array);
        
        // move the values from _content to the index.
        foreach ($array[$this->classname] as $fieldname => $value) 
        {
            
            if (is_array($value) && array_key_exists('_content', $value)  ) 
            {
                $array[$this->classname][$fieldname] = $value['_content'];
            } else 
            {
                unset($array[$this->classname][$fieldname]);
            }
        }
        
        return $array[$this->classname];
    }
    /**
     * Make xml out of an object.
     * @param object
     * @return xmldata
     */
    function object2data($object)
    {

        if (!is_object($object)) {
            debug_push(__CLASS__, __FUNCTION__);
            debug_add("This function must get an object as it's parameter not: " . gettype($object));
            $this->errstr = "This function must get an object as it's parameter not: " . gettype($object);
            debug_pop();
            return false;
        }

        $fields = get_object_vars($object);

        // remove private fields
        foreach ($fields as $key => $field)
        {
            if (substr($key, 0,2) == '__')
            {
                unset ($fields[$key]);
            }
        }
        
        $classname = $this->_get_classname( $object);
        
        $data = "<".$classname." id=\"{$object->id}\" guid=\"{$object->guid}\"   >\n";
        
        foreach ($fields as $key => $field)
        {
            if (is_numeric($field) || is_null($field) ||is_bool($field) ) 
            { 
                $data .= "<".$key.">$field</$key>\n";
            } else {
                $data .= "<".$key."><![CDATA[" . 
                            $field .
                         "]]></$key>\n";
            }
        }

        $data .= "</". $classname .">";
        return $data;
    }
    /**
     * Get the correct classname
     * @param object the object
     * @return string the mgdschmea classname
     * 
     */
    function _get_classname( $object) 
    {
        $vars = get_object_vars($object);
        if (array_key_exists( '__new_class_name__', $vars) ) 
        {
            return $object->__new_class_name__;
        }
        return get_class($object);
    }
}
