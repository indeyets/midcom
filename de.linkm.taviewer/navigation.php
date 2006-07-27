<?php
/**
 * @package de.linkm.taviewer
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer NAP interface class
 *
 * This class has been rewritten for MidCOM 2.6 utilizing all of the currently
 * available state-of-the-art technology.
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package de.linkm.taviewer
 */

class de_linkm_taviewer_navigation extends midcom_baseclasses_components_navigation
{
    /*
    var $_topic;
    var $_config_topic;
    var $_config;
    var $_l10n;
    var $_l10n_midcom;
    var $_schemas;
    */

    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * Simple constructor, calls base class.
     */
    function de_linkm_taviewer_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns all leaves for the current content topic.
     *
     * It will hide the index leaf from the NAP information unless we are in Autoindex
     * mode. The leaves' title are used as a description within NAP, and the toolbar will
     * contain edit and delete links.
     */
    function get_leaves()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $sort = $this->_config->get('sort_order');
        $reverse = false;
        if (! $sort)
        {
            $sort = 'score';
        }
        if (substr($sort, 0, 7) == 'reverse')
        {
            $sort = substr($sort, 8);
            $reverse = true;
        }

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_order($sort);
        $result = $qb->execute();

        // Prepare everything
        $leaves = array ();
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

        foreach ($result as $article)
        {
            // Match the toolbar to the correct URL.
            $toolbar[50][MIDCOM_TOOLBAR_URL] = "edit/{$article->id}.html";
            $toolbar[50][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:update', $article) == false);
            $toolbar[51][MIDCOM_TOOLBAR_URL] = "delete/{$article->id}.html";
            $toolbar[51][MIDCOM_TOOLBAR_HIDDEN] = ($_MIDCOM->auth->can_do('midgard:delete', $article) == false);

            // If we have the index article skip it for site display.
            // if and only if we are not in autoindex mode.
            if (   $article->name == 'index'
                && ! $this->_config->get('autoindex'))
            {
                $leaves[$article->id] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "",
                        MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                    ),
                    MIDCOM_NAV_ADMIN => Array
                    (
                        MIDCOM_NAV_URL => "view/{$article->id}",
                        MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                    ),
                    MIDCOM_NAV_GUID => $article->guid,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_NAV_NOENTRY => true,
                    MIDCOM_META_CREATOR => $article->creator,
                    MIDCOM_META_EDITOR => $article->revisor,
                    MIDCOM_META_CREATED => $article->created,
                    MIDCOM_META_EDITED => $article->revised
                );
            }
            else
            {
                $leaves[$article->id] = array
                (
                    MIDCOM_NAV_SITE => Array
                    (
                        MIDCOM_NAV_URL => "{$article->name}.html",
                        MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                    ),
                    MIDCOM_NAV_ADMIN => Array
                    (
                        MIDCOM_NAV_URL => "view/{$article->id}",
                        MIDCOM_NAV_NAME => ($article->title != '') ? $article->title : $article->name
                    ),
                    MIDCOM_NAV_GUID => $article->guid,
                    MIDCOM_NAV_TOOLBAR => $toolbar,
                    MIDCOM_META_CREATOR => $article->creator,
                    MIDCOM_META_EDITOR => $article->revisor,
                    MIDCOM_META_CREATED => $article->created,
                    MIDCOM_META_EDITED => $article->revised
                );
            }

        }

        debug_pop();
        return $leaves;
    }

    /**
     * The node listing will add a list of create links of all schemas to the toolbar.
     *
     * @todo Convert mgd_get_article_by_name to MgdSchema.
     */
    function get_node()
    {
        // Check visibility
        if (   $this->_config->get('autoindex') != 1
            && mgd_get_article_by_name($this->_topic->id, 'index') === false)
        {
            $hidden = true;
        }
        else
        {
            $hidden = false;
        }

        // Load Schemas
        $data = midcom_get_snippet_content($this->_config->get('schemadb'));
        eval("\$schemadb = Array ({$data}\n);");
        $this->_schemas = Array();
        if (is_array($schemadb))
        {
            foreach ($schemadb as $schema)
            {
                $this->_schemas[$schema['name']] = $schema['description'];
            }
        }

        // Create Toolbar
        $i = 0;
        foreach ($this->_schemas as $name => $desc)
        {
            $text = sprintf($this->_l10n_midcom->get('create %s'), $desc);
            $toolbar[$i] = Array
            (
                MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                MIDCOM_TOOLBAR_LABEL => $text,
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $this->_topic) == false)
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
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
            )
        );

        return array
        (
            MIDCOM_NAV_URL => '',
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_NOENTRY => $hidden,
            MIDCOM_NAV_CONFIGURATION => $this->_config,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
    }

    /**
     * This event handler will determine the content topic, which might differ due to a
     * set content symlink.
     */
    function _on_set_object()
    {
        $this->_determine_content_topic();
        return true;
    }

    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     *
     * @access protected
     */
    function _determine_content_topic()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid))
        {
            // No symlink topic
            // Workaround, we should talk to an DBA object automatically here in fact.
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }

        $this->_content_topic = new midcom_db_topic($guid);

        // Validate topic.

        if (! $this->_content_topic)
        {
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: '
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->generate_error('Failed to open symlink content topic.');
            // This will exit.
        }

        if ($this->_content_topic->get_parameter('midcom', 'component') != 'de.linkm.taviewer')
        {
            debug_print_r('Retrieved topic was:', $this->_content_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Symlink content topic is invalid, see the debug level log for details.');
            // This will exit.
        }

        debug_pop();
    }


}

?>
