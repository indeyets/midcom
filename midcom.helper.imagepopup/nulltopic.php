<?php
/**
 * Created on Mar 12, 2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
 
class midcom_helper_imagepopup_nulltopic {

    var $_parameters = array();

    function parameter( $domain, $name, $value = null)
    {
    
        if ($value !== null) {
            $this->_parameters[$domain][$name] = $value;
            return true;
        }
    
        if (array_key_exists($domain, $this->_parameters) && array_key_exists($name, $this->_parameters[$domain])  )
        {
            return $this->_parameters[$domain][$name];
        }
        
        return "";
    }

}
?>