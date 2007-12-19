<?php
/**
 * @package net.nemein.lastupdates
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.lastupdates
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nemein.lastupdates
 */
class net_nemein_lastupdates_handler_index  extends midcom_baseclasses_components_handler 
{

    /**
     * Simple default constructor.
     */
    function net_nemein_lastupdates_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $data =& $this->_request_data;
        // Default to one week ago
    }
    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $data['edited_since'] = net_nemein_lastupdates_viewer::last_weeks_monday();
        if (   isset($_REQUEST['since'])
            && !empty($_REQUEST['since']))
        {
            $_MIDCOM->relocate("since/{$_REQUEST['since']}/");
        }
        $this->_do_query();
        $this->_update_breadcrumb_line($handler_id);
        return true;
    }

    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_since($handler_id, $args, &$data)
    {
        $from_date = @strtotime($args[0]);
        if ($from_date < 100)
        {
            // Could not parse sane from date
            return false;
        }
        $data['edited_since'] = mktime(0,0,1,date('n', $from_date), date('j', $from_date), date('Y', $from_date));
        $this->_do_query();
        $this->_update_breadcrumb_line($handler_id);
        return true;
    }

    function _do_query()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $indexer =& $_MIDCOM->get_service('indexer');
        $data =& $this->_request_data;
        $data['documents'] = array();
        $data['query_failure'] = false;
        // Dummy constraint
        // TODO: get the site root URL from midcom (though this might even be cheaper...)
        $query_string = '__TOPIC_URL:http*';

        // TODO: Check for possible component/topic limiters

        // Create datefilter to limit the scope of our results
        debug_add("Creating midcom_services_indexer_filter_date('__EDITED', '{$data['edited_since']}', 0) ({$data['edited_since']}=" . date('Y-m-d H:i:s', $data['edited_since']) . ')');
        $filter = new midcom_services_indexer_filter_date('__EDITED', $data['edited_since'], 0);
        // actual query
        debug_add("Querying with '{$query_string}' (and the datefilter)");
        $result = $indexer->query($query_string, $filter);
        if ($result === false)
        {
            debug_add('Got boolean false as resultset, this means error from indexer level, probably broken query', MIDCOM_LOG_ERROR);
            debug_add("Query was '{$query_string}', see debug level logs above this line for details on the indexer response", MIDCOM_LOG_INFO);
            $data['query_failure'] = true;
            return;
        }
        $data['documents'] = $result;
        usort($data['documents'], array($this, 'sort_documents_by_edited'));
        debug_pop();
    }

    /**
     * Used as callback for usort to sort the documents array to reverse revised order (LIFO)
     */
    function sort_documents_by_edited($a, $b)
    {
        if ($a->edited > $b->edited)
        {
            return -1;
        }
        if ($b->edited > $a->edited)
        {
            return 1;
        }
        return 0;
    }

    /**
     * This function does the output.
     */
    function _show_index($handler_id, &$data)
    {
        $this->_show_results($handler_id, $data);
    }

    /**
     * This function does the output.
     */
    function _show_since($handler_id, &$data)
    {
        $this->_show_results($handler_id, $data);
    }

    /**
     * Output our query results
     */
    function _show_results($handler_id, &$data)
    {
        midcom_show_style('header');
        if (empty($data['documents']))
        {
            midcom_show_style('no_results');
        }
        else
        {
            midcom_show_style('results_start');
            foreach ($data['documents'] as $document)
            {
                $data['document'] = $document;
                midcom_show_style('results_item');
            }
            midcom_show_style('results_end');
        }
        midcom_show_style('footer');
    }

    
    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $data =& $this->_request_data;
        $tmp = Array();

        if ($handler_id == 'index-since')
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => '/since/' . date('Y-m-d', $data['edited_since']) . '/',
                MIDCOM_NAV_NAME => sprintf($data['l10n']->get('modified since %s'), strftime('%x', $data['edited_since'])),
            );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
