<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace site interface class
 *
 * See the various handler classes for details.
 *
 *
 * @package net.nehmer.marketplace
 */

class net_nehmer_marketplace_viewer extends midcom_baseclasses_components_request
{
    function net_nehmer_marketplace_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        // Generic and personal welcom pages
        $this->_request_switch['welcome'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_welcome', 'welcome'),
        );
        $this->_request_switch['welcome_self'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_welcome', 'self'),
            'fixed_args' => 'self',
            'variable_args' => 1,
        );
        $this->_request_switch['welcome_asks'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_welcome', 'welcome_mode'),
            'fixed_args' => 'ask',
        );
        $this->_request_switch['welcome_bids'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_welcome', 'welcome_mode'),
            'fixed_args' => 'bid',
        );

        // Mode welcome pages and paged category listings
        $this->_request_switch['list'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_category', 'browse'),
            'fixed_args' => 'list',
            'variable_args' => 3,
        );

        // Entry view / edit / delete
        $this->_request_switch['entry_view'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_entry', 'view'),
            'fixed_args' => Array('entry', 'view'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_edit'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_entry', 'edit'),
            'fixed_args' => Array('entry', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_delete'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_entry', 'delete'),
            'fixed_args' => Array('entry', 'delete'),
            'variable_args' => 1,
        );

        // Submit Entry Stuff
        $this->_request_switch['submit_welcome'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_submit', 'welcome'),
            'fixed_args' => 'submit',
        );
        $this->_request_switch['submit_thankyou'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_submit', 'thankyou'),
            'fixed_args' => Array('submit', 'thankyou'),
        );
        $this->_request_switch['submit_step1'] = Array
        (
            'handler' => Array('net_nehmer_marketplace_handler_submit', 'step1'),
            'fixed_args' => 'submit',
            'variable_args' => 1,
        );

    }
}

?>
