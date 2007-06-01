<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: products.php 3991 2006-09-07 11:28:16Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MetaWeblog API handler for the blog component
 * 
 * @package net.nehmer.blog
 */
class org_openpsa_products_handler_product_api extends midcom_baseclasses_components_handler
{
    /**
     * The product to operate on
     *
     * @var org_openpsa_products_product_dba
     * @access private
     */
    var $_product;

    function org_openpsa_products_handler_product_api()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        if (!$this->_config->get('api_products_enable'))
        {
            return false;
        }
        
        //$_MIDCOM->auth->require_valid_user('basic');
        
        //Content-Type
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->no_cache();
        $_MIDCOM->cache->content->content_type('text/xml');
        
        $this->_load_datamanager();
        $_MIDCOM->load_library('midcom.helper.xml');
        
        return true;
    }
    
    /**
     * Internal helper, loads the datamanager for the current product. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_product']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance.");
            // This will exit.
        }
    } 

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function _create_product($title)
    {
        $author = $_MIDCOM->auth->user->get_storage();
    
        $product = new org_openpsa_products_product_dba();
        //$product->topic = $this->_content_topic->id;
        // FIXME: Set productgroup
        $product->title = $title;
        
        if (! $product->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $product);
            debug_pop();
            return null;
        }

        // Generate URL name
        if ($product->code == '')
        {
            $product->code = midcom_generate_urlname_from_string($product->title);
            $tries = 0;
            $maxtries = 999;
            while(   !$product->update()
                  && $tries < $maxtries)
            {
                $product->code = midcom_generate_urlname_from_string($product->title);
                if ($tries > 0)
                {
                    // Append an integer if products with same name exist
                    $product->code .= sprintf("-%03d", $tries);
                }
                $tries++;
            }
        }
        
        $product->parameter('midcom.helper.datamanager2', 'schema_name', $this->_config->get('api_products_schema'));

        return $product;
    }

    // products.create_product
    function create_product($message) 
    {
        $args = $this->_params_to_args($message);
        
        if ($args[0] != $this->_content_topic->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Blog ID does not match this folder.');
        }
        
        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();
        
        if (   !array_key_exists('title', $args[3])
            || $args[3]['title'] == '')
        {
            // Create product with title coming from datetime
            $new_title = strftime('%x %X');
        }
        else
        {
            if (version_compare(phpversion(), '5.0.0', '>=')) 
            {
                $new_title = html_entity_decode($args[3]['title'], ENT_QUOTES, 'UTF-8');
            }
            else
            {
                $new_title = $args[3]['title'];
            }
        }

        $product = $this->_create_product($new_title);
        if (   !$product
            || !$product->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to create product: ' . mgd_errstr());
        }
        
        if (!$this->_datamanager->autoset_storage($product))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to initialize DM2 for product: ' . mgd_errstr());
        }

        foreach ($args[3] as $field => $value)
        {
            switch ($field)
            {
                case 'title':
                    $this->_datamanager->types['title']->value = $new_title;
                    break;

                case 'mt_excerpt':
                    $this->_datamanager->types['abstract']->value = $value;
                    break;
                    
                case 'description':
                    $this->_datamanager->types['content']->value = $value;
                    break;
                    
                case 'link':
                    // TODO: We may have to bulletproof this a bit
                    $this->_datamanager->types['name']->value = str_replace('.html', '', basename($args[3]['link']));
                    break;
                    
                case 'categories':
                    if (array_key_exists('categories', $this->_datamanager->types))
                    {
                        $this->_datamanager->types['categories']->selection = $value;
                        break;
                    }
            }
        }
        
        if (!$this->_datamanager->save())
        {
            $product->delete();
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to update product: ' . mgd_errstr());
        }

        // TODO: Map the publish property to approval
        
        // Index the product
        $indexer =& $_MIDCOM->get_service('indexer');
        net_nehmer_blog_viewer::index($this->_datamanager, $indexer, $this->_content_topic);
        
        return new XML_RPC_Response(new XML_RPC_Value($product->guid, 'string'));
    }

    // products.update_product
    function update_product($message) 
    {
        $args = $this->_params_to_args($message);
        
        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();
        
        $product = new org_openpsa_products_product_dba($args[0]);
        if (!$product)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Article not found: ' . mgd_errstr());
        }
        
        if (!$this->_datamanager->autoset_storage($product))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to initialize DM2 for product: ' . mgd_errstr());
        }

        foreach ($args[3] as $field => $value)
        {
            switch ($field)
            {
                case 'title':
                    if (version_compare(phpversion(), '5.0.0', '>=')) 
                    {
                        $this->_datamanager->types['title']->value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                    }
                    else
                    {
                        $this->_datamanager->types['title']->value = $value;
                    }
                    break;
                    
                case 'mt_excerpt':
                    $this->_datamanager->types['abstract']->value = $value;
                    break;
                    
                case 'description':
                    $this->_datamanager->types['content']->value = $value;
                    break;
                    
                case 'link':
                    // TODO: We may have to bulletproof this a bit
                    $this->_datamanager->types['name']->value = str_replace('.html', '', basename($args[3]['link']));
                    break;
                
                case 'categories':
                    if (array_key_exists('categories', $this->_datamanager->types))
                    {
                        $this->_datamanager->types['categories']->selection = $value;
                        break;
                    }
            }
        }
        
        if (!$this->_datamanager->save())
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to update product: ' . mgd_errstr());
        }
        
        // TODO: Map the publish property to approval
        
        // Index the product
        $indexer =& $_MIDCOM->get_service('indexer');
        net_nehmer_blog_viewer::index($this->_datamanager, $indexer, $this->_content_topic);
    
        return new XML_RPC_Response(new XML_RPC_Value($product->guid, 'string'));
    }

    // products.list_product_groups
    function list_product_groups($message) 
    {
        $args = $this->_params_to_args($message);
        
        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();
        
        if ($args[0] == 0)
        {
            $product_group_id = 0;
            $product_group_label = 'root';
        }
        else
        {
            $product_group = org_openpsa_products_product_group_dba($args[0]);
            if (!$product_group)
            {
                return new XML_RPC_Response(0, mgd_errno(), 'Product group ID not found.');
            }
            $product_group_id = $product_group->id;
            $product_group_label = $product_group->code;
        }
        
        $response = array();
        
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $qb = org_openpsa_products_product_group_dba::new_query_builder();
        $qb->add_constraint('up', '=', $product_group_id);
        $qb->add_order('code');
        $qb->add_order('title');
        
        $product_groups = $qb->execute();        
        foreach ($product_groups as $product_group)
        {
        
            $arg = $product_group->code ? $product_group->code : $product_group->guid;
            $link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$arg}/";
        
            $response_array = array
            (
                'guid'        => new XML_RPC_Value($product_group->guid, 'string'),
                'code'        => new XML_RPC_Value($product_group->code, 'string'),
                'title'       => new XML_RPC_Value($product_group->title, 'string'),
                'link'        => new XML_RPC_Value($link, 'string'),
                'description' => new XML_RPC_Value($product_group->description, 'string'),
                'published'   => new XML_RPC_Value(gmdate("Ymd\TH:i:s\Z", $product_group->metadata->published), 'dateTime.iso8601'),
                'productGroup' => new XML_RPC_Value($product_group_label, 'string'),
            );
            
            $response[$category] = new XML_RPC_Value($response_array, 'struct');
        }
        
        return new XML_RPC_Response(new XML_RPC_Value($response, 'struct'));
    }

    // products.add_file
    function add_file($message) 
    {
        $args = $this->_params_to_args($message);
        
        if ($args[0] != $this->_content_topic->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Blog ID does not match this folder.');
        }
        
        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();
        
        if (count($args) < 3)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid file data.');
        }

        if (!$args[3]['name'])
        {
            return new XML_RPC_Response(0, mgd_errno(), 'No filename given.');
        }
        
        // Clean up possible path information
        $attachment_name = basename($args[3]['name']);
        
        $attachment = $this->_content_topic->get_attachment($attachment_name);
        if (!$attachment)
        {
            // Create new attachment
            $attachment = $this->_content_topic->create_attachment($attachment_name, $args[3]['name'], $args[3]['type']);
            
            if (!$attachment)
            {
                return new XML_RPC_Response(0, mgd_errno(), 'Failed to create attachment: ' . mgd_errstr());
            }
        }
        
        if (!$attachment->copy_from_memory($args[3]['bits']))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to store contents to attachment: ' . mgd_errstr());
        }
        
        $attachment_array = array
        (
            'url'  => new XML_RPC_Value("{$GLOBALS['midcom_config']['midcom_site_url']}midcom-serveattachmentguid-{$attachment->guid}/{$attachment->name}", 'string'),
            'guid' => new XML_RPC_Value($attachment->guid, 'string'),
        );
        return new XML_RPC_Response(new XML_RPC_Value($attachment_array, 'struct'));
    }

    // products.delete_product
    function delete_product($message) 
    {
        $args = $this->_params_to_args($message);
        
        if (!mgd_auth_midgard($args[2], $args[3]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();
        
        $product = new org_openpsa_products_product_dba($args[1]);
        if (!$product)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Article not found: ' . mgd_errstr());
        }
        
        if (!$product->delete())
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to delete product: ' . mgd_errstr());
        }
        
        // Update the index
        $indexer =& $_MIDCOM->get_service('indexer');
        $indexer->delete($product->guid);
        
        return new XML_RPC_Response(new XML_RPC_Value(true, 'boolean'));
    }

    function _handler_product_get($handler_id, $args, &$data)
    {   
        $this->_product = new org_openpsa_products_product_dba($args[0]);
        if (!$this->_product)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Product {$args[0]} could not be found.");
            // This will exit
        }
        
        if (!$this->_datamanager->autoset_storage($this->_product))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Product {$args[0]} could not be loaded with Datamanager.");
            // This will exit
        }
        
        return true;
    }

    function _show_product_get($handler_id, &$data)
    {
        $data['datamanager'] =& $this->_datamanager;
        $data['view_product'] = $this->_datamanager->get_content_html();
        $data['product'] =& $this->_product;
        midcom_show_style('api_product_get');
    }

    function _handler_product_list($handler_id, $args, &$data)
    {   
        $data['products'] = array();

        $qb = org_openpsa_products_product_dba::new_query_builder();
        
        if ($handler_id != 'api_product_list_all')
        {
            if ($args[0] == 0)
            {
                // List only toplevel
                $qb->add_constraint('productGroup', '=', 0);
            }
            else
            {
                $product_group = new org_openpsa_products_product_group_dba($args[0]);
                if (   !$product_group
                    || !$product_group->guid)
                {
                    $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "Product group {$args[0]} could not be found.");
                    // This will exit
                } 
                
                $qb->add_constraint('productGroup', '=', $product_group->id);
            }
        }
        
        $qb->add_order('code');
        $qb->add_order('title');
        
        $products = $qb->execute();
        foreach ($products as $product)
        {
            $data['products'][] = $product;
        }
        
        return true;
    }

    function _show_product_list($handler_id, &$data)
    {
        midcom_show_style('api_product_list_header');
        foreach ($data['products'] as $product)
        {
            if (!$this->_datamanager->autoset_storage($product))
            {
                // This product has something wrong, skip it
                continue;
            }
            $data['datamanager'] =& $this->_datamanager;
            $data['view_product'] = $this->_datamanager->get_content_html();
            $data['product'] =& $product;
            
            midcom_show_style('api_product_list_item');
        }
        midcom_show_style('api_product_list_footer');
    }

}