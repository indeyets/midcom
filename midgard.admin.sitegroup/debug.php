<?php
/**
 * Created on 1/06/2006
 * @author tarjei huse
 * @package midgard.admin.sitegroup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 * Basic functions that are used bye the commandline functions for logging.
 *
 * The interfaces are the same as the midcom interfaces to make it possible
 * to use the same classes in a midcom.
 */

 /**
  * This class extends the normal midcom logging class so that
  * messages are echoed instead of being saved to a logfile
  * It is only for use by commandline utilities.
  *
  * @package midgard.admin.sitegroup
  *
  */
 class midcom_admin_sitegroup_debug extends midcom_debug {

    function midcom_admin_sitegroup_debug ()
    {
        $this->_current_prefix = "";
        $this->_prefixes = array();
        $this->_enabled = true;
        $this->_loglevel = MIDCOM_LOG_INFO;
        $this->_loglevels = array (
            MIDCOM_LOG_DEBUG => "debug",
            MIDCOM_LOG_INFO  => "info",
            MIDCOM_LOG_WARN  => "warn",
            MIDCOM_LOG_ERROR => "error",
            MIDCOM_LOG_CRIT  => "critical"
        );
    }

    function log($message, $loglevel = MIDCOM_LOG_DEBUG) {
        if ($loglevel < $this->_loglevel) {
            echo $message . "\n";
        }
    }

 }
 $GLOBALS["midcom_debugger"]

?>