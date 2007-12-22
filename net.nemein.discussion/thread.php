<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM DBA access to threads
 *
 * @package net.nemein.discussion
 */
 class net_nemein_discussion_thread_dba extends __net_nemein_discussion_thread_dba
{
    function net_nemein_discussion_thread_dba($id = null)
    {
        return parent::__net_nemein_discussion_thread_dba($id);
    }

    function _on_updating()
    {
        $qb = net_nemein_discussion_thread_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->node);
        $qb->add_constraint('posts', '>', 0);
        $qb->add_constraint('name', '=', $this->name);
        $qb->add_constraint('id', '<>', $this->id);
        $result = $qb->execute();
        if (count($result) > 0)
        {
            // There is already a thread with this URL name
            return false;
        }

        return true;
    }
}
?>
