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
class default_select_style extends midcom_baseclasses_components_handler
{
   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function default_select_style()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        // Load the Midgard Site Wizard classes from midgard-data (1.9 onwards)
        if (   !isset($this->_request_data['plugin_config']['sitewizard_path'])
            || empty($this->_request_data['plugin_config']['sitewizard_path'])
            || !file_exists($this->_request_data['plugin_config']['sitewizard_path']))
        {
            $_MIDCOM->uimessages->add
            (
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('sitewizard class not found'),
                'error'
            );
            $_MIDCOM->relocate('');
            // This will exit
        }
        
        require_once($this->_request_data['plugin_config']['sitewizard_path']);

        parent::_on_initialize();

      }

    function get_plugin_handlers()
    {
        return array
        (
            'sitewizard' => array
            (
                'handler' => array('default_select_style', 'select_style'),
            ),
        );
    }

    /**
     * @return boolean Indicating success.
     */
    function _handler_select_style()
    {
        $title = $this->_l10n->get('style selection');
        $_MIDCOM->set_pagetitle($title);

        if (   isset($_POST['sitewizard_style_submit'])
            && !empty($_POST['sitewizard_style_submit'])
            && isset($_POST['sitewizard_style_select_template'])
            && !empty($_POST['sitewizard_style_select_template']))
        {
            $session = new midcom_service_session();

            if (!$session->exists("midgard_admin_wizards_{$this->_request_data['session_id']}"))
            {

            }
            else
            {
                $host_creator = $session->get("midgard_admin_wizards_{$this->_request_data['session_id']}");
            }

            try
            {
                $host_creator->set_host_style($_POST[sitewizard_style_select_template]);

                $session->set("midgard_admin_wizards_{$this->_request_data['session_id']}", $host_creator);

                $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        elseif (   isset($_POST['sitewizard_style_submit'])
                && !empty($_POST['sitewizard_style_submit']))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('you need to select a style template')
            );
        }

        $qb = midcom_db_style::new_query_builder();
        $qb->add_constraint('name', 'LIKE', 'template_%');
        $qb->add_constraint('up', '=', 0);
        // TODO: Check for sitegroups?
        $templates = $qb->execute();

        foreach ($templates as $template)
        {
            if (   isset($this->_request_data['plugin_config']['show_style_templates'])
                && count($this->_request_data['plugin_config']['show_style_templates']) > 0)
            {
                foreach ($this->_request_data['plugin_config']['show_style_templates'] as $show)
                {
                    if ($template->name == $show)
                    {
                        $this->_request_data['templates'][] = $template;
                    }
                }
            }
            else
            {
                $this->_request_data['templates'] = $templates;
            }
        }

        return true;
    }

    function _show_select_style()
    {
        midcom_show_style('default_sitewizard_style');
    }
}

?>