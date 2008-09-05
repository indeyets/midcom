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
class create_tkk_website extends midcom_baseclasses_components_handler
{
   /**
    * Simple constructor, which only initializes the parent constructor.
    */
    function create_tkk_website()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        $_MIDCOM->load_library('org.openpsa.mail');
        midgard_admin_wizards_viewer::load_sitewizard_class(&$this->_request_data);
        parent::_on_initialize();
    }

    function get_plugin_handlers()
    {
        return array
        (
            'sitewizard' => array
            (
                'handler' => array('create_tkk_website', 'create_website'),
            ),
        );
    }

    /**
     * @return boolean Indicating success.
     */
    function _handler_create_website()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $title = $this->_l10n->get('website creation');
        $_MIDCOM->set_pagetitle($title);
        
        if (   isset($_POST['tkk_sitewizard_website_cancel'])
            && !empty($_POST['tkk_sitewizard_website_cancel']))
        {
            $_MIDCOM->relocate('');
        }

        $session = new midcom_service_session();

        if (!$session->exists("midgard_admin_wizards_{$this->_request_data['session_id']}"))
        {

        }
        else
        {
            $structure_creator = $session->get("midgard_admin_wizards_{$this->_request_data['session_id']}");
        }

        if (   isset($_POST['tkk_sitewizard_website_submit'])
            && !empty($_POST['tkk_sitewizard_website_submit']))
        {
            try
            {
            //print_r($structure_creator);

                if (isset($this->_request_data['plugin_config']['verbose']))
                {
                    $structure_creator->set_verbose($this->_request_data['plugin_config']['verbose']);
                }

                $structure_creator->set_midcom_path(MIDCOM_ROOT);
                //$structure_creator->create_creation_root_group(0, $root_page->title . " Administrators");
                $structure_creator->execute();

                $host = $structure_creator->get_host();
                $root_topic = $structure_creator->get_creation_root_topic();
                $root_group = $structure_creator->get_creation_root_group();
                $root_page = $structure_creator->get_root_page();

                $this->_request_data['report']['hostname'] = $host->name;
                $this->_request_data['report']['prefix'] = $host->prefix;
                $this->_request_data['report']['port'] = $host->port;
                $this->_request_data['report']['sitename'] = $root_page->title;
                $this->_request_data['report']['topicname'] = $root_topic->name;

                $vhost_path = $this->_request_data['plugin_config']['vhost_save_path'];

                $this->_request_data['report']['notification_email'] = $this->_request_data['plugin_config']['notification_email'];

                if (isset($vhost_path) && !empty($vhost_path))
                {
                    include(MIDCOM_ROOT . "/midgard/admin/wizards/template_vhost.php");

                    $filename = $vhost_path . $host->name . "_" . $host->port;

                    $this->_request_data['report']['vhost_filename'] = $filename;

                    if (!file_exists($filename))
                    {
                        $vhost_config = str_replace('__HOST_IP__', $this->_request_data['plugin_config']['host_ip'], $vhost_config);
                        $vhost_config = str_replace('__HOST_PORT__', $host->port, $vhost_config);
                        $vhost_config = str_replace('__SERVER_NAME__', $host->name, $vhost_config);
                        $vhost_config = str_replace('__DOC_ROOT__', $this->_request_data['plugin_config']['document_root'], $vhost_config);
                        $vhost_config = str_replace('__MYSQL_DB_USER_PASS__', $this->_request_data['plugin_config']['mysql_db_user_pass'], $vhost_config);
                        $vhost_config = str_replace('__MIDGARD_ROOT__', $this->_request_data['plugin_config']['midgard_root'], $vhost_config);

                        $_MIDCOM->uimessages->add(
                            $this->_l10n->get('midcom.admin.wizards'),
                            $this->_l10n->get('generating vhost config')
                        );

                        if ($handle = fopen($filename, 'w'))
                        {
                            if (!is_writable($filename))
                            {
                                $_MIDCOM->uimessages->add(
                                    $this->_l10n->get('midcom.admin.wizards'),
                                    $this->_l10n->get('vhost file is not writable')
                                );
                            }
                            else
                            {
                                if (!fwrite($handle, $vhost_config))
                                {
                                    $_MIDCOM->uimessages->add(
                                        $this->_l10n->get('midcom.admin.wizards'),
                                        $this->_l10n->get('failed to write vhost config')
                                    );
                                }
                                else
                                {
                                    $mail = new org_openpsa_mail();
                                    $mail->to = $this->_request_data['plugin_config']['notification_email'];
                                    $mail->from = 'sitewizard@tkk.fi';
                                    $mail->subject = $this->_l10n->get('new website created');
                                    $mail->body = $this->_l10n->get('new website created') . "\n\n";
                                    $mail->body .= $this->_l10n->get('generated vhost') . ": " . $filename . "\n\n";
                                    $mail->body .= $this->_l10n->get('move vhost config under apache vhosts and restart');

                                    if (!$mail->send())
                                    {
                                        $_MIDCOM->uimessages->add(
                                            $this->_l10n->get('midcom.admin.wizards'),
                                            $this->_l10n->get('failed to send notification email')
                                        );
                                    }

                                }
                            }
                        }

                        $session->remove("midgard_admin_wizards_{$this->_request_data['session_id']}");
                        fclose($handle);
                    }
                    else
                    {
                        $_MIDCOM->uimessages->add(
                            $this->_l10n->get('midcom.admin.wizards'),
                            $this->_l10n->get('vhost exists, not creating')
                        );
                    }
                }
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }
        else
        {
            
            $host_creator = $structure_creator->previous_link();
            
            $this->_request_data['prereport']['hostname'] = $host_creator->get_host_name();
            $this->_request_data['prereport']['prefix'] = $host_creator->get_host_prefix();
            $this->_request_data['prereport']['port'] = $host_creator->get_host_port();
            $this->_request_data['prereport']['sitename'] = $host_creator->get_root_page_title();
            $this->_request_data['prereport']['topicname'] = $host_creator->get_host_name() . $host_creator->get_host_prefix();
            
            $vhost_path = $this->_request_data['plugin_config']['vhost_save_path'];

            $this->_request_data['prereport']['notification_email'] = $this->_request_data['plugin_config']['notification_email'];
            
            if (isset($vhost_path) && !empty($vhost_path))
            {
                $this->_request_data['prereport']['vhost_filename'] = $vhost_path 
                    . $host_creator->get_host_name() . "_" . $host_creator->get_host_port();
            }
            else
            {
                $this->_request_data['prereport']['vhost_filename'] = "";
            }
            
        }

        return true;
    }

    function _show_create_website()
    {
        midcom_show_style('tkk_sitewizard_website');
    }
}

?>