<?php
/**
 * @package org.openpsa.interviews
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.1 2006/05/08 11:22:49 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.interviews NAP interface class.
 *
 * @package org.openpsa.interviews
 */
class org_openpsa_interviews_navigation extends midcom_baseclasses_components_navigation
{

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
            $leaves[$campaign->id] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => "campaign/{$campaign->guid}/",
                    MIDCOM_NAV_NAME => $campaign->title
                ),
                MIDCOM_NAV_ADMIN => array
                (
                    MIDCOM_NAV_URL => "campaign/{$campaign->guid}/",
                    MIDCOM_NAV_NAME => $campaign->title
                ),
                MIDCOM_NAV_GUID => $campaign->guid,
                MIDCOM_META_CREATOR => $campaign->creator,
                MIDCOM_META_EDITOR => $campaign->revisor,
                MIDCOM_META_CREATED => $campaign->created,
                MIDCOM_META_EDITED => $campaign->revised
            );
        }
        return $leaves;
    }
}

?>