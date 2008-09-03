<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT . '/net/nehmer/account/handler/view.php');

/**
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_profile_view extends net_nehmer_account_handler_view
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

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_profile_view()
    {
        parent::__construct();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
    }

    /**
     * Internal helper function, prepares a datamanager based on the current account.
     */
    function _prepare_datamanager()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("inside _prepare_datamanager");

        $this->_schemadb = midcom_helper_datamanager2_schema::load_database( $this->_config->get('profile_schemadb') );
        $this->_schema = $this->_config->get('profile_schema');
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        //$this->_datamanager->autoset_storage($this->_account);
        $this->_datamanager->set_schema($this->_schema);
        $this->_datamanager->set_storage($this->_account);

        foreach ($this->_datamanager->schema->field_order as $name)
        {
            if (! array_key_exists('visible_mode', $this->_datamanager->schema->fields[$name]['customdata']))
            {
                $this->_datamanager->schema->fields[$name]['customdata']['visible_mode'] = 'user';
            }
        }

        debug_pop();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_view($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (   $handler_id == 'ajax-profile-view'
            || $handler_id == 'ajax-profile-view-other')
        {
            $_MIDCOM->skip_page_style = true;
        }

        switch($handler_id)
        {
            case 'ajax-profile-view-other':
                $parent_handler_id = 'other';
                break;
            default:
                $parent_handler_id = 'self';
                break;
        }
        parent::_handler_view($parent_handler_id, $args, &$data);

        // switch ($handler_id)
        // {
        //     case 'ajax-profile-view':
        //         $_MIDCOM->auth->require_valid_user();
        //         $this->_account = $_MIDCOM->auth->user->get_storage();
        //         net_nehmer_account_viewer::verify_person_privileges($this->_account);
        //         $this->_view_self = true;
        //         $this->_view_quick = false;
        //         break;
        //     case 'ajax-profile-view-other':
        //         $this->_account = new midcom_db_person($args[0]);
        //         $this->_view_self = false;
        //         $this->_view_quick = false;
        //         break;
        // }

        // if (! $this->_account)
        // {
        //     $this->errcode = MIDCOM_ERRNOTFOUND;
        //     $this->errstring = 'The account was not found.';
        //     return false;
        // }
        // $this->_user =& $_MIDCOM->auth->get_user($this->_account);
        // $this->_avatar = $this->_account->get_attachment('avatar');
        // $this->_avatar_thumbnail = $this->_account->get_attachment('avatar_thumbnail');
        //
        // // This is temporary stuff until we get a preferences mechanism up and running.
        // $data['communitymotto'] = $this->_account->get_parameter('midcom.helper.datamanager2', 'communitymotto');
        // $data['communityactive'] = (bool) $this->_account->get_parameter('midcom.helper.datamanager2', 'communityactive');
        // // End temporary Stuff
        //
        // $this->_prepare_datamanager();
        // $this->_compute_visible_fields();
        // $this->_prepare_request_data();
        // $_MIDCOM->bind_view_to_object($this->_account, $this->_datamanager->schema->name);

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        if (   $handler_id == 'ajax-profile-view'
            || $handler_id == 'ajax-profile-view-other')
        {
            midcom_show_style('profile-view-ajax');
        }
        else
        {
            midcom_show_style('profile-view');
        }
    }

}

?>