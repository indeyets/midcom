<?php
/**
 * @package org.openpsa.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: filters.php,v 1.1 2006/06/08 16:24:37 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar filters handler
 *
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_handler_filters extends midcom_baseclasses_components_handler
{
    function org_openpsa_calendar_handler_filters()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _handle_ajax()
    {
        $update_succeeded = false;
        $errstr = NULL;
        $user = new midcom_db_person($this->_request_data['user']->id);
        if (array_key_exists('org_openpsa_calendar_filters_add', $_POST))
        {
            $target = new midcom_db_person($_POST['org_openpsa_calendar_filters_add']);
            if ($target)
            {
                $update_succeeded = $user->parameter('org_openpsa_calendar_show', $_POST['org_openpsa_calendar_filters_add'], 1);
            }
            $errstr = mgd_errstr();
        }
        elseif (array_key_exists('org_openpsa_calendar_filters_remove', $_POST))
        {
            $target = new midcom_db_person($_POST['org_openpsa_calendar_filters_remove']);
            if ($target)
            {
                $update_succeeded = $user->parameter('org_openpsa_calendar_show', $_POST['org_openpsa_calendar_filters_remove'], '');
            }
            $errstr = mgd_errstr();
        }

        $ajax = new org_openpsa_helpers_ajax();
        //This will exit.
        $ajax->simpleReply($update_succeeded, $errstr);
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_edit($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_request_data['user'] = $_MIDCOM->auth->user->get_storage();

        if (   array_key_exists('org_openpsa_calendar_filters_add', $_POST)
            || array_key_exists('org_openpsa_calendar_filters_remove', $_POST))
        {
            $this->_handle_ajax();
        }

        // Debug helpers
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/messages.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.calendar/filters.js");
        $_MIDCOM->add_jsonload("org_openpsa_calendar_filters_makeEditable();");

        $this->_request_data['buddylist'] = array();

        $qb = org_openpsa_contacts_buddy::new_query_builder();
        $qb->add_constraint('account', '=', $this->_request_data['user']->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies = $qb->execute();

        foreach ($buddies as $buddy)
        {
            $person = new org_openpsa_contacts_person($buddy->buddy);
            if ($person)
            {
                $this->_request_data['buddylist'][$person->id] = $person;
            }
        }

        // Add user to the filter list if needed
        if (   !array_key_exists($this->_request_data['user']->id, $this->_request_data['buddylist'])
            && $_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
        {
                $this->_request_data['buddylist'][$this->_request_data['user']->id] = $this->_request_data['user'];
        }

        if (array_key_exists('org_openpsa_calendar_returnurl', $_GET))
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => $_GET['org_openpsa_calendar_returnurl'],
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('back to calendar'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('choose calendars'));

        return true;
    }

    function _show_edit($handler_id, &$data)
    {
        if (count($this->_request_data['buddylist']) > 0)
        {
            midcom_show_style("show-filters-header");
            foreach ($this->_request_data['buddylist'] as $person)
            {
                $this->_request_data['person'] =& $person;
                if ($this->_request_data['user']->parameter('org_openpsa_calendar_show', $person->guid))
                {
                    $this->_request_data['subscribed'] = true;
                }
                else
                {
                    $this->_request_data['subscribed'] = false;
                }
                midcom_show_style("show-filters-item");
            }
            midcom_show_style("show-filters-footer");
        }
    }
}
?>