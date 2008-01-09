<?php
/**
 * Function for adding JavaScript buttons for saving/cancelling DataManager form via toolbar
 *
 * @package org.openpsa.helpers
 * @author Eero af Heurlin, http://www.iki.fi/rambo
 * @version $Id: dm_savecancel.php,v 1.1 2006/02/15 13:37:53 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Function for adding JavaScript buttons for saving/cancelling DataManager form via toolbar
 */
function org_openpsa_helpers_dm_savecancel(&$toolbar, &$handler)
{
    if (   !is_object($toolbar)
        || !method_exists($toolbar, 'add_item'))
    {
        return;
    }
    $toolbar->add_item(
        Array(
            MIDCOM_TOOLBAR_URL => 'javascript:document.forms["midcom_helper_datamanager__form"]["midcom_helper_datamanager_submit"].click();',
            MIDCOM_TOOLBAR_LABEL => $handler->_request_data['l10n_midcom']->get("save"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/save.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_OPTIONS  => Array(
                'rel' => 'directlink',
            ),
        )
    );
    $toolbar->add_item(
        Array(
            MIDCOM_TOOLBAR_URL => 'javascript:document.forms["midcom_helper_datamanager__form"]["midcom_helper_datamanager_cancel"].click();',
            MIDCOM_TOOLBAR_LABEL => $handler->_request_data['l10n_midcom']->get("cancel"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_OPTIONS  => Array(
                'rel' => 'directlink',
            ),
        )
    );
}

?>