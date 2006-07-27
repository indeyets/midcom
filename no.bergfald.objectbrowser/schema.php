<?php
/**
 * Created on Sep 5, 2005
 * @author tarjei huse
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package no.bergfald.objectbrowser
 * 
 * This class generates datamanager schemas from midgard objects for use by the objectbrowser.
 * 
 * 
 */
define ('MIDCOM_SCHEMA_OBJECT_TYPE', 501);
class no_bergfald_objectbrowser_schema {

    /**
     * Object attributes
     */
    var $_object_attributes = array();

    /**
     * the objecttype, from 
     */
    var $_object_type = '';

    /**
     * schemadb
     */
    var $_schemas = array();
    
    /**
     * Metainformation abut all kinds of objects
     */
    var $_meta = array();
    /**
     * value ofMGD_META_PROPERTY_PARENT for object.
     * @var string
     * @access private
     */
    var $_parent_attribute = "";
    
    /**
     * value of MGD_META_PROPERTY_UP for object.
     * @var string
     * @access private
     */
    var $_uplink_attribute = "";
    
    /**
     * value of MGD_META_TREE_PARENT for object.
     * @var string
     * @access private
     */
    var $_parent_object = "";
    
    /**
     * value of MGD_META_TREE_CHILDS for object.
     * @var string
     * @access private
     */
    var $_children_types = "";
    
    /**
     * Registry of all objects and their submembers
     */
     
    /**
     * 
     */
    
    function no_bergfald_objectbrowser_schema() 
    {

        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting schema class");
        $data = midcom_get_snippet_content('file:///no/bergfald/objectbrowser/config/schemas.inc');
        if ($data != '') {
            
            eval("\$this->_schemas = Array ( {$data}\n );");
            //debug_add ("\$this->_schemas = Array ({$data}\n);", MIDCOM_LOG_DEBUG);
        }
        
        $data = midcom_get_snippet_content('file:///no/bergfald/objectbrowser/config/metadata.inc');
        if ($data != '') {
            eval("\$this->_meta = Array ({$data}\n);");
        }
        /* this doesn't take too much time as it works on some simple arrays only..
         * */
        foreach ($_MIDGARD['schema']['types'] as $type => $l) {
            $this->classify_objecttype($type);
        }
        debug_pop();
    
    }
    
    /**
     * Singelton interface, returns the instance.
     *
     * @return no_bergfald_objectbrowser_schema
     */
    function &get_instance()
    {
        static $instance = null;
        if (!is_object($instance))
        {
            $instance = new no_bergfald_objectbrowser_schema();
            //$instance->initialize();
        }
        $ret = &$instance;
        return $ret;
    }
    

    /**
     * Get schema name
     * @return string default schema name
     */
     /*
    function get_schema_name() 
    {
    }
    */
    /**
     * Get a "common" name for an objecttype
     */
    function get_type_name($type) 
    {
        if (array_key_exists($type, $this->_meta) && 
            array_key_exists('name', $this->_meta[$type]) ) {
            return $this->_meta[$type]['name'];
        } 
        return $type;
    }

    /**
     * Get the objecttype of the current object
     */
    function get_current_type() 
    {
        return $this->_object_type;
    }
    
    /**
     * Set the current oibjecttype 
     * @param string
     * @return void
     * @access public
     */
    function set_current_type ($type ) 
    {
        $object = new $type();
        $this->set_object(&$object);
    }

    /**
     * Get the schema for an object
     * If a schema for the object is defined in config/schema.inc
     * it will use this, if not, it will try to create one.
     * If a schema has multiple types, youse the schemaname to define which one
     * we want.
     * @return array dm schema.
     * @param string objecttype
     * @param string schemaname
     */
    function get_schema($type, $schemaname = false) 
    {
        if (!array_key_exists($type, $this->_schemas)) 
        {
            $obj = new $type();
            $this->set_object(&$obj);
            $this->_schemas[$type][$type] = $this->create_schema();
            
        }
        
        if ($schemaname ) {
                        
            return $this->_schemas[$type][$schemaname];
        }
        // this one should always be defined at this point.
        return $this->_schemas[$type][$type];
    }
    
