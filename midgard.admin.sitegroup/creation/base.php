<?php
/**
 * Created on Feb 26, 2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
 
/**
 * Abstract creation class , defines the interface and common code.
 * 
 * @abstract
 * 
 */
class midgard_admin_sitegroup_creation_base 
{
    
    /**
     * The object containing the values that should be used
     * to create the object
     * @var object midgard.admin.sitegroup_creation_values
     */
    var $config = null;
    
    var $verbose = false;
    /**
     * Validation messages.
     */
    var $validation_messages = array();

    function midgard_admin_sitegroup_creation_base($config) 
    {
        $this->config = $config;
        $this->verbose = $config->get_value('verbose');
    }
    
    /**
     * The run method executes the core to create what should be created
     * @return boolean true on success.
     * @abstract
     *  
     */
    function run () 
    {
        return false;
    } 
    
    /**
     * All classes should have a validator method that must return true
     * for the class to run.
     * @return boolean 
     * @access public
     * @abstract
     * 
     */
    function validate () 
    {
        return false;
    }
    /**
     * helper that checks that all the configuration variables
     * has a value
     * @access protected
     */
    function validate_configuration_variables() {
        
        foreach (get_object_vars($this->config) as $name => $val)
        {
            if ($this->config->get_value($name) == null) {
                print "$name is missing value!\n";
                return false;
            }
        }
        return true;
    }
}

// do not end with normal midgard endings as this might provide whitespace where you do not want it.


