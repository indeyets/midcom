<?php
/**
 * @package net.nemein.alphabeticalindex
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an admin handler class for net.nemein.alphabeticalindex
 *
 * @package net.nemein.alphabeticalindex
 */
class net_nemein_alphabeticalindex_handler_edit extends midcom_baseclasses_components_handler 
{
    /**
     * The alphabet item
     *
     * @var array
     * @access private
     */
    var $_item = null;

    /**
     * The alphabet item type (internal/external)
     *
     * @var string
     * @access private
     */
    var $_type = null;

    /**
     * The alphabet items
     *
     * @var array
     * @access private
     */
    var $_items = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The Datamanager of the event to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_controller = null;
    
    /**
     * Current topic
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_topic = null;
    
    var $_defaults = array();
    
    function net_nemein_alphabeticalindex_handler_edit()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    function _on_initialize()
    {
        $this->_topic =& $this->_request_data['topic'];
    }
    
    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data($handler_id)
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['schemadb'] =& $this->_schemadb;
        $this->_request_data['item'] =& $this->_item;
        $this->_request_data['link_type'] =& $this->_type;
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb()
    {
        $src = $this->_config->get('schemadb');
        $schemadb = midcom_helper_datamanager2_schema::load_database($src);

        if (count($schemadb) < 1)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the schema db from '{$src}'!");
            // This will exit.
        }
    
        $this->_schemadb =& $schemadb;
        
        if ($this->_type == 'internal')
        {
            $this->_schemadb['edit']->append_field('cachedUrl', Array
                (
                    'title' => 'Cached URL',
                    'storage' => 'cachedUrl',
                    'type' => 'text',
                    'widget' => 'text',
                    'hidden' => true,
                )
            );
        }
        else
        {
            $this->_schemadb['edit']->append_field('node', Array
                (
                    'title' => 'Current Node',
                    'storage' => 'node',
                    'type' => 'text',
                    'widget' => 'text',
                    'hidden' => true,                    
                )
            );            
        }
    }

    /**
     * Internal helper, fires up the controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller($handler_id)
    {
        $this->_load_schemadb();
        
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb = array( 'edit' => $this->_schemadb['edit'] );
        $this->_controller->set_storage($this->_item);
        if (! $this->_controller->initialize())
        {   
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        
        $qb = net_nemein_alphabeticalindex_item::new_query_builder();
        $qb->add_constraint('guid', '=', $args[0]);
        $results = $qb->execute();
        
        if (count($results) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The item {$args[0]} was not found.");
        }
        
        $this->_item =& $results[0];
        
        
        $this->_type = 'external';
        if ($this->_item->internal)
        {
            $this->_type = 'internal';
        }
        
        if ($this->_type == 'internal')
        {
            if (empty($this->_item->cachedUrl))
            {
                $this->_item->cachedUrl = $_MIDCOM->permalinks->resolve_permalink($this->_item->objectGuid);
            }
        }
        else
        {
            if (empty($this->_item->node))
            {
                $this->_item->node = $this->_topic->id;
            }            
        }
        
        $this->_load_controller($handler_id);
        $this->_prepare_request_data($handler_id);
        $this->_update_breadcrumb_line();
        
        switch ($this->_controller->process_form())
        {
	        case 'save':
            case 'cancel':
                $_MIDCOM->relocate('');
	             // This will exit.
        }
        
        return true;
    }
    
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('item-edit');
    }
    
    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit', 'midcom'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}

?>