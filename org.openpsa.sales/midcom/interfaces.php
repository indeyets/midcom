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
            'admin.php',
            'navigation.php',
            'salesproject.php',
            'salesproject_member.php',
            'salesproject_deliverable.php',
        );
        $this->_autoload_libraries = array(
            'org.openpsa.core', 
            'org.openpsa.helpers',
            'midcom.helper.datamanager',
            'midcom.helper.datamanager2',
            'midcom.services.at',
            'org.openpsa.contactwidget',
            'org.openpsa.relatedto',
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
                $qb = new MidgardQueryBuilder('org_openpsa_salesproject_member');
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
                            //Only process one task once (someone might be both resource and contact for example)
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
                break;
                //TODO: groups ? other objects ?
        }
        debug_pop();
        return;
    }

    /**
     * AT handler for handling subscription cycles.
     * @param array $args handler arguments
     * @param object $handler reference to the cron_handler object calling this method.
     * @return bool indicating success/failure
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