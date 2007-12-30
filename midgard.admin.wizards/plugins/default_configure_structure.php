<?php
/**
 * @package midgard.admin.wizards
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a plugin for configuring the structure
 *
 * @package midgard.admin.wizards
 */
class default_configure_structure extends midcom_baseclasses_components_handler
{
    var $nullstorage_schemadb = null;

    var $nullstorage_controller = null;

   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function default_configure_structure()
    {
	    parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        if (   isset($this->_request_data['plugin_config']['sitewizard_path'])
            && !empty($this->_request_data['plugin_config']['sitewizard_path']))
        {
            require_once($this->_request_data['plugin_config']['sitewizard_path']);
        }
        else
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('sitewizard was not found')
            );
            $_MIDCOM->relocate('');
        }

        parent::_on_initialize();

      }

    function get_plugin_handlers()
    {
        return array
        (
	        'sitewizard' => array
	        (
	            'handler' => array('default_configure_structure', 'configure_structure'),
	        ),
	    );
    }

    private function prepare_nullstorage_schemadb($schemadb)
    {
        $this->nullstorage_schemadb = midcom_helper_datamanager2_schema::load_database($schemadb);
    }

    private function prepare_nullstorage_controller()
    {
        $this->nullstorage_controller =& midcom_helper_datamanager2_controller::create('nullstorage');
        $this->nullstorage_controller->set_schemadb($this->nullstorage_schemadb);
        $this->nullstorage_controller->schemaname = 'settings';
        $this->nullstorage_controller->initialize();
    }

    private function process_nullstorage_controller($session, $structure_creator)
    {
        if ($this->nullstorage_controller->process_form() == 'save')
        {
            $schemavalues = array();

            foreach ($this->nullstorage_controller->datamanager->schema->field_order as $name)
            {
                switch ($this->nullstorage_controller->datamanager->schema->fields[$name]['type'])
                {
                     default:
                         $schemavalues[$name] = $this->nullstorage_controller->datamanager->types[$name]->value;
                         break;

                     //case 'select':
                 }
            }

            $structure_creator->set_schema_values($schemavalues);

            $session->set("midgard_admin_wizards_{$this->_request_data['session_id']}", $structure_creator);

            // Relocate
            $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
        }
        elseif ($this->nullstorage_controller->process_form() == 'cancel')
        {
            // Relocate user to template selection
            //$_MIDCOM->relocate("{$prefix}template/{$this->_request_data['sitegroup']->id}/");
            // This will exit
        }
    }

	/**
     * @return bool Indicating success.
	 */
    function _handler_configure_structure()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $title = $this->_l10n->get('configure');
        $_MIDCOM->set_pagetitle($title);

        $session = new midcom_service_session();

        if (!$session->exists("midgard_admin_wizards_{$this->_request_data['session_id']}"))
        {

        }
        else
        {
            $structure_creator = $session->get("midgard_admin_wizards_{$this->_request_data['session_id']}");
        }

        try
        {
            $schemadb = $structure_creator->get_schemadb();

            print_r($schemadb);

            if ($schemadb == null || empty($schemadb['settings']['fields']))
            {
                $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
            }

            $this->prepare_nullstorage_schemadb($schemadb);
            $this->prepare_nullstorage_controller();
            $this->process_nullstorage_controller($session, $structure_creator);

            $this->_request_data['nullstorage_controller'] = $this->nullstorage_controller;
        }
        catch (midgard_admin_sitewizard_exception $e)
        {
            $e->error();
            echo "WE SHOULD HANDLE THIS \n";
        }

        return true;
    }

    function _show_configure_structure()
    {
        midcom_show_style('default_sitewizard_configure');
    }
}

?>

