<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wikipage edit handler
 *
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_edit extends midcom_baseclasses_components_handler
{
    /**
     * The wikipage we're editing
     *
     * @var net_nemein_wiki_wikipage
     * @access private
     */
    var $_page = null;

    /**
     * The Datamanager of the article to display
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    var $_preview = false;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatment is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $operations = Array();
        $operations['save'] = '';
        $operations['preview'] = $this->_l10n->get('preview');
        $operations['cancel'] = '';
        foreach ($this->_schemadb as $name => $schema)
        {
            $this->_schemadb[$name]->operations = $operations;
        }
    }

   /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager($page)
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($page))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_article->id}.");
            // This will exit.
        }
    }

    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_page);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }

    function _load_page($wikiword)
    {
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $wikiword);
        $result = $qb->execute();

        if (count($result) > 0)
        {
            $this->_page = $result[0];
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Check the edit request
     *
     * @access public
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        if (!$this->_load_page($args[0]))
        {
            return false;
        }
        $this->_page->require_do('midgard:update');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'preview':
                $this->_preview = true;
                $data['formmanager'] =& $this->_controller->formmanager;
                break;
            case 'save':
                // Reindex the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_wiki_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);
                $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('net.nemein.wiki'), sprintf($this->_request_data['l10n']->get('page %s saved'), $this->_page->title), 'ok');
                // *** FALL-THROUGH ***
            case 'cancel':
                if ($this->_page->name == 'index')
                {
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                }
                else
                {
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$this->_page->name}/");
                }
                // This will exit.
        }

        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "{$this->_page->name}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('view'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "delete/{$this->_page->name}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('delete'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:delete'),
            )
        );

        foreach (array_keys($this->_request_data['schemadb']) as $name)
        {
            if ($name == $this->_controller->datamanager->schema->name)
            {
                // The page is already of this type, skip
                continue;
            }

            $this->_view_toolbar->add_item(
                array
                (
                    MIDCOM_TOOLBAR_URL => "change/{$this->_page->name}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n->get('change to %s'),
                        $this->_l10n->get($this->_request_data['schemadb'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                    (
                        'change_to' => $name,
                    ),
                    MIDCOM_TOOLBAR_ENABLED => $this->_page->can_do('midgard:update'),
                )
            );
        }

        $_MIDCOM->bind_view_to_object($this->_page, $this->_controller->datamanager->schema->name);

        $data['view_title'] = sprintf($this->_request_data['l10n']->get('edit %s'), $this->_page->title);
        $_MIDCOM->set_pagetitle($data['view_title']);

        // Set the breadcrumb pieces
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$this->_page->name}/",
            MIDCOM_NAV_NAME => $this->_page->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "edit/{$this->_page->name}/",
            MIDCOM_NAV_NAME => $this->_request_data['l10n_midcom']->get('edit'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('markdown', 'net.nemein.wiki');

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;
        $data['preview_mode'] = $this->_preview;

        if ($this->_preview)
        {
            // Populate preview page with values from form
            $data['preview_page'] = $this->_page;
            foreach ($this->_controller->datamanager->schema->fields as $name => $type_definition)
            {
                if (!is_a($this->_controller->datamanager->types[$name], 'midcom_helper_datamanager2_type_text'))
                {
                    // Skip fields of other types
                    continue;
                }
                switch ($type_definition['storage'])
                {
                    case 'parameter':
                    case 'configuration':
                    case 'metadata':
                        // Skip
                        continue;
                    default:
                        $location = $type_definition['storage']['location'];
                }
                $data['preview_page']->$location = $this->_controller->datamanager->types[$name]->convert_to_storage();
            }

            // Load DM for rendering the page
            $datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
            $datamanager->autoset_storage($data['preview_page']);

            $data['wikipage_view'] = $datamanager->get_content_html();
            $data['wikipage'] =& $data['preview_page'];
            $data['autogenerate_toc'] = false;
            $data['display_related_to'] = false;

            // Replace wikiwords
            // TODO: We should somehow make DM2 do this so it would also work in AJAX previews
            $data['wikipage_view']['content'] = preg_replace_callback($this->_config->get('wikilink_regexp'), array($data['preview_page'], 'replace_wikiwords'), $data['wikipage_view']['content']);
        }

        midcom_show_style('view-wikipage-edit');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_change($handler_id, $args, &$data)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST')
        {
            $_MIDCOM->generate_error(MIDCOM_ERRFORBIDDEN, 'Only POST requests are allowed here.');
        }

        if (!$this->_load_page($args[0]))
        {
            return false;
        }
        $this->_page->require_do('midgard:update');

        // Change schema to redirect
        $this->_page->parameter('midcom.helper.datamanager2', 'schema_name', $_POST['change_to']);

        // Redirect to editing
        $_MIDCOM->relocate("edit/{$this->_page->name}/");
        // This will exit
    }
}
?>