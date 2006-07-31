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
class midcom_cron_tmpservice extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->dbclassloader->load_classes('midcom', 'core_classes.inc');
        require_once(MIDCOM_ROOT . '/midcom/core/temporary_object.php');

        $qb = midcom_core_temporary_object::new_query_builder();
        $qb->add_constraint('timestamp', '<', time() - $GLOBALS['midcom_config']['midcom_temporary_resource_timeout']);
        $result = $qb->execute();
        foreach ($result as $tmp)
        {
            if (! $tmp->delete())
            {
                // Print and log error
                $msg = "Failed to delete temporary object {$tmp->id}, last Midgard error was: " . mgd_errstr();
                $this->print_error($msg);
                debug_add($msg, MIDCOM_LOG_ERROR);
                debug_print_r('Tried to delete this object:', $tmp);
            }
            else
            {
                debug_add("Deleted temporary object {$tmp->id}.");
            }
        }
        debug_pop();
    }
}
?>