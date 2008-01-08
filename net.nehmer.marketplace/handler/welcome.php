<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace welcome page handler
 *
 * @package net.nehmer.marketplace
 * @todo Once Midgard 1.8 QB with parameter and (NOT) IN support rolls out, move to those
 *     functions for querying the top items.
 */

class net_nehmer_marketplace_handler_welcome extends midcom_baseclasses_components_handler
{
    /**
     * The newest bids applicable for the current configuration. This is used to quickly
     * present the newest bids on the frontpage. The list is limited in numbers by the
     * welcome_offer_count setting and the anonymous_read options in case we don't have an
     * authenticated user.
     *
     * This is an array of object instances, no datamanager loading is done at this point.
     *
     * @var Array
     * @access private
     */
    var $_top_bids = null;

    /**
     * The newest applications applicable for the current configuration. This is used to quickly
     * present the newest applications on the frontpage. The list is limited in numbers by the
     * welcome_offer_count setting and the anonymous_read options in case we don't have an
     * authenticated user.
     *
     * This is an array of object instances, no datamanager loading is done at this point.
     *
     * @var Array
     * @access private
     */
    var $_top_asks = null;

    /**
     * The mode in use for self mode (ask or bid)
     *
     * @var string
     * @access private
     */
    var $_mode = null;

    /**
     * The matching entries for self mode.
     *
     * @var array
     * @access private
     */
    var $_entries = null;

    /**
     * The datamanager encapsulating the current resultset.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    function net_nehmer_marketplace_handler_welcome()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['top_bids'] =& $this->_top_bids;
        $this->_request_data['top_asks'] =& $this->_top_asks;
        $this->_request_data['entries'] =& $this->_entries;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['mode'] =& $this->_mode;
    }

    /**
     * The welcome handler loads the newest asks / bids according to the configuration
     * settings and prepares the type listings.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        // Retrieve the newest 5 offers / applications.
        // Until we move to 1.8 we need two distinct QBs here, there we can use
        // IN / NOT IN and parametrized queries.

        // bids
        $qb = $this->_get_entry_qb(false);
        $this->_top_bids = $qb->execute_unchecked();

        // asks
        $qb = $this->_get_entry_qb(true);
        $this->_top_asks = $qb->execute_unchecked();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);

        return true;
    }

    /**
     * Returns an entry Query Builder for use on the welcome page to retrieve
     * the top-5 queries, taking the current type configuration into account
     * for anonymous accesses.
     *
     * @param boolean $ask True if you want to query asks, false for bids.
     */
    function _get_entry_qb($ask)
    {
        $qb = net_nehmer_marketplace_entry::new_query_builder();
        $qb->add_constraint('ask', '=', $ask);
        $qb->add_order('published', 'DESC');
        if ($ask)
        {
            $qb->set_limit($this->_config->get('welcome_ask_count'));
        }
        else
        {
            $qb->set_limit($this->_config->get('welcome_bid_count'));
        }
        return $qb;
    }

    /**
     * Shows the welcome page.
     *
     * Normally, you should completely customize this page anyway, therefore the
     * default styles are rather primitive at this time.
     */
    function _show_welcome($handler_id, &$data)
    {
        midcom_show_style('welcome');
    }


    /**
     * Validates the self arguments.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean True if the request can be handled, false otherwise.
     */
    function _can_handle_self($handler_id, $args, &$data)
    {
        if (   $args[0] != 'ask'
            && $args[0] != 'bid')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'ask' or 'bid' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Queries the own entries, distinguished by offers and applications in args[0].
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_self($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_mode = $args[0];

        $qb = net_nehmer_marketplace_entry::new_query_builder();
        $qb->add_constraint('account', '=', $_MIDCOM->auth->user->guid);
        $qb->add_constraint('ask', '=', ($this->_mode == 'ask'));
        $qb->add_order('published', 'DESC');
        $this->_entries = $qb->execute();

        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        $this->_datamanager->set_schema($this->_config->get("{$this->_mode}_schema"));

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get("your {$this->_mode}s"));
        $this->_component_data['active_leaf'] = ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_SELF_ASKS : NET_NEHMER_MARKETPLACE_LEAFID_SELF_BIDS;

        return true;
    }

    /**
     * Displays the own items in a standard display loop.
     */
    function _show_self($handler_id, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        midcom_show_style('self-begin');
        foreach ($this->_entries as $key => $entry)
        {
            $data['entry'] = $entry;
            $data['view_url'] = "{$prefix}entry/view/{$entry->guid}.html";
            $data['edit_url'] = "{$prefix}entry/edit/{$entry->guid}.html";
            $data['delete_url'] = "{$prefix}entry/delete/{$entry->guid}.html";
            $this->_datamanager->set_storage($entry);
            midcom_show_style('self-item');
        }
        midcom_show_style('self-end');
    }


    /**
     * The welcome handler loads the newest asks / bids according to the configuration
     * settings and prepares the type listings.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_welcome_mode($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        // Retrieve the newest 5 offers / applications.
        // Until we move to 1.8 we need two distinct QBs here, there we can use
        // IN / NOT IN and parametrized queries.

        if ($handler_id == 'welcome_asks')
        {
            $this->_mode = 'ask';
            $qb = $this->_get_entry_qb(true);
            $this->_top_asks = $qb->execute_unchecked();
        }
        else
        {
            $this->_mode = 'bid';
            $qb = $this->_get_entry_qb(false);
            $this->_top_bids = $qb->execute_unchecked();
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: " . $this->_l10n->get($this->_mode));
        $this->_component_data['active_leaf'] = ($this->_mode == 'ask') ?
            NET_NEHMER_MARKETPLACE_LEAFID_ASKS : NET_NEHMER_MARKETPLACE_LEAFID_BIDS;

        return true;
    }

    /**
     * Shows the mode specific welcome page.
     *
     * Normally, you should completely customize this page anyway, therefore the
     * default styles are rather primitive at this time.
     */
    function _show_welcome_mode($handler_id, &$data)
    {
        midcom_show_style('welcome-mode');
    }


}

?>
