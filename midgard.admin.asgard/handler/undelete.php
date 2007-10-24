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
class midgard_admin_asgard_handler_undelete extends midcom_baseclasses_components_handler
{
    var $type = '';
    
    var $_undeleted_size = 0;

    /**
     * Simple default constructor.
     */
    function midgard_admin_asgard_handler_undelete()
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
     * Get an object, deleted or not
     */
    function _get_object($guid, $type)
    {
        static $objects = array();
        
        if (!isset($objects[$guid]))
        {
            $qb = new midgard_query_builder($type);
            $qb->add_constraint('guid', '=', $guid);
            $qb->include_deleted();
            $results = $qb->execute();
            if (count($results) == 0)
            {
                $objects[$guid] = null;
            }
            else
            {
                $objects[$guid] = $results[0];
            }
        }
        
        return $objects[$guid];
    }
    
    function _undelete_parameters($guid)
    {
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $params = $qb->execute();
        foreach ($params as $param)
        {
            if (midgard_parameter::undelete($param->guid))
            {
                $this->_undeleted_size += $param->metadata->size;
            }
        }
    }
    
    function _undelete_attachments($guid)
    {
        $qb = new midgard_query_builder('midgard_attachment');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $atts = $qb->execute();
        foreach ($atts as $att)
        {
            if (!midgard_attachment::undelete($att->guid))
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed undeleting attachment %s, reason %s'), $att->name, mgd_errstr()), 'error');
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('attachment %s undeleted'), $att->name, mgd_errstr()), 'ok');
                $this->_undeleted_size += $att->metadata->size;
                $this->_undelete_parameters($att->guid);
            }
        }
    }
    
    function _undelete($guids, $type)
    {
        $ref = midgard_admin_asgard_reflector_tree::get($type);
        foreach ($guids as $guid)
        {
            $object = $this->_get_object($guid, $type);
            if (is_null($object))
            {
                // Purged, skip
                continue;
            }
            $label = $ref->get_label_property();
            
            if (!midgard_topic::undelete($guid))
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed undeleting %s, reason %s'), "{$type} {$object->$label}", mgd_errstr()), 'error');
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('%s undeleted'), "{$type} {$object->$label}", mgd_errstr()), 'ok');
                $this->_undeleted_size += $object->metadata->size;
            }

            $this->_undelete_parameters($guid);
            $this->_undelete_attachments($guid);

            // List all deleted children
            $children_types = $ref->get_child_objects($object, true);
            
            foreach ($children_types as $type => $children)
            {
                $child_guids = array();
                foreach ($children as $child)
                {
                    if ($child->metadata->deleted)
                    {
                        $child_guids[] = $child->guid;
                    }
                }
                $this->_undelete($child_guids, $type);
            }
        }
    }
    
    /**
     * Trash view
     */
    function _handler_trash_type($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
    
        $this->type = $args[0];
        $root_types = midgard_admin_asgard_reflector_tree::get_root_classes();

        $data['view_title'] = midgard_admin_asgard_plugin::get_type_label($this->type);
        $_MIDCOM->set_pagetitle($data['view_title']);
        
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        
        midgard_admin_asgard_plugin::get_common_toolbar($data);

        $dummy = new $this->type;
        $data['midcom_dba_classname'] = $_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object($dummy);
        if (!$data['midcom_dba_classname'])
        {
            return false;
        }
        $data['type'] = $this->type;
        $data['reflector'] = midgard_admin_asgard_reflector::get($data['type']);
        $data['label_property'] = $data['reflector']->get_label_property();
        
        if (   isset($_POST['undelete'])
            && is_array($_POST['undelete']))
        {
            $this->_undelete($_POST['undelete'], $this->type);
            
            if ($this->_undeleted_size > 0)
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('in total %s undeleted'), midcom_helper_filesize_to_string($this->_undeleted_size)), 'info');
            }
        }

        $_MIDCOM->load_library('org.openpsa.qbpager');
        $qb = new org_openpsa_qbpager_direct($data['type'], "{$data['midcom_dba_classname']}_trash");
        $qb->include_deleted();
        $qb->add_constraint('metadata.deleted', '=', true);
        $qb->add_order('metadata.revised', 'DESC');
        $data['qb'] =& $qb;
        $data['trash'] = $qb->execute_unchecked();
        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_trash_type($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        $data['current_type'] = $this->type;
        midcom_show_style('midgard_admin_asgard_middle');

        midcom_show_style('midgard_admin_asgard_trash_type');
        midcom_show_style('midgard_admin_asgard_footer');
    }
}
?>