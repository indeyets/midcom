<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Midgard2 dispatcher for MidCOM 3
 *
 * Dispatches Midgard HTTP requests to components.
 *
 * @package midcom_core
 */
class midcom_core_services_dispatcher_midgard2 extends midcom_core_services_dispatcher_midgard implements midcom_core_services_dispatcher
{
    /**
     * Midgard's request configuration object
     */
    private $request_config = null;

    /**
     * Read the request configuration and parse the URL
     */
    public function __construct()
    {
        if (!extension_loaded('midgard2'))
        {
            throw new Exception('Midgard 2.x is required for this MidCOM setup.');
        }
        
        $this->request_config = $_MIDGARD_CONNECTION->get_request_config();
        
        if (   !$this->request_config
            || empty($this->request_config->argv))
        {
            throw new midcom_exception_httperror('Midgard database connection not found.', 503);
        }

        foreach ($this->request_config->argv as $argument)
        {
            if (substr($argument, 0, 1) == '?')
            {
                // FIXME: For some reason we get GET parameters into the argv string too, move them to get instead
                $gets = explode('&', substr($argument, 1));
                foreach ($gets as $get_string)
                {
                    $get_pair = explode('=', $get_string);
                    if (count($get_pair) != 2)
                    {
                        break;
                    }
                    $this->get[$get_pair[0]] = $get_pair[1];
                }

                break;
            }
            
            $this->argv[] = $argument;
        }
    }
    
    /**
     * Pull data from currently loaded page into the context.
     */
    public function populate_environment_data()
    {
        $prefix = "{$this->request_config->host->prefix}/";
        foreach ($this->request_config->pages as $page)
        {
            if ($page->id != $this->request_config->host->root)
            {
                $prefix = "{$prefix}{$page->name}/";
            }
            $current_page = $page;
        }

        $_MIDCOM->context->component = $current_page->component;
        
        $_MIDCOM->context->page = $current_page;
        $_MIDCOM->context->prefix = $prefix;
        $_MIDCOM->context->host = $this->request_config->host;
        
        // Append styles from context
        if ($this->request_config->style)
        {
            $_MIDCOM->templating->append_style($this->request_config->style->id);
        }
        $_MIDCOM->templating->append_page($current_page->id);
        
        // Populate page to toolbar
        $this->populate_node_toolbar();
    }
}
?>