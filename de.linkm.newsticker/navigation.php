<?php
/**
 * @package de.linkm.newsticker
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Newsticker Viewer interface class
 *
 * @todo document
 *
 * @package de.linkm.newsticker
 */
class de_linkm_newsticker_navigation
{

    var $_topic;
    var $_config_topic;
    var $_config;
    var $_l10n;
    var $_l10n_midcom;
    var $_schemas;

    function de_linkm_newsticker_navigation()
    {
        $this->_topic = null;
        $this->_config_topic = null;
        $this->_config = $GLOBALS['midcom_component_data']['de.linkm.newsticker']['config'];
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("de.linkm.newsticker");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->_schemas = null;
    }

    function is_internal()
    {
        return false;
    }

    function get_leaves()
    {
        if (! array_key_exists('view_contentmgr', $GLOBALS))
        {
            // TEMPORARY HACK: Don't do NAP on-site, this is way too slow.
            return Array();
        }

        $topic = &$this->_topic;
        $leaves = array ();
        $now = time();

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $topic->id);
        $qb->add_order($this->_config->get('sort_order'));
        $articles = $qb->execute();

        if (! $articles)
        {
            return Array();
        }

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

        foreach ($articles as $article)
        {
            $active = false;
            if ($this->_config->get("index_list_old"))
            {
                $active = true;
            }
            else
            {
                $starttime = (trim($article->extra1) != "") ? strtotime($article->extra1) : 0;
                $endtime = (trim($article->extra2) != "") ? strtotime($article->extra2) : 0;

                if ($starttime == -1 || $endtime == -1)
                {
                    continue;
                }

                $active = ($starttime < $now && $now < $endtime);
            }

            $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$article->id}.html";
            $toolbar[50][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $article) == false);
            $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$article->id}.html";
            $toolbar[51][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:delete', $article) == false);

            $leaves[$article->id] = array (
                MIDCOM_NAV_SITE => Array (
                    MIDCOM_NAV_URL => $article->name . ".html",
                    MIDCOM_NAV_NAME => $article->title),
                MIDCOM_NAV_ADMIN => Array (
                    MIDCOM_NAV_URL => "view/" . $article->id,
                    MIDCOM_NAV_NAME => $article->title),
                MIDCOM_NAV_TOOLBAR => $toolbar,
                MIDCOM_NAV_GUID => $article->guid,
                MIDCOM_NAV_NOENTRY => ! $active,
                MIDCOM_META_CREATOR => $article->creator,
                MIDCOM_META_EDITOR => $article->revisor,
                MIDCOM_META_CREATED => $article->created,
                MIDCOM_META_EDITED => $article->revised
            );
        }
        return $leaves;
    }

    function get_node() {
        $topic = &$this->_topic;

        // Create Toolbar
        $i = 0;
        $hide_create = ($_MIDCOM->auth->can_do('midgard:create', $topic) == false);
        foreach ($this->_schemas as $name => $desc)
        {
            $text = sprintf($this->_l10n_midcom->get('create %s'), $desc);
            $toolbar[$i] = Array
            (
                MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                MIDCOM_TOOLBAR_LABEL => $text,
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true ,
                MIDCOM_TOOLBAR_HIDDEN => $hide_create
            );
            $i++;
        }
        $toolbar[100] = Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_config_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_config_topic)
            )
        );

        // Tweak topic revision date
        // TODO: Use NAP for this instead.
        // This now can leak revision time of unapproved new articles
        $topic_updated = $this->_config_topic->revised;
        $topic_updater = $this->_config_topic->revisor;

        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $topic->id);
        $qb->add_order('revised', 'DESC');
        $qb->set_limit(1);
        $result = $qb->execute();

        if ($result)
        {
            $article = $result[0];
            if ($article->revised > $topic_updated)
            {
                $topic_updated = $article->revised;
                $topic_updater = $article->revisor;
            }
        }

        return array
        (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $this->_config_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_CONFIGURATION => $this->_config,
            MIDCOM_META_CREATOR => $this->_config_topic->creator,
            MIDCOM_META_EDITOR => $topic_updater,
            MIDCOM_META_CREATED => $this->_config_topic->created,
            MIDCOM_META_EDITED => $topic_updated
        );
    }

    function set_object($object)
    {
        $this->_config_topic = $object;
        $this->_config->store_from_object($object, "de.linkm.newsticker");
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
        if (is_null($guid)) {
            /* No Symlink Topic set */
            $this->_topic = $this->_config_topic;
            return;
        }
        $object = new midcom_db_topic($guid);
        if (! $object)
        {
            debug_add("Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: "
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            $GLOBALS["midcom"]->generate_error("Failed to open symlink content topic.");
        }

        /* Check topic validity */
        $root = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
        if ($object->get_parameter('midcom', 'component') != 'de.linkm.newsticker')
        {
            debug_add("Content Topic is invalid, see LOG_INFO object dump", MIDCOM_LOG_ERROR);
            debug_print_r("Retrieved object was:", $object, MIDCOM_LOG_INFO);
            debug_print_r("ROOT topic object was:", $root, MIDCOM_LOG_INFO);
            $GLOBALS["midcom"]->generate_error("Failed to open symlink content topic.");
        }

        $this->_topic = $object;
    }

}

?>