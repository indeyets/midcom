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
    function __construct($id = null)
    {
        return parent::__construct($id);
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
    
    function _on_updated()
    {
        return $this->_update_option_cache();
    }

    function _on_created()
    {
        return $this->_update_option_cache();
    }
    
    function _on_deleted()
    {
        return $this->_update_option_cache();
    }
    
    /**
     * Update the cached 'votes' attribute of the option
     */
    function _update_option_cache()
    {
        if (!$_MIDCOM->auth->request_sudo('net.nemein.discussion'))
        {
            return true;
        }
        
        $votes_qb = new midgard_query_builder('net_nemein_quickpoll_vote');
        $votes_qb->add_constraint('article', '=', $this->article);
        $votes_qb->add_constraint('selectedoption', '=', $this->selectedoption);
        $votes = $votes_qb->count();
        
        $option = new net_nemein_quickpoll_option_dba($this->selectedoption);
        if ($option->votes != $votes)
        {
            $option->votes = $votes;
            $option->update();
        }
        
        $_MIDCOM->auth->drop_sudo();
        return true;
    }
}
?>