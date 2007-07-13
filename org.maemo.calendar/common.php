<?php
/**
 * @package org.maemo.calendar 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.maemo.calendar
 */
class org_maemo_calendar_common
{   
    function org_maemo_calendar_common()
    {
    }
    
    function fetch_available_user_tags($user_guid=false, $only_public=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $tags = array();
        $logged_in = true;
        
        $user = $_MIDCOM->auth->user->get_storage();
        
        if ($user_guid)
        {
            $logged_in = false;
            $user =& new midcom_db_person($user_guid);
        }
        
        debug_print_r("User {$user_guid}: ",$user);
        
        /* Read users tags */
        $users_tags = $user->list_parameters('org.maemo.calendar:tag');
        
        if (empty($users_tags))
        {
            debug_add("No tags defined! Creating the default tag to users parameters.");
            
            if (   $logged_in
                || (   !$logged_in
                    && $_MIDCOM->auth->request_sudo()) )
            {
                $user->set_parameter('org.maemo.calendar:tag','default','FFFF99');
                $user->set_parameter('org.maemo.calendar:tag_name','default','default');
                $users_tags = $user->list_parameters('org.maemo.calendar:tag');
                
                if (!$logged_in)
                {
                    $_MIDCOM->auth->drop_sudo();
                }
            }
            else
            {
                debug_add('Couldn\'t get SUDO privileges! Tags not added.');
            }
        }
        
        foreach ($users_tags as $tag_id => $color)
        {
            $is_public = org_maemo_calendar_common::is_tag_public(&$user, $tag_id);
            if ($only_public)
            {
                if ($is_public)
                {
                    $tags[] = array( 'name' => org_maemo_calendar_common::tag_identifier_to_name(&$user, $tag_id),
                                     'id' => $tag_id,
                                     'is_public' => $is_public,
                                     'color' => $color );                    
                }
            }
            else
            {
                $tags[] = array( 'name' => org_maemo_calendar_common::tag_identifier_to_name(&$user, $tag_id),
                                 'id' => $tag_id,
                                 'is_public' => $is_public,
                                 'color' => $color );               
            }
        }
        
        debug_print_r("Found tags: ",$tags);
        
        debug_pop();
        return $tags;
    }
    
    function is_tag_public(&$user, $tag_id)
    {
        if (! is_object($user))
        {
            $user &= $_MIDCOM->auth->user->get_storage();
        }
        
        $is_public = $user->get_parameter('org.maemo.calendar:public_tag',$tag_id);
        
        if ($is_public)
        {
            return true;
        }
        
        return false;
    }

    function tag_identifier_to_name(&$user, $tag_id)
    {
        if (! is_object($user))
        {
            $user &= $_MIDCOM->auth->user->get_storage();
        }
        
        $tag_name = $user->get_parameter('org.maemo.calendar:tag_name',$tag_id);
        
        if (empty($tag_name))
        {
            $tag_name = 'Default';
        }
        
        return $tag_name;
    }
    
    function get_users_tags($as_key_value_pairs=false)
    {
        $available_tags = org_maemo_calendar_common::fetch_available_user_tags();
        
        if ($as_key_value_pairs)
        {
            $key_val_pairs = array();
            
            foreach ($available_tags as $k => $tag_data)
            {
                $key_val_pairs[$tag_data['id']] = $tag_data['name'];
            }
            
            return $key_val_pairs;
        }
        
        return $available_tags;
    }   
        
}

?>