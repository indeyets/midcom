<?php
/**
 * @package org.openpsa.directmarketing
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: index.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum index
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_handler_message_compose extends midcom_baseclasses_components_handler
{
    /**
     * The message which has been created
     *
     * @var org_openpsa_directmarketing_message
     * @access private
     */
    var $_message = null;

    var $_datamanager = false;

    /**
     * Simple default constructor.
     */
    function org_openpsa_directmarketing_handler_message_compose()
    {
        parent::__construct();
    }

    /**
     * Internal helper, loads the datamanager for the current message. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_message']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for messages.");
            // This will exit.
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_compose($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->request_sudo();
        //Load message
        $data['message'] = new org_openpsa_directmarketing_campaign_message($args[0]);

        $data['campaign'] = new org_openpsa_directmarketing_campaign($data['message']->campaign);
        $this->_component_data['active_leaf'] = "campaign_{$data['campaign']->id}";

        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($data['message']);
        $data['message_obj'] =& $data['message'];
        $data['message_dm'] =& $this->_datamanager;

        if (   !is_object($data['message'])
            || !$data['message']->id)
        {
            debug_pop();
            return false;
        }

        if ($handler_id === 'compose4person')
        {
            $data['person'] = new org_openpsa_contacts_person($args[1]);
            if (   !is_object($data['person'])
                || !$data['person']->id)
            {
                debug_pop();
                return false;
            }
            $qb = org_openpsa_directmarketing_campaign_member::new_query_builder();
            $qb->add_constraint('person', '=', $this->_request_data['person']->id);
            $memberships = $qb->execute();
            if (empty($memberships))
            {
                $data['member'] = new org_openpsa_directmarketing_campaign_member();
                $data['member']->person = $data['person']->id;
                $data['member']->campaign = $data['message']->campaign;
                $data['member']->guid = 'dummy';
            }
            else
            {
                $data['member'] = $memberships[0];
            }
        }

        $data['message_array'] = $this->_datamanager->get_content_raw();

        if (!array_key_exists('content', $data['message_array']))
        {
            debug_add('"content" not defined in schema, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        //Substyle handling
        @debug_add("\$data['message_array']['substyle']='{$data['message_array']['substyle']}'");
        if (   array_key_exists('substyle', $data['message_array'])
            && !empty($data['message_array']['substyle'])
            && !preg_match('/^builtin:/', $data['message_array']['substyle']))
        {
            debug_add("Appending substyle {$data['message_array']['substyle']}");
            $_MIDCOM->substyle_append($data['message_array']['substyle']);
        }
        //This isn't necessary for dynamic-loading, but is nice for "preview".
        $_MIDCOM->skip_page_style = true;
        debug_add('message type: '.$data['message_obj']->orgOpenpsaObtype);
        switch($data['message_obj']->orgOpenpsaObtype)
        {
            case ORG_OPENPSA_MESSAGETYPE_EMAIL_TEXT:
            case ORG_OPENPSA_MESSAGETYPE_SMS:
                debug_add('Forcing content type: text/plain');
                $_MIDCOM->cache->content->content_type('text/plain');
            break;
            //TODO: Other content type overrides ?
        }
        debug_pop();
        $_MIDCOM->auth->drop_sudo();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_compose($handler_id, &$data)
    {
        if ($handler_id === 'compose4person')
        {
            ob_start();
            $this->_real_show_compose($handler_id, $data);
            $composed = ob_get_contents();
            ob_end_clean();
            $personalized = $data['member']->personalize_message($composed, $data['message']->orgOpenpsaObtype, $data['person']);
            echo $personalized;
            return;
        }
        return $this->_real_show_compose($handler_id, $data);
    }

    function _real_show_compose($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $prefix='';
        if (   array_key_exists('substyle', $data['message_array'])
            && !empty($data['message_array']['substyle'])
            && preg_match('/^builtin:(.*)/', $data['message_array']['substyle'], $matches_style))
        {
            $prefix = $matches_style[1].'-';
        }
        debug_add("Calling midcom_show_style(\"compose-{$prefix}message\")");
        midcom_show_style("compose-{$prefix}message");
        debug_pop();
    }
}
?>