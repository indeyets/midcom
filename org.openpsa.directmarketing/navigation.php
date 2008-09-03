<?php

/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.2 2006/02/16 15:47:26 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.directmarketing NAP interface class.
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function org_openpsa_directmarketing_navigation()
    {
        parent::__construct();
    }

    function get_leaves()
    {
        $leaves = array();
        //TODO: Add visible campaigns to leaves
        $qb = org_openpsa_directmarketing_campaign::new_query_builder();
        $qb->add_constraint('archived', '=', 0);
        $campaigns = $qb->execute();
        if (empty($campaigns))
        {
            return $leaves;
        }
        foreach ($campaigns as $campaign)
        {
            $leaves["campaign_{$campaign->id}"] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => "campaign/{$campaign->guid}",
                    MIDCOM_NAV_NAME => $campaign->title
                ),
                MIDCOM_NAV_ADMIN => array
                (
                    MIDCOM_NAV_URL => "campaign/{$campaign->guid}",
                    MIDCOM_NAV_NAME => $campaign->title
                ),
                MIDCOM_NAV_GUID => $campaign->guid,
                MIDCOM_META_CREATOR => $campaign->metadata->creator,
                MIDCOM_META_EDITOR => $campaign->metadata->revisor,
                MIDCOM_META_CREATED => $campaign->metadata->created,
                MIDCOM_META_EDITED => $campaign->metadata->revised
            );
        }
        return $leaves;
    }

    function get_node($toolbar = null)
    {
        $toolbar = Array();
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        return parent::get_node($toolbar);
    }
}

?>