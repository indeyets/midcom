<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Handle the folder editing requests
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_edit extends midcom_baseclasses_components_handler
{
    /**
     * DM2 schema
     *
     * @access private
     * @var midcom_helper_datamanager2_schema $_schema
     */
    var $_schemadb;

    /**
     * DM2 controller instance
     *
     * @access private
     * @var midcom_helper_datamanager2_controller $_controller
     */
    var $_controller;

    /**
     * ID of the handler
     *
     * @access private
     */
    var $_handler_id;

    /**
     * Constructor method
     *
     * @access public
     */
    function midcom_admin_folder_handler_edit()
    {
        $this->_component = 'midcom.admin.folder';
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Load the schemadb and other midcom.admin.folder specific stuff
     *
     * @access public
     */
    function _on_initialize()
    {
        // Load the configuration
        $_MIDCOM->componentloader->load('midcom.admin.folder');

        if (!class_exists('midcom_helper_datamanager2'))
        {
            $_MIDCOM->componentloader->load('midcom.helper.datamanager2');
        }

        $this->_config =& $GLOBALS['midcom_component_data']['midcom.admin.folder']['config'];
    }

    /**
     * Load either a create controller or an edit (simple) controller or trigger an error message
     *
     * @access private
     */
    function _load_controller()
    {
        // Get the configured schemas
        $schemadbs = $this->_config->get('schemadbs_folder');

        // Check if a custom schema exists
        if (array_key_exists($this->_topic->component, $schemadbs))
        {
            $schemadb = $schemadbs[$this->_topic->component];
        }
        else
        {
            if (!array_key_exists('default', $schemadbs))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Configuration error. No default schema for topic has been defined!');
                // This will exit
            }

            $schemadb = $schemadbs['default'];
        }

        $GLOBALS['midcom_admin_folder_mode'] = $this->_handler_id;

        // Create the schema instance
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($schemadb);

        switch ($this->_handler_id)
        {
            case 'edit':
                $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
                $this->_controller->schemadb =& $this->_schemadb;
                $this->_controller->set_storage($this->_topic);
                break;

            case 'create':
                $this->_schemadb->default->fields['name']['required'] = 0;
                $this->_controller =& midcom_helper_datamanager2_controller::create('create');
                $this->_controller->schemadb =& $this->_schemadb;
                $this->_controller->schemaname = 'default';
                $this->_controller->callback_object =& $this;

                // Suggest to create the same type of a folder as the parent is
                $this->_controller->defaults = array
                (
                    'component' => $this->_topic->component,
                );
                break;

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Unable to process the request, unknown handler id');
                // This will exit
        }

        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_event->id}.");
            // This will exit.
        }

    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_new_topic = new midcom_db_topic();
        $this->_new_topic->up = $this->_topic->id;

        if (! $this->_new_topic->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_new_topic);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new topic, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_new_topic;
    }


    /**
     * Handler for folder editing. Checks for the permissions and folder integrity.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midcom.admin.folder:topic_management');

        $this->_handler_id = str_replace('____ais-folder-', '', $handler_id);

        if ($this->_handler_id == 'create')
        {
            $this->_topic->require_do('midgard:create');
        }
        else
        {
            $this->_topic->require_do('midgard:update');
        }

        // Load the DM2 controller
        $this->_load_controller();

        // Get the content topic prefix
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        // Store the old name before editing
        $old_name = $this->_topic->name;

        switch ($this->_controller->process_form())
        {
            case 'cancel':
                $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.folder'), $this->_l10n->get('cancelled'));
                $_MIDCOM->relocate($prefix);
                break;

            case 'save':
                if ($this->_handler_id === 'edit')
                {
        			if ($_REQUEST['style'] === '__create')
        			{
            			$this->_topic->style = $this->_create_style($this->_topic->name);

            			// Failed to create the new style template
            			if ($this->_topic->style === '')
            			{
                			return false;
            			}

						$_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.folder'), $this->_l10n->get('new style created'));

        				if (! $this->_topic->update())
        				{
            				$_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.folder'), sprintf($this->_l10n->get('could not save folder: %s'), mgd_errstr()));
            				return false;
        				}

        			}

                    $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.folder'), $this->_l10n->get('folder saved'));

                    // Get the relocation url
                    $url = preg_replace("/{$old_name}\/\$/", "{$this->_topic->name}/", $prefix);
                }
                else
                {
                    $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.folder'), $this->_l10n->get('folder created'));

                    // Generate name if it is missing
                    if (!$this->_new_topic->name)
                    {
                        $this->_new_topic->name = midcom_generate_urlname_from_string($this->_new_topic->extra);
                        $this->_new_topic->update();
                    }

                    // Get the relocation url
                    $url = "{$prefix}/{$this->_new_topic->name}/";
                }

                $_MIDCOM->relocate($url);
                // This will exit
        }

        if ($this->_handler_id == 'create')
        {
            $data['title'] = sprintf($_MIDCOM->i18n->get_string('create folder', 'midcom.admin.folder'));

            // Hide the button in toolbar
            $this->_node_toolbar->hide_item('__ais/folder/create.html');

            $this->_topic->require_do('midgard:create');
        }
        else
        {
            $data['title'] = sprintf($_MIDCOM->i18n->get_string('edit folder %s', 'midcom.admin.folder'), $data['topic']->extra);

            // Hide the button in toolbar
            $this->_node_toolbar->hide_item('__ais/folder/edit.html');
        }

        // Add the view to breadcrumb trail
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__ais/folder/edit.html',
            MIDCOM_NAV_NAME => $data['title']
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);


        $data['topic'] =& $this->_topic;
        $data['controller'] =& $this->_controller;

        // Set page title
        $_MIDCOM->set_pagetitle($data['title']);

        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('edit_folder', 'midcom.admin.folder');

        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.folder');

        // Serve the correct localization
        $data['l10n'] =& $this->_l10n;

        // Add style sheet
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.folder/folder.css',
            )
        );

        return true;
    }

    /**
     * Create a new style for the topic
     *
     * @access private
     * @param string $name Name of the style
     * @return string Style path
     */
    function _create_style($style_name)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (isset($GLOBALS['midcom_style_inherited']))
        {
            $up = $_MIDCOM->style->get_style_id_from_path($GLOBALS['midcom_style_inherited']);
            debug_add("Style inherited from {$GLOBALS['midcom_style_inherited']}");
        }
        else
        {
            $up = $_MIDGARD['style'];
            debug_add("No inherited style found, placing the new style under host style (ID: {$_MIDGARD['style']}");
        }

        $style = new midcom_db_style();
        $style->name = $style_name;
        $style->up = $up;

        if (!$style->create())
        {
            debug_print_r('Failed to create a new style due to ' . mgd_errstr(), $style, MIDCOM_LOG_WARN);
            debug_pop();

            $_MIDCOM->uimessages->add('edit folder', sprintf($_MIDCOM->i18n->get_string('failed to create a new style template: %s', 'midcom.admin.folder'), mgd_errstr()), 'error');
            return '';
        }

        debug_print_r('New style created', $style);
        debug_pop();

        return $_MIDCOM->style->get_style_path_from_id($style->id);
    }

    /**
     * Shows the _Edit folder_ page.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     * @access private
     */
    function _show_edit($handler_id, &$data)
    {
        $styles_all = midcom_admin_folder_folder_management::list_styles();

        // Show the style element
        if ($this->_handler_id === 'create')
        {
            $data['page_title'] = sprintf($this->_i18n->get_string("create folder", 'midcom.admin.folder'));
        }
        else
        {
            $data['page_title'] = sprintf($this->_i18n->get_string("{$this->_handler_id} folder %s", 'midcom.admin.folder'), $this->_topic->extra);
        }

        midcom_show_style('midcom-admin-show-folder-actions');
    }
}
?>