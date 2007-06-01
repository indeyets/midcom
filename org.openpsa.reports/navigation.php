<?php

/**
 * @package org.openpsa.reports
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: navigation.php,v 1.1 2005/08/01 15:37:42 bergius Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.reports NAP interface class.
 *
 * NAP is mainly used for toolbar rendering in this component
 *
 * @package org.openpsa.reports
 */
class org_openpsa_reports_navigation extends midcom_baseclasses_components_navigation
{

    function get_leaves()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called');
        $leaves = array();
        $components = org_openpsa_reports_viewer::available_component_generators();
        foreach ($components as $component => $loc)
        {
            $parts = explode('.', $component);
            $last = array_pop($parts);
            debug_add("adding {$component} as {$last}");
            $leaves["{$this->_topic->id}:generator_{$last}"] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "{$last}/",
                    /* TODO: better localization source ?? */
                    MIDCOM_NAV_NAME => $this->_l10n->get('generator:' . $component),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_META_CREATOR => $this->_topic->creator,
                MIDCOM_META_EDITOR => $this->_topic->revisor,
                MIDCOM_META_CREATED => $this->_topic->created,
                MIDCOM_META_EDITED => $this->_topic->revised
            );
        }
        debug_pop();
        return $leaves;
    }
}

?>