<?php
/**
 * @package net.nemein.feedcollector
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.feedcollector
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nemein.feedcollector
 */
class net_nemein_feedcollector_handler_create  extends midcom_baseclasses_components_handler 
{

    /**
     * The Datamanager of the article to display (for delete mode)
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
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
     * Simple default constructor.
     */
    function net_nemein_feedcollector_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
         $this->_content_topic =& $this->_request_data['content_topic'];
    }
    
    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-admins
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
        if (   $this->_config->get('simple_name_handling')
            && ! $_MIDCOM->auth->admin)
        {
            foreach (array_keys($this->_schemadb) as $name)
            {
                $this->_schemadb[$name]->fields['name']['readonly'] = true;
            }
        }
    }
    
    /**
     * Internal helper, loads the controller for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for feedtopic {$this->_feedtopic->id}.");
            // This will exit.
        }
    }
    
    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_load_schemadb();
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_feedtopic))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for article {$this->_feedtopic->id}.");
            // This will exit.
        }
    }
    
    
    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function &dm2_create_callback (&$controller)
    {
        $this->_feedtopic = new net_nemein_feedcollector_topic_dba();
        $this->_feedtopic->node = $this->_content_topic->id;

        if (! $this->_feedtopic->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_article);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new feedtopic, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        // Callback possibility
        if ($this->_config->get('callback_function'))
        {
            if ($this->_config->get('callback_snippet'))
            {
                // mgd_include_snippet($this->_config->get('callback_snippet'));
                $eval = midcom_get_snippet_content($this->_config->get('callback_snippet'));
                
                if ($eval)
                {
                    eval($eval);
                }
            }
            
            $callback = $this->_config->get('callback_function');
            $callback($this->_feedtopic, $this->_content_topic);
        }
        
        return $this->_feedtopic;
    }

    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_create ($handler_id, $args, &$data)
    {
        $this->_content_topic->require_do('midgard:create');

        $this->_request_data['name']  = "net.nemein.feedcollector";
        $this->_request_data['topic_introduction'] = $this->_config->get('topic_introduction');
        $this->_update_breadcrumb_line($handler_id);
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");
        
        $this->_schema = 'default';
        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // *** FALL-THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate("");
                // This will exit.
        }

        $this->_request_data['controller'] =& $this->_controller;
        

        return true;
    }
    
    /**
     * This function does the output.
     *  
     */
    function _show_create($handler_id, &$data)
    {

        midcom_show_style('manage-topic-create');
    }
    
    

    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('manage'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
