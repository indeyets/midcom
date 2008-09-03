<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch (Yellow Pages) site interface class
 *
 * See the various handler classes for details.
 *
 *
 * @package net.nehmer.branchenbuch
 */
class net_nehmer_branchenbuch_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    function _on_initialize()
    {
        // Category Listing Stuff
        $this->_request_switch['welcome'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_categories', 'welcome'),
        );
        $this->_request_switch['category_list'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_categories', 'list'),
            'fixed_args' => Array('category', 'list'),
            'variable_args' => 1,
        );
        $this->_request_switch['category_list_alpha'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_categories', 'list_alpha'),
            'fixed_args' => Array('category', 'list', 'alpha'),
            'variable_args' => 2,
        );
        $this->_request_switch['category_customsearch'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_categories', 'customsearch'),
            'fixed_args' => Array('category', 'customsearch'),
            'variable_args' => 1,
        );

        // Entry Listing Stuff
        $this->_request_switch['entry_list_self'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'list_self'),
            'fixed_args' => Array('entry', 'list', 'self'),
        );
        $this->_request_switch['entry_list_alpha'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'list'),
            'fixed_args' => Array('entry', 'list', 'alpha'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_list_customsearch'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'list'),
            'fixed_args' => Array('entry', 'list', 'customsearch'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_list'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'list'),
            'fixed_args' => Array('entry', 'list'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_view_alpha'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'entry'),
            'fixed_args' => Array('entry', 'view', 'alpha'),
            'variable_args' => 2,
        );
        $this->_request_switch['entry_view_customsearch'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'entry'),
            'fixed_args' => Array('entry', 'view', 'customsearch'),
            'variable_args' => 2,
        );
        $this->_request_switch['entry_view_list'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'entry'),
            'fixed_args' => Array('entry', 'view'),
            'variable_args' => 2,
        );
        $this->_request_switch['entry_view'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'entry'),
            'fixed_args' => Array('entry', 'view'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_edit'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'edit'),
            'fixed_args' => Array('entry', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_delete'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'delete'),
            'fixed_args' => Array('entry', 'delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['entry_images'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_entries', 'images'),
            'fixed_args' => Array('entry', 'images'),
            'variable_args' => 1,
        );


        // Entry registration
        $this->_request_switch['add'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_addentry', 'welcome'),
            'fixed_args' => Array('entry', 'add'),
        );
        $this->_request_switch['add_1'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_addentry', 'categoryselect'),
            'fixed_args' => Array('entry', 'add', '1'),
            'variable_args' => 1,
        );
        $this->_request_switch['add_2'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_addentry', 'details'),
            'fixed_args' => Array('entry', 'add', '2'),
            'variable_args' => 1,
        );
        $this->_request_switch['add_4'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_addentry', 'confirm'),
            'fixed_args' => Array('entry', 'add', '4'),
        );
        $this->_request_switch['add_5'] = Array
        (
            'handler' => Array('net_nehmer_branchenbuch_handler_addentry', 'thanks'),
            'fixed_args' => Array('entry', 'add', '5'),
            'variable_args' => 1,
        );

    }
}

?>