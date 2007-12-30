<?php
/**
 * @package net.nemein.alphabeticalindex
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a creation handler class for net.nemein.alphabeticalindex
 *
 * This component lists all it's objects in Alphabetical list.
 * Items can be added through itself (internal and external) or
 * use custom schema on articles.
 * Component listens update,create,delete events on articles and folders
 * Example schema item:
'show_in_list' => Array
(
    'title' => 'Show in alphabetical list',
    'storage' => array
    (
        'location' => 'configuration',
        'domain'   => 'net.nemein.alphabeticalindex:show_in_list',
        'name'     => 'status'
    ),
    'required' => false,
    'type' => 'boolean',
    'widget' => 'checkbox',
),
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_handler_create  extends midcom_baseclasses_components_handler
{
    /**
     * The alphabet item
     *
     * @var array
     * @access private
     */
    var $_item = null;

    /**
     * The alphabet item type (internal/external)
     *
     * @var string
     * @access private
     */
    var $_type = null;

    /**
     * Current topic
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_topic = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The Datamanager of the event to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_controller = null;

    /**
     * The defaults to use for the new item.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * Simple default constructor.
     */
    function net_nemein_alphabeticalindex_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
        $this->_topic =& $this->_request_data['topic'];
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schemadb'] =& $this->_schemadb;
        $this->_request_data['item'] =& $this->_item;
        $this->_request_data['link_type'] =& $this->_type;
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb()
    {
        $src = $this->_config->get('schemadb');
        $schemadb = midcom_helper_datamanager2_schema::load_database($src);

        if (count($schemadb) < 1)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the schema db from '{$src}'!");
            // This will exit.
        }

        $this->_schemadb =& $schemadb;
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller($handler_id)
    {
        $this->_load_schemadb();

        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;

        $this->_controller->schemaname = $this->_type;

        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback
     */
    function &dm2_create_callback(&$controller)
    {
        if ($this->_type == 'external')
        {
            $this->_item = new net_nemein_alphabeticalindex_item();
            $this->_item->title = $_POST['title'];
            $this->_item->url = $_POST['url'];
            $this->_item->node = $this->_topic->id;

            if (! $this->_item->create())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('Item creation failed! We operated on this object:', $this->_item);
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new item, cannot continue. Last Midgard error was: '. mgd_errstr());
            }

            $_MIDCOM->uimessages->add(
                $_MIDCOM->i18n->get_string('net.nemein.alphabeticalindex', 'net.nemein.alphabeticalindex'),
                sprintf($_MIDCOM->i18n->get_string('item %s has been added to alphabetical index', 'net.nemein.alphabeticalindex'), $this->_item->title),
                'ok'
            );
        }
        else
        {
            if (   isset($_POST['net_nemein_alphabeticalindex_article_chooser_widget_selections'])
                && !empty($_POST['net_nemein_alphabeticalindex_article_chooser_widget_selections']))
            {
                foreach ($_POST['net_nemein_alphabeticalindex_article_chooser_widget_selections'] as $guid => $value)
                {
                    if ($value)
                    {
                        $object = new midcom_db_article($guid);

                        $qb = net_nemein_alphabeticalindex_item::new_query_builder();
                        $qb->add_constraint('objectGuid', '=', $object->guid);
                        $qb->add_constraint('node.id', '=', $this->_topic->id);

                        $results = $qb->execute();
                        if (count($results) <= 0)
                        {
                            $item = new net_nemein_alphabeticalindex_item();
                            $item->title = $object->title;
                            $item->url = "{$GLOBALS['midcom_config']['midcom_site_url']}midcom-permalink-{$object->guid}";
                            $item->objectGuid = $object->guid;
                            $item->cachedUrl = $_MIDCOM->permalinks->resolve_permalink($object->guid);
                            $item->node = $this->_topic->id;

                            if ($item->create())
                            {
                                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.alphabeticalindex', 'net.nemein.alphabeticalindex'), sprintf($_MIDCOM->i18n->get_string('item %s has been added to alphabetical index', 'net.nemein.alphabeticalindex'), $item->title), 'ok');
                            }

                            $this->_item =& $item;
                        }
                        else
                        {
                            $this->_item =& $results[0];
                        }
                    }
                }
            }
            if (   isset($_POST['net_nemein_alphabeticalindex_topic_chooser_widget_selections'])
                && !empty($_POST['net_nemein_alphabeticalindex_topic_chooser_widget_selections']))
            {
                foreach ($_POST['net_nemein_alphabeticalindex_topic_chooser_widget_selections'] as $guid => $value)
                {
                    if ($value)
                    {
                        $object = new midcom_db_topic($guid);

                        $qb = net_nemein_alphabeticalindex_item::new_query_builder();
                        $qb->add_constraint('objectGuid', '=', $object->guid);
                        $qb->add_constraint('node.id', '=', $this->_topic->id);

                        $results = $qb->execute();
                        if (count($results) <= 0)
                        {
                            $item = new net_nemein_alphabeticalindex_item();
                            $item->title = $object->extra;
                            $item->url = "{$GLOBALS['midcom_config']['midcom_site_url']}midcom-permalink-{$object->guid}";
                            $item->objectGuid = $object->guid;
                            $item->cachedUrl = $_MIDCOM->permalinks->resolve_permalink($object->guid);
                            $item->node = $this->_topic->id;

                            if ($item->create())
                            {
                                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('net.nemein.alphabeticalindex', 'net.nemein.alphabeticalindex'), sprintf($_MIDCOM->i18n->get_string('item %s has been added to alphabetical index', 'net.nemein.alphabeticalindex'), $item->title), 'ok');
                            }

                            $this->_item =& $item;
                        }
                        else
                        {
                            $this->_item =& $results[0];
                        }
                    }
                }
            }
        }

        return $this->_item;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_create($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_topic->require_do('midgard:create');

        $this->_type = $args[0];
        if (empty($this->_type))
        {
            $this->_type = 'internal';
        }

        $this->_load_controller($handler_id);
        $this->_prepare_request_data($handler_id);
        $this->_update_breadcrumb_line();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $topic = new midcom_db_topic($this->_topic->id);
                if ($topic) {
                    $topic->update();
                }
            case 'cancel':
                $_MIDCOM->relocate("");
                // This will exit.
        }

        debug_pop();
        return true;
    }

    function _show_create($handler_id, &$data)
    {
        midcom_show_style('item-create');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => sprintf
            (
                $this->_l10n_midcom->get('create %s'),
                $this->_l10n->get("{$this->_type} item")
            ),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

}
?>