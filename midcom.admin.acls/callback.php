<?php
/**
 * Created on Mar 3, 2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * 
 * 
 */
 
class midcom_admin_acls_callback {

    /**
     * Reference to the type
     */
    var $type = null;


    var $privs = array();

    function get_rows() 
    {
        if ($this->type->storage->object == null) 
        {
            
            return array();
        }
        else 
        {
            $users = array (null, 'EVERYONE');
            $usernames = array( 'you', 'everyone' );
            
            foreach ($users as $key => $userid) 
            {
                //$privs = $_MIDCOM->auth->get_all_privileges($this->type->storage->object, $special);
                $this->privs[$key] = $this->_get_priviledges_for_user($usernames[$key], $userid);
            }
            
            return $this->privs; 
        }
            
    }

    function _get_priviledges_for_user ($name, $uid) 
    {
        
        return array (
                    0 => "$name",
                    1 => $_MIDCOM->auth->can_do('midgard:read', $this->type->object, $uid),
                    2 => $_MIDCOM->auth->can_do('midgard:update',$this->type->object, $uid),
                    3 => $_MIDCOM->auth->can_do('midgard:delete',$this->type->object, $uid),
                    4 => $_MIDCOM->auth->can_do('midgard:create', $this->type->object,$uid),
                );
    }

    function set_rows($rows) 
    {}

    function set_type (&$type)
    {
        $this->type =& $type;
    }

}
?>