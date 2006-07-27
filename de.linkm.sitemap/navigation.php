<?php
/**
 * @package de.linkm.sitemap
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sitemap MidCOM Navigation class.
 *
 * @package de.linkm.sitemap
 */
class de_linkm_sitemap_navigation {

    var $_debug_prefix;
    var $_object;

    var $_l10n;
    var $_l10n_midcom;

    function de_linkm_sitemap_navigation() {
        $this->_debug_prefix = "de.linkm.sitemap navigation::";
        $this->_object = null;
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("de.linkm.sitemap");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
    }

    function get_leaves() {
        debug_push($this->_debug_prefix . "get_leaves()");

        $topic = & $this->_object;
        $leaves = array ();

        // $topic is the archive: list sitemaps
        // (torben) Note, that this should actually check if the subtopics are sitemaps
        // Essentially, I do not understand this really...
        // Disabled for now, until somebody cares to explain
        /*
        if ($sitemap_list = mgd_list_topics($topic->id))
        {
            while ($sitemap_list->fetch())
            {
                $leaves[$sitemap_list->id] = array (
                    MIDCOM_NAV_SITE => Array (
                        MIDCOM_NAV_URL => $sitemap_list->name,
                        MIDCOM_NAV_NAME => $sitemap_list->extra
                      ),
                    MIDCOM_NAV_ADMIN => null,
                    MIDCOM_META_CREATOR => $sitemap_list->creator,
                    MIDCOM_META_EDITOR => $sitemap_list->revisor,
                    MIDCOM_META_CREATED => $sitemap_list->created,
                    MIDCOM_META_EDITED => $sitemap_list->revised
                );
            }
        }
        else
        {
            debug_add("Current object is a sitemap topic: no leaves.");
        }
        */

        debug_pop();
        return $leaves;
    }

    function get_node() {
        $topic = & $this->_object;
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
            MIDCOM_NAV_CONFIGURATION => null, // Needs Baseclass conversion
            MIDCOM_META_CREATOR => $topic->creator,
            MIDCOM_META_EDITOR => $topic->revisor,
            MIDCOM_META_CREATED => $topic->created,
            MIDCOM_META_EDITED => $topic->revised
        );

    }


    function set_object($object) {
        debug_push($this->_debug_prefix . "set_object()");

        debug_add("Setting NAP Element to " . $object->name .
          " [" . $object->id . "]");
        $this->_object = $object;

        debug_pop();
        return true;
    }


}

?>