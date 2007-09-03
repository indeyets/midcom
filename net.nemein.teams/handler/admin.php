<?php
/**
 * @package net.nemein.team
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for net.nemein.team
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nemein.teams
 */
class net_nemein_teams_handler_admin  extends midcom_baseclasses_components_handler 
{

    /**
     * Simple default constructor.
     */
    function net_nemein_teams_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
    }

    function _handler_admin ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");
        
	return true;
    }

    function _handler_log ($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $title = $this->_l10n_midcom->get('log');
        $_MIDCOM->set_pagetitle(":: {$title}");

        $qb = net_nemein_teams_log_dba::new_query_builder();
	$qb->add_constraint('teamguid', '=', 'koe');
        $qb->add_order('metadata.created', 'ASC');

	$logs = $qb->execute();

	$this->_request_data['logs'] = $logs;
 

	return true;
    }
    
    function _show_admin($handler_id, &$data)
    {
        midcom_show_style('index');
    }

    function _show_log($handler_id, &$data)
    {
        midcom_show_style('log');
    }
}
?>
