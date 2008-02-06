<?php

/**
 * @package midcom.helper.search
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM Indexer Front-End, NAP interface Class
 * 
 * Nothing special in here, we stick to the defaults as we don't have any
 * leaves right now anyway.
 * 
 * @package midcom.helper.search
 */
class midcom_helper_search_navigation extends midcom_baseclasses_components_navigation
{
    function get_node()
    {
        $toolbar = Array();
        if ($_MIDCOM->auth->can_do('midgard:update', $this->_topic))
        {
            $toolbar[100] = Array
            (
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
        }
        return parent::get_node($toolbar);
    }
}