    /**
     * Get the relevant schemas for this object. 
     * @returns array of schemaname => description for each type
     */
    function list_schemas($objecttype) {
        $return = array();
        /*
         * If no schemas are defined _AND_ we cannot make a schema,
         * return false. 
         */
        if (!array_key_exists($objecttype, $this->_schemas) && 
            ! ($this->_schemas[$objecttype][$objecttype] = $this->create_schema($objecttype)) ) {
            return false;
        }
        
        foreach ($this->_schemas[$objecttype] as $schemaname => $schema) {
            $return[$schemaname] = str_replace("midgard_", "", $schema['description']);
        }   
        return $return;
    }

    /**
     * Get the name of the storageobject of a given schemaname.
     * It will search the current object set by set_object as well
     * as it's children first befor searching the whole schema db.'
     * @return string objecttype
     * @param schema_name
     */
    function get_storage($schema_name) {
        
        foreach ($this->_schemas as $type => $schemas) {
                 
            foreach ($schemas as $schema => $schema_fields) {
                if ($schema_name == $schema) {
                    return $schema_fields['storage'];
                }
            }
        }
        /* if the schemaname exists in _MIDGARD then that's the storageobject.  
         * note: this makes the class a bit less generic.
         * */        
        if (!array_key_exists($schema_name, $this->_schemas) && array_key_exists($schema_name, $_MIDGARD['schema']['types'])) 
        {
            return $schema_name;
        }
        return false;
    }
        

    /**
     * Find out if the object is a node or a leaf
     * @param mixed reference to the object or to a string with the objecttype.
     * @return boolean
     */
    function is_node($object) 
    {
        
        if (is_string($object)) {
            $type = $object;
        } elseif (is_object($object)) {
            $type = get_class($object);
        }
        if (!array_key_exists($type, $this->_meta)) {
            if (!$this->classify_objecttype($type)) {
                return false;
            }
        }
        
        //if (array_key_exists($type, $this->_meta)) {
        if (array_key_exists(MIDCOM_SCHEMA_OBJECT_TYPE, $this->_meta[$type]) 
             && $this->_meta[$type][MIDCOM_SCHEMA_OBJECT_TYPE] == 'node') { 
            return true;
        }
        //}  
        return false;
        
    
    }
    
    /**
     * determine if an object is a leaf or a node and 
     * @param mixed objectpointer or object type or void for current object.
     * */
    function is_leaf($object) 
    {
        
        if ($object === null) {
            $type = $this->_object_type;
        } elseif (is_string($object)) {
            $type = $object;
            
        }elseif (is_object($object)) {
            $type = get_class($object);
        }
        
        if (!array_key_exists($type, $this->_meta)) {
                if (!$this->classify_objecttype($type)) return false;
        }    
        
        if (array_key_exists(MIDCOM_SCHEMA_OBJECT_TYPE, $this->_meta[$type]) && 
            $this->_meta[$type][MIDCOM_SCHEMA_OBJECT_TYPE] == 'leaf') { 
                return true;
        }
        
        return false;
    
    }

