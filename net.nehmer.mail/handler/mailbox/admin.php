<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a Mailbox admin handler class for net.nehmer.mail
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nehmer.mail
 */

class net_nehmer_mail_handler_mailbox_admin extends midcom_baseclasses_components_handler 
{
    /**
     * The Controller
     *
     * @var mixed
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The defaults to use for the new mailbox.
     *
     * @var Array
     * @access private
     */
    var $_defaults = array();
    
    /**
     * Current mailbox
     *
     * @var Array
     * @access private
     */
    var $_mailbox = null;
    
    /**
     * Simple default constructor.
     */
    function net_nehmer_mail_handler_mailbox_admin()
    {
        debug_push_class(__CLASS__, __FUNCTION__);        
        debug_pop();
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
    }

    function _populate_node_toolbar($handler_id)
    {
        $this->_view_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "admin/create.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('create'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
            MIDCOM_TOOLBAR_ACCESSKEY => 'c',
            MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_user_do('midgard:create', null, 'net_nehmer_mail_mailbox'),            
        ));    
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['defaults'] =& $this->_defaults;

        $this->_request_data['mailbox'] =& $this->_mailbox;
    }
    
    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& midcom_helper_datamanager2_schema::load_database( $this->_config->get('schemadb') );
        
        $this->_defaults['name'] = 'INBOX';
        $this->_defaults['quota'] = $this->_config->get('default_quota');
    }

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller($handler_id)
    {
        $this->_load_schemadb();
        
        if ($handler_id == 'admin-edit')
        {
            $this->_controller = midcom_helper_datamanager2_controller::create('simple');
            $this->_controller->schemadb =& $this->_schemadb;
            $this->_controller->set_storage($this->_mailbox, 'mailbox');
        }
        else
        {
            $this->_controller =& midcom_helper_datamanager2_controller::create('nullstorage');
            $this->_controller->schemadb =& $this->_schemadb;
            $this->_controller->schemaname = 'mailbox';
            $this->_controller->defaults = $this->_defaults;            
        }

        if (! $this->_controller->initialize())
        {
            if ($handler_id == 'admin-edit')
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for mailbox {$this->_mailbox->id}.");
                // This will exit.
            }

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for mailboxes.");            
            // This will exit.
        }
    }
    
    function _handler_welcome($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_prepare_request_data($handler_id);
        $this->_populate_node_toolbar($handler_id);
        
        debug_pop();        
        return true;
    }

    /**
     * This is small creation form, driven by DM2 without any data backend.
     * 
     * @access private
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_user_do('midgard:create', 'net_nehmer_mail_mailbox');
        
        $this->_load_controller($handler_id);
        
        $this->_prepare_request_data($handler_id);
        $this->_populate_node_toolbar($handler_id);
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $this->_create_mailbox();
	            // This will exit.
                
            case 'cancel':
                $_MIDCOM->relocate('admin');
                // This will exit.
        }
        
        return true;
    }
    
    /**
     * Prepares an edit form using the dm2, which is frozen unless we have update privileges.
     * 
     * @access private
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_mailbox =& new net_nehmer_mail_mailbox($args[0]);
        
        if (! $this->_mailbox)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize mailbox with guid {$args[0]}.");
            // This will exit.
        }
        
        $_MIDCOM->auth->require_user_do('midgard:update', $this->_mailbox);

        $this->_load_controller($handler_id);
        
        $this->_prepare_request_data($handler_id);
        $this->_populate_node_toolbar($handler_id);
        
        // If the save is successful, we adjust the privileges.
        $oldowner = $this->_mailbox->owner;
        
        // Process the form and update the owner if necessary
        switch ($this->_controller->process_form())
        {
            case 'save':
                if ($oldowner != $this->_mailbox->owner)
                {
                    $this->_mailbox->set_privilege('midgard:owner', "user:{$data['mailbox']->owner}");
                    
                    // Revert old privileges.
                    $this->_mailbox->unset_privilege('midgard:owner', "user:{$oldowner}");
                }
                $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('mailbox saved successfully'), 'ok');
                // *** FALL THROUGH ***
            
            case 'cancel':
                $_MIDCOM->relocate('admin');
            	// This will exit.
        }
        
        return true;
    }
    
    /**
     * Deletes a mailbox (currently without safety checks).
     * 
     * @access private
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_mailbox =& new net_nehmer_mail_mailbox($args[0]);
        
        if (! $this->_mailbox)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize mailbox with guid {$atgs[0]}.");
            // This will exit
        }
        
        $_MIDCOM->auth->can_do('midgard:delete', $this->_mailbox);
        
        // This calls generate_error on failure.
        $this->_mailbox->delete();
        
        $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('mailbox deleted successfully'), 'ok');
        
        $_MIDCOM->relocate('admin');
    }   
    
    /**
     * This function handles the actual mailbox creation from the 'create' handler.
     * It depends on the request data to gain access to form/datamanager and will 
     * relocate to the mailbox view on success, or continue editing otherwise.
     * 
     * @access private
     */
    function _create_mailbox()
    {
        $mailbox = new net_nehmer_mail_mailbox();
        $mailbox->name = $this->_request_data['controller']->types['name']->value;
        $mailbox->quota = $this->_request_data['controller']->types['quota']->value;
        $mailbox->owner = $this->_request_data['controller']->types['owner']->selection[0];
        
        if (! $mailbox->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Object was:', $mailbox);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new mailbox. See the debug log for more information.');
            // This will exit.
        } 
        
        // Resign ownership to the Mailbox's owner, but don't allow deletions for the owner
        // if it is an INBOX.
        $mailbox->set_privilege('midgard:owner', "user:{$mailbox->owner}");
        $mailbox->unset_privilege('midgard:owner');

        $_MIDCOM->uimessages->add($data['l10n']->get('net.nehmer.mail'), $data['l10n']->get('mailbox created successfully'), 'ok');                
        
        $_MIDCOM->relocate("admin/edit/{$mailbox->guid}.html");
        // This will exit.
    }    
    
    /**
     * Renders the welcome page.
     * 
     * @access private
     */
    function _show_welcome($handler_id, &$data)
    {
        midcom_show_style('admin-welcome');
    }
    
    /**
     * Simple Mailbox creation view.
     * 
     * @access private
     */    
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('admin-create');
    }
    
    /**
     * Simple Mailbox editing view.
     * 
     * @access private
     */    
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('admin-edit');
    }  
    
    /**
     * Simple Mailbox deletion view.
     * 
     * @access private
     */    
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('admin-delete');
    }       
}

?>
