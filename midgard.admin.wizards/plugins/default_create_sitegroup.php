<?php
/**
 * @package midgard.admin.wizards
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a plugin for creating a sitegroup
 *
 * @package midgard.admin.wizards
 */
class default_create_sitegroup extends midcom_baseclasses_components_handler
{


   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function default_create_sitegroup()
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
                'handler' => array('default_create_sitegroup', 'create_sitegroup'),
            ),
        );
    }

    /**
     * @return boolean Indicating success.
     */
    function _handler_create_sitegroup()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $title = $this->_l10n->get('sitegroup creation');
        $_MIDCOM->set_pagetitle($title);


        if (   isset($_POST['default_sitewizard_sitegroup'])   && !empty($_POST['default_sitewizard_sitegroup'])
            && isset($_POST['default_sitewizard_adminuser'])  && !empty($_POST['default_sitewizard_adminuser'])
            && isset($_POST['default_sitewizard_adminpass'])  && !empty($_POST['default_sitewizard_adminpass']))
        {
            try
            {
                $sitewizard = new midgard_admin_sitewizard();
                $sitegroup_creator = $sitewizard->initialize_sitegroup_creation();
                $sitegroup_creator->set_sitegroup_name($_POST['default_sitewizard_sitegroup']);
                $sitegroup_creator->set_sitegroup_admin_username($_POST['default_sitewizard_adminuser']);
                $sitegroup_creator->set_sitegroup_admin_password($_POST['default_sitewizard_adminpass']);

                $session = new midcom_service_session();
                $session->set("midgard_admin_wizards_{$this->_request_data['session_id']}", $sitegroup_creator);

                $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        elseif (   isset($_POST['tkk_sitewizard_sitegroup_submit'])
                && !empty($_POST['tkk_sitewizard_sitegroup_submit']))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('you need to fill in all fields'),
                'warning'
            );
        }

        return true;
    }

    function _show_create_sitegroup()
    {
        midcom_show_style("default_sitewizard_sitegroup");
    }
}

?>