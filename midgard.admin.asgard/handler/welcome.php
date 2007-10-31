<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: parameters.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Welcome interface
 *
 * @package midgard.admin.asgard
 */
class midgard_admin_asgard_handler_welcome extends midcom_baseclasses_components_handler
{
    var $_reflectors = array();

    /**
     * Simple default constructor.
     */
    function midgard_admin_asgard_handler_welcome()
    {
        $this->_component = 'midgard.admin.asgard';
        parent::midcom_baseclasses_components_handler();
    }
    
    function _on_initialize()
    {
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->skip_page_style = true;
                
        $_MIDCOM->load_library('midcom.helper.datamanager2');
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
    }
    
    function _list_revised($since)
    {
        $classes = array();
        $revised = array();
        // List installed MgdSchema types and convert to DBA classes
        foreach ($_MIDGARD['schema']['types'] as $schema_type => $dummy)
        {
            if ($schema_type == 'midgard_parameter')
            {
                // Skip
                continue;
            }
            $mgdschema_class = midgard_admin_asgard_reflector::class_rewrite($schema_type);
            $dummy_object = new $mgdschema_class();
            $midcom_dba_classname = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($dummy_object);
            if (empty($midcom_dba_classname))
            {
                continue;
            }
            
            $classes[] = $midcom_dba_classname;
        }
        
        // List all revised objects
        foreach ($classes as $class)
        {
            if (!$_MIDCOM->dbclassloader->load_mgdschema_class_handler($class))
            {
                // Failed to load handling component, skip
                continue;
            }
            $qb_callback = array($class, 'new_query_builder');
            if (!is_callable($qb_callback))
            {
                continue;
            }
            $qb = call_user_func($qb_callback);
            $qb->add_constraint('metadata.revised', '>=', $since);
            $qb->add_order('metadata.revised', 'DESC');
            $objects = $qb->execute();
            
            if (count($objects) > 0)
            {
                if (!isset($this->_reflectors[$class]))
                {
                    $this->_reflectors[$class] = new midgard_admin_asgard_reflector($objects[0]);
                }
            }
            
            foreach ($objects as $object)
            {
                $revised["{$object->metadata->revised}_{$object->guid}"] = $object;
            }
        }
        
        krsort($revised);
        
        return $revised;
    }
    
    /**
     * Object editing view
     */
    function _handler_welcome($handler_id, $args, &$data)
    {
        $this->_prepare_request_data();

        $data['view_title'] = $this->_l10n->get('asgard');
        $_MIDCOM->set_pagetitle($data['view_title']);
        
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        
        if (isset($_GET['revised_after']))
        {
            $data['revised_after'] = $_GET['revised_after'];
        }
        else
        {
            $data['revised_after'] = date('Y-m-d H:i:s\Z', mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')));
        }
        
        // TODO: Run only on submit if this seems slow
        $data['revised'] = $this->_list_revised($data['revised_after']);
        
        midgard_admin_asgard_plugin::get_common_toolbar($data);
        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_welcome($handler_id, &$data)
    {
        $data['reflectors'] = $this->_reflectors;
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');    
        midcom_show_style('midgard_admin_asgard_welcome');
        midcom_show_style('midgard_admin_asgard_footer');
    }
}
?>