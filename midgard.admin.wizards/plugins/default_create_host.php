<?php
/**
 * @package midgard.admin.wizards
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a plugin for creating a host
 *
 * @package midgard.admin.wizards
 */
class default_create_host extends midcom_baseclasses_components_handler
{
   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        midgard_admin_wizards_viewer::load_sitewizard_class(&$this->_request_data);
        parent::_on_initialize();
    }

    function get_plugin_handlers()
    {
        return array
        (
            'sitewizard' => array
            (
                'handler' => array('default_create_host', 'create_host'),
            ),
        );
    }

    /**
     * @return boolean Indicating success.
     */
    function _handler_create_host()
    {
        $title = $this->_l10n->get('host creation');
        $_MIDCOM->set_pagetitle($title);

        if (   isset($_POST['default_sitewizard_sitename'])
            && !empty($_POST['default_sitewizard_sitename'])
            && isset($_POST['default_sitewizard_host'])
            && !empty($_POST['default_sitewizard_host']))
        {
            try
            {
                $session = new midcom_service_session();
                $structure_creator = $session->get("midgard_admin_wizards_{$this->_request_data['session_id']}");

                $setup_config = new midgard_setup_config("midcom", null, null);
                $host_creator = new midgard_admin_sitewizard_creator_host(&$setup_config);

                $host_creator->set_page_title($_POST['default_sitewizard_sitename']);
                $host_creator->set_host_url($_POST['default_sitewizard_host']);

                if (    isset($_POST['default_sitewizard_prefix'])
                    &&  !empty($_POST['default_sitewizard_prefix']))
                {
                    $host_creator->set_host_prefix($_POST['default_sitewizard_prefix']);
                }

                $host_creator->set_host_port($_POST['default_sitewizard_port']);

                $host_creator->set_make_host_copy(true);
                
                if(isset($_POST['tkk_sitewizard_host']))
                {
                    $host_creator->set_copy_host_url($_POST['tkk_sitewizard_host']);
                }
                
                $host_creator->set_copy_host_port($this->_request_data['plugin_config']['copy_host_port']);

                $structure_creator->set_host_creator($host_creator);
                $structure_creator->add_link($host_creator);

                $session->set("midgard_admin_wizards_{$this->_request_data['session_id']}", $structure_creator);

                /* TODO 
                 1. Check if host exists
                 2. Relocate to itself if host with name already exists  */

                $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        elseif (   isset($_POST['tkk_sitewizard_host_submit'])
                && !empty($_POST['tkk_sitewizard_host_submit']))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('you need to fill in both fields'),
                'warning'
            );
        }

        $this->_request_data['current_host'] = new midcom_db_host($_MIDGARD['host']);

        return true;
    }

    function _show_create_host()
    {
        midcom_show_style('default_sitewizard_host');
    }
}

?>