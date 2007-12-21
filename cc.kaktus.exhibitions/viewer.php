<?php
/**
 * @package cc.kaktus.exhibitions
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 5500 2007-03-08 13:22:25Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Exhibition component MidCOM viewer class.
 *
 * @package cc.kaktus.exhibitions
 */
class cc_kaktus_exhibitions_viewer extends midcom_baseclasses_components_request
{
    /**
     * Constructor. Connect to the parent class constructor.
     */
    public function cc_kaktus_exhibitions_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Set the accepted request switches
     *
     * @access public
     * @return void
     */
    public function _on_initialize()
    {
        // Show the listing of all of the exhibitions
        // Match /
        $this->_request_switch['list_years'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_list', 'years'),
        );

        // Exhibition editing
        // Match /edit/<event guid>/
        $this->_request_switch['edit'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_edit', 'edit'),
            'fixed_args' => array ('edit'),
            'variable_args' => 1,
        );

        // Delete an exhibition
        // Match /delete/<event guid>/
        $this->_request_switch['delete'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_delete', 'delete'),
            'fixed_args' => array ('delete'),
            'variable_args' => 1,
        );

        // Create an exhibition
        // Match /create/<schema layout>/
        $this->_request_switch['create'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_create', 'create'),
            'fixed_args' => array ('create'),
            'variable_args' => 1,
        );

        // Create a subevent for an exhibition
        // Match /create/<schema layout>/<event guid>/
        $this->_request_switch['create_subevent'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_create', 'create'),
            'fixed_args' => array ('create'),
            'variable_args' => 2,
        );

        // Delete an event
        // Match /delete/<event guid>/
        $this->_request_switch['delete'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_delete', 'delete'),
            'fixed_args' => array ('delete'),
            'variable_args' => 1,
        );

        // Show attachments and subpages of an exhibition
        // Match /list/<type>/<event guid>/
        $this->_request_switch['list_leaves'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_leaves', 'list'),
            'fixed_args' => array ('list'),
            'variable_args' => 2,
        );

        // Show the ongoing exhibition page
        // Match /current/
        $this->_request_switch['current'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_view', 'current'),
            'fixed_args' => array ('current'),
        );

        // Show the future exhibitions
        // Match /future/
        $this->_request_switch['future'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_list', 'list'),
            'fixed_args' => array ('future'),
        );

        // Match /past/
        $this->_request_switch['past'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_list', 'list'),
            'fixed_args' => array ('past'),
        );

        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler' => array ('midcom_helper_dm2config_config', 'config'),
            'fixed_args' => array ('config'),
        );

        // Show listing for requested year
        // Match /<year>/
        $this->_request_switch['list_year'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_list', 'list'),
            'variable_args' => 1,
        );

        // Show an event
        // Match /<year>/<event name>/
        $this->_request_switch['view_exhibition'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_view', 'view'),
            'variable_args' => 2,
        );

