<?php
/**
 * @package net.nemein.supportview
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * OpenPSA Supporview NAP interface class.
 * 
 * @package net.nemein.supportview
 */
class net_nemein_supportview_navigation {

    var $_object;
    var $_l10n;
    var $_l10n_midcom;

    function net_nemein_supportview_navigation() {
        $this->_object = null;
        $i18n =& $_MIDCOM->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.supportview");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
    }

    function get_leaves() {

        // list leaves under the current topic. for example:

        $topic = &$this->_object;
        $leaves = array ();
        if ($articles = mgd_list_topic_articles($topic->id, "reverse created")) {
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
            while ($articles->fetch ()) {
                // Match the toolbar to the correct URL.
                $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$articles->id}.html";
                $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$articles->id}.html";
                $leaves[$articles->id] = array (
                                                MIDCOM_NAV_SITE => Array (
                                                                          MIDCOM_NAV_URL => $articles->name.".html",
                                                                          MIDCOM_NAV_NAME => $articles->title),
                                                MIDCOM_NAV_ADMIN => Array (
                                                                           MIDCOM_NAV_URL => "view/" . $articles->id,
                                                                           MIDCOM_NAV_NAME => $articles->title),
                                                MIDCOM_NAV_GUID => $articles->guid(),
                                                MIDCOM_NAV_TOOLBAR => $toolbar,
                                                MIDCOM_META_CREATOR => $articles->creator,
                                                MIDCOM_META_EDITOR => $articles->revisor,
                                                MIDCOM_META_CREATED => $articles->created,
                                                MIDCOM_META_EDITED => $articles->revised
                                                );
            }
        }
        return $leaves;
    }


    function get_node() {

        // information about the current node (topic)
        $topic = &$this->_object;
        
        // Create Toolbar
        $toolbar[0] = Array
             (
              MIDCOM_TOOLBAR_URL => 'create.html',
              MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create entry'),
              MIDCOM_TOOLBAR_HELPTEXT => null,
              MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
              MIDCOM_TOOLBAR_ENABLED => true            
              );
        $toolbar[100] = Array(
                              MIDCOM_TOOLBAR_URL => '',
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
        
        debug_add ("Component: setting NAP Element to " . $object->name ." [" . $object->id . "]");
        $this->_object = $object;
        return true;
    }
} // navigation

?>