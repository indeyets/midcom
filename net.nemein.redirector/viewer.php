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
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);

        // Match /
        $this->_request_switch[] = array
        (
            'handler' => 'redirect'
        );

        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler' => array
            (
                'midcom_core_handler_configdm2',
                'config',
            ),
            'schemadb' => 'file:/net/nemein/redirector/config/schemadb_config.inc',
            'fixed_args' => array
            (
                'config',
            ),
        );
    }

    /**
     * Process the redirect request
     * 
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_redirect($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        
        if (   is_null($this->_config->get('redirection_type'))
            || (   $_MIDCOM->auth->admin
                && !$this->_config->get('admin_redirection')))
        {
            // No type set, redirect to config
            $_MIDCOM->relocate("{$prefix}config/");
            // This will exit
        }
        
        switch ($this->_config->get('redirection_type'))
        {
            case 'node':
                $nap = new midcom_helper_nav();
                $id = $this->_config->get('redirection_node');
                
                if (is_string($id))
                {
                    $topic = new midcom_db_topic($id);
                    
                    if (   !$topic
                        || !$topic->guid)
                    {
                        break;
                    }
                    
                    $id = $topic->id;
                }
                
                $node = $nap->get_node($id);
                
                // Node not found, fall through to configuration
                if (!$node)
                {
                    break;
                }
                
                $_MIDCOM->relocate($node[MIDCOM_NAV_FULLURL], $this->_config->get('redirection_code'));
                // This will exit
                
            case 'subnode':
                $nap = new midcom_helper_nav();
                $nodes = $nap->list_nodes($nap->get_current_node());
                
                // Subnodes not found, fall through to configuration
                if (count($nodes) == 0)
                {
                    break;
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

                        $default_lang_code = $this->_config->get('default_lang_code');
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
        }

        $_MIDCOM->relocate("{$prefix}config/", $this->_config->get('redirection_code'));
    }
}
?>