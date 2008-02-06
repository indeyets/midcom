<?php
/**
 * @package midgard.admin.wizards
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a plugin for selecting a structure
 *
 * @package midgard.admin.wizards
 */
class select_tkk_structure extends midcom_baseclasses_components_handler
{
    var $_sitegroup_id = '';

    var $_creation_root_group_guid = '';

   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function select_tkk_structure()
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

        if (   isset($this->_request_data['plugin_config']['creation_root_group_guid'])
            && !empty($this->_request_data['plugin_config']['creation_root_group_guid']))
        {
            $this->_creation_root_group_guid = $this->_request_data['plugin_config']['creation_root_group_guid'];
        }

        parent::_on_initialize();

      }

    function get_plugin_handlers()
    {
        return array
        (
            'sitewizard' => array
            (
                'handler' => array('select_tkk_structure', 'select_structure'),
            ),
        );
    }

    /**
     * Gets template structures from filesystem
     */
    function _get_template_structures_filesystem()
    {
        $dir = $this->_request_data['plugin_config']['structure_config_path'];
        $structures_array = array();

        if (is_dir($dir))
        {
            if ($dh = opendir($dir))
            {
                while (($file = readdir($dh)) !== false)
                {
                    $path = $dir . $file;
                    
                    $fileExp = explode('.', $file);
                    $filetype = $fileExp[count($fileExp) -1];
                    
                    if (file_get_contents($path) != '' && $filetype == 'inc')
                    {
                        eval("$" . "evaluated" . " = array(" . file_get_contents($path) . ");");

                        $keys = array_keys($evaluated);
                        if (count($keys) != 0)
                        {
                            if (   array_key_exists('title', $evaluated[$keys[0]])
                                && array_key_exists('description', $evaluated[$keys[0]]))
                            {
                                $structures_array = array_merge($structures_array, $evaluated);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }

         return $structures_array;
    }

    /**
     * @return boolean Indicating success.
     */
    function _handler_select_structure()
    {
        $title = $this->_l10n->get('structure selection');
        $_MIDCOM->set_pagetitle($title);

        if (   isset($_POST['tkk_sitewizard_structure_submit'])
            && !empty($_POST['tkk_sitewizard_structure_submit'])
            && isset($_POST['tkk_sitewizard_structure_select_template'])
            && !empty($_POST['tkk_sitewizard_structure_select_template']))
        {
            $session = new midcom_service_session();

            if (!$session->exists("midgard_admin_wizards_{$this->_request_data['session_id']}"))
            {
                echo "HERE";
            }
            else
            {
                $host_creator = $session->get("midgard_admin_wizards_{$this->_request_data['session_id']}");
            }

            try
            {
                $structure_creator = $host_creator->next_link();
                $structure_creator->set_verbose(true);

                if ($_POST['tkk_sitewizard_structure_select_template'] == 'none')
                {

                }
                else
                {
                    $structure_creator->read_config($this->_request_data['plugin_config']['structure_config_path']
                        . $_POST['tkk_sitewizard_structure_select_template'] . ".inc");
                    //$structure_creator->set_creation_root_group('e32da6065ac411dba4b95bbbb548039a039a');
                    //$structure_creator->set_creation_root_topic('5c7c4a76761711dc96616575a3e41a5c1a5c');
                }

                $session->set("midgard_admin_wizards_{$this->_request_data['session_id']}", $structure_creator);

                $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        elseif (   isset($_POST['tkk_sitewizard_structure_submit'])
                && !empty($_POST['tkk_sitewizard_structure_submit']))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('you need to select a structure template')
            );
        }

        $this->_request_data['structure_templates'] = $this->_get_template_structures_filesystem();

        return true;
    }

    function _show_select_structure()
    {
        midcom_show_style('tkk_sitewizard_structure');
    }
}

?>