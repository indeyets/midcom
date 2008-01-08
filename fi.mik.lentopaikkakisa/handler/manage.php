<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Flight report manager
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_handler_manage extends midcom_baseclasses_components_handler
{
    /**
     * Array of Datamanager 2 controllers for report display and management
     *
     * @var array
     * @access private
     */
    var $_controllers = array();

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    function fi_mik_lentopaikkakisa_handler_manage()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $report = new fi_mik_lentopaikkakisa_report_dba($args[0]);
        if (!$report)
        {
            return false;
        }

        $report->require_do('midgard:delete');

        $report->delete();

        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "manage/");

        return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_list($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:delete');

        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $qb = new org_openpsa_qbpager('fi_mik_lentopaikkakisa_report_dba', 'fi_mik_lentopaikkakisa_reports');
        $qb->add_order('created', 'DESC');
        $this->_request_data['report_qb'] =& $qb;
        $reports = $qb->execute();
        foreach ($reports as $report)
        {
            $this->_controllers[$report->id] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_controllers[$report->id]->schemadb =& $this->_schemadb;
            $this->_controllers[$report->id]->set_storage($report);
            $this->_controllers[$report->id]->process_ajax();
            $this->_request_data['reports'][$report->guid] = $this->_controllers[$report->id]->get_content_html();
            $this->_request_data['reports_objects'][$report->guid] = $report;
        }
        return true;
    }

    function _show_list($handler_id, &$data)
    {
        midcom_show_style('view-reports');
    }
}
?>