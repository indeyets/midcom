<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

require_once(MIDCOM_ROOT . '/net/nehmer/account/handler/publish.php');

/**
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_profile_publish extends net_nehmer_account_handler_publish
{
    /**
     * The schema database (taken from the config)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;
    
    /**
     * The schema (taken from the config)
     *
     * @var Array
     * @access private
     */
    var $_schema = null;    

    /**
     * The Datamanager of the person to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    var $_form_submitted = false;

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_profile_publish()
    {
        parent::net_nehmer_account_handler_publish();
    }
    
    function _prepare_request_data()
    {
        $this->_request_data['submitted'] = $this->_form_submitted;
        parent::_prepare_request_data();
    }
    
    /**
     * Internal helper function, prepares a datamanager based on the current account.
     */
    function _prepare_datamanager()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database( $this->_config->get('profile_schemadb') );
        $this->_schema = $this->_config->get('profile_schema');
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        $this->_datamanager->autoset_storage($this->_account);
        $this->_datamanager->set_schema($this->_schema);
        foreach ($this->_datamanager->schema->field_order as $name)
        {
            if (! array_key_exists('visible_mode', $this->_datamanager->schema->fields[$name]['customdata']))
            {
                $this->_datamanager->schema->fields[$name]['customdata']['visible_mode'] = 'user';
            }
        }
    }    
    
    function _handler_publish($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ($handler_id == 'ajax-profile-publish')
        {
            $_MIDCOM->skip_page_style = true;
        }        
        
        parent::_handler_publish($handler_id, $args, &$data);
        
        debug_pop();
        return true;
    }
    
    function _handler_publish_ok($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ($handler_id == 'ajax-profile-publish-ok')
        {
            $_MIDCOM->skip_page_style = true;
        }        
        
        parent::_handler_publish_ok($handler_id, $args, &$data);
        
        debug_pop();
        return true;
    }    

    /**
     * This function processes the form, computing the visible field list for the current
     * selection. If no form submission can be found, the method exits unconditionally.
     *
     * The online state privilege is set according to the field's presence in the request data.
     * Default is not to show online state when publishing, in case the field is missing.
     */
    function _process_form()
    {   
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (array_key_exists('net_nehmer_account_publish_delete_avatar', $_REQUEST))
        {
            // We ignore errors at this point. Access control has been verified, if
            // we delete non existant attachments, we're fine as well.
            $this->_account->delete_attachment('avatar');
            $this->_account->delete_attachment('avatar_thumbnail');
            $this->_avatar = null;
            $this->_avatar_thumbnail = null;
            
            $this->_form_submitted = true;
            
            debug_pop();
            return;
        }

        if (! array_key_exists('net_nehmer_account_publish', $_REQUEST))
        {
            debug_pop();
            return;
        }

        $this->_form_submitted = true;
        
        $this->_process_image_upload();
        
        $published_fields = Array();
        foreach ($this->_datamanager->schema->fields as $name => $field)
        {
            if (   array_key_exists($name, $_REQUEST)
                && $_REQUEST[$name] == 'on')
            {
                $published_fields[] = $name;
            }
        }

        // Update online state field.
        if (array_key_exists('onlinestate', $_REQUEST))
        {
            $this->_account->set_privilege('midcom:isonline', 'USERS', MIDCOM_PRIVILEGE_ALLOW);
        }
        else
        {
            $this->_account->set_privilege('midcom:isonline', 'USERS', MIDCOM_PRIVILEGE_DENY);
        }


        $this->_account->set_parameter('net.nehmer.account', 'visible_field_list', implode(',', $published_fields));
        $this->_account->set_parameter('net.nehmer.account', 'revised', time());
        $this->_account->set_parameter('net.nehmer.account', 'published', time());
        $this->_account->delete_parameter('net.nehmer.account', 'auto_published');
        
        debug_pop();
        $_MIDCOM->relocate('ajax/profile/publish/ok/');
    }
    
    function _show_publish($handler_id, &$data)
    {
        $style_type = '';
        
        if ($handler_id == 'ajax-profile-publish')
        {
            $style_type = '-ajax';
        }
        
        midcom_show_style("profile-publish{$style_type}-start");
        foreach($this->_fields as $name => $field)
        {
            if ($field['has_linkers'])
            {
                // First go over the linked fields:
                $first_field = true;
                $this->_request_data['total_fields'] = count($field['linkers']) + 1;
                $this->_request_data['linked_field'] = $field;
                $this->_request_data['linked_mode'] = true;
                foreach ($field['linkers'] as $linker)
                {
                    $this->_request_data['current_field'] =& $linker;
                    if ($first_field)
                    {
                        midcom_show_style("profile-publish{$style_type}-field-linked-start");
                    }
                    else
                    {
                        midcom_show_style("profile-publish{$style_type}-field-linked-next");
                    }
                }
                $this->_request_data['current_field'] =& $this->_fields[$name];
                midcom_show_style("profile-publish{$style_type}-field-linked-next");
            }
            else
            {
                $this->_request_data['linked_mode'] = false;
                $this->_request_data['current_field'] =& $this->_fields[$name];
                midcom_show_style("profile-publish{$style_type}-field");
            }

        }
        midcom_show_style("profile-publish{$style_type}-end");
    }

    function _show_publish_ok($handler_id, &$data)
    {
        if ($handler_id == 'ajax-profile-publish-ok')
        {
            midcom_show_style('profile-publish-ajax-ok');
        }
        else
        {
            midcom_show_style('profile-publish-ok');            
        }    
    }

}

?>