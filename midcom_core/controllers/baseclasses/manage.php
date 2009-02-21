<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base class for object management controller. Extend this to easily implement the regular view/edit/delete cycle
 *
 * @package midcom_core
 */
abstract class midcom_core_controllers_baseclasses_manage
{
    /**
     * The actual MgdSchema object to be managed by the controller.
     */
    protected $object = null;
    
    /**
     * Datamanager instance
     */
    protected $datamanager = null;

    /**
     * Method for loading the object to be managed. To be overridden in the actual controller.
     */
    abstract public function load_object($args);
    
    /**
     * Method for preparing a new object to be created. To be overridden in the actual controller.
     */
    abstract public function prepare_new_object($args);
    
    /**
     * Method for generating route to the object
     *
     * @return string Object URL
     */
    abstract public function get_url_show();

    /**
     * Method for generating route to editing the object
     *
     * @return string Object URL
     */    
    abstract public function get_url_edit();
    
    abstract public function populate_toolbar();
    
    public function load_datamanager(&$data, $schemadb)
    {
        // Load the object via Datamanager for configurability
        $_MIDCOM->componentloader->load('midcom_helper_datamanager');
        
        $this->datamanager = new midcom_helper_datamanager_datamanager($schemadb);
        $this->datamanager->autoset_storage($this->object);
        
        $data['datamanager'] =& $this->datamanager;
    }
    
    public function load_creation_datamanager(&$data, $schemadb, $schema_name)
    {
        // Load the Datamanager in creation mode for configurability
        $_MIDCOM->componentloader->load('midcom_helper_datamanager');
        
        $this->datamanager = new midcom_helper_datamanager_datamanager($schemadb);
        
        // TODO: Refactor all of these to DM itself
        $this->datamanager->set_schema($schema_name);
        $this->datamanager->set_storage($this->object);
        
        $data['datamanager'] =& $this->datamanager;
    }

    public function action_show($route_id, &$data, $args)
    {
        $this->load_object($args);
        $this->load_datamanager($data, $this->configuration->get('schemadb'));
        $data['object'] =& $this->object;
        
        if ($_MIDCOM->authorization->can_do('midgard:update', $data['object']))
        {
            $_MIDCOM->head->add_link_head
            (
                array
                (
                    'rel' => 'alternate',
                    'type' => 'application/x-wiki',
                    'title' => 'Edit this page!', // TODO: l10n and object type
                    'href' => $this->get_url_edit(),
                )
            );
        }
     
        $this->populate_toolbar();
    }
    // TODO: Refactor. There is code duplication with edit
    public function action_create($route_id, &$data, $args)
    { 
        if (!isset($_MIDGARD['page']))
        {
            throw new midcom_exception_notfound("No Midgard page found");
        }
        
        $data['object'] =& $this->object;
        $data['parent'] = $_MIDCOM->context->page;
        
        $_MIDCOM->authorization->require_do('midgard:create', $data['parent']);

        // Prepare the new object that datamanager will eventually create
        $this->prepare_new_object($args);
        
        // Load datamanager in creation mode
        $this->load_creation_datamanager($data, $this->configuration->get('schemadb'), 'default');
     
          // Handle saves through the datamanager
        $data['datamanager_form'] =& $this->datamanager->get_form('simple');
        try
        {   
            $data['datamanager_form']->process();
        }
        catch (midcom_helper_datamanager_exception_save $e)
        {
            // TODO: add uimessage of $e->getMessage();
            header('Location: ' . $this->get_url_show());
            exit();
        }

        $_MIDCOM->head->add_link_head
        (
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL . '/midcom_helper_datamanager/simple.css',
            )
        );
        
        $this->populate_toolbar();
        
    }

        
    public function action_edit($route_id, &$data, $args)
    {
        $this->load_object($args);
        $this->load_datamanager($data, $this->configuration->get('schemadb'));
        $data['object'] =& $this->object;
        $_MIDCOM->authorization->require_do('midgard:update', $this->object);
        
        // Handle saves through the datamanager
        $data['datamanager_form'] =& $this->datamanager->get_form('simple');
        try
        {
            $data['datamanager_form']->process();
        }
        catch (midcom_helper_datamanager_exception_datamanager $e)
        {
            // FIXME: We can remove this once signals work again
            $_MIDCOM->cache->invalidate(array($this->object->guid));

            // TODO: add uimessage of $e->getMessage();
            header('Location: ' . $this->get_url_show());
            exit();
        }
        
        $_MIDCOM->head->add_link_head
        (
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL . '/midcom_helper_datamanager/simple.css',
            )
        );
        
        $this->populate_toolbar();
    }
        
    public function action_delete($route_id, &$data, $args)
    {
        $this->load_object($args);
        $this->load_datamanager($data, $this->configuration->get('schemadb'));
        $data['object'] =& $this->object;
        
        // Make a frozen form for display purposes
        $data['datamanager_form'] =& $this->datamanager->get_form('simple');
        $data['datamanager_form']->freeze();
        
        $_MIDCOM->authorization->require_do('midgard:delete', $this->object);
        if (isset($_POST['delete']))
        {
            $this->object->delete();
            // FIXME: We can remove this once signals work again
            $_MIDCOM->cache->invalidate($this->object->guid);
            header("Location: {$_MIDGARD['prefix']}/"); // TODO: This needs a better redirect
            exit();     
        }
        
        $_MIDCOM->head->add_link_head
        (
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL . '/midcom_helper_datamanager/simple.css',
            )
        );
        
        $this->populate_toolbar();
    }
}
?>