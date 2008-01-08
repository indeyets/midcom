<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: list.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Hour report action handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_hours_list extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function org_openpsa_projects_handler_hours_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_datamanagers =& $this->_request_data['datamanagers'];
    }

    function _load_hours($identifier)
    {

        $hours = new org_openpsa_projects_hour_report($identifier);

        if (!is_object($hours))
        {
            return false;
        }

        /* checkbox widget won't work with ajax editing existing hours unless
           this is done already here */
        $this->_hack_dm_for_ajax();

        // Load the hours to datamanager
        if (!$this->_datamanagers['hours']->init($hours))
        {
            return false;
        }
        return $hours;
    }

    function _hack_dm_for_ajax()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called');
        //DM searches for this variable in the REQUEST, unfortunately we cannot cleanly pass it with the Ajax, so we add here.
        if (   array_key_exists('hours', $this->_datamanagers)
            && is_object($this->_datamanagers['hours'])
            && isset($this->_datamanagers['hours']->form_prefix))
        {
            $_REQUEST[$this->_datamanagers['hours']->form_prefix . 'submit'] = true;
        }
        //Checkbox widget *really* wants this key regardless of the actual prefix.
        $_REQUEST['midcom_helper_datamanager_submit'] = true;
        debug_add("_REQUEST is now:\n===\n" . sprint_r($_REQUEST) . "===\n");
        debug_pop();
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (count($args) == 2)
        {
            $this->_list_type = $args[0];
            $this->_list_identifier = $args[1];

            switch ($this->_list_type)
            {
                case "task":
                    $_MIDCOM->skip_page_style = true;

                    // Run QB for the type
                    $qb = org_openpsa_projects_hour_report::new_query_builder();
                    $qb->add_constraint('task', '=', $this->_list_identifier);
                    $qb->add_order('date', 'ASC');
                    $ret = $qb->execute();
                    if (   is_array($ret)
                        && count($ret) > 0)
                    {
                        foreach ($ret as $hour_report)
                        {
                            $hour_report = $this->_load_hours($hour_report->id);
                            $this->_request_data['hours_entries'][$hour_report->guid] = $this->_datamanagers['hours'];
                        }
                    }

                    return true;
                default:
                    return false;
            }
        }

    }

    function _show_list($handler_id, &$data)
    {
        midcom_show_style("show-hours-list-header");
        foreach ($this->_request_data['hours_entries'] as $guid => $entry)
        {
            $this->_request_data['hour_entry'] = $entry;
            $this->_request_data['hour_entry_guid'] = $guid;
            midcom_show_style("show-hours-list-item");
        }
        midcom_show_style("show-hours-list-footer");
    }
}
?>