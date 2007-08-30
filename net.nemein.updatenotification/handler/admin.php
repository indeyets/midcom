<?php
/**
 * @package net.nemein.updatenotification
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * 
 * @package net.nemein.updatenotification
 */
class net_nemein_updatenotification_handler_admin  extends midcom_baseclasses_components_handler 
{

    /**
     * Simple default constructor.
     */
    function net_nemein_updatenotification_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
    }
    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_save ($handler_id, $args, &$data)
    {
        $this->_request_data['name']  = "net.nemein.updatenotification";
        $this->_request_data['display_text_before_form'] = $this->_config->get('display_text_before_form');
        $this->_request_data['view_title'] = $this->_topic->extra;
        $this->_request_data['display_root_topic'] = $this->_config->get('display_root_topic');
        $this->_request_data['display_levels'] = $this->_config->get('display_levels');
        $this->_request_data['nap'] = new midcom_helper_nav();
        $this->_request_data['levels_shown'] = 0;

        $this->_update_breadcrumb_line($handler_id);
        
        $title = $this->_l10n_midcom->get('index');

        $_MIDCOM->set_pagetitle(":: {$title}");
        
        if(isset($_POST) && array_key_exists('net_nemein_updatenotification_submit', $_POST))
        {
            $person = new midcom_db_person($_MIDGARD['user']);

            $person_subscriptions = $person->list_parameters('net.nemein.updatenotification:subscribe');
            foreach($person_subscriptions as $key => $value)
            {
                $person->delete_parameter('net.nemein.updatenotification:subscribe',$key);
            }

            foreach($_POST['net_nemein_updatenotification'] as $topic_guid => $notification_types)
            {
                foreach(explode(',',$notification_types) as $notification_key => $notification_type)
                {
                    if($person->parameter('net.nemein.updatenotification:subscribe', $topic_guid))
                    {
                        $person->parameter('net.nemein.updatenotification:subscribe', $topic_guid, $person->parameter('net.nemein.updatenotification:subscribe', $topic_guid) . ',' . $notification_type);
                    }
                    else
                    {
                        $person->parameter('net.nemein.updatenotification:subscribe', $topic_guid, $notification_type);
                    }
                }
            }
        }
        
        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        return true;
    }
    

    function _show_save($handler_id, &$data)
    {

    }
        
    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('index'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
