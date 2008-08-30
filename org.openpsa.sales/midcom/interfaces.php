<?php
/**
 * @package org.openpsa.sales
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: interfaces.php,v 1.5 2006/07/17 14:57:13 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA direct marketing and mass mailing component
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_sales_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.sales';
        $this->_autoload_files = array(
            'sort_helper.php',
            'viewer.php',
        );
        $this->_autoload_libraries = array(
            'org.openpsa.core',
            'org.openpsa.helpers',
            'midcom.helper.datamanager',
            'midcom.helper.datamanager2',
            'midcom.services.at',
            'org.openpsa.contactwidget',
            'org.openpsa.relatedto',
            'net.nemein.tag',
        );
    }

    function _on_initialize()
    {
        // Load needed data classes
        $_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.products');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.projects');
        $_MIDCOM->componentloader->load_graceful('org.openpsa.invoices');

        //TODO: Check that the loads actually succeeded

        //org.openpsa.sales object types
        define('ORG_OPENPSA_OBTYPE_SALESPROJECT', 10000);
        define('ORG_OPENPSA_OBTYPE_SALESPROJECT_MEMBER', 10500);
        //org.openpsa.sales salesproject statuses
        define('ORG_OPENPSA_SALESPROJECTSTATUS_LOST', 11000);
        define('ORG_OPENPSA_SALESPROJECTSTATUS_CANCELED', 11001);
        define('ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE', 11050);
        define('ORG_OPENPSA_SALESPROJECTSTATUS_WON', 11100);
        //org.openpsa.sales salesproject deliverable statuses
        define('ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_NEW', 100);
        define('ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_PROPOSED', 200);
        define('ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DECLINED', 300);
        define('ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_ORDERED', 400);
        define('ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_STARTED', 450);
        define('ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_DELIVERED', 500);
        define('ORG_OPENPSA_SALESPROJECT_DELIVERABLE_STATUS_INVOICED', 600);

        return true;
    }


    function _on_resolve_permalink($topic, $config, $guid)
    {
        $task = new org_openpsa_sales_salesproject($guid);
        if (!$task)
        {
            return null;
        }

        return "salesproject/{$task->guid}/";
    }

    /**
     * Used by org_openpsa_relatedto_suspect::find_links_object to find "related to" information
     *
     * Currently handles persons
     */
    function org_openpsa_relatedto_find_suspects($object, $defaults, &$links_array)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !is_array($links_array)
            || !is_object($object))
        {
            debug_add('$links_array is not array or $object is not object, make sure you call this correctly', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }

        switch(true)
        {
            case is_a($object, 'midcom_baseclasses_database_person'):
                //Fall-trough intentional
            case is_a($object, 'midcom_org_openpsa_person'):
                //List all projects and tasks given person is involved with
                $this->_org_openpsa_relatedto_find_suspects_person($object, $defaults, $links_array);
                break;
            case is_a($object, 'midcom_baseclasses_database_event'):
            case is_a($object, 'midcom_org_openpsa_event'):
                $this->_org_openpsa_relatedto_find_suspects_event($object, $defaults, $links_array);
                break;
                //TODO: groups ? other objects ?
        }
        debug_pop();
        return;
    }

      /**
     * Used by org_openpsa_relatedto_find_suspects to in case the givcen object is a person
     *
     * Current rule: all participants of event must be either manager,contact or resource in task
     * that overlaps in time with the event.
     */
    function _org_openpsa_relatedto_find_suspects_event(&$object, &$defaults, &$links_array)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called');
        if (   !is_array($object->participants)
            || count($object->participants) < 2)
        {
            //We have invalid list or less than two participants, abort
            debug_pop();
            return;
        }
        $qb = new midgard_query_builder('org_openpsa_salesproject_member');
        // Target sales project starts or ends inside given events window or starts before and ends after
        $qb->begin_group('OR');
            $qb->begin_group('AND');
                $qb->add_constraint('salesproject.start', '>=', $object->start);
                $qb->add_constraint('salesproject.start', '<=', $object->end);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('salesproject.end', '<=', $object->end);
                $qb->add_constraint('salesproject.end', '>=', $object->start);
            $qb->end_group();
            $qb->begin_group('AND');
                $qb->add_constraint('salesproject.start', '<=', $object->start);
                $qb->begin_group('OR');
                    $qb->add_constraint('salesproject.end', '>=', $object->end);
                    $qb->add_constraint('salesproject.end', '=', 0);
                $qb->end_group();
            $qb->end_group();
        $qb->end_group();
        //Target sales project is active
        $qb->add_constraint('salesproject.status', '=', ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE);
        //Each event participant is either manager or member (resource/contact) in task
        foreach ($object->participants as $pid => $bool)
        {
            $qb->begin_group('OR');
                $qb->add_constraint('salesproject.owner', '=', $pid);
                $qb->add_constraint('person', '=', $pid);
            $qb->end_group();
        }
        //mgd_debug_start();
        $qbret = @$qb->execute();
        //mgd_debug_stop();
        if (!is_array($qbret))
        {
            debug_add('QB returned with error, aborting, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        $seen_tasks = array();
        foreach ($qbret as $resource)
        {
            debug_add("processing resource #{$resource->id}");
            if (isset($seen_tasks[$resource->salesproject]))
            {
                //Only process one task once (someone might be both owner and contact for example)
                continue;
            }
            $seen_tasks[$resource->salesproject] = true;
            $to_array = array('other_obj' => false, 'link' => false);
            $task = new org_openpsa_sales_salesproject($resource->salesproject);
            $link = new org_openpsa_relatedto_relatedto();
            org_openpsa_relatedto_suspect::defaults_helper($link, $defaults, $this->_component, $task);
            $to_array['other_obj'] = $task;
            $to_array['link'] = $link;

            $links_array[] = $to_array;
        }
        debug_add('done');
        debug_pop();
        return;
    }

    /**
     * Used by org_openpsa_relatedto_find_suspects to in case the givcen object is a person
     */
    function _org_openpsa_relatedto_find_suspects_person(&$object, &$defaults, &$links_array)
    {
        $qb = new midgard_query_builder('org_openpsa_salesproject_member');
        $qb->add_constraint('person', '=', $object->id);
        $qb->add_constraint('salesproject.status', '=', ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE);
        $qbret = @$qb->execute();
        $seen_sp = array();
        if (is_array($qbret))
        {
            foreach ($qbret as $member)
            {
                debug_add("processing resource #{$resource->id}");
                if (isset($seen_sp[$member->salesproject]))
                {
                    //Only process one salesproject once (someone might be both resource and contact for example)
                    continue;
                }
                $seen_sp[$resource->salesproject] = true;
                $to_array = array('other_obj' => false, 'link' => false);
                $sp = new org_openpsa_sales_salesproject($member->salesproject);
                $link = new org_openpsa_relatedto_relatedto();
                org_openpsa_relatedto_suspect::defaults_helper($link, $defaults, $this->_component, $sp);
                $to_array['other_obj'] = $sp;
                $to_array['link'] = $link;

                $links_array[] = $to_array;
            }
        }
        $qb2 = org_openpsa_sales_salesproject::new_query_builder();
        $qb2->add_constraint('owner', '=', $object->id);
        $qb2->add_constraint('status', '=', ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE);
        $qb2ret = @$qb2->execute();
        if (is_array($qb2ret))
        {
            foreach ($qb2ret as $sp)
            {
                debug_add("processing salesproject #{$sp->id}");
                if (isset($seen_sp[$sp->id]))
                {
                    //Only process one task once (someone might be both resource and contact for example)
                    continue;
                }
                $seen_sp[$sp->id] = true;
                $to_array = array('other_obj' => false, 'link' => false);
                $link = new org_openpsa_relatedto_relatedto();
                org_openpsa_relatedto_suspect::defaults_helper($link, $defaults, $this->_component, $sp);
                $to_array['other_obj'] = $sp;
                $to_array['link'] = $link;

                $links_array[] = $to_array;
            }
        }
    }

    /**
     * AT handler for handling subscription cycles.
     * @param array $args handler arguments
     * @param object &$handler reference to the cron_handler object calling this method.
     * @return boolean indicating success/failure
     */
    function new_subscription_cycle($args, &$handler)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !isset($args['deliverable'])
            || !isset($args['cycle']))
        {
            $msg = 'deliverable GUID or cycle number not set, aborting';
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }

        $deliverable = new org_openpsa_sales_salesproject_deliverable($args['deliverable']);
        if (!$deliverable)
        {
            $msg = "Deliverable {$args['deliverable']} not found, error " . mgd_errstr();
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }

        return $deliverable->new_subscription_cycle($args['cycle']);
    }
}
?>