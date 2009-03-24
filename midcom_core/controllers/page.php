<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Page management controller
 *
 * @package midcom_core
 */
class midcom_core_controllers_page extends midcom_core_controllers_baseclasses_manage
{
    public function __construct($instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function load_object($args)
    {
        if (!isset($_MIDCOM->context->page->id))
        {
            throw new midcom_exception_notfound("No Midgard page found");
        }
        
        $this->object = $_MIDCOM->context->page;
    }
    
    public function prepare_new_object(&$data, $args)
    {
        $this->object = new midgard_page();
        $this->object->up = $_MIDCOM->context->page->id;
        $this->object->info = 'active';
    }
    
    public function get_url_show()
    {
        return $_MIDGARD['self'];
    }
    
    public function get_url_edit()
    {
        return $_MIDCOM->dispatcher->generate_url('page_edit', array());
    }

    public function get_object_show($route_id, &$data, $args)
    {
        $this->load_object($args);
        return $this->object;
    }

    public function action_show($route_id, &$data, $args)
    {
        parent::action_show($route_id, $data, $args);
        
        // Neutron introspection file
        $_MIDCOM->head->add_link_head
        (
            array
            (
                'rel' => 'neutron-introspection',
                'type' => 'application/neutron+xml',
                'href' => $_MIDCOM->dispatcher->generate_url
                (
                    'page_variants', array
                    (
                        'variant' => array
                        (
                            'identifier' => 'page',
                            'variant' => 'neutron-introspection',
                            'type' => 'xml',
                        )
                    )
                )
            )
        );

        if ($route_id == 'page_variants')
        {
            switch ($this->dispatcher->request_method)
            {
                case 'PUT':
                    $_MIDCOM->authorization->require_do('midgard:update', $data['object']);
                    // Intentional fall-through
                case 'GET':
                    // Get variant of the page
                    $variant = new midcom_core_helpers_variants();
                    $variant->datamanager = $data['datamanager'];
                    $variant->object = $data['object'];
                    echo $variant->handle($args['variant'], $this->dispatcher->request_method);
                    die();
                case 'MKCOL':
                    // Create subpage
                    $_MIDCOM->authorization->require_do('midgard:create', $data['object']);
                    $this->prepare_new_object($args);
                    $this->object->name = $args['name']['identifier'];    
                    $this->object->create();
                    break;
                default:
                    throw new midcom_exception_httperror("{$this->dispatcher->request_method} not allowed", 405);
            }
        }
    }
}
?>