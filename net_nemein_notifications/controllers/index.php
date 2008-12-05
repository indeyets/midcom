<?php
/**
 * @package net_nemein_notifications
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Basic controller
 *
 * @package net_nemein_notifications
 */
class net_nemein_notifications_controllers_index
{
    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    public function action_index($route_id, &$data, $args)
    {        
        // $notifier = new net_nemein_notifications_notifier('e857294ea89a11dbb67e7f95d175e24ee24e');
        // $notifier->send_mail(array(
        //     'title' => 'Testing....'
        // ));
        
        $qb = new org_openpsa_qbpager_pager('net_nemein_notifications_notification');
        $qb->add_constraint('recipient', '=', 2);
        $qb->add_order('metadata.published', 'DESC');
        
        $qb->results_per_page = 10;
        
        $data['notifications'] = $qb->execute();        
        $data['previousnext'] = $qb->get_previousnext();
    }
}
?>