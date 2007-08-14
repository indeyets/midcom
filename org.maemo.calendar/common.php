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
    
    function fetch_user_calendar_color($user_guid=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $logged_in = true;
        
        $user = $_MIDCOM->auth->user->get_storage();
        
        if ($user_guid)
        {
            $logged_in = false;
            $user =& new midcom_db_person($user_guid);
        }
        
        $color = $user->get_parameter('org.maemo.calendar:preferences','calendar_color');
        
        if (empty($color))
        {
            if (   $logged_in
                || (   !$logged_in
                    && $_MIDCOM->auth->request_sudo()) )
            {
                $user->set_parameter('org.maemo.calendar:preferences','calendar_color','ce8e4b');
                $color = $user->get_parameter('org.maemo.calendar:preferences','calendar_color');
                
                if (!$logged_in)
                {
                    $_MIDCOM->auth->drop_sudo();
                }
            }
            else
            {
                debug_add('Couldn\'t get SUDO privileges!');
            }            
        }
        
        debug_print_r("User calendar color: ",$color);        
        debug_pop();

        return $color;
    }
    
    function update_user_calendar_color($color, $user_guid=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $logged_in = true;
        
        $user = $_MIDCOM->auth->user->get_storage();
        
        if ($user_guid)
        {
            $logged_in = false;
            $user =& new midcom_db_person($user_guid);
        }

        if (   $logged_in
            || (   !$logged_in
                && $_MIDCOM->auth->request_sudo()) )
        {
            $user->set_parameter('org.maemo.calendar:preferences','calendar_color',$color);
            
            if (!$logged_in)
            {
                $_MIDCOM->auth->drop_sudo();
            }
        }
        else
        {
            debug_add('Couldn\'t get SUDO privileges!');
        }

        debug_pop();                
        return true;
    }
    
    function save_user_tag($tag_id, $data, $user_guid=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->require_valid_user();
        
        debug_print_r('save with: ', $data);
        
        $logged_in = true;
        
        $user =& $_MIDCOM->auth->user->get_storage();
        
        if ($user_guid)
        {
            $logged_in = false;
            $user =& new midcom_db_person($user_guid);
        }
        
        if (empty($tag_id))
        {
            $tag_id = org_maemo_calendar_common::_create_tag_id(&$user,$data['name']);
            debug_add("no tag_id was specified. created id {$tag_id}.");
        }
        
        if (   $logged_in
            || (   !$logged_in
                && $_MIDCOM->auth->request_sudo()) )
        {            
            $existing = $user->get_parameter('org.maemo.calendar:tag',$tag_id);
        
            if (empty($existing))
            {
                debug_add("Tag {$tag_id} doesn't exist. Create.");
            }
            else
            {   
                if (   empty($data['color'])
                    || empty($data['name']) )
                    // || !isset($data['ispublic']) )
                {
                    debug_add("All required data wasn't available. quitting");
                    return false;
                }
            }
            
            if (isset($data['color']))
            {
                $user->set_parameter('org.maemo.calendar:tag',$tag_id,$data['color']);                
            }
            if (isset($data['name']))
            {
                $user->set_parameter('org.maemo.calendar:tag_name',$tag_id,$data['name']);
            }
            // if ($data['ispublic'])
            // {
            //     $user->set_parameter('org.maemo.calendar:public_tag',$tag_id,true);
            // }
            // else
            // {
            //     $user->set_parameter('org.maemo.calendar:public_tag',$tag_id,'');
            // }

            if (!$logged_in)
            {
                $_MIDCOM->auth->drop_sudo();
            }
        }
        else
        {
            debug_add('Couldn\'t get SUDO privileges! Tags not added.');
        }
             
        debug_pop();
        return true;
    }
    
    function remove_user_tag($tag_id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);        
        $_MIDCOM->auth->require_valid_user();
        
        $user->set_parameter('org.maemo.calendar:tag',$tag_id,$data['color']);
        
        $existing = $user->get_parameter('org.maemo.calendar:tag',$tag_id);
    
        if (empty($existing))
        {
            debug_add("Tag {$tag_id} doesn't exist.");
            debug_pop();
            return false;
        }
        
        $user->set_parameter('org.maemo.calendar:tag',$tag_id,'');
        $user->set_parameter('org.maemo.calendar:tag_name',$tag_id,'');
                
        debug_pop();
        return true;
    }
    
    function fetch_available_user_tags($user_guid=false) //, $only_public=false
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
        
        //debug_print_r("User {$user_guid}: ",$user);
                
        /* Read users tags */
        $users_tags = $user->list_parameters('org.maemo.calendar:tag');
        
        // if (empty($users_tags))
        // {
        //     debug_add("No tags defined! Creating the default tag to users parameters.");
        //     
        //     if (   $logged_in
        //         || (   !$logged_in
        //             && $_MIDCOM->auth->request_sudo()) )
        //     {
        //         $_l10n =& $_MIDCOM->i18n->get_l10n('org.maemo.calendar');
        //         $tag_name = $_l10n->get($this->_config->get('default_tag_name'));
        //         $tag_id = org_maemo_calendar_common::_create_tag_id(&$user,$tag_name);
        //         
        //         $user->set_parameter('org.maemo.calendar:tag',$tag_id,'FFFF99');
        //         $user->set_parameter('org.maemo.calendar:tag_name',$tag_id,$tag_name);
        //         $users_tags = $user->list_parameters('org.maemo.calendar:tag');
        //         
        //         if (!$logged_in)
        //         {
        //             $_MIDCOM->auth->drop_sudo();
        //         }
        //     }
        //     else
        //     {
        //         debug_add('Couldn\'t get SUDO privileges! Tags not added.');
        //     }
        // }
        
        foreach ($users_tags as $tag_id => $color)
        {
            // $is_public = org_maemo_calendar_common::is_tag_public(&$user, $tag_id);
            // if ($only_public)
            // {
            //     if ($is_public)
            //     {
            //         $tags[] = array( 'name' => org_maemo_calendar_common::tag_identifier_to_name(&$user, $tag_id),
            //                          'id' => $tag_id,
            //                          'is_public' => $is_public,
            //                          'color' => $color );                    
            //     }
            // }
            // else
            // {
                $tags[] = array( 'name' => org_maemo_calendar_common::tag_identifier_to_name(&$user, $tag_id),
                                 'id' => $tag_id,
//                                 'is_public' => $is_public,
                                 'color' => $color );               
            // }
        }
        
        debug_print_r("Found tags: ",$tags);
        
        debug_pop();
        return $tags;
    }
    
    // function is_tag_public(&$user, $tag_id)
    // {
    //     if (! is_object($user))
    //     {
    //         $user &= $_MIDCOM->auth->user->get_storage();
    //     }
    //     
    //     $is_public = $user->get_parameter('org.maemo.calendar:public_tag',$tag_id);
    //     
    //     if ($is_public)
    //     {
    //         return true;
    //     }
    //     
    //     return false;
    // }

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
    
    function _create_tag_id(&$user,$tag_name)
    {
        return $user->id . '_' . midcom_generate_urlname_from_string($tag_name, "_");
    }
    
    function get_user_timezone($user_guid=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $logged_in = true;
        $user = $_MIDCOM->auth->user->get_storage();
        
        if ($user_guid)
        {
            $logged_in = false;
            $user =& new midcom_db_person($user_guid);
        }
        
        $user_timezone_identifier = $user->get_parameter('org.maemo.calendar:preferences','timezone_identifier');
        if (empty($user_timezone_identifier))
        {
            debug_add("No timezone defined! Adding the default timezone to users parameters.");
            
            // if (   $logged_in
            //     || (   !$logged_in
            //         && $_MIDCOM->auth->request_sudo()) )
            // {
            if ($_MIDCOM->auth->request_sudo())
            {
                $default_timezone_name = date_default_timezone_get();
                $user->set_parameter('org.maemo.calendar:preferences','timezone_identifier',$default_timezone_name);
                $user_timezone_identifier = $user->get_parameter('org.maemo.calendar:preferences','timezone_identifier');
                
                // if (!$logged_in)
                //                 {
                    $_MIDCOM->auth->drop_sudo();
                // }
            }
            else
            {
                debug_add('Couldn\'t get SUDO privileges! Timezone not added.');
            }
        }
        
        $timezone = timezone_open($user_timezone_identifier);
        
        debug_pop();
        
        return $timezone;
    }
    
    function active_timezone($timezone_identifier=false,$update=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        debug_add("Called with parameters timezone_identifier:{$timezone_identifier}, update={$update}");
        
        $identifier = false;
        if ($timezone_identifier)
        {
            $identifier = $timezone_identifier;
            $update = true;
        }
        
        if (! $identifier)
        {
            $session =& new midcom_service_session('org.maemo.calendarpanel');
            if ($session->exists('active_timezone'))
            {
                $identifier = $session->get('active_timezone');                
            }
            else
            {
                $user_timezone = org_maemo_calendar_common::get_user_timezone();
                $identifier = timezone_name_get($user_timezone);
                $update = true;
            }
            unset($session);
        }
        
        if ($update)
        {
            $session =& new midcom_service_session('org.maemo.calendarpanel');
            $session->set('active_timezone',$identifier);
            unset($session);            
        }
        
        debug_add("Ended up using timezone identifier: {$identifier}");
        
        $timezone = timezone_open($identifier);
        
        debug_pop();
        
        return $timezone;
    }
    
    function render_timezone_list($selected_zone,$use_groups=false) {
        $structure = '';
        $timezone_identifiers = timezone_identifiers_list();
        $i = 0;
        $current_continent = false;
        foreach ($timezone_identifiers as $zone) {
            $zone = explode('/',$zone);
            if (isset($zone[1]))
            {
                $zones[$i]['continent'] = $zone[0];
                $zones[$i]['city'] = $zone[1];
                $i++;                        
            }
        }
        asort($zones);
        foreach ($zones as $zone) {
            extract($zone);
            if (   $continent == 'Africa'
                || $continent == 'America'
                || $continent == 'Antarctica'
                || $continent == 'Arctic'
                || $continent == 'Asia'
                || $continent == 'Atlantic'
                || $continent == 'Australia'
                || $continent == 'Europe'
                || $continent == 'Indian'
                || $continent == 'Pacific')
            {
                if (   !$current_continent
                    && $use_groups)
                {
                    $structure .= "<optgroup label=\"{$continent}\">\n"; // continent                            
                }
                elseif (   $current_continent != $continent
                        && $use_groups)
                {
                    $structure .= "</optgroup>\n<optgroup label=\"{$continent}\">\n"; // continent
                }
                
                $selected = "";
                if ($city != '')
                {
                    $value = "{$continent}/{$city}";
                    $text = str_replace('_',' ',$city);
                    if (!$use_groups)
                    {
                        $text = str_replace('_',' ',$continent) . '/' . str_replace('_',' ',$city);
                    }
                    
                    if ($value == $selected_zone)
                    {
                        $selected = "selected=\"selected\" ";
                    }
                    $structure .= "<option {$selected} value=\"{$value}\">{$text}</option>\n"; //Timezone
                }
                else
                {
                    if ($continent == $selected_zone)
                    {
                        $selected = "selected=\"selected\" ";
                    }
                    $structure .= "<option {$selected} value=\"{$continent}\">{$continent}</option>\n"; //Timezone                            
                }
                
                $current_continent = $continent;
            }
        }
        if ($use_groups)
        {
            $structure .= "</optgroup>\n";            
        }
        
        return $structure;
    }    
        
}

?>