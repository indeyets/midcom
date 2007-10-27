<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market welcome page handler
 *
 * @package net.nehmer.jobmarket
 * @todo Once Midgard 1.8 QB with parameter and (NOT) IN support rolls out, move to those
 *     functions for querying the top items.
 */

class net_nehmer_jobmarket_handler_welcome extends midcom_baseclasses_components_handler
{

    /**
     * This is an array holding the computed type list.
     *
     * The elements are indexed by type name and contain the following keys:
     *
     * - all keys from the configuration array
     * - string offer_search_url
     * - string application_search_url
     *
     * @var Array
     * @access private
     */
    var $_type_list = null;

    /**
     * The newest offers applicable for the current configuration. This is used to quickly
     * present the newest offers on the frontpage. The list is limited in numbers by the
     * welcome_offer_count setting and the anonymous_read options in case we don't have an
     * authenticated user.
     *
     * This is an array of object instances, no datamanager loading is done at this point.
     *
     * @var Array
     * @access private
     */
    var $_top_offers = null;

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
    var $_top_applications = null;

    /**
     * The mode in use for self mode (offers or applications)
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
     * The datamanager encaspulating the current resultset.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    function net_nehmer_jobmarket_handler_welcome()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['type_list'] =& $this->_type_list;
        $this->_request_data['top_offers'] =& $this->_top_offers;
        $this->_request_data['top_applications'] =& $this->_top_applications;
        $this->_request_data['entries'] =& $this->_entries;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        $this->_request_data['mode'] =& $this->_mode;
    }

    /**
     * The welcome handler loades the newest offers / applications according to the configuration
     * settings and prepares the type listings.
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->_type_list = Array();
        foreach ($this->_config->get('type_config') as $name => $config)
        {
            $this->_type_list[$name] = $config;
            if ($config['offer_schema'])
            {
                if (   $_MIDCOM->auth->user !== null
                         || $config['offer_anonymous_read'])
                {
                    $this->_type_list[$name]['offer_search_url'] = "{$prefix}search/offer/" . urlencode($name) . '.html';
                }
                else
                {
                    $this->_type_list[$name]['offer_search_url'] = null;
                }
            }
            if ($config['application_schema'])
            {
                if (   $_MIDCOM->auth->user !== null
                    || $config['application_anonymous_read'])
                {
                    $this->_type_list[$name]['application_search_url'] = "{$prefix}search/application/" . urlencode($name) . '.html';
                }
                else
                {
                    $this->_type_list[$name]['application_search_url'] = null;
                }
            }
        }

        // Retrieve the newest 5 offers / applications.
        // Until we move to 1.8 we need two distinct QBs here, there we can use
        // IN / NOT IN and parametrized queries.

        // offers
        $qb = $this->_get_entry_qb(true);
        $this->_top_offers = $qb->execute_unchecked();

        // applications
        $qb = $this->_get_entry_qb(false);
        $this->_top_applications = $qb->execute_unchecked();

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
     * @param bool $offer Set this to true to query offers, to fale to query
     */
    function _get_entry_qb($offer)
    {
        $qb = net_nehmer_jobmarket_entry::new_query_builder();
        $qb->add_constraint('offer', '=', $offer);
        if (   $this->_config->get('welcome_teasers_checkprivs')
            && $_MIDCOM->auth->user === null)
        {
            $keyname = (($offer) ? 'offer' : 'application') . '_anonymous_read';
            foreach ($this->_type_list as $name => $config)
            {
                if (! $config[$keyname])
                {
                    $qb->add_constraint('type', '<>', $name);
                }
            }
        }
        $qb->add_order('published', 'DESC');
        if ($offer)
        {
            $qb->set_limit($this->_config->get('welcome_offer_count'));
        }
        else
        {
            $qb->set_limit($this->_config->get('welcome_application_count'));
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
     */
    function _can_handle_self($handler_id, $args, &$data)
    {
        if (   $args[0] != 'offer'
            && $args[0] != 'application')
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Need one of 'offfer' or 'application' as first argument, got '{$args[0]}'", MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        return true;
    }

    /**
     * Queries the own entries, distinguished by offers and applications ian args[0].
     */
    function _handler_self($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        $this->_mode = $args[0];
        $this->_type_list = $this->_config->get('type_config');

        $qb = net_nehmer_jobmarket_entry::new_query_builder();
        $qb->add_constraint('account', '=', $_MIDCOM->auth->user->guid);
        $qb->add_constraint('offer', '=', ($this->_mode == 'offer'));
        $qb->add_order('published', 'DESC');
        $this->_entries = $qb->execute();

        $schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra . ': ' . $data['l10n']->get("your {$this->_mode}s"));
        $this->_component_data['active_leaf'] = ($this->_mode == 'offer') ?
            NET_NEHMER_JOBMARKET_LEAFID_SELF_OFFERS : NET_NEHMER_JOBMARKET_LEAFID_SELF_APPLICATIONS;

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
            $param_name = ($entry->offer ? 'offer_schema' : 'application_schema');
            $this->_datamanager->set_schema($this->_type_list[$entry->type][$param_name]);
            $this->_datamanager->set_storage($entry);
            midcom_show_style('self-item');
        }
        midcom_show_style('self-end');
    }


}

?>
