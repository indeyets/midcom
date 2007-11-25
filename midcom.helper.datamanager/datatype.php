<?php 

/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * The DATATYPE concept is the main interface between the data stored
 * at a Midgard Object, the Widget that displays the Data and the 
 * Datamanager core class.
 * 
 * It does:
 * 
 * - Load and store the data
 * - Present the data in a usable way to the client (get_value)
 * - Widget instantiation
 * - Provide a mechanism to synchronize the data with the widget class
 * - Provide good configuration defaults.
 *  
 * This is the cornerstone of the Datamanager Design. Though the 
 * Datamanager knows how to use Widgets, the DATATYPE is the instance 
 * actually responsible for creation, configuration of and 
 * data synchronization with the widget.
 * 
 * A important point is that this is a base class for a class hierarchy 
 * of datatypes. To make creating child classes a little bit easier and
 * less error_prone, the classes constructor has been moved to the 
 * method _constructor, which can be overwritten by clients. The advantage
 * of this is that you can just call parent:: on this and don't need to
 * remember the name of the parent class to call its constructor. As PHP
 * automatically uses the parent classes constructor if non is defined,
 * it is enough to define the _constructor method in the subclasses.
 * 
 * See also this PHP Manual Note:
 * 
 * <i>
 * If you have a complex class hierarchy, I find that it's a good idea 
 * to have a function constructor() in every class, and the 'real' php 
 * constructor only exists in the absolute base class. From the basic 
 * constructor, you call $this->constructor(). Now descendants simply 
 * have to call parent::constructor() in their own constructor, which 
 * eliminates complicated parent calls.
 * </i>
 * 
 * The default behavior implemented in this class is sufficient for data
 * with a text-representation. The derived datatype TEXT is more or less
 * exactly this class, apart from some additional defaults.
 * 
 * Note, that since the introduction of the create method, all datatypes must be
 * able to be initialized without a storage object, leading in an empty value
 * and (in turn) an empty form element. The viewport must not neccesserily be
 * operational, but the form interface must.
 * 
 * <b>Datatype configuration</b>
 * 
 * The basic datatype requires at least two configuration options:
 * 
 * <i>location</i> referrs to the storage location of the data. This can be either
 * a valid member field of the storage object (e.g. "abstract"), "parameter" or
 * "attachment". The latter two will define the name of the parameter or attachment
 * automatically you just tell the Type to store itself in a parameter, that's it.
 * Note, that parameters are limited to 255 characters in length, as are many of 
 * the regular Midgard object members.
 * 
 * <i>widget</i> defines the Widget to use for displaying the content of the
 * type. You need to specify only the actual name of the widget, not the complete
 * class name (e.g. "text" instead of "midcom_helper_datamanager_widget_text" or 
 * something like that).
 * 
 * Datatypes authors are strongly encouraged to define defaults for both of these
 * configuration parameters, so that trival configurations can be made easily. The
 * default storage location should be either a parameter or an attachment, as this
 * is the only way to ensure multiple definitions of the same type will work out
 * of the box.
 * 
 * @abstract Datatype base class
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_datatype {
    
    /**
     * A reference to our Datamanager
     * 
     * @var midcom_helper_datamanager
     * @access protected
     */
    var $_datamanager;
    
    /**
     * A reference to our storage object.
     * 
     * @var MidgardObject
     * @access protected
     */
    var $_storage;
    
    /**
     * A copy of the field definition we use.
     * 
     * @var Array
     * @access protected
     */
    var $_field;
    
    /**
     * A reference to the widget we use to paint us. 
     * 
     * <i>Important Note:</i> You should not access this member directly, instead use the
     * get_widget() call even in subclasses (hence the declaration as private). This is 
     * in place in case widget instantiation will be made dynamic in the future.
     * 
     * @var midcom_helper_datamanager_widget
     * @see get_widget()
     * @access private
     */
    var $_widget;
    
    /**
     * Our actual value as extracted from the database object.
     * 
     * @var mixed
     * @access protected
     */
    var $_value;
    
    /**
     * Construct a Datatype.
     * 
     * The actual work is done by the private _constructor helper.
     * 
     * @param midcom_helper_datamanager $datamanager The datamanager this type is assigned to.
     * @param MidgardObject $storage The storage object to use.
     * @param Array $field The field definition to construct a datatype from.
     * @see midcom_helper_datamanager_datatype::_constructor()
     */
    function midcom_helper_datamanager_datatype (&$datamanager, &$storage, $field) 
    {
        return $this->_constructor ($datamanager, $storage, $field);
    }
    
    /**
     * The constructor populates the internal members with a reference to the
     * datamanager we belong to, a reference of the Midgard Object we must use
     * for storage and the definition of the field we use.
     * 
     * Override this method if you need a custom class construction.
     * 
     * @param midcom_helper_datamanager $datamanager The datamanager this type is assigned to.
     * @param MidgardObject $storage The storage object to use.
     * @param Array $field The field definition to construct a datatype from.
     * @return bool	False on failure, object is set to false then.
     * @access protected
     */
    function _constructor (&$datamanager, &$storage, $field) 
    {
        // Run this function after completing $field, it depends on $field
        // containing the widget and location fields.
        
        $this->_datamanager =& $datamanager;
        $this->_storage =& $storage;
        $this->_field = $field;
        $this->_widget = null;
        
        if (is_null($storage)) 
        {
            $this->_value= $this->_get_default_value();
        } 
        else 
        {
            if (! $this->load_from_storage()) 
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Load from storage failed");
                $GLOBALS["midcom_errstr"] = "Could not load data from storage. See Debug Log";
                debug_add("Leaving");
                debug_pop();
                return false;
            }
        } 
        
        $classname = "midcom_helper_datamanager_widget_{$this->_field['widget']}";
        debug_add("We have to instantiate a widget of type {$classname} for field {$this->_field['name']}."); 
        $this->_widget = new $classname($this->_datamanager, $this->_field, $this->_get_widget_default_value());
    }
    
    /**
     * Loads the data from the object.
     * 
     * The default behavior is to simply load
     * the string stored in the field of the database and use it as value. It 
     * returns true, if the object was successfully loaded. If the field in the 
     * database is empty or not existent (in case of an attachment for example),
     * the field's default value is used instead (see _get_default_value()).
     * 
     * It will only load the data into the datatype. Synchronization with the
     * widget using sync_widget_with_data has to be triggered externally, as
     * it is done by the Datamanager core.
     * 
     * May be overridden in datatypes to implement more advanced storage options.
     * 
     * @return bool Indicating success.
     */
    function load_from_storage () 
    {
        if (is_null ($this->_storage)) 
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The storage object is null, can't do anything.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        switch ($this->_field["location"]) 
        {
            case "parameter":
                $this->_value = $this->_storage->parameter("midcom.helper.datamanager", "data_" . $this->_field["name"]);
                if ($this->_value === false)
                {
                    $this->_value = $this->_get_default_value();
                }
                return true;
                break;
                
            case "config":
                $this->_value = $this->_storage->parameter($this->_field["config_domain"], $this->_field["config_key"]);
                if ($this->_value === false)
                {
                    $this->_value = $this->_get_default_value();
                }
                return true;
                break;
                
            case "attachment":
                $this->_value = mgd_load_var_from_attachment ($this->_storage, "data_" . $this->_field["name"]);
                if ($this->_value === false)
                {
                    $this->_value = $this->_get_default_value();
                }
                return true;
                break;
                
            default:
                if (! array_key_exists($this->_field["location"], get_object_vars($this->_storage))) 
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add ("The field [{$this->_field['location']}] is no member the storage object.");
                    debug_pop();
                    return false;
                }
                $location = $this->_field["location"];
                $this->_value = $this->_storage->{$location};
                if ($this->_field['datatype'] != 'array')
                {
                    if (trim($this->_value) == "")
                    {
                        $this->_value = $this->_get_default_value();
                    }
                }
                return true;
                break;
        }
    }
    
    /**
     * Saves the data of the datatype.
     * 
     * The default behavior is to simply store
     * the variable's string representation into the database. It returnes
     * one of the MIDCOM Datamanager's process_form() returncodes accordingly.
     * 
     * It will not synchronize the datatype's data with the widget. This has
     * to be done manually using sync_data_with_widget, which is what the
     * Datamanager core does when necessary.
     * 
     * This member should make no changes to the storage object in memory that cannot
     * be synchronized to the database; therfore it is imperative to call the update
     * method after changing the storage object itself to see whether the changes are
     * valid. if not, the in-memory object must be reverted.
     * 
     * @return int Any valid returncode from midcom_helper_datamanager::process_form().
     */
    function save_to_storage () 
    {
        // update $this->_storage and save parameters / attachments
        if (is_null ($this->_storage)) 
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The storage object is null, can't do anything.", MIDCOM_LOG_WARN);
            debug_pop();
            return MIDCOM_DATAMGR_FAILED;
        }
        
        switch ($this->_field["location"])
        {
            case "attachment":
                if (! mgd_save_var_as_attachment($this->_storage, $this->_value, "data_{$this->_field['name']}")) 
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add ("ERROR saving attachment \"" . $this->_field["name"] . "\"!", MIDCOM_LOG_ERROR);
                    $error = mgd_errstr();
                    $this->_datamanager->append_error(
                        sprintf($this->_datamanager->_l10n->get("failed saving field %s: %s") . "<br>\n",
                            $this->_field["description"],
                            $error));
                    debug_pop();
                    return MIDCOM_DATAMGR_FAILED;
                } 
                else 
                {
                    // Return Save Delayed for now to enforce an object update (callbacks)
                    return MIDCOM_DATAMGR_SAVE_DELAYED;
                }
            
            case "parameter":
                $this->_storage->parameter ("midcom.helper.datamanager", "data_{$this->_field['name']}", $this->_value);
                return MIDCOM_DATAMGR_SAVED;
                
            case "config":
                $this->_storage->parameter ($this->_field["config_domain"], $this->_field["config_key"], $this->_value); 
                return MIDCOM_DATAMGR_SAVED;
                
            default:
                $backup = $this->_storage->{$this->_field["location"]};
                $this->_storage->{$this->_field["location"]} = $this->_value;
                // Tell Datamanager to update once instead of updating each field individually
                return MIDCOM_DATAMGR_SAVE_DELAYED;
        }
    }
    
    /**
     * This member function will yield the default value of the widget.
     * 
     * It is called during load_from_storage() if and only if the data record
     * on disk is empty. By default it will return the schema key "default" 
     * of the corresponding field definition or a value returned by the helper
     * _get_empty_value() if the key is unset.
     * 
     * There should be no need in overriding this function in subclasses, use
     * _get_empty_value() instead.
     * 
     * @return mixed The default ("empty") value for the widget.
     * @access private
     * @see midcom_helper_datamanager_datatype::_get_empty_value();
     */
    function _get_default_value() 
    {
        if (array_key_exists("default", $this->_field))
        {
            return $this->_field["default"];
        }
        else
        {
            return $this->_get_empty_value();
        }
    }
    
    /**
     * Returns the empty value for the widget.
     * 
     * The calle will automatically revert to this if and only if no default
     * value has been specified in the schema.
     * 
     * This member should be overwritten in subclasses that do not use a simple
     * textual data representation upon storage.
     * 
     * @return mixed The empty value for the widget.
     * @access protected
     */
    function _get_empty_value() 
    {
        return "";
    }
    
    /**
     * Returns the Datatype's Value.
     * 
     * The exact representation of it depends on
     * the Datatype. The default behavior is to just pass the value of
     * the member _value to through to the caller.
     * 
     * This member should be overwritten if no simple textual datatypes
     * are in use.
     * 
     * @return mixed The value of the type.
     */
    function get_value () 
    {
        return $this->_value;
    }
    
    /**
     * This should return the representation of the current datatype in a CSV
     * environment, which essentially must be some kind of string.
     * 
     * It is up to the
     * datatypes what part of their internal representation they return here. It is
     * safe to return multiline-strings, quoting is handled by the datamanager here,
     * sou you just return the unencoded data.
     * 
     * This member should be overwritten if no simple textual datatypes
     * are in use.
     * 
     * @return string CSV-representation of the type.
     */
    function get_csv_data() 
    {
        return $this->_value;
    }
    
    /**
     * Replaces the internal storage object reference, used only from the datamanager
     * itself during the creation process after it has created a new, empty storage
     * object.
     * 
     * @param MidgardObject $storage The new storage object to use.
     * @access private
     */
    function _datamanager_set_storage (&$storage) 
    {
        $this->_storage =& $storage;
    }
    
    /**
     * This function must return the default value to be used when creating
     * the widget.
     * 
     * This defaults to get_value(), but certain datatyps might
     * need to overwrite this to avoid reimplementing the base class 
     * constructor.
     * 
     * @return mixed The value to initialize the widget with.
     * @access protected
     */    
    function _get_widget_default_value () 
    {
        return $this->get_value();
    }
    
    /**
     * Returns a reference of the Widget class associated to this datatype
     * instance. 
     * 
     * The default behavior should be fine in most cases, which returns the member
     * _widget.
     * 
     * @return midcom_helper_datamanager_widget The widget associated with this type.
     */
    function & get_widget() 
    {
        return $this->_widget;
    }
    
    /**
     * Synchronize the widget with the data in the datatype.
     * 
     * The default behavior will work with most simple types, as it just
     * passes the content of the member _value to the snippet's set_value method.
     */
    function sync_widget_with_data() 
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        $widget =& $this->get_widget();
        $widget->set_value($this->_value);
        debug_pop();
    }

    /**
     * Synchronize the data in the type with the widget.
     * 
     * The default behavior will work with most simple types, as it just
     * passes the content of the snippet as delivered by get_value to the
     * into the member _value.
     */
    function sync_data_with_widget() 
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
    	$widget =& $this->get_widget();
        $this->_value = $widget->get_value();
        debug_pop();
    }
    
    /**
     * Checks whether the widget is currently empty or not. 
     * 
     * This is used primarily 
     * during the required field check. The base class implementation is sufficient
     * for all text-based datatypes, as it checks against an empty string.
     * 
     * You should override this member everywhere you have non-textual datatypes or
     * special data representations.
     * 
     * @return bool Indicating if the value is empty.
     */
    function is_empty() 
    {
        return (trim($this->_value) == '');
    }
    
    
    /**
     * Validates the value of the type using HTML_QuickForm. 
     * 
     * As far as I see it right now, this function will work for all
     * simple types and does not need overriding there. Validation for
     * complex types like BLOBs will fail at the moment.
     * 
     * @todo Documentation incomplete (tarjei).
     */
    /* function validate - validate the value of a function wrt. to different rules.
     * @params 
     *         string field_description - comes from the schema.
     *         array validationrules - the different rules to apply.
     * Note: 
     * The ruleparams is an array of params related to each quickform rule. 
     * From the Quickform_RuleRegister->registerRule()  manual: 
     * @param     string    $ruleName   Name of validation rule
     * @param     string    $type       Either: 'regex', 'function' or null
     * @param     string    $data1      Name of function, regular expression or
     *                                  HTML_QuickForm_Rule object class name
     * @param     string    $data2      Object parent of above function or HTML_QuickForm_Rule file path
     * 
     */
    function validate ($field_description, $validation_rules) {
        require_once('HTML/QuickForm.php'); // needed for some globals. 

        //$this->_datamanager->append_error("Validating $field_description<br>\n");
        foreach ($validation_rules as $rule => $params ) {
            /* is this a selfmade rule? If so, you're responsible for making the class available youreself! */
            $object = null;
            $function = null; 
            if (array_key_exists('function',$params)) {
                if (array_key_exists('function',$params)) {
                    $function = $params['function'];
                    $type     = null;
                    if (array_key_exists('object',$params)) {
                       $object   = $params['object'];
                    }
                } 
                $this->_datamanager->_rule_registry->registerRule($field_description, $rule, $function, $object);
            } else { 
                /* check if the rule exists, and exit if not.*/
                $function = $GLOBALS['_HTML_QuickForm_registered_rules'][$rule][0];
                $object   = $GLOBALS['_HTML_QuickForm_registered_rules'][$rule][1]; 
                $type  = key($GLOBALS['_HTML_QuickForm_registered_rules'][$rule]);
            }

            if (!array_key_exists($rule, $GLOBALS['_HTML_QuickForm_registered_rules'])) {
                $msg = sprintf($this->_datamanager->_l10n->get("Nonexisting validation rule $rule used. Please check your schema.<br>"));
                $this->_datamanager->append_error($msg . "<br>");
                return false;
            }

            $options = null;
            if (array_key_exists('format',$params)) {
                $options = $params['format'];
            }

            if (!$this->_datamanager->_rule_registry->validate($rule, $this->_value, $options, false)) {
                $msg = sprintf($this->_datamanager->_l10n->get($params['message']));
                $this->_datamanager->append_error("<font color='red'>$field_description: $msg</font><br>\n");
                return false;
            }
        }
        return true;
    }

    /**
     * This function destroys the Widget so that the type can be destroyed
     * as well. This is usually only called by the Datamanager main class.
     */
    function destroy()
    {
        unset ($this->_widget);
    }
}


?>