        // Show event details if applicable
        // Match /<year>/<event extra>/<subpage extra>/
        $this->_request_switch['view_subpage'] = array
        (
            'handler' => array ('cc_kaktus_exhibitions_handler_view', 'view'),
            'variable_args' => 3,
        );
    }

    /**
     * Load the master event for exhibition listing
     *
     * @access private
     * @return boolean Indicating success
     */
    private function _load_master_event()
    {
        // Attempt to create the master event if it hasn't been initialized yet
        if (!$this->_config->get('master_event'))
        {
            // Try to create
            $this->_request_data['master_event'] = new midcom_db_event();
            $this->_request_data['master_event']->up = 0;
            $this->_request_data['master_event']->title = "Master event cc.kaktus.exhibitions ({$_MIDGARD['host']})";
            $this->_request_data['master_event']->description = "Master event for cc.kaktus.exhibitions for host ({$_MIDGARD['host']})";
            $this->_request_data['master_event']->start = 0;
            $this->_request_data['master_event']->end = 0;

            // Show an error page on creation failure
            if (!$this->_request_data['master_event']->create())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create the master event to initialize the component");
                // This will exit
            }

            $this->_topic->set_parameter('cc.kaktus.exhibitions', 'master_event', $this->_request_data['master_event']->guid);
            return true;
        }

        $this->_request_data['master_event'] = new midcom_db_event($this->_config->get('master_event'));
        return true;
    }

    /**
     * Set the common items for toolbar
     *
     * @access private
     */
    private function _populate_toolbar()
    {
        // If privileges allow to create
        if (   $this->_load_master_event()
            && $this->_request_data['master_event']->can_do('midgard:create'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "create/exhibition/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create an exhibition'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                )
            );
        }

        // Component configuration
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
    }

    /**
     * Load the schemadb and populate common toolbar items
     *
     * @access public
     * @return boolean Indicating success
     */
    public function _on_handle($handler, $args)
    {
        // Load schema database
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        // Populate the toolbar
        $this->_populate_toolbar();

        return true;
    }

    /**
     * Determine the return page after editing
     *
     * @access public
     * @static
     */
    static public function determine_return_page($guid, $layout = null)
    {
        $event = new midcom_db_event($guid);

        if (is_null($layout))
        {
            $layout = $event->get_parameter('midcom.helper.datamanager2', 'schema_name');
        }

        switch ($layout)
        {
            case 'subpage':
                $parent = new midcom_db_event($event->up);
                return date('Y', $parent->start) . "/{$parent->extra}/{$event->extra}/";

            case 'attachment':
                $parent = new midcom_db_event($event->up);
                return date('Y', $parent->start) . "/{$parent->extra}/";

            case 'exhibition':
                return date('Y', $event->start) . "/{$event->extra}/";

            default:
                return '';
        }

        return '';
    }

    /**
     * Generate a URL name
     *
     * @access public
     * @static
     * @return String
     */
    static public function generate_name($title)
    {
        $title = utf8_decode($title);

        // Hand set the accent characters
        $accents = array
        (
            'ä' => 'a',
            'Ä' => 'a',
            'á' => 'a',
            'Á' => 'a',
            'à' => 'a',
            'À' => 'a',
            'ã' => 'a',
            'Ã' => 'a',
            'â' => 'a',
            'Â' => 'a',
            'ö' => 'o',
            'Ö' => 'o',
            'ó' => 'o',
            'Ó' => 'o',
            'ò' => 'o',
            'Ò' => 'o',
            'ô' => 'o',
            'Ô' => 'o',
            'õ' => 'o',
            'Õ' => 'o',
            'ú' => 'u',
            'Ú' => 'u',
            'ù' => 'u',
            'Ù' => 'u',
            'û' => 'u',
            'Û' => 'u',
            'ü' => 'u',
            'Ü' => 'u',
            'í' => 'i',
            'Í' => 'i',
            'ì' => 'i',
            'Ì' => 'i',
            'ï' => 'i',
            'Ï' => 'i',
            'î' => 'i',
            'Î' => 'i',
            'é' => 'e',
            'É' => 'e',
            'è' => 'e',
            'È' => 'e',
            'ë' => 'e',
            'Ë' => 'e',
            'ê' => 'e',
            'Ê' => 'e',
            'ý' => 'y',
            'Ý' => 'y',
            'ÿ' => 'y',
        );

        foreach ($accents as $accent => $ascii)
        {
            $title = str_replace($accent, $ascii, $title);
        }

        $title = strtolower($title);

        $string = '';

        // Check each character for non-allowed characters
        for ($i = 0; $i < strlen($title); $i++)
        {
            $char = substr($title, $i, 1);

            if (!ereg('[a-zA-Z0-9\-_]', $char))
            {
                $string .= '-';
            }
            else
            {
                $string .= $char;
            }
        }

        return $string;
    }

    static public function get_image_size($string)
    {
        $attachment = null;

        if (mgd_is_guid($string))
        {
            $attachment = new midcom_baseclasses_database_attachment($string);
        }
        elseif (preg_match('/<a.+?href=[\'"].*?\/midcom-serveattachmentguid-([0-9a-f]+)\//', $string, $regs))
        {
            $attachment = new midcom_baseclasses_database_attachment($regs[1]);
        }
        elseif (preg_match('/src=[\'"].*?\/midcom-serveattachmentguid-([0-9a-f]+)\//', $string, $regs))
        {
            $attachment = new midcom_baseclasses_database_attachment($regs[1]);
        }

        if (   !$attachment
            || !isset($attachment->guid)
            || !$attachment->guid)
        {
            return array
            (
                'x' => 0,
                'y' => 0,
            );
        }

        $size = array ();
        $size['x'] = $attachment->get_parameter('midcom.helper.datamanager2.type.blobs', 'size_x');
        $size['y'] = $attachment->get_parameter('midcom.helper.datamanager2.type.blobs', 'size_y');

        return $size;
    }
}
?>