<?php
/**
 * @package net.nemein.bookmarks
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package net.nemein.bookmarks
 */
class net_nemein_bookmarks_navigation
{
    var $_topic;
    var $_config_topic;
    var $_config;
    var $_l10n;
    var $_l10n_midcom;
    var $_oldest_time;
    var $_oldest_creator;
    var $_latest_time;
    var $_latest_revisor;

    function net_nemein_bookmarks_navigation()
    {
        $this->_topic = null;
        $this->_config_topic = null;
        $this->_config = $GLOBALS["midcom_component_data"]['net.nemein.bookmarks']['config'];
        $i18n =& $_MIDCOM->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.bookmarks");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->_oldest_time = null;
        $this->_oldest_creator = null;
        $this->_latest_time = null;
        $this->_latest_revisor = null;
    }

    function is_internal()
    {
        return false;
    }

    function get_leaves()
    {
        $topic = &$this->_topic;
        $leaves = array ();
        $now = time();
        $tags = net_nemein_bookmarks_helper_list_tags($topic->id);

        // Prep toolbar
        $toolbar[50] = Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        );


        // Tags are the actual shown leaves
        foreach($tags as $tag => $bookmarks)
        {
            $active = "true";
            $showtag = "false";
            foreach($bookmarks as $bookmark)
            {
                if ($_MIDGARD['user'] == $bookmark->author || $_MIDGARD['admin'])
                {
                    $showtag = "true";
                    break;
                } elseif (!$_MIDGARD['user']) {
                    $showtag = "true";
                    break;
                }
            }
            if ($showtag == "true" && !empty($tag))
            {
                // Figure out when the tag was first used and when last edited
                foreach ($bookmarks as $url => $object)
                {
                    // Bookmarks must be leaves to support searching
                    $leaves[$object->id] = array (
                        MIDCOM_NAV_SITE =>
                            Array (
                                MIDCOM_NAV_URL => rawurlencode($tag).".html",
                                MIDCOM_NAV_NAME => $object->title,
                            ),
                        MIDCOM_NAV_ADMIN => null,
                        MIDCOM_NAV_NOENTRY => true,
                        MIDCOM_NAV_GUID => $object->guid(),
                        MIDCOM_META_CREATOR => $object->creator,
                        MIDCOM_META_EDITOR => $object->revisor,
                        MIDCOM_META_CREATED => $object->created,
                        MIDCOM_META_EDITED => $object->revised
                    );

                    if ($object->created < $this->_oldest_time || !$this->_oldest_time)
                    {
                        // Oldest not set or article is older than it
                        $this->_oldest_time = $object->created;
                        $this->_oldest_creator = $object->creator;
                    }

                    if ($object->revised > $this->_latest_time || !$this->_latest_time)
                    {
                        // Latest not set or article is newer than it
                        $this->_latest_time = $object->revised;
                        $this->_latest_revisor = $object->revisor;
                    }
                }

                $bookmark_count = count($tags[$tag]);
                $toolbar[50][MIDCOM_TOOLBAR_URL] = "list/{$tag}.html";
                $leaves[$tag] = array (
                    MIDCOM_NAV_SITE =>
                        Array (
                            MIDCOM_NAV_URL => rawurlencode($tag).".html",
                            MIDCOM_NAV_NAME => $tag . " (" . $bookmark_count . ")"
                        ),
                    MIDCOM_NAV_ADMIN => Array (
                        MIDCOM_NAV_URL => "list/" . rawurlencode($tag),
                        MIDCOM_NAV_NAME => $tag . " (" . $bookmark_count . ")"),
                    MIDCOM_NAV_VISIBLE => $active,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_NAV_GUID => $this->_topic->guid(),
                    MIDCOM_META_CREATOR => $this->_oldest_creator,
                    MIDCOM_META_EDITOR => $this->_latest_revisor,
                    MIDCOM_META_CREATED => $this->_oldest_time,
                    MIDCOM_META_EDITED => $this->_latest_time,
                );
            }
        }
        return $leaves;
    }

    function get_node()
    {
        $topic = &$this->_topic;

        // Create Toolbar
        $toolbar[100] = Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        );

        return array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $this->_config_topic->extra,
            MIDCOM_NAV_SCORE => $this->_config_topic->score,
            MIDCOM_NAV_VISIBLE => $this->_config->get("visible"),
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_META_CREATOR => $this->_config_topic->creator,
            MIDCOM_META_EDITOR => $this->_config_topic->revisor,
            MIDCOM_META_CREATED => $this->_config_topic->created,
            MIDCOM_META_EDITED => $this->_config_topic->revised
        );
    }

    function set_object($object)
    {
        $this->_config_topic = $object;
        $this->_config->store_from_object($object, "net.nemein.bookmarks");
        $this->_check_for_content_topic();

        // Load Schemas
        $data = midcom_get_snippet_content($this->_config->get("schemadb"));
        eval("\$schemadb = Array ({$data}\n);");
        $this->_schemas = Array();
        if (is_array($schemadb))
        {
            foreach ($schemadb as $schema)
            {
                $this->_schemas[$schema["name"]] = $schema["description"];
            }
        }

        return true;
    }

    function _check_for_content_topic()
    {
        $guid = $this->_config->get("symlink_topic");
        if (is_null($guid))
        {
            /* No Symlink Topic set */
            $this->_topic = $this->_config_topic;
            return;
        }
        $object = mgd_get_object_by_guid($guid);
        if (! $object || $object->__table__ != "topic")
        {
            debug_add("Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: "
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $_MIDCOM->generate_error("Failed to open symlink content topic.");
        }

        /* Check topic validity */
        $root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        if ($object->parameter("midcom", "component") != "net.nemein.bookmarks")
        {
            debug_add("Content Topic is invalid, see LOG_INFO object dump", MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            debug_print_r("ROOT topic object was:", $root, MIDCOM_LOG_INFO);
            $_MIDCOM->generate_error("Failed to open symlink content topic.");
        }

        $this->_topic = $object;
    }

    /*
     * function get_current_leaf()
     * {
     *
     *   return $GLOBALS["net_nemein_bookmarks_nap_activeid"];
    }*/

} // navigation

?>
