<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an Buddylist handler class for org.maemo.calendar
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package org.maemo.calendar
 */

class org_maemo_calendar_handler_buddylist_admin extends midcom_baseclasses_components_handler 
{
    
    var $_return_string = '';
    
    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_buddylist_admin()
    {
        parent::midcom_baseclasses_components_handler();
        $_MIDCOM->cache->content->no_cache();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
        $this->current_user = $_MIDCOM->auth->user->get_storage();
    }
    
    function _handler_search($handler_id, $args, &$data)
    {
        if ($handler_id == 'ajax-buddylist-search')
        {
            $_MIDCOM->skip_page_style = true;            
        }
        
        return true;
    }
    
    function _handler_add($handler_id, $args, &$data)
    {
        if ($handler_id == 'ajax-buddylist-add')
        {
            $_MIDCOM->skip_page_style = true;            
        }
        
        $user =& $_MIDCOM->auth->user->get_storage();
        $user->require_do('midgard:create');
        
        $target = new midcom_db_person($args[0]);
        if (!$target)
        {
            return false;
        }
        
        $this->_return_string = 'added';
        
        $this->_add_person_as_buddy($target);
        
        return true;        
    }
    
    function _handler_remove($handler_id, $args, &$data)
    {
        if ($handler_id == 'ajax-buddylist-remove')
        {
            $_MIDCOM->skip_page_style = true;            
        }
        
        $user =& $_MIDCOM->auth->user->get_storage();
        $user->require_do('midgard:create');

        $target = new midcom_db_person($args[0]);
        if (!$target)
        {
            return false;
        }

        // Check we're buddies already
        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        $qb->add_constraint('buddy', '=', $target->guid);
        $buddies = $qb->execute();
        if (count($buddies) == 0)
        {
            return false;
        }

        foreach ($buddies as $buddy)
        {
            if (! $buddy->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to add buddy, reason ".mgd_errstr());
                // This will exit
            }
        }
        
        $this->_return_string = 'deleted';
        
        return true;
    }
    
    function _handler_action($handler_id, $args, &$data)
    {
        if (strpos($handler_id, 'ajax-'))
        {
            $_MIDCOM->skip_page_style = true;            
        }
        
        switch ($args[0])
        {
            case 'approve':
                $user = $_MIDCOM->auth->user->get_storage();
                $qb = net_nehmer_buddylist_entry::new_query_builder();
                $qb->add_constraint('account', '=', $args[1]);
                $qb->add_constraint('buddy', '=', $user->guid);
                $qb->add_constraint('isapproved', '=', false);
                $buddy_qb = $qb->execute();
                if (count($buddy_qb) == 0)
                {
                    return false;
                }
                $buddy = $buddy_qb[0];
                $buddy->approve();
                $this->_return_string = 'approved';
                $this->_add_person_as_buddy($buddy->get_account_user(), true);
                break;
            case 'deny':
                $user = $_MIDCOM->auth->user->get_storage();
                $qb = net_nehmer_buddylist_entry::new_query_builder();
                $qb->add_constraint('account', '=', $args[1]);
                $qb->add_constraint('buddy', '=', $user->guid);
                $qb->add_constraint('isapproved', '=', false);
                $buddy_qb = $qb->execute();
                if (count($buddy_qb) == 0)
                {
                    return false;
                }
                $buddy = $buddy_qb[0];
                $buddy->reject();
                $this->_return_string = 'denied';
                break;
        }
        
        return true;
    }
    
    function _add_person_as_buddy(&$person, $auto_approve=false)
    {
        $user =& $_MIDCOM->auth->user->get_storage();
        $user->require_do('midgard:create');    
        
        if (! net_nehmer_buddylist_entry::is_on_buddy_list($person))
        {
            $buddy = new net_nehmer_buddylist_entry();
            $buddy->account = $user->guid;
            $buddy->buddy = $person->guid;
            if (!$buddy->create())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to add buddy, reason ".mgd_errstr());
                // This will exit
            }

            if ($auto_approve)
            {
                if (! $_MIDCOM->auth->request_sudo($this->_component))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add('Failed to auto-approve the buddy request, could not acquire sudo.',
                        MIDCOM_LOG_ERROR);
                    debug_pop();                    
                    return;
                }

                $buddy->approve();

                $_MIDCOM->auth->drop_sudo();
            }
            
            $this->_return_string = 'added_new';
        }
    }
    
    function _show_search($handler_id, &$data)
    {
        midcom_show_style('buddylist-search');
    }
    
    function _show_add($handler_id, &$data)
    {
        if ($handler_id != 'ajax-buddylist-add')
        {        
            midcom_show_style('buddylist-add');
        }
        else
        {
            echo $this->_return_string;
        }
    }
    
    function _show_remove($handler_id, &$data)
    {
        if ($handler_id != 'ajax-buddylist-remove')
        {        
            midcom_show_style('buddylist-remove');
        }
        else
        {
            echo $this->_return_string;
        }
    }
    
    function _show_action($handler_id, &$data)
    {
        echo $this->_return_string;
    }
}

?>