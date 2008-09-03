<?php

/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Flight competition site interface class.
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_viewer extends midcom_baseclasses_components_request
{

    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
        
        // Match /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_index', 'index'),
        );

        // Match /report/
        $this->_request_switch['report'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_report', 'new'),
            'fixed_args' => Array('report'),
        );

        // Match /score/organization
        $this->_request_switch['score_organization'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_score', 'score'),
            'fixed_args' => Array('score', 'organization'),
        );

        // Match /score/pilot
        $this->_request_switch['score_pilot'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_score', 'score'),
            'fixed_args' => Array('score', 'pilot'),
        );

        // Match /flights.xml
        $this->_request_switch['xml'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_download', 'xml'),
            'fixed_args' => Array('flights.xml'),
        );
       
        // Match /flights.csv
        $this->_request_switch['csv'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_download', 'csv'),
            'fixed_args' => Array('flights.csv'),
        );
    }

    function _on_handle($handler, $args)
    {
        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'report.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('report flight'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'fi_mik_flight_dba'),
            )
        );
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'href'  => MIDCOM_STATIC_URL . '/fi.mik.lentopaikkakisa/lentopaikkakisa.css',
                'media' => 'screen',
            )
        );
         
        return true;
    }
}
?>