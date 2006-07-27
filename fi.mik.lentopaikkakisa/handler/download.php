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

    function _handler_xml($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");
        
        $qb = fi_mik_lentopaikkakisa_report_dba::new_query_builder();
        $qb->add_order('created', 'DESC');
        $this->_request_data['all'] = $qb->execute();
        
        return true;
    }
    
    function _show_xml($handler_id, &$data)
    {
        echo "<reports>\n";
        $mapper = new midcom_helper_xml_objectmapper();
        foreach ($this->_request_data['all'] as $report)
        {
            echo $mapper->object2data($report);
        }
        echo "</reports>\n";
    }    

    function _handler_csv($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->header('Content-Type: text/plain;charset=UTF-8');
        
        $qb = fi_mik_lentopaikkakisa_report_dba::new_query_builder();
        $qb->add_order('created', 'DESC');
        $this->_request_data['all'] = $qb->execute();
        
        return true;
    }
    
    function _show_csv($handler_id, &$data)
    {
        foreach ($this->_request_data['all'] as $report)
        {
            // FIXME: Use DM2 CSV output system
            echo date('Y-m-d', $report->date).",{$report->organization},{$report->aerodrome},{$report->plane},".str_replace(',','.',$report->score).",{$report->sendername}\n";
        }
    }  
}
?>