<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Base class for object management controller. Extend this to easily implement the regular Create, Read, Update and Delete cycle
 *
 * @package midcom_core
 */
abstract class midcom_core_controllers_baseclasses_crud
{
    /**
     * The actual MgdSchema object to be managed by the controller.
     */
    protected $object = null;
    
    /**
     * Datamanager instance
     */
    protected $datamanager = null;

    public function __construct(midcom_core_component_interface $instance)
    {
        $this->configuration = $instance->configuration;
    }

    /**
     * Method for loading the object to be managed. To be overridden in the actual controller.
     */
    abstract public function load_object(array $args);
    
    /**
     * Method for preparing a new object to be created. To be overridden in the actual controller.
     */
    abstract public function prepare_new_object(array $args);
    
    /**
     * Method for generating route to the object
     *
     * @return string Object URL
     */
    abstract public function get_url_read();

    /**
     * Method for generating route to editing the object
     *
     * @return string Object URL
     */    
    abstract public function get_url_update();
    
    public function load_datamanager($schemadb)
    {
        // Load the object via Datamanager for configurability
        $_MIDCOM->componentloader->load('midcom_helper_datamanager');
        
        $this->datamanager = new midcom_helper_datamanager_datamanager($schemadb);
        $this->datamanager->autoset_storage($this->object);
        
        $this->data['datamanager'] =& $this->datamanager;
    }
    
    public function load_creation_datamanager($schemadb, $schema_name)
    {
        // Load the Datamanager in creation mode for configurability
        $_MIDCOM->componentloader->load('midcom_helper_datamanager');
        
        $this->datamanager = new midcom_helper_datamanager_datamanager($schemadb);
        
        // TODO: Refactor all of these to DM itself
        $this->datamanager->set_schema($schema_name);
        $this->datamanager->set_storage($this->object);
        
        $this->data['datamanager'] =& $this->datamanager;
    }

    // TODO: Refactor. There is code duplication with edit
    public function get_create(array $args)
    { 
        if (!isset($_MIDGARD['page']))
        {
            throw new midcom_exception_notfound('No Midgard page found');
        }
        
        $this->data['object'] =& $this->object;
        $this->data['parent'] = $_MIDCOM->context->page;
        
        // Prepare the new object that datamanager will eventually create
        $this->prepare_new_object($args);

        $_MIDCOM->authorization->require_do('midgard:create', $this->data['parent']);
        
        // Load datamanager in creation mode
        $this->load_creation_datamanager($this->configuration->get('schemadb'), 'default');
     
          // Handle saves through the datamanager
        $this->data['datamanager_form'] =& $this->datamanager->get_form('simple');

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
    }

    public function post_create(array $args)
    {
        $this->get_create($args);

        try
        {   
            $this->data['datamanager_form']->process();
        }
        catch (midcom_helper_datamanager_exception_save $e)
        {
            // TODO: add uimessage of $e->getMessage();
            header('Location: ' . $this->get_url_read());
            exit();
        }
    }

    public function get_read(array $args)
    {
        $this->load_object($args);
        $this->load_datamanager($this->configuration->get('schemadb'));
        $this->data['object'] =& $this->object;

        if ($_MIDCOM->authorization->can_do('midgard:update', $this->data['object']))
        {
            $_MIDCOM->head->add_link_head
            (
                array
                (
                    'rel' => 'alternate',
                    'type' => 'application/x-wiki',
                    'title' => 'Edit this page!', // TODO: l10n and object type
                    'href' => $this->get_url_update(),
                )
            );
        }
    }

    public function get_update(array $args)
    {
        $this->load_object($args);
        $this->load_datamanager($this->configuration->get('schemadb'));
        $this->data['object'] =& $this->object;
        $_MIDCOM->authorization->require_do('midgard:update', $this->object);
        
        // Handle saves through the datamanager
        $this->data['datamanager_form'] =& $this->datamanager->get_form('simple');
        
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
    }

    public function post_update(array $args)
    {
        $this->get_update($args);

        try
        {
            $this->data['datamanager_form']->process();
        }
        catch (midcom_helper_datamanager_exception_datamanager $e)
        {
            // FIXME: We can remove this once signals work again
            $_MIDCOM->cache->invalidate(array($this->object->guid));

            // TODO: add uimessage of $e->getMessage();
            header('Location: ' . $this->get_url_read());
            exit();
        }
    }
        
    public function get_delete(array $args)
    {
        $this->load_object($args);
        $this->load_datamanager($this->configuration->get('schemadb'));
        $this->data['object'] =& $this->object;
        
        // Make a frozen form for display purposes
        $this->data['datamanager_form'] =& $this->datamanager->get_form('simple');
        $this->data['datamanager_form']->freeze();
        
        $_MIDCOM->authorization->require_do('midgard:delete', $this->object);
        
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
    }
    
    public function post_delete(array $args)
    {
        $this->get_delete($args);

        if (isset($_POST['delete']))
        {
            $this->object->delete();
            // FIXME: We can remove this once signals work again
            $_MIDCOM->cache->invalidate($this->object->guid);
            header("Location: {$_MIDGARD['prefix']}/"); // TODO: This needs a better redirect
            exit();     
        }
    }
}
?>