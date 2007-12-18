<?php
/**
 * @package midgard.admin.asgard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: parameters.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Undelete/purge interface
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
    
    /**
     * Recover the parameters related to a deleted object
     * 
     * @access private
     */
    function _undelete_parameters($guid)
    {
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $params = $qb->execute();
        foreach ($params as $param)
        {
            $undeleted = false;
            if (version_compare(phpversion(), '5.0.0', '<'))
            {
                if (call_user_func(array('midgard_parameter', 'undelete'),$param->guid))
                {
                    $undeleted = true;
                }
            }
            else
            {
                if ($param->undelete())
                {
                    $undeleted = true;
                }
            }
            if ($undeleted)
            {
                $this->_undeleted_size += $param->metadata->size;
            }
        }
    }
    
    /**
     * Recover the attachments related to a deleted object
     * 
     * @access private
     */
    function _undelete_attachments($guid)
    {
        $qb = new midgard_query_builder('midgard_attachment');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $atts = $qb->execute();
        foreach ($atts as $att)
        {
            $undeleted = false;
            if (version_compare(phpversion(), '5.0.0', '<'))
            {
                if (call_user_func(array('midgard_attachment', 'undelete'),$att->guid))
                {
                    $undeleted = true;
                }
            }
            else
            {
                if ($att->undelete())
                {
                    $undeleted = true;
                }
            }
            if (!$undeleted)
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

    /**
     * Purge the parameters related to a deleted object
     * 
     * @access private
     */
    function _purge_parameters($guid)
    {
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $params = $qb->execute();
        foreach ($params as $param)
        {
            if (!$param->metadata->deleted)
            {
                $param->delete();
            }
            
            if ($param->purge())
            {
                $this->_purged_size += $param->metadata->size;
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed purging attachment %s => %s, reason %s'), $param->domain,$param->name, mgd_errstr()), 'error');
            }
        }
    }
    
    /**
     * Purge the attachments related to a deleted object
     * 
     * @access private
     */
    function _purge_attachments($guid)
    {
        $qb = new midgard_query_builder('midgard_attachment');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $atts = $qb->execute();
        foreach ($atts as $att)
        {
            if (!$att->metadata->deleted)
            {
                $att->delete();
            }

            if ($att->purge())
            {
                $this->_purged_size += $att->metadata->size;
                $this->_purge_parameters($att->guid);
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed purging attachment %s, reason %s'), $att->name, mgd_errstr()), 'error');
            }
        }
    }
    
    /**
     * Helper method for undeleting objects
     * 
     * @access private
     * @param Array $guids
     * @param string $type
     */
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
            
            $undeleted = false;
            if (version_compare(phpversion(), '5.0.0', '<'))
            {
                if (call_user_func(array($type, 'undelete'), $guid))
                {
                    $undeleted = true;
                }
            }
            else
            {
                if ($object->undelete($guid))
                {
                    $undeleted = true;
                }
            }
                        
            if (!$undeleted)
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
     * Helper method for purging objects
     * 
     * @access private
     * @param Array $guids
     * @param string $type
     */
    function _purge($guids, $type)
    {
        $ref = midgard_admin_asgard_reflector_tree::get($type);
        foreach ($guids as $guid)
        {
            $object = $this->_get_object($guid, $type);
            if (is_null($object))
            {
                // Something wrong
                continue;
            }

            // first kill your children
            $children_types = $ref->get_child_objects($object, true);
            
            foreach ($children_types as $type => $children)
            {
                $child_guids = array();
                foreach ($children as $child)
                {
                    if (!$child->metadata->deleted)
                    {
                        $child->delete();
                    }
                }
                $this->_purge($child_guids, $type);
            }

            // then shoot your dogs

            $this->_purge_parameters($guid);
            $this->_purge_attachments($guid);

            $label = $ref->get_label_property();

            // now shoot yourself
            
            if (!$object->purge())
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed purging %s, reason %s'), "{$type} {$object->$label}", mgd_errstr()), 'error');
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('%s purged'), "{$type} {$object->$label}", mgd_errstr()), 'ok');
                $this->_purged_size += $object->metadata->size;
            }

        }
    }

    /**
     * Trash view
     */
    function _handler_trash($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
        $_MIDCOM->cache->content->no_cache();
    
        $data['view_title'] = $this->_l10n->get('trash');
        $_MIDCOM->set_pagetitle($data['view_title']);
        
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        midgard_admin_asgard_plugin::get_common_toolbar($data);

        $data['types'] = array();
        foreach ($_MIDGARD['schema']['types'] as $type => $int)
        {
            $qb = new midgard_query_builder($type);
            $qb->include_deleted();
            $qb->add_constraint('metadata.deleted', '=', true);
            $data['types'][$type] = $qb->count();
        }

        // Set the breadcrumb data
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__mfa/asgard/',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('midgard.admin.asgard', 'midgard.admin.asgard'),
        );
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__mfa/asgard/trash/',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('trash', 'midgard.admin.asgard'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded object in editor.
     */
    function _show_trash($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        midcom_show_style('midgard_admin_asgard_trash');
        midcom_show_style('midgard_admin_asgard_footer');
    }
  
    /**
     * Trash view
     */
    function _handler_trash_type($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
        $_MIDCOM->cache->content->no_cache();
    
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
        
        if (   isset($_POST['undelete']) && !isset($_POST['purge'])
            && is_array($_POST['undelete']))
        {

            $this->_undelete($_POST['undelete'], $this->type);
            
            if ($this->_undeleted_size > 0)
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('in total %s undeleted'), midcom_helper_filesize_to_string($this->_undeleted_size)), 'info');
            }
            $_MIDCOM->relocate("__mfa/asgard/trash/{$this->type}/");
        }

        if (   isset($_POST['purge'])
            && is_array($_POST['undelete']))
        {
            $this->_purge($_POST['undelete'], $this->type);
            
            if ($this->_purged_size > 0)
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('in total %s purged'), midcom_helper_filesize_to_string($this->_purged_size)), 'info');
            }
            $_MIDCOM->relocate("__mfa/asgard/trash/{$this->type}/");
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