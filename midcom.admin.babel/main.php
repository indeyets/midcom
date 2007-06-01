<?php
class midcom_admin_babel_main extends midcom_baseclasses_components_handler 
{

    var $_debug_prefix;

    var $_snippetdir;
    
    /** which language is edited */
    var $_lang = 'en';

    /** path of the component to localize */
    var $_component_path = null;

    /** data to be saved */
    var $_save_new;
    var $_save_update;

    /** midcom_l10n instance $_component_path */
    var $_component_l10n;


    function midcom_admin_babel_main() 
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
    	$_MIDCOM->auth->require_admin_user();
    	$_MIDCOM->style->prepend_component_styledir('midcom.admin.babel');
	
        $this->_debug_prefix = "midcom_admin_babel::";

        $this->_save_new = false;
        $this->_save_update = false;

        $this->_l10n = $_MIDCOM->i18n->get_l10n('midcom.admin.babel');

        $_MIDCOM->cache->content->no_cache();

        $_MIDCOM->add_link_head
        (
            array 
            ( 
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL ."/midcom.helper.datamanager2/legacy.css"
            )
        );
        
        $_MIDCOM->add_link_head
        (
            array 
            ( 
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL ."/midcom.admin.babel/babel.css"
            )
        );
    }

    function get_plugin_handlers()
    {
        return array
        (
	       'select' => array
            (
                'handler' => Array('midcom_admin_babel_main', 'select'),
            ),
            'status' => array
            (
                'handler' => Array('midcom_admin_babel_main', 'status'),
                'fixed_args' => 'status',
                'variable_args' => 1,
            ),
            'edit' => array
            (
                'handler' => Array('midcom_admin_babel_main', 'edit'),
                'fixed_args' => 'edit',
                'variable_args' => 2,
            ),
            'save' => array
            (
            	'handler' => Array('midcom_admin_babel_main', 'save'),
            	'fixed_args' => 'save',
                'variable_args' => 2,
    	    ),
        );
    }

    function validate_language($lang)
    {
        // TODO: Validate via ML instead
        if (array_key_exists($lang, $this->_l10n->_language_db))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Checks if component is a part of the default MidCOM distribution
     * or an external component
     *
     * @param string $component Component to check
     */
    function is_core_component($component)
    {
        // TODO: Put this into a centralized location
        $core_components = array
        (
            'midcom',
            // From midcom dependencies
            'midcom.admin.babel',            
            'midcom.admin.folder',
            'midcom.admin.help',
            'midcom.admin.settings',
            'midcom.admin.styleeditor',
            'midgard.admin.acl',
            'midgard.admin.sitegroup',
            'no.bergfald.rcs',
            // From task_midgardcms dependencies
            'net.nehmer.blog',
            'net.nemein.calendar',
            'midcom.helper.imagepopup',
            'midcom.helper.search',
            'de.linkm.sitemap',
            'midgard.admin.sitewizard',
            'net.nehmer.static',
            // Other dependencies
            'midcom.helper.datamanager',
            'midcom.helper.datamanager2',
            'midcom.helper.xml',
            'net.nehmer.markdown',
            'net.nemein.tag',
            'net.nemein.rss',
            'org.openpsa.calendarwidget',
        );
        
        if (in_array($component, $core_components))
        {
            return true;
        }
        return false;
    }

    function _handler_select($handler_id, $args, &$data)
    {
        $this->_update_breadcrumb_line($handler_id);
        return true;
    }
    
    function _show_select($handler_id, &$data)
    {
        midcom_show_style('midcom_admin_babel_select');
    }

    function _handler_save($handler_id, $args, &$data)
    {
        $this->_component_path = $args[0];
        $this->_lang = $args[1];
        if (!$this->validate_language($this->_lang))
        {
            return false;
        }
        
        if (array_key_exists('f_cancel', $_POST))
        {
            $_MIDCOM->relocate("__ais/l10n/status/{$this->_lang}/");
            // This will exit
        }
    
        debug_add("saving data for component '".$this->_component_path."', language '".$this->_lang."'", MIDCOM_LOG_DEBUG);
        
    	$this->_component_l10n = $_MIDCOM->i18n->get_l10n($this->_component_path);
			
        if (array_key_exists("string_id", $_REQUEST)) 
        {
            $this->_save_update = Array (
                "id" => $_REQUEST["string_id"],
                "value" => $_REQUEST["string_value"]
            );
        }

        if ((array_key_exists("new_stringid", $_REQUEST)) &&
            ($_REQUEST["new_stringid"] != "") &&
            (array_key_exists("new_en", $_REQUEST)) &&
            ($_REQUEST["new_en"] != "")) 
        {
            $this->_save_new = Array (
                "stringid" => $_REQUEST["new_stringid"],
                "en" => $_REQUEST["new_en"]
            );
            if ((array_key_exists("new_loc", $_REQUEST)) &&
                ($_REQUEST["new_loc"] != ""))
            {
                $this->_save_new["loc"] = $_REQUEST["new_loc"];
            }
        }

        $changes = false;
            
        // update data
        if ($this->_save_update) 
        {
            debug_add("Updating strings", MIDCOM_LOG_DEBUG);
            foreach ($this->_save_update["id"] as $k => $v) 
            {
                $id = $this->_save_update["id"][$k];
                $loc = $this->_save_update["value"][$k];
                $origloc = $this->_component_l10n->get($id, $this->_lang);
                
                if ($this->_component_l10n->string_exists($id, $this->_lang)) 
                {
                    if ($loc == $origloc) 
                    {
                        debug_add("    '$id' is unchanged, skipping it.");
                        continue;
                    }
                    if ($loc == "") 
                    {
                        debug_add("    Resetting '$id'", MIDCOM_LOG_DEBUG);
                        $this->_component_l10n->delete($id, $this->_lang);
                        $changes = true;
                    } 
                    else 
                    {
                        debug_add("    Updating '$id' -> '$loc'", MIDCOM_LOG_DEBUG);
                        $this->_component_l10n->update($id, $this->_lang, $loc);
                        $changes = true;
                    }
                } 
                else if ($loc != "") 
                {
                    debug_add("    Creating '$id' -> '$loc'", MIDCOM_LOG_DEBUG);
                    $this->_component_l10n->update($id, $this->_lang, $loc);
                    $changes = true;
                } 
                else 
                {
                    debug_add("    Ignoring '$id' -> '$loc'", MIDCOM_LOG_DEBUG);
                }
            }
        }
			
        // create new strings
        if ($this->_save_new) 
        {
            debug_add("Creating new string", MIDCOM_LOG_DEBUG);
            // create english string
            $this->_component_l10n->update($this->_save_new["stringid"], "en", $this->_save_new["en"]);
                // create loc'd string
            if (array_key_exists("loc", $this->_save_new))
                $this->_component_l10n->update($this->_save_new["stringid"], $this->_lang, $this->_save_new["loc"]);
             
            $changes = true;
        }
        
        if ($changes) 
        {
            debug_add("Changes have been made, Flushing to disk now.");
            $this->_component_l10n->flush();
        }
        
        $this->_update_breadcrumb_line($handler_id);
        debug_pop();
        
        $_MIDCOM->relocate("__ais/l10n/edit/{$this->_component_path}/{$this->_lang}/");
        // This will exit
    }
    
    function _show_save($handler_id, &$data)
    {
        if ($this->_lang && $this->_component_path)
        {
            $this->_show_edit();
        }
        else
        {
            $this->_show_select();
        }
    }
    
    function calculate_language_status($lang)
    {
        $status = array
        (
            'components_core' => array(),
            'components_other' => array(),
            'strings_all' => array
            (
                'total'      => 0,
                'translated' => 0,
            ),
            'strings_core' => array
            (
                'total'      => 0,
                'translated' => 0,
            ),
            'strings_other' => array
            (
                'total'      => 0,
                'translated' => 0,
            )
        );
        
        $components = array('midcom');
        
        // Load translation status of each component
        foreach ($_MIDCOM->componentloader->manifests as $manifest)
        {
            $components[] = $manifest->name;
        }
        
        foreach ($components as $component)
        {
            $component_l10n = $_MIDCOM->i18n->get_l10n($component);
            
            if (midcom_admin_babel_main::is_core_component($component))
            {
                $string_array = 'components_core';
            }
            else
            {
                $string_array = 'components_other';
            }       
            
            $status[$string_array][$component] = array();

            $string_ids = array_unique($component_l10n->get_all_string_ids());
            
            $status[$string_array][$component]['total'] = count($string_ids);
            $status['strings_all']['total'] += $status[$string_array][$component]['total'];
            
            if ($string_array == 'components_core')
            {
                $status['strings_core']['total'] += $status[$string_array][$component]['total'];
            }
            else
            {
                $status['strings_other']['total'] += $status[$string_array][$component]['total'];
            }
            
            $status[$string_array][$component]['translated'] = 0;
            
            foreach ($string_ids as $id) 
            {
                if ($component_l10n->string_exists($id, $lang))
                {
                    $status[$string_array][$component]['translated']++;
                    $status['strings_all']['translated']++;
                    
                    if (midcom_admin_babel_main::is_core_component($component))
                    {
                        $status['strings_core']['translated']++;
                    }
                    else
                    {
                        $status['strings_other']['translated']++;
                    }
                }
            }         
        }
        
        return $status;
    }
    
    function _handler_status($handler_id, $args, &$data)
    {
        $this->_lang = $args[0];
        if (!$this->validate_language($this->_lang))
        {
            return false;
        }
        
        $this->_update_breadcrumb_line($handler_id);
        
        return true;
    }
    
    function _show_status($handler_id, &$data)
    {
        $data['language'] = $this->_lang;
        
        $status = $this->calculate_language_status($this->_lang);
        $data['components_core'] = $status['components_core'];
        $data['components_other'] = $status['components_other'];
        $data['strings_all'] = $status['strings_all'];
        
        midcom_show_style('midcom_admin_babel_status_header');

        $data['section'] = 'core';
        midcom_show_style('midcom_admin_babel_status_section_header');
        foreach ($data['components_core'] as $component => $string_counts)
        {
            $data['component'] = $component;
            $data['string_counts'] = $string_counts;
            midcom_show_style('midcom_admin_babel_status_item');
        }
        midcom_show_style('midcom_admin_babel_status_section_footer');
        
        $data['section'] = 'other';
        midcom_show_style('midcom_admin_babel_status_section_header');
        foreach ($data['components_other'] as $component => $string_counts)
        {
            $data['component'] = $component;
            $data['string_counts'] = $string_counts;
            midcom_show_style('midcom_admin_babel_status_item');
        }
        midcom_show_style('midcom_admin_babel_status_section_footer');

        midcom_show_style('midcom_admin_babel_status_footer');
    }

    function _handler_edit($handler_id, $args, &$data) 
    {
        $this->_component_path = $args[0];
        $this->_lang = $args[1];
        if (!$this->validate_language($this->_lang))
        {
            return false;
        }
    
        debug_push($this->_debug_prefix . "handle");

        // make sure text is displayed as utf-8 => REALLY?
        //header("Content-type: text/html; charset=UTF-8");

        if (   $this->_component_path
            && $this->_lang) 
        {
            debug_add("Loading i10n class for ".$this->_component_path, MIDCOM_LOG_DEBUG);        
            if (!$this->_component_l10n = $_MIDCOM->i18n->get_l10n($this->_component_path))
            {
                debug_pop();
                return false;
            }
            else
            {
                if ($this->_component_path == 'midcom')
                {
                    $data['component_translated'] = 'MidCOM Core';
                }
                else
                {
                    $_MIDCOM->componentloader->manifests[$this->_component_path]->get_name_translated();
                    $data['component_translated'] = $_MIDCOM->componentloader->manifests[$this->_component_path]->name_translated;
                }
            
                $this->_update_breadcrumb_line($handler_id);
                debug_pop();
                return true;
            }
        }
        
        debug_pop();
        return false;
    }
 
    function _show_edit($handler_id, &$data)
    {
        $this->_show_permission_check($handler_id, &$data);
        
        $this->_request_data['view_component'] = $this->_component_path;
        $this->_request_data['view_lang'] = $this->_lang;
        $this->_request_data['view_language_db'] = $this->_i18n->get_language_db();
        
        $view_strings = Array();
        $ids = $this->_component_l10n->get_all_string_ids();
        if (is_array($ids) && (count($ids) > 0))
        {
            foreach ($ids as $id) 
            {
                if ($this->_component_l10n->string_exists($id, $this->_lang))
                {
                    $loc = $this->_component_l10n->get($id, $this->_lang);
                }
                else
                {
                    $loc = '';
                }
                $view_strings[$id] = array
                (
                    'en'         => $this->_component_l10n->get($id, 'en'),
                    $this->_lang => $loc
                );
            }
        }

    	$this->_request_data['view_strings'] = $view_strings;

        midcom_show_style('midcom_admin_babel_edit');

        return true;
    }

    function _show_permission_check($handler_id, &$data)
    {
        if ($this->_component_path == "midcom") 
        {
            $path = MIDCOM_ROOT . '/midcom/locale';
        } 
        else 
        {
            $path = MIDCOM_ROOT . '/' . str_replace(".", "/", $this->_component_path) . '/locale';
        }
        $en = "{$path}/default.en.txt";
        $main = "{$path}/default.{$this->_lang}.txt";
        
        if (    ! is_writable($path)
            || (file_exists($en) && ! is_writable($en))
            || (file_exists($main) && ! is_writable($main)))
        {
            midcom_show_style('midcom_admin_babel_permission_denied');
        }
    }
    
    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();
        
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '__ais/l10n/',
            MIDCOM_NAV_NAME => $this->_l10n->get('midcom.admin.babel'),
        );
        
        switch ($handler_id)
        {
            case '____ais-l10n-status':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__ais/l10n/status/{$this->_lang}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('translation status for language %s'), $this->_l10n->_language_db[$this->_lang]['enname']),
                );
                break;
            case '____ais-l10n-edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__ais/l10n/status/{$this->_lang}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('translation status for language %s'), $this->_l10n->_language_db[$this->_lang]['enname']),
                );
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__ais/l10n/status/{$this->_lang}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('edit strings for %s [%s]'), $this->_request_data['component_translated'], $this->_l10n->_language_db[$this->_lang]['enname']),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}

?>