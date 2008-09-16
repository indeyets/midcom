<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_datatype_privilege extends midcom_helper_datamanager_datatype
{

    var $_object = null;
    var $_privilege = 'midgard:read';
    var $_assignee = null;
    var $_classname = null;
    var $_privilege_object = null;

    function __construct(&$datamanager, &$storage, $field)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!array_key_exists('privilege_object', $field))
        {
            $field['privilege_object'] = $this->_object;
        }
        else
        {
            $this->_object = $field['privilege_object'];
        }

        if (!array_key_exists('privilege', $field))
        {
            $field['privilege'] = $this->_privilege;
        }
        else
        {
            $this->_privilege = $field['privilege'];
        }

        if (!array_key_exists('privilege_assignee', $field))
        {
            $field['privilege_assignee'] = $this->_assignee;
        }
        else
        {
            $this->_assignee = $field['privilege_assignee'];
        }

        if (!array_key_exists('privilege_classname', $field))
        {
            $field['privilege_classname'] = $this->_classname;
        }
        else
        {
            $this->_classname = $field['privilege_classname'];
        }

        // Require privilege editing from current user
        // TODO: Would it be better to just hide this particular DM field if the user is not allowed to set the priv?
        $_MIDCOM->auth->require_do('midgard:privileges', $this->_object);

        $field['widget'] = 'config_radiobox';


        // Can_do works only for users, not groups
        $inherit_label = 'Inherit';
        if ($this->_assignee)
        {
            debug_add("This privilege is a regular privilege");
            $assignee_object = $_MIDCOM->auth->get_assignee($this->_assignee);
            if (is_a($assignee_object, 'midcom_core_user'))
            {
                // Check if we have some value set
                $this->load_from_storage();

                if (   $this->_value == ''
                    || $this->_value == MIDCOM_PRIVILEGE_INHERIT)
                {
                    debug_add("The assignee is a user, and the value is inherited. Checking for the inherited value");
                    // OK, no specific value set, we can get "inherited" value with can_do
                    if ($_MIDCOM->auth->can_do($this->_privilege, $this->_object, $this->_assignee))
                    {
                        $inherit_label = 'Inherited (Allow)';
                    }
                    else
                    {
                        $inherit_label = 'Inherited (Deny)';
                    }
                }
            }
        }
        elseif (   $this->_classname
                && is_a($this->_object, 'midcom_baseclasses_database_person')
                && class_exists($this->_classname))
        {
            debug_add("This privilege is a class-based privilege");
            // Check if we have some value set
            $this->load_from_storage();

            if (   $this->_value == ''
                || $this->_value == MIDCOM_PRIVILEGE_INHERIT)
            {
                // OK, no specific value set, we can get "inherited" value with can_do
                debug_add("The assignee of the class-based privilege is a user, and the value is inherited. Checking for the inherited value");
                $user = $_MIDCOM->auth->get_user($this->_object->id);
                //TODO Bergie: The inherit deny flags are misleading since the can_user_do does not check the full chain from groups for example...
                if ($_MIDCOM->auth->can_user_do($this->_privilege, $user, $this->_assignee['identifier']))
                {
                    $inherit_label = 'Inherited (Allow)';
                }
                else
                {
                    $inherit_label = 'Inherited (Deny)';
                }
            }
        }

        // TODO: display the inherited value
        $field['widget_radiobox_choices'] = array(
            '' => $inherit_label,
            MIDCOM_PRIVILEGE_ALLOW => 'Allow',
            MIDCOM_PRIVILEGE_DENY => 'Deny',
        );
        parent::_constructor($datamanager, $storage, $field);
        debug_pop();
    }

    function load_from_storage()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   is_object($this->_object)
            && method_exists($this->_object, 'get_privileges'))
        {
            debug_add("Searching or privilege with name '{$this->_privilege}' AND (assignee '{$this->_assignee}' OR classname '{$this->_classname}')");
            $privileges = $this->_object->get_privileges();
            if ($privileges)
            {
                foreach ($privileges as $privilege)
                {
                    if (   $privilege->name == $this->_privilege
                        && $this->_assignee
                        && $privilege->assignee == $this->_assignee)
                    {
                        debug_add("Privilege was set to this assignee: {$privilege->assignee}");
                        debug_pop();
                        $this->_privilege_object = $privilege;
                        $this->_value = $this->_privilege_object->value;
                        return true;
                    }
                    elseif (   $privilege->name == $this->_privilege
                        && $this->_classname
                        && $privilege->classname == $this->_classname)
                    {
                        debug_add("Class-type privilege was set to this class name: {$privilege->classname}");
                        debug_pop();
                        $this->_privilege_object = $privilege;
                        $this->_value = $this->_privilege_object->value;
                        return true;
                    }
                }
            }

            debug_add("Privilege not found from target object, resorting to empty value");
            debug_pop();
            $this->_value = $this->_get_default_value();
            return true;
        }
        else
        {
            debug_add("This is not a MidCOM object");
            debug_pop();
            return false;
        }
    }

    function save_to_storage()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($this->_value != '')
        {
            if ($this->_classname)
            {
                debug_add("calling this->_object->set_privilege({$this->_privilege}, 'SELF', {$this->_value}, {$this->_classname})");
                $this->_object->set_privilege($this->_privilege, 'SELF', $this->_value, $this->_classname);
            }
            elseif ($this->_assignee)
            {
                debug_add("calling this->_object->set_privilege({$this->_privilege}, {$this->_assignee}, {$this->_value})");
                $this->_object->set_privilege($this->_privilege, $this->_assignee, $this->_value);
            }
        }
        else
        {
            // Set the value to INHERIT
            if ($this->_classname)
            {
                debug_add("calling this->_object->unset_privilege({$this->_privilege}, 'SELF', {$this->_classname})");
                $this->_object->unset_privilege($this->_privilege, 'SELF', $this->_classname);
            }
            elseif ($this->_assignee)
            {
                debug_add("calling this->_object->unset_privilege({$this->_privilege}, {$this->_assignee})");
                $this->_object->unset_privilege($this->_privilege, $this->_assignee);
            }
        }

        debug_pop();
        return true;
    }

    function _get_default_value()
    {
        // TODO: The parent fails for some reason
        return '';
    }
}
?>