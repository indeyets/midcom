<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:tmpservice.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 *
 * @package midcom.services
 */
class midcom_cron_loginservice extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called!');
        $_MIDCOM->dbclassloader->load_classes('midcom', 'core_classes.inc');

        $qb = new midgard_query_builder('midcom_core_login_session_db');
        $qb->add_constraint('timestamp', '<', time() - $GLOBALS['midcom_config']['auth_login_session_timeout']);
        $result = $qb->execute();
        //debug_print_r('$result', $result);
        foreach ($result as $tmp)
        {
            if (! $tmp->delete())
            {
                // Print and log error
                $msg = "Failed to delete login session {$tmp->id}, last Midgard error was: " . mgd_errstr();
                $this->print_error($msg);
                debug_add($msg, MIDCOM_LOG_ERROR);
                debug_print_r('Tried to delete this object:', $tmp);
            }
            else
            {
                if (method_exists($tmp, 'purge'))
                {
                    $tmp->purge();
                }
                debug_add("Deleted login session {$tmp->id}.");
            }
        }
        debug_pop();
    }
}
?>