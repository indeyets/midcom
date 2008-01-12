<?php
/**
 * @package no.odindata.quickform
 * @author Tarjei Huse, tarjei@nu.no
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Quickform site view class
 *
 * @package no.odindata.quickform
 */
class no_odindata_quickform_viewer extends midcom_baseclasses_components_request
{

    var $msg;

    /**
     * The schema database associated with articles.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = Array();

    /**
     * An index over the schema database associated with the topic mapping
     * schema keys to their names. For ease of use.
     *
     * @var Array
     * @access private
     */
    var $_schemadb_article_index = Array();

    /**
     * The datamanager instance controlling the article to show, or null in case there
     * is no article at this time. The request data key 'datamanager' is set to a
     * reference to this member during class startup.
     *
     * @var midcom_helper_datamanager
     * @access private
     */
    var $_datamanager = null;

    function no_odindata_quickform_viewer($topic, $config)
    {
        $this->msg = '';
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        // no caching as different input requires different emails .)
        $_MIDCOM->cache->content->no_cache();

        $this->_request_data['topic'] =& $this->_topic;

        // argv has the following format: topic_id/mode
        $this->_request_switch[] = Array
        (
            'handler' => 'index',

        // No further arguments, we have neither fixed nor variable arguments.
        );
        $this->_request_switch[] = Array
        (
            'fixed_args' => 'submitok',
            'handler' => Array('no_odindata_quickform_handler_aftersubmits', 'submitok'),
            'variable_args' => 0,
        );

        // Match /submitnotok/
        $this->_request_switch[] = Array
        (
            'handler' => Array('no_odindata_quickform_handler_aftersubmits', 'submitnotok'),
            'fixed_args' => 'submitnotok',
            'variable_args' => 0,
        );

        // Match /reports/
        $this->_request_switch[] = Array
        (
            'handler' => Array('no_odindata_quickform_handler_reports', 'report_index'),
            'fixed_args' => 'reports',
            'variable_args' => 0,
        );
        // Match /reports/list_all/
        $this->_request_switch[] = Array
        (
            'handler' => Array('no_odindata_quickform_handler_reports', 'report_list_all'),
            'fixed_args' => Array('reports', 'list_all'),
            'variable_args' => 0,
        );

        // Match /reports/list_by_key/
        $this->_request_switch[] = Array
        (
            'handler' => Array('no_odindata_quickform_handler_reports', 'report_list_by_key'),
            'fixed_args' => Array('reports', 'list_by_key'),
            'variable_args' => 0,
        );

        // Match /reports/list_by_key_distinct/
        $this->_request_switch[] = Array
        (
            'handler' => Array('no_odindata_quickform_handler_reports', 'report_list_by_key_distinct'),
            'fixed_args' => Array('reports', 'list_by_key_distinct'),
            'variable_args' => 0,
        );

        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('no_odindata_quickform_handler_configuration', 'configdm'),
            'schemadb' => 'file:/no/odindata/quickform/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );
    }

    /**
     * Add the generally used toolbar items
     *
     * @access private
     * @return void
     */
    function _populate_toolbar()
    {
        $this->_node_toolbar->add_item
        (
            Array
            (
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => ($this->_topic->can_do('midgard:update') && $this->_topic->can_do('midcom:component_config')) ? true : false,
            )
        );
        $this->_node_toolbar->add_item
        (
            Array
            (
                MIDCOM_TOOLBAR_URL => 'reports/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('Reports'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('Reports helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_data-edit-table.png',
                MIDCOM_TOOLBAR_ENABLED => ($this->_topic->can_do('midgard:update') && $this->_topic->can_do('midcom:component_config')) ? true : false,
            )
        );
    }

