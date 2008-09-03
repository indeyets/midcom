<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace NAP interface class
 *
 * @package net.nehmer.marketplace
 */

class net_nehmer_marketplace_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function __construct()
    {
        parent::__construct();
    }

    function get_leaves()
    {
        $leaves = Array();

        if ($_MIDCOM->auth->user)
        {
            $leaves[NET_NEHMER_MARKETPLACE_LEAFID_ASKS] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "ask.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('asks'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
            $leaves[NET_NEHMER_MARKETPLACE_LEAFID_BIDS] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "bid.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('bids'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );

            $leaves[NET_NEHMER_MARKETPLACE_LEAFID_SELF_ASKS] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "self/ask.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('your asks'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
            $leaves[NET_NEHMER_MARKETPLACE_LEAFID_SELF_BIDS] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "self/bid.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('your bids'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );

            $leaves[NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "submit.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('submit new entry'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
            $leaves[NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_ASK] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "submit/ask.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('submit ask'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
            $leaves[NET_NEHMER_MARKETPLACE_LEAFID_SUBMIT_BID] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "submit/bid.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('submit bid'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
        }

        return $leaves;
    }

    /*
    function get_node()
    {
        return array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_NOENTRY => $hidden,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
    }
     */

}

?>