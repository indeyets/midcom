<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market site interface class
 *
 * See the various handler classes for details.
 *
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_viewer extends midcom_baseclasses_components_request
{
    function net_nehmer_jobmarket_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        // Generic and personal welcom pages
        $this->_request_switch['welcome'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_welcome', 'welcome'),
        );
        $this->_request_switch['welcome_self'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_welcome', 'self'),
            'fixed_args' => 'self',
            'variable_args' => 1,
        );

        // Job-Ticker (paged)
        $this->_request_switch['ticker'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_ticker', 'ticker'),
            'fixed_args' => 'ticker',
            'variable_args' => 2,
        );

        // Search, generic and type limited:
        $this->_request_switch['search_all'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_search', 'search'),
            'fixed_args' => 'search',
            'variable_args' => 1,
        );
        $this->_request_switch['search_result'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_search', 'result'),
            'fixed_args' => Array('search', 'result'),
            'variable_args' => 1,
        );
        $this->_request_switch['search_type'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_search', 'search'),
            'fixed_args' => 'search',
            'variable_args' => 2,
        );

        // Submit Entry Stuff
        $this->_request_switch['submit_welcome'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_submit', 'welcome'),
            'fixed_args' => 'submit',
        );
        $this->_request_switch['submit_thankyou'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_submit', 'thankyou'),
            'fixed_args' => Array('submit', 'thankyou'),
        );
        $this->_request_switch['submit_welcome_mode'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_submit', 'welcome'),
            'fixed_args' => 'submit',
            'variable_args' => 1,
        );
        $this->_request_switch['submit_step1'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_submit', 'step1'),
            'fixed_args' => 'submit',
            'variable_args' => 2,
        );

        // Entry view / edit / delete
        $this->_request_switch['entry_view'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_entry', 'view'),
            'fixed_args' => Array('entry', 'view'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_edit'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_entry', 'edit'),
            'fixed_args' => Array('entry', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_delete'] = Array
        (
            'handler' => Array('net_nehmer_jobmarket_handler_entry', 'delete'),
            'fixed_args' => Array('entry', 'delete'),
            'variable_args' => 1,
        );


    }
}

?>
