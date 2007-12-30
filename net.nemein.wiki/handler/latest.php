<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wikipage latest handler
 *
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_latest extends midcom_baseclasses_components_handler
{
    var $_updated_pages = 0;
    var $_max_pages = 0;

    function net_nemein_wiki_handler_latest()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /*
    function _seek_nodes($nodes, $parent_id)
    {
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $parent_id);
        $qb->add_constraint('
    }
    */

    /**
     * List all items updated with then given timeframe
     */
    function _seek_updated($from, $to = null)
    {
        if (is_null($to))
        {
            $to = time();
        }

        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic.component', '=', 'net.nemein.wiki');
        $qb->add_constraint('topic', 'INTREE', $this->_topic->id);
        $qb->add_constraint('metadata.revised', '<=', date('Y-m-d H:i:s', $to));
        $qb->add_constraint('metadata.revised', '>=', date('Y-m-d H:i:s', $from));
        $qb->add_order('metadata.revised', 'DESC');
        $result = $qb->execute();

        $rcs =& $_MIDCOM->get_service('rcs');

        foreach ($result as $page)
        {
            $rcs_handler = $rcs->load_handler($page);
            if (!$rcs_handler)
            {
                // Skip this one
                continue;
            }

            // Get object history
            $history = $rcs_handler->list_history();
            foreach ($history as $version => $history)
            {
                if ($this->_updated_pages >= $this->_max_pages)
                {
                    // End here
                    return;
                }

                if (   $history['date'] < $from
                    || $history['date'] > $to)
                {
                    // We can ignore revisions outside the timeframe
                    continue;
                }

                $history_date = date('Y-m-d', $history['date']);

                if (!isset($this->_request_data['latest_pages'][$history_date]))
                {
                    $this->_request_data['latest_pages'][$history_date] = array();
                }

                if (!isset($this->_request_data['latest_pages'][$history_date][$page->guid]))
                {
                    $this->_request_data['latest_pages'][$history_date][$page->guid] = array();
                }

                $this->_updated_pages++;

                $history['object'] = $page;
                $this->_request_data['latest_pages'][$history_date][$page->guid][$version] = $history;
            }
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_latest($handler_id, $args, &$data)
    {
        $this->_request_data['latest_pages'] = Array();

        $this->_max_pages = $this->_config->get('latest_count');

        // Start by looking for items within last two weeks
        $from = mktime(0, 0, 0, date('m'), date('d') - 14, date('Y'));
        $this->_seek_updated($from);

        $i = 0;
        while (   $this->_updated_pages < $this->_max_pages
               && $i < 20)
        {
            // Expand seek by another two weeks
            $to = $from;
            $from = mktime(0, 0, 0, date('m', $to), date('d', $to) - 14, date('Y', $to));
            $this->_seek_updated($from, $to);
            $i++;
        }

        $data['view_title'] = sprintf($this->_request_data['l10n']->get('latest updates in %s'), $this->_topic->extra);
        $_MIDCOM->set_pagetitle($data['view_title']);

        return true;
    }

    function _show_latest($handler_id, &$data)
    {
        $data['wikiname'] = $this->_topic->extra;
        if (count($data['latest_pages']) > 0)
        {
            $dates_shown = array();
            midcom_show_style('view-latest-header');
            foreach ($data['latest_pages'] as $date => $objects)
            {
                if (!isset($dates_shown[$date]))
                {
                    $data['date'] = $date;
                    midcom_show_style('view-latest-date');
                    $dates_shown[$date] = true;
                }

                foreach ($objects as $guid => $versions)
                {
                    foreach ($versions as $version => $history)
                    {
                        $data['version'] =& $version;
                        $data['history'] =& $history;
                        $data['wikipage'] =& $history['object'];
                        midcom_show_style('view-latest-item');
                    }
                }
            }

            midcom_show_style('view-latest-footer');
        }
    }
}
?>
