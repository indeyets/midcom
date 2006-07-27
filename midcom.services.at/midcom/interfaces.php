<?php
/**
 * @package midcom.services.at
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * At service library, this interface class is used to register jobs to the service.
 * 
 * <b>Job registration</b>
 *
 * First load this library, either via autoload_libraries or using  $_MIDCOM->componentloader->load('midcom.services.at')
 * Then call midcom_services_at_interface::register with the following parameters
 *
 * - <i>int start</i> timestamp on/after which the job is run (we have approximately
 *   one minute resolution).
 * - <i>string component</i> name of the component whichs interface class is used to run
 *   the jobs.
 * - <i>string method</i> the name of the method to cal to run the job, the method must return
 *   strict true or an error (including the return value) will be displayed and logged.
 *   The method can use the $handler->display_error() method to display better error messages.
 * - <i>array args</i> contains the arguments used by the method above, passed on to the method
 *   as an array.
 * 
 * Example method (from org.openpsa.directmarketing):
 * <code>
 *  function at_test($args, &$handler)
 *  {
 *      debug_push_class(__CLASS__, __FUNCTION__);
 *      $message = "got args:\n===\n" . sprint_r($args) . "===\n";
 *      debug_add($message);
 *      $handler->print_error($message);
 *      debug_pop();
 *      return true;
 *  }
 * </code>
 *
 * Example job registration (for the method above):
 * <code>
 * midcom_services_at_interface::register(
 *   time()+120,
 *   'org.openpsa.directmarketing',
 *   'at_test',
 *   array
 *   (
 *      'foo' => 'bar',
 *   ));
 * </code>
 *
 * @package midcom.services.at
 */
class midcom_services_at_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor defines constants the  library uses and loads required classes
     */
    function midcom_services_at_interface()
    {
        parent::midcom_baseclasses_components_interface();

        define ('MIDCOM_SERVICES_AT_STATUS_SCHEDULED', 100);
        define ('MIDCOM_SERVICES_AT_STATUS_RUNNING', 110);
        define ('MIDCOM_SERVICES_AT_STATUS_FAILED', 120);

        $this->_component = 'midcom.services.at';
        $this->_purecode = true;
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = Array('entry.php');
    }
    
    
    
    /**
     * Registers a job to the AT service.
     *
     * @param int $start Timestamp after which the job is run
     * @param string $component The name of the component which should run the job
     * @param string $method The method in interface class to call to run the job
     * @param array $args Arguments array for the method
     * @return bool Indicating success/failure in registering the job
     */
    function register($start, $component, $method, $args)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $entry = new midcom_services_at_entry();
        $entry->start = $start;
        $entry->component = $component;
        $entry->method = $method;
        $entry->arguments = $args;
        //mgd_debug_start();
        $ret = $entry->create();
        //mgd_debug_stop();
        debug_pop();
        return $ret;
    }
}
?>