<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market NAP interface class
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nehmer_jobmarket_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    function get_leaves()
    {
        $leaves = Array();

        $leaves[NET_NEHMER_JOBMARKET_LEAFID_SEARCH_OFFERS] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "search/offer.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('search offers'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        $leaves[NET_NEHMER_JOBMARKET_LEAFID_SEARCH_APPLICATIONS] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "search/application.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('search applications'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        $leaves[NET_NEHMER_JOBMARKET_LEAFID_SUBMIT] = array
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
        $leaves[NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_APPLICATION] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "submit/application.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('submit application'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        $leaves[NET_NEHMER_JOBMARKET_LEAFID_SUBMIT_OFFER] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "submit/offer.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('submit offer'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        $leaves[NET_NEHMER_JOBMARKET_LEAFID_TICKER_OFFERS] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "ticker/offer/1.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('jobticker: offers'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        $leaves[NET_NEHMER_JOBMARKET_LEAFID_TICKER_APPLICATIONS] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "ticker/application/1.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('jobticker: applications'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        if ($_MIDCOM->auth->user)
        {
            $leaves[NET_NEHMER_JOBMARKET_LEAFID_SELF_OFFERS] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "self/offer.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('your offers'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
            $leaves[NET_NEHMER_JOBMARKET_LEAFID_SELF_APPLICATIONS] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "self/application.html",
                    MIDCOM_NAV_NAME => $this->_l10n->get('your applications'),
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