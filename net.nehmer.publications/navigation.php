<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * n.n.publications NAP interface class
 *
 * This class has been rewritten for MidCOM 2.6 utilizing all of the currently
 * available state-of-the-art technology.
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package net.nehmer.publications
 */

class net_nehmer_publications_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nehmer_publications_navigation()
    {
        parent::__construct();
    }

    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        $leaves = array ();

        $leaves[NET_NEHMER_PUBLICATIONS_LEAFID_ARCHIVE] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "archive.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('archive'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
        $leaves[NET_NEHMER_PUBLICATIONS_LEAFID_FEEDS] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "feeds.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('available feeds'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );


        return $leaves;
    }

}

?>