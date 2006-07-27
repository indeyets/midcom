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

class fi_mik_lentopaikkakisa_handler_score extends midcom_baseclasses_components_handler
{
    function fi_mik_lentopaikkakisa_handler_score() 
    {
        parent::midcom_baseclasses_components_handler();       
    }

    function _handler_pilot($handler_id, $args, &$data)
    {
        $this->_request_data['scores'] = Array();
        $this->_request_data['total'] = 0;
        
        $this->_request_data['view_title'] = sprintf($this->_request_data['l10n']->get('scores by %s'), $this->_request_data['l10n']->get('pilot'));
        $_MIDCOM->set_pagetitle($this->_request_data['view_title']);
    
        $qb = fi_mik_lentopaikkakisa_report_dba::new_query_builder();
        $qb->add_order('sendername', 'DESC');
        $reports = $qb->execute();
        
        foreach ($reports as $report)
        {
            if (!array_key_exists($report->sendername, $this->_request_data['scores']))
            {
                $this->_request_data['scores'][$report->sendername] = $report->score;
            }
            else
            {
                $this->_request_data['scores'][$report->sendername] += $report->score;
            }
            $this->_request_data['total'] += $report->score;
        }
        arsort($this->_request_data['scores']);
        return true;
    }
    
    function _show_pilot($handler_id, &$data)
    {
        midcom_show_style('view-scores');
    }   

    function _handler_organization($handler_id, $args, &$data)
    {
        $this->_request_data['scores'] = Array();
        $this->_request_data['total'] = 0;

        $this->_request_data['view_title'] = sprintf($this->_request_data['l10n']->get('scores by %s'), $this->_request_data['l10n']->get('club'));
        $_MIDCOM->set_pagetitle($this->_request_data['view_title']);

        $qb = fi_mik_lentopaikkakisa_report_dba::new_query_builder();
        $qb->add_order('organization', 'DESC');
        $reports = $qb->execute();
        
        foreach ($reports as $report)
        {
            if (!array_key_exists($report->organization, $this->_request_data['scores']))
            {
                $this->_request_data['scores'][$report->organization] = $report->score;
            }
            else
            {
                $this->_request_data['scores'][$report->organization] += $report->score;
            }
            $this->_request_data['total'] += $report->score;
        }
        arsort($this->_request_data['scores']);        
        return true;
    }
    
    function _show_organization($handler_id, &$data)
    {
        midcom_show_style('view-scores');
    }     
}
?>