    /**
     * Do the preparation for the later handler phases. Returns always true
     *
     * @access private
     * @return boolean Indicating success
     */
    function _on_handle($handler, $args)
    {
        $this->_populate_toolbar();
        $this->_load_schema_database();

        $this->_request_data['datamanager'] = & $this->_datamanager;

        if (   $this->_config->get('schema_name') == ''
            && array_key_exists('default', $this->_schemadb))
        {
            $this->_schema_name = 'default' ;
        }
        else
        {
            $this->_schema_name = $this->_config->get('schema_name');
        }

        return true;
    }

    /**
     * Prepares a dm form from the config schema and displays it
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        $to_mail = $this->_config->get('mail_new_item');
        $to_article = $this->_config->get('save_form_as_article');

        $save_ok = false;


        if (! $this->_prepare_creation_datamanager($this->_schema_name))
        {
            debug_pop();
            return false;
        }

        $this->_request_data['form_description'] = $this->_config->get('form_description');

        // Now launch the datamanager processing loop
        switch ($this->_datamanager->process_form_to_array())
        {
            case MIDCOM_DATAMGR_EDITING:
                break;

            case MIDCOM_DATAMGR_SAVED:
                if($to_mail)
                {
                    if ($this->_save_to_mail())
                    {
                        $save_ok = true;
                    }
                    else
                    {
                        $save_ok = false;
                    }
                }

                if($to_article)
                {
                    if ($this->_save_to_article())
                    {
                        $save_ok = true;
                    }
                    else
                    {
                        $save_ok = false;
                    }
                }

                if ($save_ok)
                {
                    $_MIDCOM->relocate('submitok.html');
                }
                else
                {
                    $_MIDCOM->relocate('submitnotok.html');
                }
                // this will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                // Redirect to view page.
                $_MIDCOM->relocate('');
                // This will exit()

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = 'The Datamanager failed critically while processing the form, see the debug level log for more details.';
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }

        debug_pop();
        return true;

       }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
       midcom_show_style('show-form');
    }

    /**
     * Internal helper, loads the configured schema database into the class.
     * It is not yet evaluated by a datamanager, only the file is loaded.
     *
     * @see $_schemadb
     * @see $_schemadb_index
     * @access private
     */
    function _load_schema_database()
    {
        $path = $this->_config->get('schemadb');

        $data = midcom_get_snippet_content($path);

        eval("\$this->_schemadb = Array ({$data}\n);");

        // This is a compatibility value for the configuration system
        //TODO: remove
        //$GLOBALS['de_linkm_taviewer_schemadbs'] =& $this->_schemadbs;

        if (is_array($this->_schemadb))
        {
            if (count($this->_schemadb) == 0)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Could not load the schema database associated with this topic: The schema DB in {$path} was empty.");
                // This will exit.
            }
            foreach ($this->_schemadb as $schema)
            {
                $this->_schemadb_index[$schema['name']] = $this->_l10n->get($schema['description']);
            }
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Could not load the schema database associated with this topic. The schema DB was no array.');
            // This will exit.
        }
    }

    /**
     * Prepares the datamanager for creation of a new article. When returning false,
     * it sets errstr and errcode accordingly.
     *
     * @param string $schema The name of the schema to initialize for
     * @return boolean Indicating success
     * @access private
     */
    function _prepare_creation_datamanager($schema)
    {
        $this->_datamanager = new midcom_helper_datamanager_getvar($this->_schemadb);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Failed to create a datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        // show js if the editor needs it.
        $this->_datamanager->set_show_javascript(true);

        if (! $this->_datamanager->init_creation_mode($schema, $this))
        {
            $this->errstr = "Failed to initialize the datamanager in creation mode for schema '{$schema}'.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        return true;
    }


    /**
     * Save to mail
     */
    function _save_to_mail()
    {
        debug_push('_save_to_mail');
        $subject = $this->_config->get('mail_subject');
        $to      = $this->_config->get('mail_address_to');
        $data    = $this->_datamanager->get_array();
        $fields  = $this->_datamanager->get_fieldnames();
        $schema  = $this->_datamanager->get_layout_database();

        $mail =  new org_openpsa_mail();
        $mail->to = $to;

        //$data['_schema'] = '';
        $headers = '';
        $email_to = '';
        $message = '';
        $mail->body = '';


        if (array_key_exists('email', $fields))
        {
            if (   !array_key_exists('midcom_helper_datamanager_field_email', $_POST)
                || trim($_POST['midcom_helper_datamanager_field_email']) === '')
            {
                return false;
            }

            $mail->from = $_POST['midcom_helper_datamanager_field_email'];
        }
        else
        {
            $mail->from = $this->_config->get('mail_address_from');
        }

        foreach ($fields as $field => $description)
        {
            if ($field == 'email' )
            {
                $email_to = $data[$field];
            }

            $description = $this->_l10n->get($description);

            if (   array_key_exists ('widget' , $schema[$this->_datamanager->get_layout_name()]['fields'][$field])
                && $schema[$this->_datamanager->get_layout_name()]['fields'][$field]['widget'] == 'radiobox')
            {
                $mail->body .= "{$description}\n";
                $mail->body .= "    ". $schema[$this->_datamanager->get_layout_name()]['fields'][$field]['widget_radiobox_choices'][$data[$field]] . "\n";
            }
            else
            {
                $mail->body .= "{$description}\n";
                $mail->body .= "    " . $data[$field] . "\n";
            }
            $mail->body .= "\n";
        }


        if ($this->_config->get('mail_address_from'))
        {
            $mail->from = $this->_config->get('mail_address_from');
        }

        if ($this->_config->get('mail_reply_to_from_submitter') && array_key_exists('email', $fields) && $fields['email'] && array_key_exists('email', $data) && $data['email'])
        {
            $reply_to = $data['email'];
        }
        elseif ($this->_config->get('mail_reply_to'))
        {
            $reply_to = $this->_config->get('mail_reply_to');
        }
        else
        {
            $reply_to = $mail->from;
        }

        $mail->subject = $this->_config->get('mail_subject');
        $mail->headers['Reply-To'] = $reply_to;
        $mail->to = $this->_config->get('mail_address_to');

        // Handle possible attachments
        foreach ($this->_datamanager->attachments as $attachment)
        {
            $mail->attachments[] = $attachment;
        }

        /* too bad I didn't get validation of this stuff! TODO! */
        if (   $this->_config->get('mail_reciept')
            && $email_to !== '')
        {
            $smessage = $this->_config->get('mail_reciept_message') . '\n';

            if ($this->_config->get('mail_reciept_data'))
            {
                $smessage .= $mail->body;
            }
            if ($this->_config->get('mail_reply_to_recipient'))
            {
                $reply_to = $this->_config->get('mail_reply_to_recipient');
            }
            elseif ($this->_config->get('mail_reply_to'))
            {
                $reply_to = $this->_config->get('mail_reply_to');
            }
            else
            {
                $reply_to = '';
            }
            $mail->headers['Reply-To'] = $reply_to;
            $mail->body = $smessage;

            if (!$mail->send())
            {
                debug_add("Mail to recipient {$mail->to} failed, error message: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
                /*
                ob_start();
                print_r($mail);
                $mail_r = ob_get_contents();
                ob_end_clean();
                debug_add("Mail object dump:\n===\n{$mail_r}===\n");
                */
                debug_pop();
                return false;
            }
            else
            {
                debug_add("Mail sent to '{$mail->to}' with subject '{$mail->subject}'", MIDCOM_LOG_INFO);
                /*
                ob_start();
                print_r($mail);
                $mail_r = ob_get_contents();
                ob_end_clean();
                debug_add("Mail object dump:\n===\n{$mail_r}===\n");
                */
            }
        }
        else
        {
            debug_add('Mail should not be sent to submitter: ' . $this->_config->get('mail_reciept'), MIDCOM_LOG_INFO);
        }

        $mail->body .= "\nMail submitted on " . strftime('%x %X', time());
        $mail->body .= "\nFrom IP: {$_SERVER['REMOTE_ADDR']}";

        debug_add("Sending mail to '{$mail->to}' with subject '{$mail->subject}'", MIDCOM_LOG_INFO);
        /*
        ob_start();
        print_r($mail);
        $mail_r = ob_get_contents();
        ob_end_clean();
        debug_add("Mail object dump:\n===\n{$mail_r}===\n");
        debug_pop();
        */

        if (!$mail->send())
        {
            debug_add("Mail to recipient {$mail->to} failed, error message: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
            /*
            ob_start();
            print_r($mail);
            $mail_r = ob_get_contents();
            ob_end_clean();
            debug_add("Mail object dump:\n===\n{$mail_r}===\n");
            */
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }


    /**
     * Save to article
     */
    function _save_to_article()
    {
        $sudo_mode = false;
        if(!$this->_request_data['topic']->can_do('midgard:create'))
        {
            $sudo_mode = true;
            $_MIDCOM->auth->request_sudo('no.odindata.quickform');
        }

        $data    = $this->_datamanager->get_array();
        $fields  = $this->_datamanager->get_fieldnames();
        $schema  = $this->_datamanager->get_layout_database();

        $article = new midcom_db_article();
        $article->name = time();
        $article->topic = $this->_request_data['topic']->id;


        $stat = $article->create();
        if (!$stat)
        {
            debug_add("Article creation failed, error message: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
            ob_start();
            print_r($article);
            $article_r = ob_get_contents();
            ob_end_clean();
            debug_add("Article object dump:\n===\n{$article_r}===\n");
            debug_pop();
            return false;
        }
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('name', '=', $article->name);
        $qb->add_constraint('topic', '=', $this->_request_data['topic']->id);
        $tmp_article = $qb->execute();

        if(count($tmp_article) == 0)
        {
            return false;
        }

        $article = new midcom_db_article($tmp_article[0]->id);

        $tmp_data = '';
        foreach ($fields as $field => $description)
        {
            if (   array_key_exists ('widget' , $schema[$this->_datamanager->get_layout_name()]['fields'][$field])
                && $schema[$this->_datamanager->get_layout_name()]['fields'][$field]['widget'] == 'radiobox')
            {
                $tmp_data = $schema[$this->_datamanager->get_layout_name()]['fields'][$field]['widget_radiobox_choices'][$data[$field]];
            }
            else
            {
                $tmp_data = $data[$field];
            }

            if(isset($schema['default']['fields'][$field]['location']) && ($schema['default']['fields'][$field]['location'] != 'parameter'))
            {
                $article->$schema['default']['fields'][$field]['location'] = $tmp_data;
            }
            else
            {
                $article->parameter('midcom.helper.datamanager2', $field, $tmp_data);
            }
        }
        print_r($article);
        $article->update();

        if ($sudo_mode)
        {
            $_MIDCOM->auth->drop_sudo();
        }
        return true;
    }

     /**
     * Callback for the datamanager create mode.
     *
     * @access protected
     */
    function _dm_create_callback(&$datamanager)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $result = Array
        (
            'success' => true,
            'storage' => null,
        );

        $midgard = $_MIDCOM->get_midgard();
        $this->_article = new midcom_baseclasses_database_article();
        if (   array_key_exists('create_index', $_REQUEST)
            && $_REQUEST['create_index'] == 1)
        {
            $this->_article->name = 'index';
        }

        $this->_article->topic = $this->_topic->id;
        $this->_article->author = $midgard->user;
        if (! $this->_article->create())
        {
            debug_add('Could not create article: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }

        if ( $this->_config->get('auto_approved') == true )
        {
            $meta =& midcom_helper_metadata::retrieve($this->_article);
            $meta->approve();
        }

        $result['storage'] =& $this->_article;
        debug_pop();
        return $result;
    }
}

?>
