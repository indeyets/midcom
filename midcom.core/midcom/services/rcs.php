<?php
/**
 * Created on 31/07/2006
 * @author tarjei huse
 * @package midcom.services.rcs
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 * The RCS service gives a write only interface to different services wanting to save changes to objects.
 * 
 * The RCS service will try to initialize the backend based on GNU RCS, but, if that fails, fall back 
 * to the nullrcs handler. The nullrcs handler does not save anything at all. 
 */
/**
 * On startup the class will call _probe_rcs that checks if the rcs prerequisites 
 * exists and (if they do) save the config.
 * 
 * <b>Configurationparameters that are in use by this service:
 * * string midcom_services_rcs_bin_dir - the prefix for the rcs utilities (normally /usr/bin)
 * * string midcom_services_rcs_root - the directory where the rcs files get placed.
 * * boolean midcom_services_rcs_use - if set, midcom will fail hard if the rcs service is not operational. 
 * 
 */
require 'rcs/backend.php';
require 'rcs/config.php';
class midcom_services_rcs extends midcom_baseclasses_core_object {
    
    /**
     * The handler that rcs uses to save an object.
     */
    var $_handler = null;
    /**
     * An instance of midcom
     */
    var $_midcom = null;
    /**
     * Constructor 
     * @param $config the midcom_config array
     * @param $midcom midcom_application reference.
     */
    function midcom_services_rcs  ($config, &$midcom) 
    {
        parent::midcom_baseclasses_core_object();
        $this->_config = new midcom_services_rcs_config($config);
        $this->_midcom = $midcom;
    }
    /**
     * Loads the handler  
     */
    function _load_handler() 
    {
        if ($this->_handler === NULL) 
        { 
            $this->_handler = $this->config->get_handler(&$this->_midcom);
        }
    }
    
    function update (&$object, $message) {
        $this->_load_handler();
        return $this->_handler->update(&$object, $message);
    } 
    
    /**
     * This function first checks for for different prerequisites and
     * if they exists, returns an array of the prereqs
     * 
     * This function is not in use. MidCOM rcs should be properly configured
     * to work.
     * 
     * It is kept here more for historical purposes and will be removed.
     * 
     * @deprecated 2.6 - 25/08/2006  
     * @return array 
     *  
     */
    function _probe_rcs_config() {
        $set = array();
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add ("RCS interface: checking for /AegirCore/config/config");
        
        // we like config in midcom best!
        if (array_key_exists('midcom_rcs_root',$this->_config) ) 
        {
            $set['rcsroot'] = $this->_config['midcom_rcs_root'];
            
        } elseif (mgd_snippet_exists("/AegirCore/config/config")) 
        {
            
          debug_add ("RCS interface: Including /AegirCore/config/config");
          mgd_include_snippet_php("/AegirCore/config/config");
        } elseif (!isset($set) || !is_array($set) || !array_key_exists("rcsroot", $set)) 
        {
            debug_add("NemeinRCS interface: Aegir rcsroot not set by Aegir, going to default");
            if (   $_MIDGARD['config']['prefix'] == '/usr'
                || $_MIDGARD['config']['prefix'] == '/usr/local')
            {
                $set['rcsroot'] = '/var/lib/midgard/rcs';
            }
            else
            {
                $set['rcsroot'] = "{$_MIDGARD['config']['prefix']}/var/lib/midgard/rcs";
            }
        }
        
        if (!array_key_exists('rcsroot', $set)) 
        {
            $_MIDCOM->generate_error('RCSROOT SHOULD BE SET AT THIS POINT!', MIDCOM_ERRCRIT);
        }
        
        if ($this->_check_config($set)) {
            return $set;
        } elseif ($this->_config['midcom_use_rcs']) {
            $_MIDCOM->generate_error('Error in rcs configuration. Please check the log for details.', MIDCOM_ERRCRIT);
        }
        
        return $set;
    }
    
}


?>