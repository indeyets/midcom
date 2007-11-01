<?php
/**
 * @package net.nehmer.account
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Karma Cronjob Handler
 *
 * - Invoked by daily by the MidCOM Cron Service
 * - Recalculates Karma for everybody
 *
 * @package net.nehmer.account
 */
class net_nehmer_account_cron_karma extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $calculator = new net_nehmer_account_calculator();

        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '<>', 'admin');
        $qb->add_order('metadata.score', 'DESC');
        $persons = $qb->execute();
        
        foreach ($persons as $person)
        {
            if (substr($person->firstname, 0, 7) == 'DELETE ')
            {
                continue;
            }
        
            $karmas = $calculator->calculate_person($person, true);
            debug_add("{$person->name} got Karma of {$karmas['karma']}.");
        }

        debug_pop();
    }
}
?>