    /**
     * Classify an object according to its MGD_META properties as well as some heuristics.
     * The clasification is stored in the _meta array.
     * @param objecttype
     * @return boolean success 
     *
     * From Piotras blog:
     * MGD_META_PROPERTY_PARENT
     * ( use it when you want to know which property ( it's name ) points to parent object in midgard tree )
     * Schema definition: <property name="topic" type="integer" parentfield="topic"/>
     *
     * MGD_META_PROPERTY_UP
     * ( use it when you want to know which property ( it's name ) points to up object in midgard tree, usually object of the same type )
     * Schema definition: <property name="up" type="integer" upfield="up"/>
     *
     * MGD_META_PROPERTY_PRIMARY
     * ( use it when you want to know which property ( it's name ) is used as primary field ( id or guid ))
     * Schema definition: <property name="id" type="integer" primaryfield="id"/>
     *
     * MGD_META_TREE_PARENT
     * ( use it when you want to know the name of parent object's in midgard tree )
     * Schema definition: <type name="NewMidgardArticle" parent="NewMidgardTopic">
     *
     * MGD_META_TREE_CHILDS
     * ( use it when you want to know how many child ( in midgard tree ) objects ( and their names ) may be used for object )
     * There is no schema definition for this. Child types are created automagically when schema is loaded.
     */
    function classify_objecttype($type) {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!array_key_exists($type, $_MIDGARD['schema']['types'])) {
            debug_add("Tried to classify {$type} for some reason", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        $this->_meta[$type][MIDCOM_SCHEMA_OBJECT_TYPE] = 'undef';
        /* default value for hiding objects... */
        if (! array_key_exists( 'hide', $this->_meta[$type])) {
            $this->_meta[$type]['hide'] = 0;
        }
        
        $this->_meta[$type][MGD_META_PROPERTY_PARENT] = mgd_get_class_data(MGD_META_PROPERTY_PARENT, $type);
        $this->_meta[$type][MGD_META_PROPERTY_UP] = mgd_get_class_data(MGD_META_PROPERTY_UP, $type);
        $this->_meta[$type][MGD_META_PROPERTY_PRIMARY] = mgd_get_class_data(MGD_META_PROPERTY_PRIMARY, $type);
        $this->_meta[$type][MGD_META_TREE_CHILDS] = mgd_get_class_data(MGD_META_TREE_CHILDS, $type);
        $this->_meta[$type][MGD_META_TREE_PARENT] = mgd_get_class_data(MGD_META_TREE_PARENT, $type);
        
        if (count($this->_meta[$type][MGD_META_TREE_CHILDS]) > 0) {
            $this->_meta[$type][MIDCOM_SCHEMA_OBJECT_TYPE]  = 'node';
        }
        
        if ($type != $this->_meta[$type][MGD_META_TREE_PARENT]) {
            $this->_meta[$type][MIDCOM_SCHEMA_OBJECT_TYPE] = 'leaf';
        }
        
        debug_pop();
        return true;
    }    

    /**
     * Soes this objecttype exist? 
     */
     function objecttype_exists($type) {
        return array_key_exists($type,$this->_meta);
     }
    
    /**
     * Get the up attribute of a node
     * @param string class name
     * @return mixed string up parameter or false. 
     */
    function get_up_attribute($objecttype) 
    {
        
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($this->is_leaf($objecttype)) {
            debug_pop();
            return $this->get_leaf_up_attribute($objecttype);
        }
        //debug_add("Checking up attribute for  $objecttype: ");
        if ( array_key_exists(MGD_META_PROPERTY_UP, $this->_meta[$objecttype]) 
             && $this->_meta[$objecttype][MGD_META_PROPERTY_UP] != '') {
            
            debug_pop();
            return $this->_meta[$objecttype][MGD_META_PROPERTY_UP];
        }
        debug_pop();
        return false;
    }
        /**
     * Get the up attribute of a leaf 
     * @param string class name
     * @return mixed string up attribute or false. 
     */
    function get_leaf_up_attribute($objecttype) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ( array_key_exists(MGD_META_PROPERTY_PARENT, $this->_meta[$objecttype]) 
             && $this->_meta[$objecttype][MGD_META_PROPERTY_PARENT] != '') {
            debug_pop();
            return $this->_meta[$objecttype][MGD_META_PROPERTY_PARENT];
        }
        
        debug_pop();
        return false;
    }
    
    /**
     * Get the children of the object
     * @param string objecttype
     */
    function get_children ($type ) {
        if (is_object($type)) {
            $type = get_class($type);
        }
        if (array_key_exists($type, $this->_meta)) {
            return $this->_meta[$type][MGD_META_TREE_CHILDS];
        } else {
            if ($this->classify_objecttype($type)) {
                return $this->get_children($type);
            } else{
                return array();
            }
        }
    } 
    
