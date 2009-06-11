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
    
    public function prepare_new_object($args)
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

    public function get_show($args)
    {
        parent::get_show($args);
        
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

        if ($_MIDCOM->context->route_id == 'page_variants')
        {
            // Get variant of the page
            $variant = new midcom_core_helpers_variants();
            $variant->datamanager = $this->data['datamanager'];
            $variant->object = $this->data['object'];
            echo $variant->handle($args['variant'], $this->dispatcher->request_method);
            die();
        }
    }

    public function post_show($args)
    {
        $this->get_show($args);
    }

    public function put_show($args)
    {
        parent::get_show($args);
        
        $_MIDCOM->authorization->require_do('midgard:update', $this->data['object']);

        // Get variant of the page
        $variant = new midcom_core_helpers_variants();
        $variant->datamanager = $this->data['datamanager'];
        $variant->object = $this->data['object'];
        echo $variant->handle($args['variant'], $this->dispatcher->request_method);
        die();
    }

    public function mkcol_show($args)
    {
        parent::get_show($args);

        // Create subpage
        $_MIDCOM->authorization->require_do('midgard:create', $this->data['object']);
        $this->prepare_new_object($args);
        $this->object->name = $args['name']['identifier'];    
        $this->object->create();
    }
}
?>