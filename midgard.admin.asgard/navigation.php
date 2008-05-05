﻿<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: acl_editor.php 5538 2007-03-20 13:22:41Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Navigation class for Asgard
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_navigation extends midcom_baseclasses_components_purecode
{

    /**
     * Root types
     *
     * @access public
     * @var string
     */
    var $root_types = array();

    /**
     * Some object
     *
     * @var midgard_object
     * @access private
     */
    var $_object = null;

    /**
     * Object path to the current object.
     *
     * @access private
     * @var Array
     */
    var $_object_path = array();

    var $_reflectors = array();
    var $_request_data = array();
    var $expanded_root_types = array();
    var $shown_objects = array();

    function midgard_admin_asgard_navigation($object, &$request_data)
    {
        $this->_component = 'midgard.admin.asgard';
        parent::midcom_baseclasses_components_purecode();

        $this->_object = $object;
        $this->_object_path = $this->get_object_path();
        $this->_request_data =& $request_data;

        midgard_admin_asgard_plugin::get_default_mode(&$request_data);

        $this->root_types = midcom_helper_reflector_tree::get_root_classes();

        if (array_key_exists('current_type', $this->_request_data))
        {
            $this->expanded_root_types[] = $this->_request_data['current_type'];
        }
    }

    /*
    function handle_session()
    {
        $session = new midcom_service_session();
        if ($session->exists('midgard_admin_asgard_navigation_roots'))
        {
            $this->expanded_root_types = $session->get('midgard_admin_asgard_navigation_roots');
        }
        if (isset($_GET['midgard_admin_asgard_navigation_open']))
        {
            if (!in_array($_GET['midgard_admin_asgard_navigation_open'], $this->root_types))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "MgdSchema type '{$_GET['midgard_admin_asgard_navigation_open']}' was not found.");
            }
            $this->expanded_root_types[] = $_GET['midgard_admin_asgard_navigation_open'];
            $session->set('midgard_admin_asgard_navigation_roots', $this->expanded_root_types);
        }

        if (isset($_GET['midgard_admin_asgard_navigation_close']))
        {
            if (!in_array($_GET['midgard_admin_asgard_navigation_close'], $this->root_types))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "MgdSchema type '{$_GET['midgard_admin_asgard_navigation_close']}' was not found.");
            }

            $new_root_types = array();
            foreach ($this->expanded_root_types as $type)
            {
                if ($type != $_GET['midgard_admin_asgard_navigation_close'])
                {
                    $new_root_types[] = $type;
                }
            }
            $this->expanded_root_types = $new_root_types;
            $session->set('midgard_admin_asgard_navigation_roots', $this->expanded_root_types);
        }
    }
    */

    function &_get_reflector(&$object)
    {
        if (is_string($object))
        {
            $classname = $object;
        }
        else
        {
            $classname = get_class($object);
        }
        if (!isset($this->_reflectors[$classname]))
        {
            $this->_reflectors[$classname] = midcom_helper_reflector_tree::get($object);
        }

        // Get the configuration value first
        if ($this->_config->get('show_sg0'))
        {
            $this->_reflectors[$classname]->show_sg0_objects = true;
        }
        else
        {
            $this->_reflectors[$classname]->show_sg0_objects = false;
        }

        // Then override it with user preference if requred
        switch (midgard_admin_asgard_plugin::get_preference('show_sg0'))
        {
            case 'no':
                $this->_reflectors[$classname]->show_sg0_objects = false;
                break;

            case 'yes':
                $this->_reflectors[$classname]->show_sg0_objects = true;
                break;
        }

        return $this->_reflectors[$classname];
    }

    function get_object_path()
    {
        $object_path = array();
        if (!is_object($this->_object))
        {
            return $object_path;
        }

        $object_path[] = $this->_object->guid;

        $parent = $this->_object->get_parent();
        while (   is_object($parent)
               && $parent->guid)
        {
            $object_path[] = $parent->guid;
            $parent = $parent->get_parent();
        }

        return array_reverse($object_path);
    }

    function _list_child_elements($object, $prefix = '    ', $level = 0)
    {
        if ($level > 25)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Recursion level 25 exceeded, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return;
        }
        $siblings = midcom_helper_reflector_tree::get_child_objects($object);
        if (   is_array($siblings)
            && count($siblings) > 0)
        {
            echo "{$prefix}<ul>\n";
            foreach ($siblings as $type => $children)
            {
                foreach ($children as $child)
                {
                    if (isset($this->shown_objects[$child->guid]))
                    {
                        continue;
                    }

                    $ref =& $this->_get_reflector(&$child);

                    $selected = $this->_is_selected($child);
                    $css_class = $type;
                    $this->_common_css_classes($child, $ref, $css_class);
                    $this->shown_objects[$child->guid] = true;

                    echo "{$prefix}    <li class=\"{$css_class}\">";

                    $label = htmlspecialchars($ref->get_object_label($child));
                    $icon = $ref->get_object_icon($child);
                    if (empty($label))
                    {
                        $label = "#{$child->id}";
                    }

                    echo "<a href=\"{$_MIDGARD['self']}__mfa/asgard/object/{$this->_request_data['default_mode']}/{$child->guid}/\" title=\"GUID: {$child->guid}, ID: {$child->id}\">{$icon}{$label}</a>\n";


                    if ($selected)
                    {
                        $this->_list_child_elements($child, "{$prefix}        ", $level+1);
                    }

                    echo "{$prefix}    </li>\n";
                }
            }
            echo "{$prefix}</ul>\n";
        }
    }

    /**
     * Renders the given root objects to HTML and calls _list_child_elements()
     *
     * @param array &$root_objects reference to the array of root objects
     * @param midcom_helper_reflector_tree &$ref Reflector singleton
     */
    function _list_root_elements(&$root_objects, &$ref)
    {
        echo "<ul class=\"midgard_admin_asgard_navigation\">\n";

        foreach ($root_objects as $object)
        {
            $label = $ref->get_object_label($object);
            $selected = $this->_is_selected($object);
            $css_class = get_class($object);
            $this->_common_css_classes($object, $ref, $css_class);
            $this->shown_objects[$object->guid] = true;

            echo "    <li class=\"{$css_class}\">";

            $label = htmlspecialchars($label);
            $icon = $ref->get_object_icon($object);

            if (empty($label))
            {
                $label = "#oid_{$object->id}";
            }

            echo "<a href=\"{$_MIDGARD['self']}__mfa/asgard/object/{$this->_request_data['default_mode']}/{$object->guid}/\" title=\"GUID: {$object->guid}, ID: {$object->id}\">{$icon}{$label}</a>\n";

        /* If there is exactly one root object, show its children, since 
         * this is what the user most likely wants to reach
         */
            if ($selected || sizeof($root_objects) == 1)
            {
                $this->_list_child_elements($object);
            }

            echo "    </li>\n";
        }

        echo "</ul>\n";
    }

    function _draw_plugins()
    {
        $this->_request_data['chapter_name'] = $_MIDCOM->i18n->get_string('asgard plugins', 'midgard.admin.asgard');
        midcom_show_style('midgard_admin_asgard_navigation_chapter');
        $customdata = $_MIDCOM->componentloader->get_all_manifest_customdata('asgard_plugin');
        foreach ($customdata as $component => $plugin_config)
        {
            $this->_request_data['section_url'] = "{$_MIDGARD['self']}__mfa/asgard_{$component}/";
            $this->_request_data['section_name'] = $_MIDCOM->i18n->get_string($plugin_config['name'], $component);
            $class = $plugin_config['class'];
            midcom_show_style('midgard_admin_asgard_navigation_section_header');

            $methods = get_class_methods($class);
            if (   is_array($methods)
                && in_array('navigation', $methods)
                && ($this->_request_data['plugin_name'] == "asgard_{$component}"))
            {
                call_user_func(array($class,'navigation'));
            }

            midcom_show_style('midgard_admin_asgard_navigation_section_footer');
        }
    }

    function _is_selected(&$object)
    {
        if (in_array($object->guid, $this->_object_path))
        {
            return true;
        }
        return false;
    }

    function _common_css_classes(&$object, &$ref, &$css_class)
    {
        $css_class .= " {$ref->mgdschema_class}";
        if ($object->sitegroup !== $ref->sg_context)
        {
            $css_class .= ' cross_sg';
        }
        if ($object->sitegroup === 0)
        {
            $css_class .= ' sg0';
        }
        if ($this->_is_selected($object))
        {
            $css_class .= ' selected';
        }
        if (   is_object($this->_object)
            && $object->guid == $this->_object->guid)
        {
            $css_class .= ' current';
        }
        if ( !$object->can_do('midgard:update'))
        {
            $css_class .= ' readonly';
        }
    }

    function draw()
    {
        $this->_draw_plugins();
        $this->_request_data['chapter_name'] = $_MIDCOM->i18n->get_string('midgard objects', 'midgard.admin.asgard');
        midcom_show_style('midgard_admin_asgard_navigation_chapter');

        // Use a different method for displaying the navigation
        if (midgard_admin_asgard_plugin::get_preference('navigation_type') === 'dropdown')
        {
            $this->_draw_select_navigation();
            return;
        }

        if (!empty($this->_object_path))
        {
            $root_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_object_path[0]);
        }
        
        // Included or excluded types
        $types = array();

        // Get the types that might have special display conditions
        if (   $this->_config->get('midgard_types')
            && preg_match_all('/\|([a-z0-9\.\-_]+)/', $this->_config->get('midgard_types'), $regs))
        {
            $types = $regs[1];
        }
        
        // Override with user selected
        // @TODO: Should this just include to the configuration selection, although it would break the consistency
        // of other similar preference sets, which simply override the global settings?
        if (   midgard_admin_asgard_plugin::get_preference('midgard_types')
            && preg_match_all('/\|([a-z0-9\.\-_]+)/', midgard_admin_asgard_plugin::get_preference('midgard_types'), $regs))
        {
            $types = $regs[1];
        }
        
        // Get the inclusion/exclusion model
        $model = $this->_config->get('midgard_types_model');
        if (   midgard_admin_asgard_plugin::get_preference('midgard_types_model'))
        {
            $model = midgard_admin_asgard_plugin::get_preference('midgard_types_model');
        }
        
        // Get the possible regular expression
        $regexp = $this->_config->get('midgard_types_regexp');
        if (midgard_admin_asgard_plugin::get_preference('midgard_types_regexp'))
        {
            $regexp = midgard_admin_asgard_plugin::get_preference('midgard_types_regexp');
        }
        
        // "Convert" quickly to PERL regular expression
        if (!preg_match('/^[\/|]/', $regexp))
        {
            $regexp = "/{$regexp}/";
        }
        
        $label_mapping = Array();
        foreach ($this->root_types as $root_type)
        {
            // If there are any types or if the regular expression has been set, check which
            // types should be shown
            if (   !empty($types)
                || $regexp !== '//')
            {
                // Skip the excluded
                if ($model === 'exclude')
                {
                    if (   !empty($types)
                        && in_array($root_type, $types))
                    {
                        continue;
                    }
                    
                    if (   $regexp !== '//'
                        && preg_match($regexp, $root_type))
                    {
                        continue;
                    }
                }
                else
                {
                    if (   !empty($types)
                        && !in_array($root_type, $types))
                    {
                        continue;
                    }
                    
                    if (   $regexp !== '//'
                        && !preg_match($regexp, $root_type))
                    {
                        continue;
                    }
                }
            }
            
            $ref = $this->_get_reflector($root_type);
            $label_mapping[$root_type] = $ref->get_class_label();
        }
        asort($label_mapping);
        
        foreach ($label_mapping as $root_type => $label)
        {
            $ref = $this->_get_reflector($root_type);
            if (in_array($root_type, $this->expanded_root_types))
            {
                $this->_request_data['section_url'] = "{$_MIDGARD['self']}__mfa/asgard/{$root_type}";
            }
            else
            {
                $this->_request_data['section_url'] = "{$_MIDGARD['self']}__mfa/asgard/{$root_type}";
            }

            $this->_request_data['section_name'] = $label;
            midcom_show_style('midgard_admin_asgard_navigation_section_header');
            if (   isset($root_object)
                && (   is_a($root_object, $root_type)
                    || midcom_helper_reflector::is_same_class($root_type,$root_object->__midcom_class_name__))
                || in_array($root_type, $this->expanded_root_types))
            {
                $root_objects = $ref->get_root_objects();
                if (count($root_objects) > 0)
                {
                    $this->_list_root_elements($root_objects, &$ref);
                }

            }
            midcom_show_style('midgard_admin_asgard_navigation_section_footer');
        }
    }

    function _draw_select_navigation()
    {
        if (!empty($this->_object_path))
        {
            $root_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_object_path[0]);
            $this->_request_data['root_object'] =& $root_object;
            $this->_request_data['navigation_type'] = $root_object->__new_class_name__;
        }
        elseif (isset($this->expanded_root_types[0]))
        {
            $this->_request_data['navigation_type'] = $this->expanded_root_types[0];
        }
        else
        {
            $this->_request_data['navigation_type'] = '';
        }

        $label_mapping = Array();

        foreach ($this->root_types as $root_type)
        {
            $ref = $this->_get_reflector($root_type);
            $label_mapping[$root_type] = $ref->get_class_label();
        }
        asort($label_mapping);

        $this->_request_data['label_mapping'] = $label_mapping;
        $this->_request_data['expanded_root_types'] = $this->expanded_root_types;

        midcom_show_style('midgard_admin_asgard_navigation_sections');

        // Stop here if there is no MgdSchema object path to show
        if (!$this->_request_data['navigation_type'])
        {
            return;
        }

        if (in_array($this->_request_data['navigation_type'], $this->expanded_root_types))
        {
            $this->_request_data['section_url'] = "{$_MIDGARD['self']}__mfa/asgard/{$this->_request_data['navigation_type']}";
        }
        else
        {
            $this->_request_data['section_url'] = "{$_MIDGARD['self']}__mfa/asgard/{$this->_request_data['navigation_type']}";
        }

        $ref = $this->_get_reflector($this->_request_data['navigation_type']);

        // Show the navigation of the requested object
        $root_objects = $ref->get_root_objects();

        $this->_request_data['section_url'] = "{$_MIDGARD['self']}__mfa/asgard/{$this->_request_data['navigation_type']}";
        $this->_request_data['section_name'] = $ref->get_class_label();

        if (   is_array($root_objects)
            && count($root_objects) > 0)
        {
            midcom_show_style('midgard_admin_asgard_navigation_section_header');
            $this->_list_root_elements($root_objects, $ref);
            midcom_show_style('midgard_admin_asgard_navigation_section_footer');
        }
    }
}
?>