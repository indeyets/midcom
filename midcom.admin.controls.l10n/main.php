<?php

class midcom_admin_controls_l10n_main {

    var $_debug_prefix;

    var $_config;
    var $_topic;
    
    var $_snippetdir;
    
    /** which language is edited */
    var $_lang;

    /** path of the component */
    var $_component;

    /** path of the l10n library */
    var $_library;
 
    /** data to be saved */
    var $_save_new;
    var $_save_update;

    /** midcom int'l service */
    var $_i18n;
    
    /** midcom_l10n instance with current $_library */
    var $_l10n;


    function midcom_admin_controls_l10n_main($topic, $config) {
        $this->_debug_prefix = "midcom_admin_controls_l10n::";

        $this->_config = $config;
        $this->_topic = $topic;

        $this->_lang = false;
        $this->_component = false;
        $this->_library = false;
		
        $this->_save_new = false;
        $this->_save_update = false;
        
        $this->_i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_i18n->set_charset("UTF-8");
        $this->_l10n = null;
    }


    function can_handle($argc, $argv) {
        debug_push($this->_debug_prefix . "can_handle");

        // welcome screen
        if ($argc == 0) {
            debug_add("showing language and component selection screen", MIDCOM_LOG_DEBUG);
            debug_pop();
            return true;
        }
        
        // "edit", f_lang, f_component set
        if (($argc == 1) && ($argv[0] == "edit")) 
        {
            $this->_component = $_REQUEST["f_component"];
            $this->_lang = $_REQUEST["f_lang"];
            debug_add("editing component '".$this->_component."', language '".$this->_lang."'", MIDCOM_LOG_DEBUG);
            return true;
        }
        
        if (($argc == 1) && ($argv[0] == "save")) 
        {
            $this->_component = $_REQUEST["f_component"];
            $this->_lang = $_REQUEST["f_lang"];
            debug_add("saving data for component '".$this->_component."', language '".$this->_lang."'", MIDCOM_LOG_DEBUG);
			
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

                return true;
        }

        debug_pop();
        return true;
    }


    function handle() {
        debug_push($this->_debug_prefix . "handle");

        // make sure text is displayed as utf-8
        header("Content-type: text/html; charset=UTF-8");
        
        if ($this->_component) 
        {
            $loader =& $GLOBALS["midcom"]->get_component_loader();
            //$lib = $loader->path_to_snippetpath($this->_component) . "/_l10n";
            debug_add("Loading i10n class for ".$this->_component, MIDCOM_LOG_DEBUG);
            
            $this->_l10n = $this->_i18n->get_l10n($this->_component);
			
            $changes = false;
            
            // update data
            if ($this->_save_update) 
            {
                debug_add("Updating strings", MIDCOM_LOG_DEBUG);
                foreach ($this->_save_update["id"] as $k => $v) 
                {
                    $id = $this->_save_update["id"][$k];
                    $loc = $this->_save_update["value"][$k];
                    $origloc = $this->_l10n->get($id, $this->_lang);
                    
                    if ($this->_l10n->string_exists($id, $this->_lang)) 
                    {
                        if ($loc == $origloc) 
                        {
                            debug_add("    '$id' is unchanged, skipping it.");
                            continue;
                        }
                        if ($loc == "") 
                        {
                            debug_add("    Resetting '$id'", MIDCOM_LOG_DEBUG);
                            $this->_l10n->delete($id, $this->_lang);
                            $changes = true;
                        } 
                        else 
                        {
                            debug_add("    Updating '$id' -> '$loc'", MIDCOM_LOG_DEBUG);
                            $this->_l10n->update($id, $this->_lang, $loc);
                            $changes = true;
                        }
                    } 
                    else if ($loc != "") 
                    {
                        debug_add("    Creating '$id' -> '$loc'", MIDCOM_LOG_DEBUG);
                        $this->_l10n->update($id, $this->_lang, $loc);
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
                $this->_l10n->update($this->_save_new["stringid"], "en", $this->_save_new["en"]);

                // create loc'd string
                if (array_key_exists("loc", $this->_save_new))
                    $this->_l10n->update($this->_save_new["stringid"], $this->_lang, $this->_save_new["loc"]);
                 
                $changes = true;
            }
            
            if ($changes) 
            {
                debug_add("Changes have been made, Flushing to disk now.");
                $this->_l10n->flush();
            }
        }

        debug_pop();
        return true;
    }
 

    function show() {
        debug_push($this->_debug_prefix . "show");
        
        if ($this->_lang && $this->_component)
        {
            $this->_show_edit();
        }
        else
        {
            $this->_show_select_lang();
        }

        debug_pop();
        return true;
    }


    function _show_select_lang() {
        global $view_language_db;
        $view_language_db = $this->_i18n->get_language_db();
        midcom_show_style("select_lang");
    }


    function _show_edit() {
        global $midcom;
        global $view_component;
        global $view_strings;
        global $view_lang;
        global $view_language_db;

        $this->_show_permission_check();
        
        // _lang and _component have to be set!

        $view_component = $this->_component;
        $view_lang = $this->_lang;
        $view_language_db = $this->_i18n->get_language_db();
        
        $view_strings = Array();
        $ids = $this->_l10n->get_all_string_ids();

        if (is_array($ids) && (count($ids) > 0))
        {
            foreach ($ids as $id) 
            {
                if ($this->_l10n->string_exists($id, $this->_lang))
                {
                    $loc = $this->_l10n->get($id, $this->_lang);
                }
                else
                {
                    $loc = "";
                }
                $view_strings[$id] = array(
                    "en" => $this->_l10n->get($id, "en"),
                    $this->_lang => $loc
                );
            }
        }

        midcom_show_style("edit");
    }

    function _show_permission_check() {
        if ($this->_component == "midcom") 
        {
            $path = MIDCOM_ROOT . '/midcom/locale';
        } 
        else 
        {
            $path = MIDCOM_ROOT . '/' . str_replace(".", "/", $this->_component) . '/locale';
        }
        $en = "{$path}/default.en.txt";
        $main = "{$path}/default.{$this->_lang}.txt";
        
        if (    ! is_writable($path)
            || (file_exists($en) && ! is_writable($en))
            || (file_exists($main) && ! is_writable($main)))
        {
            midcom_show_style('permission_denied');
        }
    }

    function get_metadata() {
        // metadata for the current element
 
        return array (
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITOR => 0,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_EDITED => 0
        );
    }

}

?>