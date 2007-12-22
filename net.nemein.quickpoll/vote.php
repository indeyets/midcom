<?php
/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_vote_dba extends __net_nemein_quickpoll_vote_dba
{
    function net_nemein_quickpoll_vote_dba($id = null)
    {
        return parent::__net_nemein_quickpoll_vote_dba($id);
    }

    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        if ($this->user)
        {
            return $this->user;
        }
        return $this->ip;
    }
 }
?>