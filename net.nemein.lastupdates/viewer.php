<?php
/**
 * @package net.nemein.lastupdates
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package net.nemein.lastupdates
 */
class net_nemein_lastupdates_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_lastupdates_viewer($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */
         
        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/lastupdates/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /since/YYYY-MM-DD
        $this->_request_switch['index-since'] = array
        (
            'fixed_args' => array('since'),
            'variable_args' =>  1,
            'handler' => Array('net_nemein_lastupdates_handler_index', 'since'),
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('net_nemein_lastupdates_handler_index', 'index'),
        );


    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
        
    }

    function last_weeks_monday()
    {
        $weekday = date('w');
        if ($weekday == 0)
        {
            $adjust = -6;
        }
        else
        {
            $adjust = -1*$weekday+1;
        }
        $stamp = mktime(0,0,1,date('n'), date('j')-7+$adjust, date('Y'));
        return $stamp;
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_populate_node_toolbar();

        return true;
    }

}

?>