    /**
     * Determine if an objecttype should ne hidden 
     * @param string object type
     * @return boolean true if the object should be hidden
     */    
     function nav_hide($type) {
        debug_push_class(__CLASS__, __FUNCTION__);
       
        if ($this->_meta[$type]['hide'] ) {
            debug_pop();
            return true;
        }
        debug_pop();
        return false;
     }
    /**
     * Get the name attribute from an object.
     * It will return the objects type if no other options seem to exist.
     */
    function get_name_attribute(&$object) 
    {
        $type = get_class($object);
        
        
        if (array_key_exists($type, $this->_meta) &&  array_key_exists('name_attribute', $this->_meta[$type] )) {
               return $this->_meta[$type]['name_attribute'];
        }
        $fields = get_object_vars($object);
        if (array_key_exists('title', $fields)) {  return array('title' );  }
        if (array_key_exists('name', $fields)) {  return array ('name' );  }
        
        
        return  array () ;
        
        
    }
    /**
     *  Define the objecttype and object attributes
     *  @access public
     *  @param pointer to the current object
     */
    function set_object(&$object) 
    {
        
        $this->_object_attributes   = get_object_vars($object);
        // TODO make check for midcom va midgard objectss
        $this->_object_type         = get_class($object);
        $this->_parent_attribute    = $object->get_data(MGD_META_PROPERTY_PARENT);
        $this->_uplink_attribute    = $object->get_data(MGD_META_PROPERTY_UP);
        $this->_parent_object       = $object->get_data(MGD_META_TREE_PARENT);
        $this->_children_types      = $object->get_data(MGD_META_TREE_CHILDS);
        //$this->classify_objecttype($this->_object_type);
    }
    
    /**
     * Create a suggested schema out of the current object.
     * This will never be perfect - but may be a good starter =)
     * @param string objecttype or false to use current object.
     * @return array DataManager compliant schema
     */
     
     function create_schema($object_type = false) 
     {
        
        if ($object_type) {
            $this->set_current_type($object_type);
        }
     
        
        $ret = array (
                "name"        => $this->_object_type,
                "description" => str_replace ('midgard_', '', $this->_object_type),
                "storage"     => $this->_object_type,
                );
        
        $fields = array();
        foreach ($this->_object_attributes as $attribute => $value) {
            
            
            switch ($attribute) {
                /* attribute fields */
                case 'title':
                case 'name':
                    $fields['main'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "text",
                                "location"    => $attribute, 
                                "required" => false,
                                "readonly" => false,
                                );
                break;
                case 'id':
                case 'guid':
                case 'author':
                case 'owner':
                    $fields['details'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "text",
                                "location"    => $attribute, 
                                "required" => false,
                                "readonly" => true,
                                );
                     break;
                
                case 'sitegroup':
                case 'lang':
                    $fields['advanced'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "text",
                                "location"    => $attribute, 
                                "required" => false,
                                "readonly" => true,
                                );
                break;            
                /* large fields */
                case 'description':
                
                case 'content':
                case 'abstract':
                
                    $fields['main'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "text",
                                "location"    => $attribute,
                                "widget"      => "html", 
                                "required" => false,
                                );
                break;
                /* codefields */
                case 'value':
                case 'code':
                $fields['main'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "text",
                                "location"    => $attribute,
                                "widget_text_inputstyle" => "longtext",
                                "widget_text_height" => '100%',
                                "required" => false,
                                );
                break;
                /* metadata */
                case    "calstart":
                case    "caldays" :
                
                    $fields['meta'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "date",
                                "location"    => $attribute,
                                "widget"      => "date", 
                                "required" => false,
                                );
                break;
                case    "icon"    :
                case    "view"    :
                case    "print"   :
                $fields['meta'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "text",
                                "location"    => $attribute,
                                "widget"      => "text", 
                                "required" => false,
                                );
                break;
                
                /* hide fields we know are internal */
                case '__table__':
                case 'sid':
                case 'errno':
                case 'errstr':
                case 'realm':
                break;
                
                case 'up' :
                       $fields['advanced'][$attribute] = array (
                                "description" => "The objects up attribute ",
                                "datatype"    => 'number',
                                "location"    => $attribute,
                                "widget"      => "text", 
                                "required" => false,
                                'readonly' => true,
                                'hidden'    => true,
                                );
                break;
                default: 
                   $fields['advanced'][$attribute] = array (
                                "description" => $attribute,
                                "datatype"    => "text",
                                "location"    => $attribute,
                                "widget"      => "text", 
                                "required" => false,
                                );
                break;
                
                
            }
            
        }
        $ret['fields'] =  array();
        $fieldgroups = array(  'main','details','advanced', 'metadata', 'others');
        foreach (  $fieldgroups as $fieldgroup ) {
            if (!array_key_exists($fieldgroup, $fields)) {
                // empty fieldgroup..
                continue;
            }
            
            $newgroup = $fields[$fieldgroup];
            /* this is probably not the most effcient implementation */
            $fieldgroup_fields = array_keys($newgroup);
            $startfield = $fieldgroup_fields[0];
            $endfield   = $fieldgroup_fields[count($fieldgroup_fields)-1];
            if (count ($fieldgroup_fields)> 1) {
               $newgroup[$startfield]["start_fieldgroup"] = 
                        array ( 'title' => $fieldgroup,
                                'css_group' => 'ais_fieldgroup_' .$fieldgroup,
                                'css_title' => 'ais_fieldgroup_title_' . $fieldgroup 
                        );
                   
               $newgroup[$endfield]["end_fieldgroup"] = 1;
               
            } elseif (count ($fieldgroup_fields) == 1) {
                
               
               $newgroup[$startfield]["start_fieldgroup"] = 
                        array ( 'title' => $fieldgroup,
                                'css_group' => 'ais_fieldgroup_' .$fieldgroup,
                                'css_title' => 'ais_fieldgroup_title_' . $fieldgroup 
                        );
               
               $newgroup[$startfield]["end_fieldgroup"] = 1;
                
            }
            
            $ret['fields'] = array_merge($ret['fields'] , $newgroup);
            
        }
        $string = $this->schema_2_string($ret, $this->_object_type);
        /**
         * save schema to temp for now. 
         */
        if ($fh = @fopen("/tmp/schema_" . $this->_object_type . ".inc",'w')) {
            @fwrite($fh, $string);
            fclose($fh); 
        }
        return  $ret;   
     }
     
