<?php

/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum NAP interface class.
 * 
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_navigation extends midcom_baseclasses_components_navigation
{
    function fi_mik_lentopaikkakisa_navigation() 
    {
        parent::__construct();
    }


    function get_leaves() 
    {
        // At the moment we have no leaves to show
        $leaves = array();
        
        $leaves["{$this->_topic->id}:scores_organization"] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "score/organization.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('scores by organization'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );
        $leaves["{$this->_topic->id}:scores_pilot"] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => "score/pilot.html",
                MIDCOM_NAV_NAME => $this->_l10n->get('scores by pilot'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );
        
        return $leaves;
    }


    function get_node() 
    {
       $toolbar[100] = Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
            )
        );

        return array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_NOENTRY => false,
            MIDCOM_NAV_CONFIGURATION => $this->_config,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );
    }
} 

?>