<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:querybuilder.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM DBA baseclass for MgdSchema object decorators..
 *
 * @package midcom
 */
abstract class midcom_core_dbaobject extends midcom_baseclasses_core_object
{
    public $__object = null;
    public $__metadata = null;

    public function __construct($id = null)
    {
        if (is_object($id))
        {
            $this->__object = $id;
        }
        else
        {
            try
            {
                $mgdschemaclass = $this->__mgdschema_class_name__;
                $this->__object = new $mgdschemaclass($id);
            }
            catch (midgard_error_exception $e)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Constructing object ' . $id . ' failed, reason: ' . $e->getMessage(), MIDCOM_LOG_INFO);
                debug_pop();
                return;
            }
        }
          
        if (   $this->__object->guid
            && mgd_is_guid($this->__object->guid))
        {
            midcom_baseclasses_core_dbobject::post_db_load_checks($this);
        }
    }
    
    // Magic getter and setter for object property mapping
    public function __get($property) 
    { 
        if (!is_object($this->__object)) 
        {
            return null;
        }
        
        if ($property == 'metadata')
        {
            if (is_null($this->__metadata))
            {
                $this->__metadata = $this->get_metadata();
            }
            return $this->__metadata;
        }
        
        if (   substr($property, 0, 2) == '__'
            && $property != '__guid')
        {
            // API change safety
            if ($property == '__new_class_name__')
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Deprecated property __new_class_name__ used with object of type {$this->__mgdschema_class_name__}", MIDCOM_LOG_WARN);
                debug_pop();
                
                $property = '__mgdschema_class_name__';
            }

            return $this->$property;
        }
        
        return $this->__object->$property; 
    }
    public function __set($property, $value) 
    {
        return $this->__object->$property = $value;
    }
    public function __isset($property) 
    {
        return isset($this->__object->$property); 
    }

    // Main API
    public function create() 
    {
        return midcom_baseclasses_core_dbobject::create($this);
    }
    public function create_attachment($name, $title, $mimetype) 
    {
        return midcom_baseclasses_core_dbobject::create_attachment($this, $name, $title, $mimetype);
    }
    public function create_new_privilege_object($privilege, $assignee = null, $value = MIDCOM_PRIVILEGE_ALLOW, $classname = '') 
    {
        return midcom_baseclasses_core_dbobject::create_new_privilege_object($this, $privilege, $assignee, $value, $classname);
    }
    public function delete()
    {
         return midcom_baseclasses_core_dbobject::delete($this);
    }
    public function delete_attachment($name) 
    {
        return midcom_baseclasses_core_dbobject::delete_attachment($this, $name);
    }
    public function delete_parameter($domain, $name)
    {
        return midcom_baseclasses_core_dbobject::delete_parameter($this, $domain, $name);
    }
    public function delete_tree() {
        return midcom_baseclasses_core_dbobject::delete_tree($this);
    }
    public function get_attachment($name)
    {
        return midcom_baseclasses_core_dbobject::get_attachment($this, $name);
    }
    public function get_attachment_qb()
    {
        return midcom_baseclasses_core_dbobject::get_attachment_qb($this);
    }
    public function get_by_guid($guid) 
    { 
        return midcom_baseclasses_core_dbobject::get_by_guid($this, $guid);
    }
    public function get_by_id($id)
    {
        return midcom_baseclasses_core_dbobject::get_by_id($this, $id);
    }
    public function get_by_path($path)
    {
        return midcom_baseclasses_core_dbobject::get_by_path($this, $path);
    }  
    public function & get_metadata() 
    {
        return midcom_helper_metadata::retrieve($this);
    }
    public function get_parameter($domain, $name) 
    {
        return midcom_baseclasses_core_dbobject::get_parameter($this, $domain, $name);
    }
    public function get_parent()
    {
        return midcom_baseclasses_core_dbobject::get_parent($this);
    }
    public function get_parent_guid()
    { 
        return midcom_baseclasses_core_dbobject::get_parent_guid($this);
    }
    public function get_privilege($privilege, $assignee, $classname = '')
    {
        return midcom_baseclasses_core_dbobject::get_privilege($this, $privilege, $assignee, $classname);
    }
    public function get_privileges() {
        return midcom_baseclasses_core_dbobject::get_privileges($this);
    }
    public function is_object_visible_onsite()
    {
        return midcom_baseclasses_core_dbobject::is_object_visible_onsite($this->__object);
    }
    public function list_attachments()
    {
        return midcom_baseclasses_core_dbobject::list_attachments($this);
    }
    public function list_parameters($domain = null)
    {
        return midcom_baseclasses_core_dbobject::list_parameters($this, $domain);
    }
    public function refresh()
    {
        return midcom_baseclasses_core_dbobject::refresh($this);
    }
    public function set_parameter($domain, $name, $value)
    {
        return midcom_baseclasses_core_dbobject::set_parameter($this, $domain, $name, $value);
    }
    public function set_privilege($privilege, $assignee = null, $value = MIDCOM_PRIVILEGE_ALLOW, $classname = '')
    {
        return midcom_baseclasses_core_dbobject::set_privilege($this, $privilege, $assignee, $value, $classname);
    }
    public function unset_privilege($privilege, $assignee = null, $classname = '')
    {
        return midcom_baseclasses_core_dbobject::unset_privilege($this, $privilege, $assignee, $classname);
    }
    public function unset_all_privileges()
    {
        return midcom_baseclasses_core_dbobject::unset_all_privileges($this);
    }
    public function update()
    {
        return midcom_baseclasses_core_dbobject::update($this);
    }
    public function is_locked()
    {
        return $this->metadata->is_locked();
    }
    public function lock()
    {
        if ($this->__object->is_locked())
        {
            return true;
        }
        return $this->__object->lock();
    }
    public function unlock()
    {
        if (!$this->__object->is_locked())
        {
            return true;
        }
        return $this->__object->unlock();
    }
    public function get_properties()
    {
        if (!$this->__object)
        {
            $classname = $this->__mgdschema_class_name__;
            $this->__object = new $classname();
        }
        return array_keys(get_object_vars($this->__object));
    }

    // Legacy API
    // TODO: Get rid of these
    function guid() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Deprecated method guid() used with object of type {$this->__mgdschema_class_name__}", MIDCOM_LOG_WARN);
        debug_pop();
        return $this->__object->guid; 
    }
    function parameter($domain, $name)
    {
        if (func_num_args() == 2)
        {
            return $this->get_parameter($domain, $name);
        }
        else
        {
            $value = func_get_arg(2);
            if (   $value === false
                || $value === null
                || $value === '')
            {
                return $this->delete_parameter($domain, $name);
            }
            else
            {
                return $this->set_parameter($domain, $name, $value);
            }
        }
    }
    function _parent_parameter($domain, $name)
    {
        if (func_num_args() == 2)
        {
            return $this->__object->parameter($domain, $name);
        }
        else
        {
            $value = func_get_arg(2);
            return $this->__object->parameter($domain, $name, $value);
        }
    }

    // ACL Shortcuts
    public function can_do($privilege, $user = null)
    {
        return $_MIDCOM->auth->can_do($privilege, $this->__object, $user);
    }
    public function can_user_do($privilege, $user = null)
    {
        return $_MIDCOM->auth->can_user_do($privilege, $user, $this->__midcom_class_name__);
    }
    public function require_do($privilege, $message = null)
    {
        $_MIDCOM->auth->require_do($privilege, $this->__object, $message);
    }
    public function require_user_do($privilege, $message = null)
    {
        $_MIDCOM->auth->require_user_do($privilege, $message, $this->__midcom_class_name__);
    }

    // DBA API
    public function get_class_magic_default_privileges()
    {
        return array
        (
            'EVERYONE' => array(),
            'ANONYMOUS' => array(),
            'USERS' => array()
        );
    }

    // Event handlers
    function _on_created() {}
    function _on_creating() { return true; }
    function _on_deleted() {}
    function _on_deleting() { return true; }
    function _on_loaded() { return true; }
    function _on_prepare_exec_query_builder(&$qb) { return true; }
    function _on_prepare_new_query_builder(&$qb) {}
    function _on_process_query_result(&$result) {}
    function _on_prepare_new_collector(&$mc) {}
    function _on_prepare_exec_collector(&$mc) { return true; }
    function _on_process_collector_result(&$result) {}
    function _on_updated() {}
    function _on_updating() { return true; }
    function _on_imported() {}
    function _on_importing() { return true; }

    // Exec handlers
    public function __exec_create() { return @$this->__object->create(); }
    public function __exec_update() { return @$this->__object->update(); }
    public function __exec_delete() { return @$this->__object->delete(); }
    public function __exec_get_by_id($id) { return $this->__object->get_by_id($id); }
    public function __exec_get_by_guid($guid) { return $this->__object->get_by_guid($guid); }
    public function __exec_get_by_path($path) { return $this->__object->get_by_path($path); }

    // functions related to the RCS service.
    var $_use_rcs = true;
    var $_rcs_message = false;
    public function disable_rcs() { $this->_use_rcs = false; }
    public function enable_rcs() { $this->_use_rcs  = true; }
    public function set_rcs_message($msg) { $this->_rcs_message = $msg; }
    public function get_rcs_message() { return $this->_rcs_message; }
}
?>