<?php

/**
 * Request class quickform
 * @package no.odindata.quickform
 * 
 */

class no_odindata_quickform_viewer  extends midcom_baseclasses_components_request {
    
    var $msg;

    /**
     * The schema database accociated with articles.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb = Array();
    
    /**
     * An index over the schema database accociated with the topic mapping
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
    	//$page = mgd_get_object_by_guid($config->get("root_page"));
        parent::midcom_baseclasses_components_request($topic, $config);
        
        $this->msg = "";
        /* fancy aegir css switch! */
        
        
    }

	function _on_initialize() {
		
        
        
        // no caching as different input requires different emails .)              
       	$_MIDCOM->cache->content->no_cache();
        // argv has the following format: topic_id/mode
		$this->_request_switch[] = Array
        (
            'handler' => 'index',
            
            // No further arguments, we have neither fixed nor variable arguments.
        );
		$this->_request_switch[] = Array
        (
        	'fixed_args' => 'submitok',
            'handler' => 'submitok',
            'variable_args' => 0,
        );
		$this->_request_switch[] = Array
        (
        	'fixed_args' => 'submitnotok',
            'handler' => 'submitnotok',
            'variable_args' => 0,
        );
        
        $this->_request_data['datamanager'] = & $this->_datamanager;
       	$this->_load_schema_database();
        if ($this->_config->get('schema_name') == '' && array_key_exists('default', $this->_schemadb)) {
            $this->_schema_name = 'default' ;
        } else {
            $this->_schema_name = $this->_config->get('schema_name'); 
        }
        
    }
    /**
     * Prepares a dm form from the config schema and displays it 
     */
   	function _handler_index() {
	    if (! $this->_prepare_creation_datamanager($this->_schema_name))
        {
            debug_pop();
            return false;
        }
        
        // Now launch the datamanger processing loop
        switch ($this->_datamanager->process_form_to_array()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                break;

            case MIDCOM_DATAMGR_SAVED:
                if ($this->_save_to_mail()) {
                    $_MIDCOM->relocate("submitok.html");
                } else {
                    $_MIDCOM->relocate("submitnotok.html");
                }
                // this will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                // Redirect to view page.
                $_MIDCOM->relocate("");
                // This will exit()
                
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "The Datamanager failed critically while processing the form, see the debug level log for more details.";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
        
        debug_pop();
        return true;

   	} 
   
    function _show_index() {
       
       midcom_show_style("show-form");
    }
    
    function _handler_submitok() {
        return true;
    }

    function _show_submitok() {
        midcom_show_style("show-form-finished");
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
        
        $path = $this->_request_data['config']->get('schemadb');
        
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
                    "Could not load the schema database accociated with this topic: The schema DB in {$path} was empty.");
                // This will exit.
            }
            foreach ($this->_schemadb as $schema)
            {
                $this->_schemadb_index[$schema['name']] = $schema['description'];
            }
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Could not load the schema database accociated with this topic. The schema DB was no array.');
            // This will exit.
        }
    }

    /**
     * Prepares the datamanager for creation of a new article. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @param string $schema The name of the schema to initialize for
     * @return bool Indicating success
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
            $this->errstr = "Failed to initialize the datamanger in creation mode for schema '{$schema}'.";
            $this->errcode = MIDCOM_ERRCRIT; 
            return false;
        }
        return true;
    }
    
    
    /* 
     * save to mail
     * */
    function _save_to_mail() 
    {
        debug_push("_save_to_mail"); 
        $subject = $this->_config->get('mail_subject');
        $to      = $this->_config->get('mail_address_to');
        $data    = $this->_datamanager->get_array();
        $fields  = $this->_datamanager->get_fieldnames();
        $schema  = $this->_datamanager->get_layout_database();
        //$data['_schema'] = '';
        $headers = "";
        $email_to = "";
        $message = "";
        foreach ($fields as $field => $description) {
                
                if ($field == 'email' ) {
                    $email_to = $data[$field];
                }
                if (array_key_exists ('widget' , $schema[$this->_datamanager->get_layout_name()]['fields'][$field]) &&
                        $schema[$this->_datamanager->get_layout_name]['fields'][$field]['widget'] == 'radiobox') {
                    $message .= "\n$description " . $schema[$this->_datamanager->get_layout_name()]['fields'][$field]['widget_radiobox_choices'][$data[$field]] . "\n";
                } else {
                    $message .= "\n$description " . $data[$field] . "\n";
                }
        }

        $charset = $this->_config->get('mail_encoding');
        $headers .= "Content-Type: text/plain; charset={$charset}\n";
        
        if ($this->_config->get('mail_address_from') ) {
            $headers .= "From: " . $this->_config->get('mail_address_from') . "\n";
            $headers .= "Reply-To: " . $this->_config->get('mail_address_from') . "\n";
            $headers .= "Return-Path: " . $this->_config->get('mail_address_from') . "\n";
        }
        /* too bad I didn't get validation of this stuff! TODO! */ 
        if ($this->_config->get('mail_reciept') && $email_to != '') {
            $smessage = $this->_config->get('mail_reciept_message') . "\n";
            
            if ($this->_config->get("mail_reciept_data")) {
                $smessage .= $message;
            }
            
            if (!mail ($email_to,  $subject,$smessage, $headers)) {    
                debug_add("Mail to recepient failed: mail(" .$email_to . "., $subject, $smessage, $headers);", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            } else {
                debug_add("Mail to recepient sent with command: mail(" . $data['email'] . "., $subject, $smessage, $headers);", MIDCOM_LOG_INFO);
            }
        } else {
                debug_add("Mail should not be sent to submitter: " . $this->_config->get('mail_reciept') , MIDCOM_LOG_INFO);
        }

        $message .= "\nMail submitted on " . date("d/m/Y",time()) ;
        $message .= "\nFrom IP: " . $_SERVER['REMOTE_ADDR'];
        
        debug_add("Sending mail to user: mail($to, $subject, message, $headers);", MIDCOM_LOG_INFO);
        
        debug_pop();
        return mail($to, $subject, $message, $headers);
            
        
        
    
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
