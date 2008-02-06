<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Flight reports in downloadable format
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_handler_download extends midcom_baseclasses_components_handler
{
    function fi_mik_lentopaikkakisa_handler_download()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_xml($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

        $qb = fi_mik_flight_dba::new_query_builder();
        $qb->add_order('created', 'DESC');
        $this->_request_data['all'] = $qb->execute();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_xml($handler_id, &$data)
    {
        $_MIDCOM->load_library('midcom.helper.xml');
        echo "<reports>\n";
        $mapper = new midcom_helper_xml_objectmapper();
        foreach ($this->_request_data['all'] as $report)
        {
            echo $mapper->object2data($report);
        }
        echo "</reports>\n";
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_csv($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('application/csv');
        $_MIDCOM->header('Content-Type: application/csv;charset=UTF-8');

        $qb = fi_mik_flight_dba::new_query_builder();
        $qb->add_order('created', 'DESC');
        $this->_request_data['all'] = $qb->execute();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_csv($handler_id, &$data)
    {
        $pilots = array();
        $organizations = array();
        $aircraft = array();
        echo "date,firstname,lastname,username,operator,aircraft,origin,destination,score_origin,score_destination\n";
        foreach ($this->_request_data['all'] as $report)
        {
            // FIXME: Use DM2 CSV output system
            if (!isset($pilots[$report->pilot]))
            {
                $pilots[$report->pilot] = new midcom_db_person($report->pilot);
            }
            if (!isset($organizations[$report->operator]))
            {
                $organizations[$report->operator] = new midcom_db_group($report->operator);
            }
            if (!isset($aircraft[$report->aircraft]))
            {
                $aircraft[$report->aircraft] = new org_openpsa_calendar_resource_dba($report->aircraft);
            }
            echo date('Y-m-d', $report->end).",{$pilots[$report->pilot]->firstname},{$pilots[$report->pilot]->lastname},{$pilots[$report->pilot]->username},{$organizations[$report->operator]->official},{$aircraft[$report->aircraft]->title},{$report->origin},{$report->destination},".str_replace(',','.',$report->scoreorigin).",".str_replace(',','.',$report->scoredestination)."\n";
        }
    }
}
?>