     /**
      * Get a schemastring
      * @param array schema the schema to be added
      * @param string schemaname
      * 
      * @return string schema defenition
      */
     function schema_2_string($schema, $schemaname) 
     {
        
        
        
        $top = " \"$schemaname\" => array ( \n";
        /* in case the schema hasn't been reduced to it's parts yet. */
        if (array_key_exists($schemaname, $schema)) {
            $schema = $schema[$schemaname];
        }
        foreach ($schema as $key => $value ) {
                  
            if ( $key != 'fields') {
              $top .= "    \"$key\" => \"$value\",\n";                  
            
            }
        
        }
        $top .="   'fields' => Array(\n";
        foreach ($schema['fields'] as $field => $value) {
            $top .= "       \"$field\" => Array(\n";
            foreach ($value as $fieldval => $val) {
                if (is_array($val)) {
                    $top .= "         \"$fieldval\" => Array(\n";
                    foreach ($val as $fieldvalval => $valval) {
                        $top .= "           \"$fieldvalval\" => '$valval',\n";        
                    } 
                    $top .= "         ),\n";
                } elseif(is_bool($val)) {
                    $txt = ($val) ? '"true"' : '"false"' ;
                    $top .= "         \"$fieldval\" => " .$txt. ",\n";
                } else {
                    $top .= "         \"$fieldval\" => '$val',\n";
                }
            }
            $top .= "       ),\n";
        }
     
        $top .= "    ),\n )\n";
        return $top;
     }
     /* export the metaarray to a set of strings.
      * */
     function meta_2_string() {
        $ret = "";
        
        foreach($this->_meta as $type => $value) {
        $ret .= " '$type' => array ( \n";
        foreach ( $value as $key => $data) {
            $ret .= "   '$key' => '$data',\n";
        }
        $ret .= "),\n\n";
        
        }
        return $ret;
     }
}
?>
