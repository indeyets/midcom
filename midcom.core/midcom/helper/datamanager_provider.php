<?php
require_once('PEAR.php');
class midcom_helper_exception extends pear_error {
    function midcom_helper_exception ($msg) {
        print $msg;
    }
}
/**
 * This is a helper class to encapsulate datamanager usage. It should be used instead of
 * the normal 
 */

class midcom_helper_datamanager_provider {

    /**
     * the dm instance to work on
     */
    var $datamanager = null;
    /**
     * the datamanager schema to use.
     * @var array
     * @access private (use load_schema_database)
     */
    var $_schema = null;

    /**
     * pointer to the current dataobject, null
     * if object creation 
     * @
     */
    var $_object = null;
    
    
    /**
     * The reloaction url.
     * @var string reloaction url 
     */
    var $reloaction_url = "";
    
    /**
     * Object id to use for the article, defaults to guid.
     * @var string object id
     * @access private (use set_object_id())
     */
    var $object_id = null;
    
    /**
     * Error string
     * @var string
     */
    var $errstr;
    /**
     * error code
     * @var int
     */
    var $errcode;

    function midcom_helper_datamanager_provider($schema, $object = null) {

        $this->_schema = $schema;
        $this->_object = $object;
        
        
    }
   /**
    * factory function
    * @param string schema path
    * @return object midcom_helper_datamanager_provider or midcom_helper_exception
    */
    function &factory ($schema_path,$show_js = true,  $object = null) 
    {
        
        $ret = $this->load_schema_database($schema_path);
        if (is_object($ret)) {
            return $ret; // ret is an exception
        }
        
        if ($object != null) {
            
            $obj = new midcom_helper_datamanager_provider(&$object);
            if ($obj->_prepare_creation_datamanager()) {
                return $obj;
            }
        } else {
            $obj = new midcom_helper_datamanager_provider();
            if ($obj->_prepare_datamanager($show_js)) {
                return $obj;
            }
        }
        return new midcom_helper_exception ($this->errstr, $this->errcode);
        
    }
    /**
     * Display the form
     */
    function display() {
        return $this->datamanager->display();
    }
    /**
     * run the dm form
     * 
     */
     function run () 
     {
        
        switch ($this->_datamanager->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                $this->on_datamanager_editing();
                break;

            case MIDCOM_DATAMGR_SAVED:
                $this->on_datamanager_saved();
                break;                

            case MIDCOM_DATAMGR_CANCELLED:
                $this->on_datamanager_cancelled();
                // This will exit()
                
            case MIDCOM_DATAMGR_FAILED:
                $this->on_datamanager_failed();
                return false;
        }
     }
    /**
     * event functiion for failure
     */
    function on_datamanager_failed() {
        $this->errstr = "The Datamanager failed critically while processing the form, see the debug level log for more details.";
        $this->errcode = MIDCOM_ERRCRIT;
    }
    /**
     * 
     * Stub function to be accessed if you want to do something special in editing
     */        
    function on_datamanager_editing() {}
    /**
     * extend this if you want another saved behaviour.
     * @param none
     * @return void -> you should relocate.
     */
    function on_datamanager_saved() {
        // Reindex the topic 
        $indexer =& $_MIDCOM->get_service('indexer');
        $indexer->index($this->datamanager);
        
        // Redirect to view page.
        $GLOBALS['midcom']->relocate($this->get_relocation_url() . "/" . $this->get_object_id());
        // This will exit()

    }
    /**
     * extend this if you want another canceld behavior
     * @params none
     * @return void
     */
    function on_datamanager_canceled() {
        $GLOBALS['midcom']->relocate($this->get_relocation_url());
    }
    /**
     * set the id to be used at the end of the relocation url
     * @access public
     * @param string id to be used.
     */
    function set_object_id($id) {
        $this->object_id = $id;
    }
    /**
     * Helper method, returns the reloaction url
     * @return string the relocation url;
     */
    function get_relocation_url() {
        return $this->relocation_url;
    }
        
    function get_object_id() {
        if ($this->object_id === null) {
            return $object->guid;
        }
        return $this->object_id;
    }
    
    /**
     * Set the reloaction url base.
     */
    function set_relocation_url($url) {
        $this->relocation_url = $url;
    }
    /**
     * Loads a schema database
     * @access private
     * @param 
     */
     function load_schema_database($path) 
     {
        $data = midcom_get_snippet_content($path);
        eval("\$this->_schema = Array ({$data}\n);");
        if (is_array($this->_schema))
        {
            if (count($this->_schema) == 0)
            {
                debug_add('The schema database in {$path} was empty, we cannot use this.', MIDCOM_LOG_ERROR);
                debug_print_r('Evaluated data was:', $data);
                return new midcom_helper_exception(
                    'Could not load the schema database accociated with this topic: The schema DB was empty.');
                // This will exit.
            }
            /*
            foreach ($this->_schemadb_topic as $schema)
            {
                $this->_schemadb_topic_index[$schema['name']] = $schema['description'];
            }
            */
        }
        else
        {
            debug_add('The schema database was no array, we cannot use this.', MIDCOM_LOG_ERROR);
            debug_print_r('Evaluated data was:', $data);
            return new midcom_helper_exception(
                'Could not load the schema database accociated with this topic. The schema DB was no array.');
            // This will exit.
        }
        debug_pop();
     }

    /**
     * Prepares the datamanager for the object.
     * @return datamanager or exception. 
     * @access private
     * @param bool view to signal to datamanager that it will show the edit form. 
     */
    function _prepare_datamanager($view = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_datamanager = new midcom_helper_datamanager($this->_schema);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Could not create the datamanager instance, see the debug level logfile for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $this->_datamanager->set_show_javascript($view);
        if (! $this->_datamanager->init($this->_object, 'topic')) 
        {
            $this->errstr = 'Could not initialize the datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }
   
    /**
     * Prepares the datamanager for creation of a new topic. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @param string $schema The name of the schema to initialize for
     * @return bool Indicating success
     * @access private
     */
    function _prepare_creation_datamanager($view = true)
    {
        $this->_datamanager = new midcom_helper_datamanager($this->_schema);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Failed to create a datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT; 
            return false;
        }
        $this->_datamanager->set_show_javascript($view);
        if (! $this->_datamanager->init_creation_mode($this->_schema, $this))
        {
            $this->errstr = "Failed to initialize the datamanger in creation mode for schema '{$schema}'.";
            $this->errcode = MIDCOM_ERRCRIT; 
            return false;
        }
        return true;
    }

    /**
     *
     */

}
