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
class midcom_cron_purgedeleted extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        if (   !class_exists('midgard_query_builder')
            || !($dummy_qb = new midgard_query_builder('midgard_topic'))
            || !method_exists($dummy_qb, 'include_deleted'))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Midgard 1.8.1+ feature set required", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        return true;
    }

    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called!');
        $cut_off = mktime(23, 59, 59, date('n'), date('j')-$$GLOBALS['midcom_config']['cron_pure_deleted_after'], date('Y'));
        foreach ($_MIDGARD['schema']['types'] as $mgdschema => $dummy)
        {
            debug_add("Processing class {$mgdschema}");
            $qb = new midgard_query_builder($mgdschema);
            $qb->add_constraint('metadata.deleted', '<>', 0);
            $qb->add_constraint('metadata.revised', '<', gmdate('Y-m-d H:i:s', $cut_off));
            $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
            $qb->include_deleted();
            $objects = $qb->execute();
            unset($qb);
            if (!is_array($objects))
            {
                debug_add("QB failed fatally on class {$mgdschema}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }
            $found = count($objects);
            $purged = 0;
            foreach ($objects as $obj)
            {
                if (!$obj->purge())
                {
                    debug_add("Failed to purge {$mgdschema} {$obj->guid}, deleted: {$obj->metadata->deleted},  revised: {$obj->metadata->revised}. errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_print_r('Failed object', $obj);
                    continue;
                }
                $purged++;
            }
            if ($found > 0)
            {
                debug_add("Found {$found} {$mgdschema} objects, purged {$purged}", MIDCOM_LOG_INFO);
            }
            else
            {
                debug_add("No {$mgdschema} objects deleted before " . gmdate('Y-m-d H:i:s', $cut_off) . "UTC (in sitegroup {$_MIDGARD['sitegroup']}) found");
            }
        }

        debug_pop();
    }
}
?>