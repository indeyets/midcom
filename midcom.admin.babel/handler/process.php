<?php
/**
 * @package midcom.admin.babel
 */
class midcom_admin_babel_handler_process extends midcom_baseclasses_components_handler
{
    var $_debug_prefix;

    /** which language is edited */
    var $_lang = 'en';

    /** path of the component to localize */
    var $_component_path = null;

    /** data to be saved */
    var $_save_new;
    var $_save_update;

    /** midcom_l10n instance $_component_path */
    var $_component_l10n;

    /**
     * Simple constructor
     *
     * @access public
     */
    function __construct()
    {
//        $this->_component = 'midcom.admin.babel';
        parent::__construct();
    }

    function _on_initialize()
    {

        $this->_l10n = $_MIDCOM->i18n->get_l10n('midcom.admin.babel');
        $this->_request_data['l10n'] = $this->_l10n;
        $this->_debug_prefix = 'midcom_admin_babel::';
        $this->_save_new = false;
        $this->_save_update = false;
        
        $this->_fallback_language = $_MIDCOM->i18n->get_fallback_language();

        $_MIDCOM->cache->content->no_cache();

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel'   => 'stylesheet',
                'type'  => 'text/css',
                'media' => 'screen',
                'href'  => MIDCOM_STATIC_URL .'/midcom.admin.babel/babel.css'
            )
        );

        // Initialize Asgard plugin

        midgard_admin_asgard_plugin::prepare_plugin($this->_l10n->get('midcom.admin.babel'),$this->_request_data);

    }

    function _prepare_toolbar(&$data)
    {
        midgard_admin_asgard_plugin::get_common_toolbar($data);
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '__mfa/asgard_midcom.admin.babel/',
            MIDCOM_NAV_NAME => $this->_l10n->get('midcom.admin.babel'),
        );

        switch ($handler_id)
        {
            case '____mfa-asgard_midcom.admin.babel-status':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.babel/status/{$this->_lang}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('translation status for language %s'), $this->_l10n->_language_db[$this->_lang]['enname']),
                );
                break;
            case '____mfa-asgard_midcom.admin.babel-edit':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.babel/status/{$this->_lang}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('translation status for language %s'), $this->_l10n->_language_db[$this->_lang]['enname']),
                );
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.babel/status/{$this->_lang}/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('edit strings for %s [%s]'), $this->_request_data['component_translated'], $this->_l10n->_language_db[$this->_lang]['enname']),
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
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
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_select($handler_id, $args, &$data)
    {
        $this->_update_breadcrumb_line($handler_id);
        $this->_prepare_toolbar($data);
        $_MIDCOM->set_pagetitle($data['view_title']);
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_select($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();
        midcom_show_style('midcom_admin_babel_select');
        midgard_admin_asgard_plugin::asgard_footer();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
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
            $_MIDCOM->relocate("__mfa/asgard_midcom.admin.babel/status/{$this->_lang}/");
            // This will exit
        }

        debug_add("saving data for component '{$this->_component_path}', language '{$this->_lang}'");

        $this->_component_l10n = $_MIDCOM->i18n->get_l10n($this->_component_path);

        if (array_key_exists('string_id', $_REQUEST))
        {
            $this->_save_update = array
            (
                'id' => $_REQUEST['string_id'],
                'value' => $_REQUEST['string_value']
            );
        }

        if (   array_key_exists('new_stringid', $_REQUEST)
            && $_REQUEST['new_stringid']
            && array_key_exists('new_fallback', $_REQUEST)
            && $_REQUEST['new_fallback'])
        {
            $this->_save_new = Array
            (
                'stringid' => $_REQUEST['new_stringid'],
                $this->_fallback_language => $_REQUEST['new_fallback']
            );
            
            if (   array_key_exists('new_loc', $_REQUEST)
                && $_REQUEST['new_loc'])
            {
                $this->_save_new['loc'] = $_REQUEST['new_loc'];
            }
        }

        $changes = false;

        // update data
        if ($this->_save_update)
        {
            debug_add('Updating strings', MIDCOM_LOG_DEBUG);
            foreach ($this->_save_update['id'] as $k => $v)
            {
                $id = $this->_save_update['id'][$k];
                $loc = $this->_save_update['value'][$k];
                $origloc = $this->_component_l10n->get($id, $this->_lang);

                if ($this->_component_l10n->string_exists($id, $this->_lang))
                {
                    if ($loc == $origloc)
                    {
                        debug_add("'{$id}' is unchanged, skipping it.");
                        continue;
                    }
                    
                    if (!$loc)
                    {
                        debug_add("Resetting '{$id}'", MIDCOM_LOG_DEBUG);
                        $this->_component_l10n->delete($id, $this->_lang);
                        $changes = true;
                    }
                    else
                    {
                        debug_add("Updating '{$id}' -> '{$loc}'", MIDCOM_LOG_DEBUG);
                        $this->_component_l10n->update($id, $this->_lang, $loc);
                        $changes = true;
                    }
                }
                else if (!$loc)
                {
                    debug_add("    Creating '{$id}' -> '{$loc}'", MIDCOM_LOG_DEBUG);
                    $this->_component_l10n->create($id, $this->_lang, $loc);
                    $changes = true;
                }
                else
                {
                    debug_add("    Ignoring '{$id}' -> '{$loc}'", MIDCOM_LOG_DEBUG);
                }
            }
        }

        // create new strings
        if ($this->_save_new)
        {
            debug_add('Creating new string', MIDCOM_LOG_DEBUG);
            
            // create fallback language string
            $this->_component_l10n->update($this->_save_new['stringid'], $this->_fallback_language, $this->_save_new[$this->_fallback_language]);
            
            // create loc'd string
            if (array_key_exists('loc', $this->_save_new))
            {
                $this->_component_l10n->update($this->_save_new['stringid'], $this->_lang, $this->_save_new['loc']);
            }

            $changes = true;
        }

        if ($changes)
        {
            debug_add('Changes have been made, Flushing to disk now.');
            $this->_component_l10n->flush();
        }

        $this->_update_breadcrumb_line($handler_id);
        $_MIDCOM->set_pagetitle($data['view_title']);
        debug_pop();

        $_MIDCOM->relocate("__mfa/asgard_midcom.admin.babel/edit/{$this->_component_path}/{$this->_lang}/");
        // This will exit
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
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

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_status($handler_id, $args, &$data)
    {
        $this->_lang = $args[0];
        if (!$this->validate_language($this->_lang))
        {
            return false;
        }

        $this->_update_breadcrumb_line($handler_id);
        $this->_prepare_toolbar($data);
        $_MIDCOM->set_pagetitle($data['view_title']);
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_status($handler_id, &$data)
    {
        midgard_admin_asgard_plugin::asgard_header();

        $data['language'] = $this->_lang;

        $status = midcom_admin_babel_plugin::calculate_language_status($this->_lang);
        $data['components_core'] = $status['components_core'];
        $data['components_other'] = $status['components_other'];
        $data['strings_all'] = $status['strings_all'];

        midcom_show_style('midcom_admin_babel_status_header');

        $data['section'] = 'core';
        midcom_show_style('midcom_admin_babel_status_section_header');
        
        foreach ($data['components_core'] as $component => $string_counts)
        {
            $data['component'] = $component;
            $data['icon'] = $_MIDCOM->componentloader->get_component_icon($component);
            $data['string_counts'] = $string_counts;
            midcom_show_style('midcom_admin_babel_status_item');
        }
        
        midcom_show_style('midcom_admin_babel_status_section_footer');

        $data['section'] = 'other';
        midcom_show_style('midcom_admin_babel_status_section_header');
        
        foreach ($data['components_other'] as $component => $string_counts)
        {
            $data['component'] = $component;
            $data['icon'] = $_MIDCOM->componentloader->get_component_icon($component);
            $data['string_counts'] = $string_counts;
            midcom_show_style('midcom_admin_babel_status_item');
        }
        
        midcom_show_style('midcom_admin_babel_status_section_footer');

        midcom_show_style('midcom_admin_babel_status_footer');
        midgard_admin_asgard_plugin::asgard_footer();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_component_path = $args[0];
        $this->_lang = $args[1];
        if (!$this->validate_language($this->_lang))
        {
            return false;
        }

        debug_push($this->_debug_prefix . 'handle');

        // make sure text is displayed as utf-8 => REALLY?
        //header('Content-type: text/html; charset=UTF-8');

        if (   $this->_component_path
            && $this->_lang)
        {
            debug_add('Loading i10n class for '.$this->_component_path, MIDCOM_LOG_DEBUG);
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
                $this->_prepare_toolbar($data);
                $_MIDCOM->set_pagetitle($data['view_title']);
                debug_pop();
                return true;
            }
        }

        debug_pop();
        return false;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {


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
                    $this->_fallback_language => $this->_component_l10n->get($id, $this->_fallback_language),
                    $this->_lang => $loc
                );
            }
        }

        $this->_request_data['view_strings'] = $view_strings;

        midgard_admin_asgard_plugin::asgard_header();
        $this->_show_permission_check($handler_id, &$data);
        midcom_show_style('midcom_admin_babel_edit');
        midgard_admin_asgard_plugin::asgard_footer();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_permission_check($handler_id, &$data)
    {
        if ($this->_component_path == 'midcom')
        {
            $path = MIDCOM_ROOT . '/midcom/locale';
        }
        else
        {
            $path = MIDCOM_ROOT . '/' . str_replace('.', '/', $this->_component_path) . '/locale';
        }
        
        $fallback = "{$path}/default.{$this->_fallback_language}.txt";
        $main = "{$path}/default.{$this->_lang}.txt";

        if (   !is_writable($path)
            || (   file_exists($fallback)
                && ! is_writable($fallback))
            || (   file_exists($main)
                && ! is_writable($main)))
        {
            midcom_show_style('midcom_admin_babel_permission_denied');
        }
    }
}
?>