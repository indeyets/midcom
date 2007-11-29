<?php
/**
 * @package net.nemein.redirector
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Redirector interface class.
 * 
 * @package net.nemein.redirector
 */
class net_nemein_redirector_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_redirector_viewer($topic, $config) 
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        // Match /
        $this->_request_switch[] = array(
            'handler' => 'redirect'
        );

        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/redirector/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );
    }
    
    function _handler_redirect($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if (is_null($this->_config->get('redirection_type')))
        {
            // No type set, redirect to config
            $_MIDCOM->relocate("{$prefix}config.html");
            // This will exit
        }
        
        switch ($this->_config->get('redirection_type'))
        {
            case 'subnode':
                $nap = new midcom_helper_nav();
                $nodes = $nap->list_nodes($nap->get_current_node());
                if (count($nodes) == 0)
                {
                    $_MIDCOM->relocate("{$prefix}config.html");
                    // This will exit
                }
                
                // Redirect to first node
                $node = $nap->get_node($nodes[0]);
                $_MIDCOM->relocate($node[MIDCOM_NAV_FULLURL], $this->_config->get('redirection_code'));
                // This will exit                    
                
            case 'url':
                if ($this->_config->get('redirection_url') != '')
                {
                    $url = $this->_config->get('redirection_url');
                    
                    // If the replace option is selected
                    if (strpos($url, '__LANG__') !== false)
                    {
                        $content_language = mgd_get_lang();
                        
                        $qb = new midgard_query_builder('midgard_language');
                        $qb->add_constraint('id', '=', $content_language);
                        $lang = $qb->execute();
                        
                        $default_lang_code = 'en';
                        $hosts = $_MIDCOM->i18n->get_language_hosts();
                        foreach($hosts as $k => $host) 
                        {
                            if (   $host->prefix != '' 
                                && $host->lang == 0) 
                            {
                                $default_lang_code = str_replace('/', '', $host->prefix);
                            }
                        }
                        $lang_code = $lang[0]->code;
                        if($lang_code == '') 
                        {
                            $lang_code = $default_lang_code;
                        }

                        // replace LANG string from redirection_url to current lang name
                        $url = str_replace('__LANG__', $lang_code, $url);
                    }

                    // Support varying host prefixes
                    if (strpos($url, '__PREFIX__') !== false)
                    {
                        $url = str_replace('__PREFIX__', $_MIDGARD['self'], $url);
                    }
        
                    $_MIDCOM->relocate($url, $this->_config->get('redirection_code'));
                    // This will exit                    
                }
                // Otherwise fall-through to config
            default:
                $_MIDCOM->relocate("{$prefix}config.html", $this->_config->get('redirection_code'));
                // This will exit
        }
        
        return true;
    }  
}