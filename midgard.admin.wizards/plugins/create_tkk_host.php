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
class create_tkk_host extends midcom_baseclasses_components_handler
{
    var $_sitegroup_id = '';

   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function create_tkk_host()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        midgard_admin_wizards_viewer::load_sitewizard_class(&$this->_request_data);
        parent::_on_initialize();

        $this->_sitegroup_id = $this->_request_data['plugin_config']['default_sitegroup_id'];
      }

    function get_plugin_handlers()
    {
        return array
        (
            'sitewizard' => array
            (
                'handler' => array('create_tkk_host', 'create_host'),
            ),
        );
    }
    
    function _host_exists($hostname, $prefix)
    {
    /*
        $mc = new midgard_collector('midgard_host', 'name', $hostname);
        $mc->add_constraint('name', '=', $hostname);
        $mc->add_constraint('prefix', '=', $prefix);
        $mc->set_key_property('id');
        $mc->execute();
        $count = $mc->count();
      */  
        $qb = new midgard_query_builder('midgard_host');
        $qb->add_constraint('name', '=', $hostname);
        $qb->add_constraint('prefix', '=', $prefix);
        $count = $qb->count(); 
        
        if ($count > 0)
        {
            return true;
        }
        
        return false;
    }

    /**
     * @return boolean Indicating success.
     */
    function _handler_create_host()
    {
        $title = $this->_l10n->get('host creation');
        $_MIDCOM->set_pagetitle($title);

        if (   isset($_POST['tkk_sitewizard_sitename'])
            && !empty($_POST['tkk_sitewizard_sitename'])
            && isset($_POST['tkk_sitewizard_host'])
            && !empty($_POST['tkk_sitewizard_host']))
        {
            if (!$this->_host_exists($_POST['tkk_sitewizard_host'], $_POST['tkk_sitewizard_prefix']))
            {     
                try
                {
                    $sitewizard = new midgard_admin_sitewizard();

                    $host_creator = $sitewizard->initialize_host_creation($this->_sitegroup_id);
                    $host_creator->set_page_title($_POST['tkk_sitewizard_sitename']);
                    $hostname_cleaned = str_replace('http://', '', $_POST['tkk_sitewizard_host']);  
                    $hostname_cleaned = str_replace('https://', '', $hostname_cleaned);   
                    $pieces = explode(':', $hostname_cleaned);
                    $hostname_cleaned = $pieces[0];
                   
                    $host_creator->set_host_url($hostname_cleaned);

                    if (    isset($_POST['tkk_sitewizard_prefix'])
                        &&  !empty($_POST['tkk_sitewizard_prefix']))
                    {
                        $host_creator->set_host_prefix($_POST['tkk_sitewizard_prefix']);
                    }

                    $host_creator->set_host_port(80);
                
                    if (    isset($this->_request_data['plugin_config']['create_child_style'])
                        &&  is_bool($this->_request_data['plugin_config']['create_child_style']))
                    {
                        $host_creator->set_create_child_style($this->_request_data['plugin_config']['create_child_style']);
                    }

                    $host_creator->set_make_host_copy(true);
                    $host_creator->set_copy_host_url($_POST['tkk_sitewizard_host']);

                    if (    isset($this->_request_data['plugin_config']['copy_host_port'])
                        &&  !empty($this->_request_data['plugin_config']['copy_host_port']))
                    {
                        $host_creator->set_copy_host_port($this->_request_data['plugin_config']['copy_host_port']);
                    }
                    else
                    {
                        $host_creator->set_copy_host_port(8001);
                    }

                    if (    isset($_POST['tkk_sitewizard_prefix'])
                        &&  !empty($_POST['tkk_sitewizard_prefix']))
                    {
                        $host_creator->set_copy_host_prefix("/" . $_POST['tkk_sitewizard_host'] . $_POST['tkk_sitewizard_prefix']);
                    }
                    else
                    {
                        $host_creator->set_copy_host_prefix("/" . $_POST['tkk_sitewizard_host']);
                    }

                    if (    isset($this->_request_data['plugin_config']['copy_host_name'])
                        &&  !empty($this->_request_data['plugin_config']['copy_host_name']))
                    {
                        $host_creator->set_copy_host_url($this->_request_data['plugin_config']['copy_host_name']);
                    }

                    $session = new midcom_service_session();
                    $session->set("midgard_admin_wizards_{$this->_request_data['session_id']}", $host_creator);

                    $_MIDCOM->relocate($this->_request_data['next_plugin_full_path']);
                }
                catch (midgard_admin_sitewizard_exception $e)
                {
                    $e->error();
                    echo "WE SHOULD HANDLE THIS \n";
                }
            }
            else
            {
                $_MIDCOM->uimessages->add(
                    $this->_l10n->get('midcom.admin.wizards'),
                    $this->_l10n->get('The host name already exists')
                );
            }
        }
        elseif (   isset($_POST['tkk_sitewizard_host_submit'])
                && !empty($_POST['tkk_sitewizard_host_submit']))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('you need to fill in both fields')
            );
        }

        $this->_request_data['current_host'] = new midcom_db_host($_MIDGARD['host']);

        return true;
    }

    function _show_create_host()
    {
        midcom_show_style('tkk_sitewizard_host');
    }
}

?>