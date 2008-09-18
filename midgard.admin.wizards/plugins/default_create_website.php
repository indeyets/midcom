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
class default_create_website extends midcom_baseclasses_components_handler
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
                'handler' => array('default_create_website', 'create_website'),
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


        if (   isset($_POST['sitewizard_website_submit'])
            && !empty($_POST['sitewizard_website_submit']))
        {
            $session = new midcom_service_session();

            if (!$session->exists("midgard_admin_wizards_{$this->_request_data['session_id']}"))
            {

            }
            else
            {
                $text = $this->_request_data['session_id']; 
                $_MIDCOM->uimessages->add("Midgard Setup", $text, 'warning'); 
                $structure_creator = $session->get("midgard_admin_wizards_{$this->_request_data['session_id']}");
            }

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

                /* Get host and its root page */
                $host_creator = $structure_creator->get_host_creator();
                $host = $host_creator->get_host();
                $root_page = $host_creator->get_root_page();

                $this->_request_data['report']['hostname'] = $host->name;
                $this->_request_data['report']['prefix'] = $host->prefix;
                $this->_request_data['report']['port'] = $host->port;
                $this->_request_data['report']['sitename'] = $root_page->title;
                $this->_request_data['report']['topicname'] = $root_topic->name;
                
                $pieces = explode('.', $host->name);
                $alias = '';
               
                if(!empty($pieces))
                {
                    if ($pieces[0] == 'www')
                    {
                        $alias = $pieces[1] . "." . $pieces[2];
                    }
                } 
                else {
                    $alias = $host->name;
                }
                
                $this->_request_data['report']['alias'] = $alias;

                $vhost_path = $this->_request_data['plugin_config']['vhost_save_path'];

                $this->_request_data['report']['notification_email'] = $this->_request_data['plugin_config']['notification_email'];

                if (   isset($vhost_path) 
                    && !empty($vhost_path))
                {
                    $filename = "{$vhost_path}{$host->name}_{$host->port}";
                    $this->_generate_vhost($filename, $host);
                }
                
                if (   $this->_request_data['plugin_config']['staging2live']
                    && isset($this->_request_data['plugin_config']['staging2live_root_username'])
                    && isset($this->_request_data['plugin_config']['staging2live_root_password'])
                    && isset($this->_request_data['plugin_config']['staging2live_live_config'])
                    && isset($this->_request_data['plugin_config']['staging2live_live_url']))
                {
                    $this->_generate_live($host);
                }
            }
            catch (midgard_admin_sitewizard_exception $e)
            {
                $e->error();
                echo "WE SHOULD HANDLE THIS \n";
            }
        }

        return true;
    }
    
    function _generate_live($host)
    {
        // We use the old site wizard sitegroup creator command-line script to create the live DB
        $sitegroup_creator = MIDCOM_ROOT . '/midgard/admin/sitegroup/bin/create-sitegroup.php';
        if (!file_exists($sitegroup_creator))
        {
            $_MIDCOM->uimessages->add
            (
                $this->_l10n->get('midcom.admin.wizards'),
                sprintf($this->_l10n->get('sitegroup creator script %s not found'), $sitegroup_creator),
                'error'
            );
            return;
        }
        $sitegroup = mgd_get_sitegroup($host->sitegroup);
        
        // Safety for the arguments
        $conffile = escapeshellarg($this->_request_data['plugin_config']['staging2live_live_config']);
        $sitegroupname = escapeshellarg($sitegroup->name);
        $root_username = escapeshellarg($this->_request_data['plugin_config']['staging2live_root_username']);
        $root_password = escapeshellarg($this->_request_data['plugin_config']['staging2live_root_password']);
        $admin_username = escapeshellarg("{$this->_request_data['plugin_config']['staging2live_root_username']}_{$sitegroup->name}");
        
        // Create sitegroup to live server
        $sitegroup_creator_command = "php {$sitegroup_creator} -c {$conffile} -u {$root_username} -p {$root_password} -s {$sitegroupname} -an {$admin_username} -ap {$root_password}";
        $return = exec($sitegroup_creator_command);
        // TODO: Check that it worked        
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Running {$sitegroup_creator_command}, got {$return}", MIDCOM_LOG_DEBUG);
        debug_pop();
        
        // Create replication subscription
        $_MIDCOM->load_library('midcom.helper.replicator');
        $subscription = new midcom_helper_replicator_subscription_dba();
        $subscription->sitegroup = $host->sitegroup;
        $subscription->title = "Staging 2 Live";
        $subscription->status = 1; // MIDCOM_REPLICATOR_AUTOMATIC
        $subscription->transporter = 'http';
        $subscription->exporter = 'staging2live';
        $subscription->create();
        $subscription->parameter('midcom.helper.datamanager2', 'schema_name', 'staging2live');
        $subscription->parameter('midcom_helper_replicator_transporter_http', 'url', $this->_request_data['plugin_config']['staging2live_live_url']);
        $subscription->parameter('midcom_helper_replicator_transporter_http', 'username', "{$this->_request_data['plugin_config']['staging2live_root_username']}*{$sitegroup->name}");
        $subscription->parameter('midcom_helper_replicator_transporter_http', 'password', $this->_request_data['plugin_config']['staging2live_root_password']);
        $subscription->parameter('midcom_helper_replicator_transporter_http', 'use_force', true);
        
        // Touch everything
        foreach ($_MIDGARD['schema']['types'] as $classname => $null)
        {
            $dba_class = $_MIDCOM->dbclassloader->get_midcom_class_name_for_legacy_midgard_class($classname);
            if (   !$dba_class
                || !class_exists($dba_class))
            {
                continue;
            }
            
            $qb = $_MIDCOM->dbfactory->new_query_builder($dba_class);
            $qb->add_constraint('sitegroup', '=', $sitegroup->id);
            $results = $qb->execute();
            foreach ($results as $result)
            {
                $metadata = $result->get_metadata();
                $approve = false;
                if ($metadata->is_approved())
                {
                    $approve = true;
                }
                
                $result->update();
                
                if ($approve)
                {
                    $metadata->approve();
                }
                
                // The object should now happily be in the replication queue
            }
        }
        
        // Re-edit host to force another export in order to workaround a Midgard quirk where style and root end up being 0
        $dba_host = new midcom_db_host($host->guid);
        $dba_host->update();
    }
    
    function _generate_vhost($filename, $host)
    {
        // FIXME: Move vhost template to config
        include(MIDCOM_ROOT . "/midgard/admin/wizards/template_vhost.php");

        $this->_request_data['report']['vhost_filename'] = $filename;

        if (!file_exists($filename))
        {
            $vhost_config = str_replace('__HOST_IP__', $this->_request_data['plugin_config']['host_ip'], $vhost_config);
            $vhost_config = str_replace('__HOST_PORT__', $host->port, $vhost_config);
            $vhost_config = str_replace('__SERVER_NAME__', $host->name, $vhost_config);
            $vhost_config = str_replace('__SERVER_ALIAS__', $this->_request_data['report']['alias'], $vhost_config);
            $vhost_config = str_replace('__DOC_ROOT__', $this->_request_data['plugin_config']['document_root'], $vhost_config);
            $vhost_config = str_replace('__MYSQL_DB_USER_PASS__', $this->_request_data['plugin_config']['mysql_db_user_pass'], $vhost_config);
            $vhost_config = str_replace('__MIDGARD_ROOT__', $this->_request_data['plugin_config']['midgard_root'], $vhost_config);

            $_MIDCOM->uimessages->add
            (
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('generating vhost config')
            );

            if (!is_writable(dirname($filename)))
            {
                $_MIDCOM->uimessages->add
                (
                    $this->_l10n->get('midcom.admin.wizards'),
                    $this->_l10n->get('vhost file is not writable'),
                    'error'
                );
            }
            else
            {
                if ($handle = fopen($filename, 'w'))
                {
                    if (!fwrite($handle, $vhost_config))
                    {
                        $_MIDCOM->uimessages->add
                        (
                            $this->_l10n->get('midcom.admin.wizards'),
                            $this->_l10n->get('failed to write vhost config'),
                            'error'
                        );
                    }
                    else
                    {
                        $mail = new org_openpsa_mail();
                        $mail->to = $this->_request_data['plugin_config']['notification_email'];
                        $mail->from = 'sitewizard@midgard-project.org';
                        $mail->subject = $this->_l10n->get('new website created');
                        $mail->body = $this->_l10n->get('new website created') . "\n\n";
                        $mail->body .= $this->_l10n->get('generated vhost') . ": " . $filename . "\n\n";
                        $mail->body .= $this->_l10n->get('move vhost config under apache vhosts and restart');

                        if (!$mail->send())
                        {
                            $_MIDCOM->uimessages->add
                            (
                                $this->_l10n->get('midcom.admin.wizards'),
                                $this->_l10n->get('failed to send notification email'),
                                'error'
                            );
                        }

                    }
                }
                
                $session->remove("midgard_admin_wizards_{$this->_request_data['session_id']}");
                fclose($handle);
            }
        }
        else
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('midcom.admin.wizards'),
                $this->_l10n->get('vhost exists, not creating')
            );
        }
    }

    function _show_create_website()
    {
        midcom_show_style('default_sitewizard_website');
    }
}

?>
