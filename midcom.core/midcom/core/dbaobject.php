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
class midcom_core_dbaobject extends midcom_baseclasses_core_object
{
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
                $mgdschemaclass = $this->__new_class_name__;
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
        return midcom_baseclasses_core_dbobject::create($this->__object);
    }
    public function create_attachment($name, $title, $mimetype) 
    {
        return midcom_baseclasses_core_dbobject::create_attachment($this->__object, $name, $title, $mimetype);
    }
    public function create_new_privilege_object($privilege, $assignee = null, $value = MIDCOM_PRIVILEGE_ALLOW, $classname = '') 
    {
        return midcom_baseclasses_core_dbobject::create_new_privilege_object($this->__object, $privilege, $assignee, $value, $classname);
    }
    public function delete()
    {
         return midcom_baseclasses_core_dbobject::delete($this->__object);
    }
    public function delete_attachment($name) { return midcom_baseclasses_core_dbobject::delete_attachment($this->__object, $name); }
    public function delete_parameter($domain, $name) { return midcom_baseclasses_core_dbobject::delete_parameter($this->__object, $domain, $name); }
    public function delete_tree() { return midcom_baseclasses_core_dbobject::delete_tree($this->__object); }
    public function get_attachment($name) { return midcom_baseclasses_core_dbobject::get_attachment($this->__object, $name); }
    public function get_attachment_qb() { return midcom_baseclasses_core_dbobject::get_attachment_qb($this->__object); }
    public function get_by_guid($guid) { return midcom_baseclasses_core_dbobject::get_by_guid($this, $guid->__object); }
    public function get_by_id($id) { return midcom_baseclasses_core_dbobject::get_by_id($this->__object, $id); }
    public function get_by_path($path) { return midcom_baseclasses_core_dbobject::get_by_path($this->__object, $path); }  
    public function & get_metadata() { return midcom_baseclasses_core_dbobject::get_metadata($this->__object); }
    public function get_parameter($domain, $name) { return midcom_baseclasses_core_dbobject::get_parameter($this, $domain, $name); }
    public function get_parent() { return midcom_baseclasses_core_dbobject::get_parent($this->__object); }
    public function get_parent_guid() { return midcom_baseclasses_core_dbobject::get_parent_guid($this->__object); }
    public function get_privilege($privilege, $assignee, $classname = '') { return midcom_baseclasses_core_dbobject::get_privilege($this->__object, $privilege, $assignee, $classname); }
    public function get_privileges() { return midcom_baseclasses_core_dbobject::get_privileges($this->__object); }
    public function is_object_visible_onsite() { return midcom_baseclasses_core_dbobject::is_object_visible_onsite($this->__object); }
    public function is_owner($person = null) { return midcom_baseclasses_core_dbobject::is_owner($this->__object, $person); }
    public function list_attachments() { return midcom_baseclasses_core_dbobject::list_attachments($this->__object); }
    public function list_parameters($domain = null) { return midcom_baseclasses_core_dbobject::list_parameters($this->__object, $domain); }
    public function refresh() { return midcom_baseclasses_core_dbobject::refresh($this); }
    public function set_parameter($domain, $name, $value) { return midcom_baseclasses_core_dbobject::set_parameter($this, $domain, $name, $value); }
    public function set_privilege($privilege, $assignee = null, $value = MIDCOM_PRIVILEGE_ALLOW, $classname = '') { return midcom_baseclasses_core_dbobject::set_privilege($this, $privilege, $assignee, $value, $classname); }
    public function unset_privilege($privilege, $assignee = null, $classname = '') { return midcom_baseclasses_core_dbobject::unset_privilege($this, $privilege, $assignee, $classname); }
    public function unset_all_privileges() { return midcom_baseclasses_core_dbobject::unset_all_privileges($this); }
    public function update() { return midcom_baseclasses_core_dbobject::update($this); }

    // Legacy API
    // TODO: Get rid of these
    function guid() { return $this->__object->guid; }
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
    public function can_do($privilege, $user = null) { return $_MIDCOM->auth->can_do($privilege, $this->__object, $user); }
    public function can_user_do($privilege, $user = null) { return $_MIDCOM->auth->can_user_do($privilege, $user, '{$this->_class_definition["midcom_class_name"]}'); }
    public function require_do($privilege, $message = null) { $_MIDCOM->auth->require_do($privilege, $this->__object, $message); }
    public function require_user_do($privilege, $message = null) { $_MIDCOM->auth->require_user_do($privilege, $message, '{$this->_class_definition["midcom_class_name"]}'); }

    // DBA API
    public function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array(),
            'ANONYMOUS' => Array(),
            'USERS' => Array()
        );
    }
}
?>