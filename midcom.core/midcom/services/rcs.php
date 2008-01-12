<?php
/**
 * Created on 31/07/2006
 *
 * The RCS service gives a write only interface to different services wanting to save changes to objects.
 *
 * The RCS service will try to initialize the backend based on GNU RCS, but, if that fails, fall back
 * to the nullrcs handler. The nullrcs handler does not save anything at all.
 *
 * @author tarjei huse
 * @package midcom.services.rcs
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On startup the class will call _probe_rcs that checks if the rcs prerequisites
 * exists and (if they do) save the config.
 *
 * <b>Configuration parameters that are in use by this service:</b>
 * * string midcom_services_rcs_bin_dir - the prefix for the rcs utilities (normally /usr/bin)
 * * string midcom_services_rcs_root - the directory where the rcs files get placed.
 * * boolean midcom_services_rcs_enable - if set, midcom will fail hard if the rcs service is not operational.
 *
 */
require 'rcs/backend.php';
require 'rcs/config.php';

/**
 * @package midcom.services.rcs
 */
class midcom_services_rcs extends midcom_baseclasses_core_object
{

    /**
     * Array of handlers that rcs uses to manage object versioning.
     */
    var $_handlers = Array();

    /**
     * The configuration object for the rcs service.
     * @var midcom_services_rcs_config
     */
    var $config;

    /**
     * Constructor
     * @param array $config the midcom_config
     * @param midcom_application $midcom midcom_application reference.
     */
    function midcom_services_rcs($config)
    {
        parent::midcom_baseclasses_core_object();
        $this->config = new midcom_services_rcs_config($config);
    }

    /**
     * Loads the handler
     */
    function load_handler(&$object)
    {
        if (!$object->guid)
        {
            return false;
        }

        if (!array_key_exists($object->guid, $this->_handlers))
        {
            $this->_handlers[$object->guid] = $this->config->get_handler($object);
        }

        return $this->_handlers[$object->guid];
    }

    /**
     * Create or update the RCS file for the object.
     * @param object &$object the midgard object to be saved
     * @param string $message the update message to save (optional)
     */
    function update(&$object, $message = null)
    {
        $handler = $this->load_handler($object);
        if (   !is_object($handler)
            || !method_exists($handler, 'update'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not load handler!');
            debug_pop();
            return false;
        }
        if (   !$handler->update($object, $message)
            && $this->config->use_rcs())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('RCS: Could not save file!');
            debug_pop();
            return false;
        }
        return true;
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
    function _probe_rcs_config()
    {
        $set = array();
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add ("RCS interface: checking for /AegirCore/config/config");

        // we like config in midcom best!
        if (array_key_exists('midcom_rcs_root',$this->config) )
        {
            $set['rcsroot'] = $this->config['midcom_rcs_root'];

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
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'RCS root directory must be set.');
        }

        if ($this->_check_config($set))
        {
            return $set;
        }
        elseif ($this->_config['midcom_services_rcs_enable'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Error in rcs configuration. Please check the log for details.');
        }

        return $set;
    }

}


?>
