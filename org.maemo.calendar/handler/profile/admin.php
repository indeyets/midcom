<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_profile_admin extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_profile_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    function _handler_edit($handler_id, $args, &$data)
    {
        if ($handler_id == 'ajax-profile-edit')
        {
            $_MIDCOM->skip_page_style = true;
        }
        
        return true;        
    }
    
    function _show_edit($handler_id, &$data)
    {
        if ($handler_id == 'ajax-profile-edit')
        {
            midcom_show_style('profile-edit-ajax');
        }
        else
        {
            midcom_show_style('profile-edit');            
        }        
    }    
        
}

?>