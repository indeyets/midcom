<?php

/**
 * @package net.siriux.photos
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Photo Gallery Navigation Class
 * 	
 * @todo document
 * 
 * @package net.siriux.photos	
 */
class net_siriux_photos_navigation {

    var $_object;
    var $_config;
    var $_l10n;
    var $_l10n_midcom;

    function net_siriux_photos_navigation() {
        $this->_object = NULL;
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.siriux.photos");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->_config = $GLOBALS['midcom_component_data']['net.siriux.photos']['config'];
    }

    function is_internal() {
        return FALSE;
    }

    function get_leaves() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $topic = &$this->_object;
        $leaves = array ();
        $articles = mgd_list_topic_articles($topic->id, $this->_config->get("sort_order"));
        if ($articles)  
        {
            // Prep toolbar
            $toolbar[50] = Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
            $toolbar[51] = Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
            $toolbar[53] = Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('rotate left'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/rotate_ccw.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
            $toolbar[54] = Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('rotate right'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/rotate_cw.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
            $i = 0;
            while ($articles->fetch()) 
            {
                $startfrom = ((int)($i / 10)) * 10;
                $i++; 
                // Match the toolbar to the correct URL.
                $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$articles->id}.html?startfrom={$startfrom}";
                $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$articles->id}.html?startfrom={$startfrom}";
                $toolbar[53][MIDCOM_TOOLBAR_URL] = "rotate_ccw/{$articles->id}.html?startfrom={$startfrom}";
                $toolbar[54][MIDCOM_TOOLBAR_URL] = "rotate_cw/{$articles->id}.html?startfrom={$startfrom}";
                
                $guid = $articles->guid();
                
                $leaves[$articles->id] = Array (
                    MIDCOM_NAV_SITE => Array (
                        MIDCOM_NAV_URL => $articles->name,
                        MIDCOM_NAV_NAME => $articles->title),
                    MIDCOM_NAV_ADMIN => Array (
                        MIDCOM_NAV_URL => "edit/{$articles->id}",
                        MIDCOM_NAV_NAME => $articles->title),
                    MIDCOM_NAV_GUID => $guid,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_META_CREATOR => $articles->creator,
                    MIDCOM_META_EDITOR => $articles->revisor,
                    MIDCOM_META_CREATED => $articles->created,
                    MIDCOM_META_EDITED => $articles->revised
                );
            }

        }
        else
        {
            debug_add("Failed to list articles, error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
        }
        debug_pop();
        return $leaves;
    }

    function get_node() {
        $topic = &$this->_object;
        
        // Create Toolbar
        $toolbar[0] = Array
        (
            MIDCOM_TOOLBAR_URL => 'upload.html',
	        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('upload photos'),
	        MIDCOM_TOOLBAR_HELPTEXT => null,
	        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/images.png',
	        MIDCOM_TOOLBAR_ENABLED => true            
        );
        $toolbar[100] = Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        );
        
        return array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_META_CREATOR => $topic->creator,
            MIDCOM_META_EDITOR => $topic->revisor,
            MIDCOM_META_CREATED => $topic->created,
            MIDCOM_META_EDITED => $topic->revised
        );
    }

    function set_object($object) {
        $this->_object = $object;
        $this->_config->store_from_object($object, 'net.siriux.photos');
        return TRUE;
    }

} // navigation

?>