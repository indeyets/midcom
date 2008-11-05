<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class only contains static functions which are there to hook into
 * the classes you derive from the MidgardSchema DB types like (New)MidgardArticle.
 * You should never have to fiddle with this class, the code generator (TODO)
 * shipping with MidCOM will do that.
 *
 * The static members will invoke a number of callback methods so that you should
 * normally never have to override the base midgard methods like update or the like.
 *
 * <b>Implementation Notes:</b>
 *
 * It is important to understand that all these functions here are <i>merged</i> into
 * the actual auto-generated classes of the DBA layer. Due to this reason, the first
 * argument of all the functions is a reference to $object, which acts as replacement
 * for the $this reference you have available normally. The exposed API in the DBA
 * classes is therefore always without that parameter.
 *
 * This class breaks the database layer a bit right now as it bypasses regular MidCOM
 * Rules when working with parameters. They are set/get directly without being passed
 * through the MidCOM layer. This will change again when MgdSchema starts to support
 * Parameters natively.
 *
 * @package midcom.baseclasses
 */
class midcom_baseclasses_core_dbobject extends midcom_baseclasses_core_object
{
    static $parameter_cache = array();
    /**
     * "Pre-flight" checks for update method
     *
     * Separated so that dbfactory->import() can reuse the code
     **/
    function update_pre_checks(&$object)
    {
        debug_push_class($object, __FUNCTION__);
        if (! $_MIDCOM->auth->can_do('midgard:update', $object))
        {
            debug_add("Failed to load object, update privilege on the " . get_class($object) . " {$object->id} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        if (! $object->_on_updating())
        {
            debug_add("The _on_updating event handler returned false.");
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }

    /**
     * Execute a DB update of the object passed. This will call the corresponding
     * event handlers. Calling sequence with method signatures:
     *
     * 1. Validate privileges using can_do. The user needs midgard:update privilege on the content object.
     * 2. bool $object->_on_updating() is executed. If it returns false, update is aborted.
     * 3. bool $object->__exec_update() is executed to do the actual DB update. This has to exectue parent::update()
     *    and return its value, nothing else.
     * 4. void $object->_on_updated() is executed to notify the class from a successful DB update.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating success.
     */
    function update(&$object)
    {
        if (!midcom_baseclasses_core_dbobject::update_pre_checks($object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Pre-flight check returned false', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Now, if possible, set revised and revisor.
        midcom_baseclasses_core_dbobject::_set_revisor($object);

        if (! $object->__exec_update())
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to update the record, last Midgard error: " . mgd_errstr());
            debug_pop();
            return false;
        }

        midcom_baseclasses_core_dbobject::update_post_ops($object);

        return true;
    }

    /**
     * Post object creation operations for create
     *
     * Separated so that dbfactory->import() can reuse the code
     **/
    function update_post_ops(&$object)
    {
        debug_push_class($object, __FUNCTION__);

        if ($object->_use_rcs) 
        {
            $rcs =& $_MIDCOM->get_service('rcs');
            $rcs->update($object, $object->get_rcs_message());
        }

        $object->_on_updated();

        $_MIDCOM->cache->invalidate($object->guid);

        if ($GLOBALS['midcom_config']['attachment_cache_enabled'])
        {
            $atts = $object->list_attachments();
            foreach ($atts as $att)
            {
                $_MIDCOM->cache->invalidate($att->guid);
                $att->update_cache();
            }
        }

        // Invalidate Midgard pagecache if we touched style/page element
        if (   function_exists('mgd_cache_invalidate')
            && (   is_a($object, 'midcom_baseclasses_database_element')
                || is_a($object, 'midcom_baseclasses_database_pageelement'))
            )
        {
            debug_add('invalidating Midgard page cache');
            mgd_cache_invalidate();
        }

        $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_UPDATE, $object);       

        debug_pop();
    }


    /**
     * This is an internal helper which updates the revised/revisor timestamp on a
     * record if the corresponding fields exists.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @access private
     */
    function _set_revisor(&$object)
    {
        if (array_key_exists('revisor', $object))
        {
            if (is_null($_MIDCOM->auth->user))
            {
                $object->revisor = 0;
            }
            else
            {
                // Bypass MidCOM ACL here, at least for now.
                $object->revisor = $_MIDCOM->auth->user->_storage->id;
            }
        }
        if (array_key_exists('revised', $object))
        {
            $object->revised = gmstrftime('%Y-%m-%d %T');
        }
    }

    /**
     * This is an internal helper which updates the created and metadata.published timestamps 
     * and the creator and metadata.authors links on a record if the corresponding fields exists.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @access private
     */
    function _set_creator(&$object)
    {
        if (array_key_exists('creator', $object))
        {
            // Bypass MidCOM ACL here, at least for now.
            if (is_null($_MIDCOM->auth->user))
            {        
                $object->creator = 0;
            }
            else
            {
                $object->creator = $_MIDCOM->auth->user->_storage->id;
            }
        }

        if (array_key_exists('created', $object))
        {
            $object->created = time();
        }

        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('$object->metadata before magick', $object->metadata);
        debug_pop();
        */

        if (!is_null($_MIDCOM->auth->user))
        {
            // Default the authors to current user
            if (empty($object->metadata->authors))
            {
                $creator = $_MIDCOM->auth->user->_storage->id;
                $object->metadata->authors = "|{$_MIDCOM->auth->user->_storage->guid}|";
            }
        
            // Default the owner to first group of current user
            if (empty($object->metadata->owner))
            {
                $groups = $_MIDCOM->auth->user->list_all_memberships();
                if (count($groups) > 0)
                {
                    $first_group = array_shift($groups);
                    $object->metadata->owner = str_replace('group:', '', $first_group->id);
                }
            }      
        }

        // Default the publication time to current date/time
        if (empty($object->metadata->published))
        {
            $object->metadata->published = time();
        }
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('$object->metadata after magick', $object->metadata);
        debug_pop();
        */
    }

    /**
     * This is an internal helper adds full privileges to the owner of the object.
     * This is essentially sets teh midgard:owner privilege for the current user.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @access private
     */
    function _set_owner_privileges(&$object)
    {
        if (! $_MIDCOM->auth->user)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add ("Could not retrieve the midcom_core_user instance for the creator of " . get_class($object) . " {$object->guid}, skipping owner privilege assignment.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return;
        }

        // Circumvent the main privilege class as we need full access here regardeless of
        // the actual circumstances.
        $privilege = new midcom_core_privilege_db();
        $privilege->assignee = $_MIDCOM->auth->user->id;
        $privilege->name = 'midgard:owner';
        $privilege->objectguid = $object->guid;
        $privilege->value = MIDCOM_PRIVILEGE_ALLOW;

        if (! $privilege->create())
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Could not set the owner privilege {$name}, see debug level log for details. Last Midgard Error: " . mgd_errstr(),
                MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }
    }

    /**
     * "Pre-flight" checks for create method
     *
     * Separated so that dbfactory->import() can reuse the code
     **/
    function create_pre_checks(&$object)
    {
        debug_push_class($object, __FUNCTION__);

        if (   $_MIDGARD['sitegroup']
            && $object->sitegroup !== $_MIDGARD['sitegroup']
            && !$_MIDGARD['admin'])
        {
            // Workaround http://trac.midgard-project.org/ticket/275
            $object->sitegroup = $_MIDGARD['sitegroup'];
        }

        $parent = $object->get_parent();
        if (! is_null($parent))
        {
            if (   ! $_MIDCOM->auth->can_do('midgard:create', $parent)
                && ! $_MIDCOM->auth->can_user_do('midgard:create', null, get_class($object)))
            {
                debug_add("Failed to create object, create privilege on the parent " . get_class($parent) . " {$parent->guid} or the actual object class not granted for the current user.",
                    MIDCOM_LOG_ERROR);
                mgd_set_errno(MGD_ERR_ACCESS_DENIED);
                return false;
            }
        }
        else
        {
            if (! $_MIDCOM->auth->can_user_do('midgard:create', null, get_class($object)))
            {
                debug_add("Failed to create object, general create privilege not granted for the current user.", MIDCOM_LOG_ERROR);
                mgd_set_errno(MGD_ERR_ACCESS_DENIED);
                return false;
            }
        }

        if (! $object->_on_creating())
        {
            debug_add("The _on_creating event handler returned false.");
            debug_pop();
            return false;
        }
        
        debug_pop();
        return true;
    }

    /**
     * Execute a DB create of the object passed. This will call the corresponding
     * event handlers. Calling sequence with method signatures:
     *
     * 1. Validate privileges using can_do. The user needs midgard:create privilege to the parent object or in general, if there is no parent.
     * 2. bool $object->_on_creating() is executed. If it returns false, create is aborted.
     * 3. bool $object->__exec_create() is executed to do the actual DB create. This has to exectue parent::create()
     *    and return its value, nothing else.
     * 4. void $object->_on_created() is executed to notify the class from a successful DB creation.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating success.
     */
    function create(&$object)
    {
        if (!midcom_baseclasses_core_dbobject::create_pre_checks($object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Pre-flight check returned false', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        // Legacy Midgard Metadata emulation
        // Now, if possible, set created and creator.
        midcom_baseclasses_core_dbobject::_set_creator($object);
        midcom_baseclasses_core_dbobject::_set_revisor($object);

        if (! $object->__exec_create() && $object->id == 0)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to create the record, last Midgard error: " . mgd_errstr());
            debug_pop();
            return false;
        }

        midcom_baseclasses_core_dbobject::create_post_ops($object);

        return true;
    }

    /**
     * Post object creation operations for create
     *
     * Separated so that dbfactory->import() can reuse the code
     **/
    function create_post_ops(&$object)
    {
        // WORKAROUND START
        // Auto-populate the GUID as the core sometimes forgets this (#72) or
        // reports the wrong guid in case we have another case of duplicate guids (#118) in the
        // repligard table (can happen before 1.7.3, with 1.7.3 midgard fails to create
        // records, which could be even worse and is not covered here):
        if (! $object->guid)
        {
            $tmp = new $object->__mgdschema_class_name__();
            if (! $tmp->get_by_id($object->id))
            {
                debug_push_class($object, __FUNCTION__);
                debug_add('Workaround for #72/#118, failed to get_by_id to load the GUID, this is bad. We abort.',
                    MIDCOM_LOG_CRIT);
                debug_add("We tried to load object {$object->id} and got: " . mgd_errstr());
                $object->delete();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Workaround for #72/#118: Failed to load GUID for ID {$object->id} ('
                    . mgd_errstr()
                    . '). Object could not be created.');
                // This will exit.
            }
            $object->guid = $tmp->guid;
        }
        // END WORKAROUND

        // Now assign all midgard privileges to the creator, this is necessary to get
        // an owner like scheme to work by default.
        // TODO: Check if there is a better solution like this.
        midcom_baseclasses_core_dbobject::_set_owner_privileges($object);

        $object->_on_created();
        $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_CREATE, $object);
        if ($object->_use_rcs) 
        {
            $rcs =& $_MIDCOM->get_service('rcs');
            $rcs->update($object, $object->get_rcs_message());
        }
        
        $parent = $object->get_parent();
        if (   $parent
            && $parent->guid)
        {
            // Invalidate parent from cache so content caches have chance to react
            $_MIDCOM->cache->invalidate($parent->guid);
        }
        
        // Invalidate Midgard pagecache if we touched style/page element
        if (   function_exists('mgd_cache_invalidate')
            && (   is_a($object, 'midcom_baseclasses_database_element')
                || is_a($object, 'midcom_baseclasses_database_pageelement'))
            )
        {
            mgd_cache_invalidate();
        }
    }

    /**
     * "Pre-flight" checks for delete method
     *
     * Separated so that dbfactory->import() can reuse the code
     **/
    function delete_pre_checks(&$object)
    {
        $midgard_language = $_MIDCOM->i18n->get_midgard_language();
        
        // Current language is not zero, selective delete to prevent deleting too much of objects.
        // Object will not be deleted if it doesn't have a language property at all or if its
        // language property is not the one requested for deletion
        if (   $midgard_language !== 0
            && (   !isset($object->lang)
                || $object->lang !== $midgard_language))
        {
            return false;
        }
        
        if (! $_MIDCOM->auth->can_do('midgard:delete', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to delete object, delete privilege on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            // debug_print_r('Object was:', $object);
            debug_pop();
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        if (! $object->_on_deleting())
        {
            return false;
        }

        return true;
    }

    /**
     * Execute a DB delete of the object passed. This will call the corresponding
     * event handlers. Calling sequence with method signatures:
     *
     * 1. Validate privileges using can_do. The user needs midgard:delete privilege on the content object.
     * 2. bool $object->_on_deleting() is executed. If it returns false, delete is aborted.
     * 3. All extensions of the object are deleted
     * 4. bool $object->__exec_delete() is executed to do the actual DB delete. This has to exectue parent::delete()
     *    and return its value, nothing else.
     * 5. void $object->_on_deleted() is executed to notify the class from a successful DB deletion.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating success.
     */
    function delete(&$object)
    {
        if (!midcom_baseclasses_core_dbobject::delete_pre_checks($object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Pre-flight check returned false', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $delete_extensions = true;
        if (   isset($object->lang)
            && $object->lang != 0)
        {
            // Don't delete parameters and attachments if the object is ML and not in lang0
            $delete_extensions = false;
        }
        
        if ($delete_extensions)
        {
            // Delete all extensions:
            // Attachments can't have attachments so no need to query those
            if (!is_a($object, 'midcom_baseclasses_database_attachment'))
            {
                $list = $object->list_attachments();
                foreach ($list as $attachment)
                {
                    if (!$attachment->delete())
                    {
                        debug_push_class($object, __FUNCTION__);
                        debug_add("Failed to delete attachment ID {$attachment->id}", MIDCOM_LOG_ERROR);
                        // debug_print_r('Full record:', $attachment);
                        debug_pop();
                        return false;
                    }
                }
            }

            $query = new midgard_query_builder('midgard_parameter');
            $query->add_constraint('parentguid', '=', $object->guid);
            $result = @$query->execute();
            if ($result)
            {
                foreach ($result as $parameter)
                {
                    if (! $parameter->delete())
                    {
                        debug_push_class($object, __FUNCTION__);
                        debug_add("Failed to delete parameter ID {$parameter->id}", MIDCOM_LOG_ERROR);
                        debug_pop();
                        return false;
                    }
                }
            }

            if (! midcom_baseclasses_core_dbobject::_delete_privileges($object))
            {
                debug_push_class($object, __FUNCTION__);
                debug_add('Failed to delete the object privileges.', MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }

        // Finally, delete the object itself
        if (! $object->__exec_delete())
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to create the record, last Midgard error: " . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        // Explicitly set this in case someone needs to check against it 
        midcom_baseclasses_core_dbobject::delete_post_ops($object);

        return true;
    }

    /**
     * Execute a DB delete of the object passed and delete its descendants. This will call the corresponding
     * event handlers. Calling sequence with method signatures:
     *
     * 1. Get all of the child objects
     * 2. Delete them recursively starting from the top, working towards the root
     * 3. Finally delete the root object
     *
     * @param MidgardObject &$object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return boolean Indicating success.
     */
    function delete_tree(&$object)
    {
        static $lang_restrict = null;
        
        if (is_null($lang_restrict))
        {
            $lang_restrict = $_MIDCOM->i18n->get_midgard_language();
        }
        
        if (!class_exists('midcom_helper_reflector_tree'))
        {
            $_MIDCOM->componentloader->load('midcom.helper.reflector');
        }
        
        // Get the child nodes
        $children = midcom_helper_reflector_tree::get_child_objects($object);
        
        // Children found
        if (   $children
            && count($children))
        {
            // Delete first the descendants
            foreach ($children as $type => $array)
            {
                foreach ($array as $child)
                {
                    if (!$child->delete_tree())
                    {
                        debug_push_class(__CLASS__, __FUNCTION__);
                        debug_print_r('Failed to delete the children of this object:', $object, MIDCOM_LOG_NOTICE);
                        debug_pop();
                    }
                }
            }
        }
        
        // Current language is not zero, selective delete to prevent deleting too much of objects.
        // Object will not be deleted if it doesn't have a language property at all or if its
        // language property is not the one requested for deletion
        if (   $lang_restrict !== 0
            && (   !isset($object->lang)
                || $object->lang !== $lang_restrict))
        {
            return true;
        }
        
        if (!$object->delete())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Failed to delete the object', $object, MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        return true;
    }

    /**
     * Post object creation operations for delete
     *
     * Separated so that dbfactory->import() can reuse the code
     **/
    function delete_post_ops(&$object)
    {
        $object->_on_deleted();
        $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_DELETE, $object);
        if ($object->_use_rcs) 
        {
            $rcs =& $_MIDCOM->get_service('rcs');
            $rcs->update($object, $object->get_rcs_message());
        }

        $_MIDCOM->cache->invalidate($object->guid);

        // Invalidate Midgard pagecache if we touched style/page element
        if (   function_exists('mgd_cache_invalidate')
            && (   is_a($object, 'midcom_baseclasses_database_element')
                || is_a($object, 'midcom_baseclasses_database_pageelement'))
            )
        {
            mgd_cache_invalidate();
        }
    }
    
    /**
     * Helper method for undeleting objects
     *
     * @static
     * @access public
     * @param Array $guids
     * @param string $type
     * @return boolean Indicating success
     * @todo We should only undelete parameters & attachments deleted inside some small window of the main objects delete
     */
    function undelete($guids, $type)
    {
        $undeleted_size = 0;
        
        $ref = midcom_helper_reflector_tree::get($type);
        
        if (!is_array($guids))
        {
            $guids = array
            (
                $guids,
            );
        }

        $stats = array();
        foreach ($guids as $guid)
        {
            $object = midcom_helper_reflector::get_object($guid, $type);
            if (is_null($object))
            {
                // Purged, skip
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Object identified with GUID {$guid} is purged, cannot undelete", MIDCOM_LOG_INFO);
                debug_pop();
                continue;
            }
            $label = $ref->get_label_property();

            $undeleted = false;
            if ($object->undelete($guid))
            {
                $undeleted = true;
                // refresh
                $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
                $undeleted_size += $object->metadata->size;
            }

            if (!$undeleted)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to undelete object with GUID {$guid} errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
            }
            else
            {
                $parent = $object->get_parent();
                if (   $parent
                    && $parent->guid)
                {
                    // Invalidate parent from cache so content caches have chance to react
                    $_MIDCOM->cache->invalidate($parent->guid);
                }
                
                // Invalidate Midgard pagecache if we touched style/page element
                if (   function_exists('mgd_cache_invalidate')
                    && (   is_a($object, 'midcom_baseclasses_database_element')
                        || is_a($object, 'midcom_baseclasses_database_pageelement'))
                    )
                {
                    mgd_cache_invalidate();
                }
            }
            $stats[$guid] = $undeleted;

            // FIXME: We should only undelete parameters & attachments deleted inside some small window of the main objects delete
            $undeleted_size += midcom_baseclasses_core_dbobject::undelete_parameters($guid);
            $undeleted_size += midcom_baseclasses_core_dbobject::undelete_attachments($guid);

            //FIXME: are we sure we want to undelete all children here unconditionally, shouldn't it be left as UI decision ??
            // List all deleted children
            $children_types = $ref->get_child_objects($object, true);
            
            if (empty($children_types))
            {
                continue;
            }

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
                $undeleted_size += midcom_baseclasses_core_dbobject::undelete($child_guids, $type);
            }
        }

        return $undeleted_size;
    }

    /**
     * Recover the parameters related to a deleted object
     *
     * @static
     * @access public
     * @param string $guid
     * @return boolean Indicating success
     * @todo We should only undelete parameters & attachments deleted inside some small window of the main objects delete
     */
    function undelete_parameters($guid)
    {
        $undeleted_size = 0;
        
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $params = $qb->execute();
        foreach ($params as $param)
        {
            $undeleted = false;
            if ($param->undelete($param->guid))
            {
                $undeleted = true;
            }
            if ($undeleted)
            {
                $undeleted_size += $param->metadata->size;
            }
        }

        return $undeleted_size;
    }

    /**
     * Recover the attachments related to a deleted object
     *
     * @static
     * @access public
     * @param string $guid
     * @return boolean Indicating success
     * @todo We should only undelete parameters & attachments deleted inside some small window of the main objects delete
     */
    function undelete_attachments($guid)
    {
        $undeleted_size = 0;
        
        $qb = new midgard_query_builder('midgard_attachment');
        $qb->include_deleted();
        $qb->add_constraint('parentguid', '=', $guid);
        $qb->add_constraint('metadata.deleted', '=', true);
        $atts = $qb->execute();
        foreach ($atts as $att)
        {
            $undeleted = false;
            if ($att->undelete($att->guid))
            {
                $undeleted = true;
            }
            if (!$undeleted)
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed undeleting attachment %s, reason %s'), $att->name, mgd_errstr()), 'error');
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('attachment %s undeleted'), $att->name, mgd_errstr()), 'ok');
                $undeleted_size += $att->metadata->size;
                $undeleted_size += midcom_baseclasses_core_dbobject::undelete_parameters($att->guid);
            }
        }

        return $undeleted_size;
    }

    /**
     * Helper method for purging objects
     *
     * @static
     * @access public
     * @param Array $guids
     * @param string $type
     * @return boolean Indicating success
     */
    function purge($guids, $type)
    {
        $purged_size = 0;
        
        $ref = midcom_helper_reflector_tree::get($type);
        foreach ($guids as $guid)
        {
            $object = midcom_helper_reflector::get_object($guid, $type);
            if (is_null($object))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to get object {$type} {$guid}", MIDCOM_LOG_ERROR);
                debug_pop();
                // Something wrong
                continue;
            }

            // first kill your children
            $children_types = $ref->get_child_objects($object, true);
            
            if (is_array($children_types))
            {
                foreach ($children_types as $type => $children)
                {
                    $child_guids = array();
                    foreach ($children as $child)
                    {
                        if (!$child->metadata->deleted)
                        {
                            $child->delete();
                        }
                        $child_guids[] = $child->guid;
                    }
                    midcom_baseclasses_core_dbobject::purge($child_guids, $type);
                }
            }

            // then shoot your dogs

            $purged_size += midcom_baseclasses_core_dbobject::purge_parameters($guid);
            $purged_size += midcom_baseclasses_core_dbobject::purge_attachments($guid);

            $label = $ref->get_label_property();

            // now shoot yourself

            if (!$object->purge())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to purge object " . get_class($object) . " {$object->guid}", MIDCOM_LOG_INFO);
                debug_pop();
            }
            else
            {
                $purged_size += $object->metadata->size;
            }
        }
        
        return $purged_size;
    }

    /**
     * Purge the parameters related to a deleted object
     *
     * @static
     * @access public
     * @param string $guid
     * @return boolean Indicating success
     */
    function purge_parameters($guid)
    {
        $purged_size = 0;
        
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
                $purged_size += $param->metadata->size;
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed purging attachment %s => %s, reason %s'), $param->domain,$param->name, mgd_errstr()), 'error');
            }
        }
        
        return $purged_size;
    }

    /**
     * Purge the attachments related to a deleted object
     *
     * @static
     * @access public
     * @param string $guid
     * @return boolean Indicating success
     */
    function purge_attachments($guid)
    {
        $purged_size = 0;
        
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
                $purged_size += $att->metadata->size;
                midcom_baseclasses_core_dbobject::purge_parameters($att->guid);
            }
            else
            {
                $_MIDCOM->uimessages->add($this->_l10n->get('midgard.admin.asgard'), sprintf($this->_l10n->get('failed purging attachment %s, reason %s'), $att->name, mgd_errstr()), 'error');
            }
        }

        return $purged_size;
    }

    /**
     * Copies values from oldobject to newobject in case the types are compatible
     *
     * @param MidgardObject $newobject A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param MidgardObject $oldobject a parent objcet (usually a midgard_* base class) which to copy.
     * @return bool Indicating success.
     */
    function cast_object(&$newobject, &$oldobject)
    {
        if (is_a($oldobject, $newobject->__mgdschema_class_name__))
        {
            $vars = get_object_vars($oldobject);
            foreach ($vars as $name => $value)
            {
                if (   $name == '__res'
                    || (  substr($name, 0, 2) == '__'
                        && substr($name, -2) == '__'))
                {
                    // This is a special variable, we must not overwrite them.
                    continue;
                }
                $newobject->$name = $value;
            }
            return true;
        }
        else
        {
            debug_push_class($newobject, __FUNCTION__);
            debug_add('Failed to cast ' . get_class($oldobject) . " to a {$newobject->__mgdschema_class_name__}: Incompatible Types", MIDCOM_LOG_INFO);
            midcom_baseclasses_core_dbobject::_clear_object($newobject);
            debug_pop();
            return false;
        }
    }

    /**
     * Loads the object identified by the ID from the database. After a successful
     * load by either get_by_id or get_by_guid methods or the copy constructor run,
     * the _on_loaded event handler is called. Thus, you have this calling sequence:
     *
     * 1. void $object->get_by_id($id) <i>or</i> $object->get_by_guid($guid) <i>or</i> copy constructor run.
     * 2. Validate privileges using can_do. The user needs midgard:read privilege on the content object.
     * 3. bool $object->_on_loaded() is executed to notify the class from a successful operation, which might
     *    abort the class construction again by returning false.
     *
     * This method is usually only called from the constructor of the class.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param mixed $id The object to load from the database. This can be either null (the default), indicating an empty object,
     *     a Midgard database row-ID or a Midgard GUID, the latter is detected using mgd_is_guid(). In addition, you can
     *     specifiy a parent objcet (usually a midgard_* base class) which will then use a copy constructor semantics instead.
     * @return bool Indicating success.
     * @see midcom_baseclasses_core_dbobject::post_db_load_checks
     * @see midcom_baseclasses_core_dbobject::cast_object
     */
    function load(&$object, $id)
    {
        $object->id = 0;

        if (is_object($id))
        {
            if (!midcom_baseclasses_core_dbobject::cast_object($object, $id))
            {
                return false;
            }
        }
        else if (mgd_is_guid($id))
        {
            if (! $object->__exec_get_by_guid($id))
            {
                debug_push_class($object, __FUNCTION__);
                debug_add("Failed to load the record identified by {$id}: " . mgd_errstr(), MIDCOM_LOG_INFO);
                midcom_baseclasses_core_dbobject::_clear_object($object);
                debug_pop();
                return false;
            }
        }
        else if (is_numeric($id))
        {
            // Be sure we have an integer here, there are some problems when strings
            // are passed.
            if (! $object->__exec_get_by_id((int) $id))
            {
                debug_push_class($object, __FUNCTION__);
                debug_add("Failed to load the record identified by {$id}: " . mgd_errstr(), MIDCOM_LOG_INFO);
                midcom_baseclasses_core_dbobject::_clear_object($object);
                debug_pop();
                return false;
            }
        }
        else
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to load object, argument {$id} is not valid..",
                MIDCOM_LOG_INFO);
            midcom_baseclasses_core_dbobject::_clear_object($object);
            debug_pop();
            return false;
        }

        // This is a fallback implementation for earlier Versions of MidgardSchema, which
        // did not report loader errors during get_by_(gu)id appropriately.
        if (   $object->id == 0
            || $object->action == 'delete')
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to load the record identified by {$id}: " . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_add('Midgard Version is too old, you should upgrade to latest 1.7 at least. get_by_id/guid is broken in this version.',
                MIDCOM_LOG_CRIT);
            midcom_baseclasses_core_dbobject::_clear_object($object);
            debug_pop();
            return false;
        }
        
        return midcom_baseclasses_core_dbobject::post_db_load_checks($object);
    }

    /**
     * After we instantiated the midgard object do some post processing and ACL checks
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating success.
     * @see midcom_baseclasses_core_dbobject::load
     */
    function post_db_load_checks(&$object)
    {
        // TODO: Do this with midgard_object_class::is_multilang($object) when it works
        if (   $GLOBALS['midcom_config']['i18n_multilang_strict']
            && isset($object->lang)
            && !is_a($object, 'midcom_baseclasses_database_parameter')
            && !is_a($object, 'midcom_baseclasses_database_attachment')
            && $object->lang != $_MIDCOM->i18n->get_midgard_language())
        {
            // TODO: Some other error code might be nicer here
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to load object, language {$object->lang} does not match Midgard's global language setting.",
                MIDCOM_LOG_INFO);
            midcom_baseclasses_core_dbobject::_clear_object($object);
            debug_pop();
            return false;
        }

        if (! $_MIDCOM->auth->can_do('midgard:read', $object))
        {
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);

            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to load object, read privilege on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_INFO);
            midcom_baseclasses_core_dbobject::_clear_object($object);
            debug_pop();
            return false;
        }

        $result = $object->_on_loaded();
        if (! $result)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("The _on_loaded event handler returned false for " . get_class($object) . " {$object->guid}.", MIDCOM_LOG_INFO);
            debug_pop();
            midcom_baseclasses_core_dbobject::_clear_object($object);
        }
        
        // Register the GUID as loaded in this request
        if (isset($_MIDCOM->cache->content))
        {
            $_MIDCOM->cache->content->register($object->guid);
        }
        
        return $result;
    }
    
    /**
     * Generates URL-safe name for an object and stores it if needed
     *
     * NOTE: Calling this in _on_updated has possibility on introducing infinite loops
     * since this will call update if deemed necessary, which calls _on_updated, etc ad nauseam
     *
     * @param MidgardObject &$object A class inherited from one of the MgdSchema driven Midgard classes
     * @return bool indicating success/failure
     */
    function generate_urlname(&$object, $titlefield = 'title')
    {
        if (!isset($object->name))
        {
            return false;
        }
        
        if (   !isset($object->$titlefield)
            || empty($object->$titlefield))
        {
            return false;
        }

        if (   isset($object->lang)
            && !empty($object->name))
        {
            /**
             * ML Object with non-empty name
             *
             * Check that we're doing anything stupid like overwriting name 
             * derived in "lang0" with one derived from localized title
             */
            // FIXME: use midgard_connection::get_default_lang() with 1.9/2.0
            $fallback_language = mgd_get_default_lang();
            if ($object->lang != $fallback_language)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Not regenerating name for {$object->guid}, current version is not the fallback-language one", MIDCOM_LOG_INFO);
                debug_add("\$object->lang={$object->lang} != \$fallback_language={$fallback_language}");
                debug_pop();
                // This is localized version of the object, do not overwrite name silently abort instead
                return true;
            }
        }

        $name = midcom_generate_urlname_from_string($object->$titlefield);
        // Strip the incrementing count suffix from name for checking
        $object_name_wosuffix = preg_replace('/-[0-9]{3}$/', '', $object->name);
        if (   $object_name_wosuffix == $name
            || (   !empty($object_name_wosuffix)
                && $object_name_wosuffix == midcom_generate_urlname_from_string($object->name)))
        {
            // We're happy with the existing URL name
            return true;
        }
        
        $object->name = $name;
        if ($object->update())
        {
            return true;
        }
        if (mgd_errno() !== MGD_ERR_OBJECT_NAME_EXISTS)
        {
            // The error is not duplicate name, don't bother retrying
            return false;
        }
        $tries = 0;
        $maxtries = 999;
        while($tries < $maxtries)
        {
            // Append an integer if articles with same name exist
            $object->name = $name . sprintf("-%03d", $tries);
            $tries++;
            // TODO: Use reflection and core collector to do saner checking
            if ($object->update())
            {
                return true;
            }
            if (mgd_errno() !== MGD_ERR_OBJECT_NAME_EXISTS)
            {
                // The error is not duplicate name, don't bother retrying any more
                return false;
            }
        }
        // we fell through, this should only happen if we run out of maxtries space.
        return false;
    }

    /**
     * This is a simple wrapper with (currently) no additional functionality
     * over get_by_id that resynchronizes the object state with the database.
     * Use this if you think that your current object is stale. It does full
     * access control.
     *
     * On any failure, the object is cleared.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating Success
     */
    function refresh(&$object)
    {
        // FIXME: be GUID based, but needs further testing...
        //return $object->get_by_guid($object->guid);
        return $object->get_by_id($object->id);
    }

    /**
     * This call wraps the original get_by_id call to provide access control.
     * The calling sequence is as with the corresponding constructor.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param int $id The id of the object to load from the database.
     * @return bool Indicating Success
     */
    function get_by_id(&$object, $id)
    {
        $object->__exec_get_by_id((int) $id);

        if (   $object->id != 0
            && $object->action != 'delete')
        {
            if (! $_MIDCOM->auth->can_do('midgard:read', $object))
            {
                debug_push_class($object, __FUNCTION__);
                debug_add("Failed to load object, read privilege on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                    MIDCOM_LOG_ERROR);
                midcom_baseclasses_core_dbobject::_clear_object($object);
                debug_pop();
                return false;
            }

            $result = $object->_on_loaded();
            if (! $result)
            {
                midcom_baseclasses_core_dbobject::_clear_object($object);
            }
            return $result;
        }
        else
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to load the record identified by {$id}, last Midgard error was:" . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
    }

    /**
     * This call wraps the original get_by_guid call to provide access control.
     * The calling sequence is as with the corresponding constructor.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $guid The guid of the object to load from the database.
     * @return bool Indicating Success
     */
    function get_by_guid(&$object, $guid)
    {
        $object->__exec_get_by_guid((string) $guid);

        if (   $object->id != 0
            && $object->action != 'delete')
        {
            if (! $_MIDCOM->auth->can_do('midgard:read', $object))
            {
                debug_push_class($object, __FUNCTION__);
                debug_add("Failed to load object, read privilege on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                    MIDCOM_LOG_ERROR);
                midcom_baseclasses_core_dbobject::_clear_object($object);
                debug_pop();
                return false;
            }

            $result = $object->_on_loaded();
            if (! $result)
            {
                midcom_baseclasses_core_dbobject::_clear_object($object);
            }
            return $result;
        }
        else
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to load the record identified by {$id}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
    }

    /**
     * This call wraps the original get_by_guid call to provide access control.
     * The calling sequence is as with the corresponding constructor.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $path The path of the object to load from the database.
     * @return bool Indicating Success
     */
    function get_by_path(&$object, $path)
    {
        $object->__exec_get_by_path((string) $path);

        if (   $object->id != 0
            && $object->action != 'delete')
        {
            if (! $_MIDCOM->auth->can_do('midgard:read', $object))
            {
                //debug_push_class($object, __FUNCTION__);
                //debug_add("Failed to load object, read privilege on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                //    MIDCOM_LOG_ERROR);
                midcom_baseclasses_core_dbobject::_clear_object($object);
                //debug_pop();
                return false;
            }

            $result = $object->_on_loaded();
            if (! $result)
            {
                midcom_baseclasses_core_dbobject::_clear_object($object);
            }
            return $result;
        }
        else
        {
            //debug_push_class($object, __FUNCTION__);
            //debug_add("Failed to load the record identified by path {$path}, last Midgard error was: " . mgd_errstr(), MIDCOM_LOG_INFO);
            //debug_pop();
            return false;
        }
    }

    /**
     * This method is deprecated. It does nothing.
     * @access private
     */
    function _clear_object (&$object)
    {
        $vars = get_object_vars($object);
        foreach ($vars as $name => $value)
        {
            if (   $name == '__res' 
                || (  substr($name, 0, 2) == '__' 
                    && substr($name, -2) == '__')) 
            { 
                // This is a special variable, we must not overwrite them. 
                continue; 
            } 
            $object->$name = null; 
        } 
        return;
    }

    /**
     * Internal helper function, called upon successful delete. It will unconditionally
     * drop all privileges assigned to the given object.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating Success.
     * @access private
     */
    function _delete_privileges(&$object)
    {
        $qb = new midgard_query_builder('midcom_core_privilege_db');
        $qb->add_constraint('objectguid', '=', $object->guid);
        $qb->add_constraint('value', '<>', MIDCOM_PRIVILEGE_INHERIT);
        $result = @$qb->execute();

        if (! $result)
        {
            if (mgd_errstr() == 'MGD_ERR_OK')
            {
                // Workaround
                return true;
            }

            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to retrieve all privileges for the " . get_class($object) . " {$object->guid}: " . mgd_errstr(), MIDCOM_LOG_INFO);
            if (isset($php_errormsg))
            {
                debug_add("Error message was: {$php_errormsg}", MIDCOM_LOG_ERROR);
            }

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The query builder failed to execute, see the log file for more information.');
            // This will exit.
        }

        foreach($result as $dbpriv)
        {
            $dbpriv->delete();
        }
        return true;
    }

    /**
     * Return a parameter from the database.
     *
     * No event handlers are called here yet.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $domain The parameter domain.
     * @param string $name The parameter name.
     * @return string The parameter value or false otherwise (remember typesafe comparisons to protect against '' strings).
     */
    function get_parameter(&$object, $domain, $name)
    {
        if (!$object->guid)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Cannot retrieve information on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain]))
        {
            // We have this domain in cache already thanks to some parameter listing
            if (!isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain][$name]))
            {
                return '';
            }
            return midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain][$name];
        }

        $value = $object->_parent_parameter($domain, $name);
        return $value;
    }

    /**
     * List the parameters of an object. This will either list the parameters of
     * a single domain or the complete set of parameters, depending on the value
     * of $domain.
     *
     * It delegates the actual execution to two separate helper functions.
     *
     * No event handlers are called here yet.
     *
     * In case of a complete query, the result will be an associative array indexed
     * by the domain name and containing another array with parameter name/value pairs.
     * For example:
     *
     * <pre>
     * Array
     * (
     *     [Asgard] => Array
     *     (
     *         [lang] => en_US
     *         [act] => view
     *         [actloc] => tree
     *     )
     *     [AsgardTreeHost] => Array
     *     (
     *         [selected] => host0
     *         [sitegroup0_host0] => 1
     *         [sitegroup1_host0] => 1
     *     )
     * )
     * </pre>
     *
     * If you query only a single domain, the result will be a single associative
     * array containing the parameter name/value pairs. For example:
     *
     * <pre>
     * Array
     * (
     *     [lang] => en_US
     *     [act] => view
     *     [actloc] => tree
     * )
     * </pre>
     *
     * In both cases an empty Array will indicate that no parameter was found, while
     * false will indicate a failure while querying the database.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $domain The parameter domain to query, this may be null to indicate a full listing.
     * @return Array Parameter list (see above for details) or false on failure.
     */
    function list_parameters(&$object, $domain)
    {
        if (! $object->id)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Cannot retrieve information on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (! is_null($domain))
        {
            return midcom_baseclasses_core_dbobject::_list_parameters_domain($object, $domain);
        }

        return midcom_baseclasses_core_dbobject::_list_parameters_all($object);
    }

    /**
     * List the parameters of a single domain of an object.
     *
     * No event handlers are called here yet.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $domain The parameter domain to query.
     * @return Array Parameter listing or false on failure.
     * @see list_parameters()
     */
    function _list_parameters_domain(&$object, $domain)
    {
        if (!$object->guid)
        {
            return array();
        }

        /*
        $parameters = midcom_baseclasses_core_dbobject::_list_parameters_all($object);
        if (!isset($parameters[$domain]))
        {
            return array();
        }
        return $parameters[$domain];
        */

        if (!isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid]))
        {
            midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid] = array();
        }
        
        if (isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain]))
        {
            return midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain];
        }

        midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain] = array();

        $mc = midgard_parameter::new_collector('parentguid', $object->guid);
        $mc->set_key_property('name');
        $mc->add_value_property('value');
        $mc->add_constraint('domain', '=', $domain);
        $mc->execute();
        $parameters = $mc->list_keys();

        if (count($parameters) == 0)
        {
            unset($mc);
            return midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain];
        }

        foreach ($parameters as $name => $values)
        {
            midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain][$name] = $mc->get_subkey($name, 'value');
        }

        unset($mc);

        return midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain];
    }

    /**
     * List all parameters of an object.
     *
     * No event handlers are called here yet.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return Array Parameter listing or false on failure.
     * @see list_parameters()
     */
    function _list_parameters_all(&$object)
    {
        if (!$object->guid)
        {
            return array();
        }

        if (!isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid]))
        {
            midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid] = array();
        }

        if (isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid]['__midcom_baseclasses_core_dbobject_all']))
        {
            return midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid];
        }

        $mc = midgard_parameter::new_collector('parentguid', $object->guid);
        $mc->set_key_property('guid');
        $mc->add_value_property('domain');
        $mc->add_value_property('name');
        $mc->add_value_property('value');
        $mc->execute();
        $parameters = $mc->list_keys();

        if (count($parameters) == 0)
        {
            unset($mc);
            return midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid];
        }

        foreach ($parameters as $guid => $values)
        {
            $name = $mc->get_subkey($guid, 'name');
            $domain = $mc->get_subkey($guid, 'domain');

            if (!isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain]))
            {
                midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain] = array();
            }

            midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain][$name] = $mc->get_subkey($guid, 'value');
        }

        unset($mc);

        // Flag that we have queried all domains for this object
        midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid]['__midcom_baseclasses_core_dbobject_all'] = array();

        return midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid];
    }

    /**
     * Set a parameter of a given object to the value specified.
     *
     * This is either a create or an update operation depending on whether there was
     * already a parameter of that domain/name present, or not.
     *
     * The user needs both update and parameter manipulationpermission on the parent object for updates.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $domain The Parameter Domain.
     * @param string $name The Parameter name.
     * @param string $value The Parameter value. If this is empty, the corresponding parameter is deleted.
     * @return bool Indicating success.
     */
    function set_parameter(&$object, $domain, $name, $value)
    {
        if (! $object->guid)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Cannot set parameters on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (   ! $_MIDCOM->auth->can_do('midgard:update', $object)
            || ! $_MIDCOM->auth->can_do('midgard:parameters', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to set parameters, midgard:update or midgard:parameters on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }

        if (isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain]))
        {
            // Invalidate run-time cache
            unset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain]);
        }

        $result = $object->_parent_parameter($domain, $name, $value);

        $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_UPDATE, $object);
        return true;
    }

    /**
     * Delete a parameter of a given object to the value specified.
     *
     * Current implementation note: Deletion is not yet implemented in MgdSchema.
     * Therefore we set the parameters to an emtpy string for now, which should
     * have almost the same effect for most cases and thus is good enough for now.
     * Note, that empty string parameters are filtered in the getter methods until
     * this matter is resolved.
     *
     * The user needs both update and parameter manipulationpermission on the parent object for updates.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $domain The Parameter Domain.
     * @param string $name The Parameter name.
     * @return bool Indicating success.
     */
    function delete_parameter(&$object, $domain, $name)
    {
        if (! $object->guid)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Cannot set parameters on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (   ! $_MIDCOM->auth->can_do('midgard:update', $object)
            || ! $_MIDCOM->auth->can_do('midgard:parameters', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to delete parameters, midgard:update or midgard:parameters on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            debug_pop();
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }

        if (isset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain]))
        {
            // Invalidate run-time cache
            unset(midcom_baseclasses_core_dbobject::$parameter_cache[$object->guid][$domain]);
        }

        $result = $object->_parent_parameter($domain, $name, '');

        $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_UPDATE, $object);
        return $result;
    }

    /**
     * Read all privilege records from the object in question and return them
     * accordingly.
     *
     * You need privilege access to get this information (midgard:read (tested during
     * construction) and midgard:privileges) otherwise, the call will fail.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return Array A list of midcom_core_privilege objects or false on failure.
     */
    function get_privileges(&$object)
    {
        if (! $_MIDCOM->auth->can_do('midgard:privileges', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Could not query the privileges, permission denied.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $result = midcom_core_privilege::get_all_privileges($object->guid);
        
        return $result;
    }

    /**
     * Set a privilege on an object.
     *
     * This requires both midgard:update and midgard:privileges.
     *
     * You can either pass a ready made privilege record or a privilege/assignee/value
     * combination suitable for usage with create_new_privilege_object() (see there).
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param mixed $privilege Either the full privilege object (midcom_core_privilege) to set or the name of the privilege (string).
     *     If the name was specified, the other parameters must be specified as well.
     * @param int $value The privilege value, this defaults to MIDCOM_PRIVILEGE_ALLOW (invalid if $privilege is a midcom_core_privilege).
     * @param mixed $assignee A valid assignee suitable for midcom_core_privilege::set_privilege(). This defaults to the currently
     *     active user if authenticated or to 'EVERYONE' otherwise (invalid if $privilege is a midcom_core_privilege).
     * @param string $classname An optional class name to which a SELF privilege gets restricted to. Only valid for SELF privileges
     *     (invalid if $privilege is a midcom_core_privilege).
     * @return bool Indicating success.
     * @see midcom_services_auth
     */
    function set_privilege(&$object, $privilege, $assignee = null, $value = MIDCOM_PRIVILEGE_ALLOW, $classname = '')
    {

        if (   ! $_MIDCOM->auth->can_do('midgard:update', $object)
            || ! $_MIDCOM->auth->can_do('midgard:privileges', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to set a privilege on object object, midgard:update or midgard:privileges on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        // PONDER: will this cause issues with class based privileges ??
        /* yes
        if (   empty($classname)
            && is_object($object))
        {
            $classname = get_class($object);
        }
        */

        if (is_a('midcom_core_privilege', $privilege))
        {
            $result = $privilege->store();
        }
        else if (is_string($privilege))
        {
            $tmp = $object->create_new_privilege_object($privilege, $assignee, $value, $classname);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Invalid arguments for set_privilege: Failed to create the privilege. See debug level log for details.');
                // This will exit.
            }
            $result = $tmp->store();
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Invalid arguments for set_privilege: Unknown $privilege argument type. See debug level log for details.');
            // This will exit.
        }

        return $result;
    }

    /**
     * Unset a privilege on an object (e.g. set it to INHERIT).
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param mixed $privilege Either the full privilege object (midcom_core_privilege) to set or the name of the privilege (string).
     *     If the name was specified, the other parameters must be specified as well.
     * @param mixed $assignee A valid assignee suitable for midcom_core_privilege::set_privilege(). This defaults to the currently
     *     active user if authenticated or to 'EVERYONE' otherwise (invalid if $privilege is a midcom_core_privilege).
     * @param string $classname An optional class name to which a SELF privilege gets restricted to. Only valid for SELF privileges
     *     (invalid if $privilege is a midcom_core_privilege).
     * @return bool Indicating Success.
     */
    function unset_privilege(&$object, $privilege, $assignee = null, $classname = '')
    {
        if (   ! $_MIDCOM->auth->can_do('midgard:update', $object)
            || ! $_MIDCOM->auth->can_do('midgard:privileges', $object))
        {
            debug_push_class($this, __FUNCTION__);
            debug_add("Failed to unset a privilege on object object, midgard:update or midgard:privileges on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if ($assignee === null)
        {
            if ($_MIDCOM->auth->user === null)
            {
                $assignee = 'EVERYONE';
            }
            else
            {
                $assignee = $_MIDCOM->auth->user;
            }
        }

        if (is_a($privilege, 'midcom_core_privilege'))
        {
            $priv = $privilege;
        }
        else if (is_string($privilege))
        {
            $priv = $object->get_privilege($privilege, $assignee, $classname);
            if (! $priv)
            {
                return false;
            }
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Invalid arguments for unset_privilege. See debug level log for details.');
            // This will exit.
        }

        return $priv->drop();
    }

    /**
     * Looks up a privilege by its parameters.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $privilege The name of the privilege.
     * @param mixed $assignee Either a valid magic assignee (SELF, EVERYONE, USERS, ANONYMOUS), a midcom_core_user or a
     *     midcom_core_group object or subtype thereof.
     * @param string $classname An optional class name to which a SELF privilege is restricted to.
     * @return midcom_core_privilege The privilege record from the database.
     */
    function get_privilege(&$object, $privilege, $assignee, $classname = '')
    {
        if (! $_MIDCOM->auth->can_do('midgard:privileges', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to get a privilege, midgard:update or midgard:privileges on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (is_object($assignee))
        {
            $assignee = $assignee->id;
        }
        $obj = midcom_core_privilege::get_privilege($object, $privilege, $assignee, $classname);

        return $obj;
    }

    /**
     * Unsets all privilege on an object .
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating success.
     */
    function unset_all_privileges(&$object)
    {
        $privileges = $object->get_privileges();
        if (! $privileges)
        {
            debug_add('Failed to access the privileges. See above for details.', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($privileges as $privilege)
        {
            if (! $object->unset_privilege($privilege))
            {
                debug_push_class($object, __FUNCTION__);
                debug_add('Failed to drop a privilege record, see debug log for more information, aborting.', MIDCOM_LOG_WARN);
                debug_pop();
                return false;
            }
        }
        return true;
    }

    /**
     * Retrieves an attachment on an arbitrary object by its name.
     * If multiple attachments match the name (should not happen in reality), the
     * first match will be returned.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $name The name of the attachment to look up.
     * @return midcom_baseclasses_database_attachment The attachment found, or false on failure.
     */
    function get_attachment(&$object, $name)
    {
        if (! $object->id)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Cannot retrieve attachments on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        // Locate attachment
        $qb = $object->get_attachment_qb();
        $qb->add_constraint('name', '=', $name);
        $result = $_MIDCOM->dbfactory->exec_query_builder($qb);

        if (count($result) == 0)
        {
            return false;
        }

        return $result[0];
    }

    /**
     * Delete an attachment on an arbitrary object by its name.
     * If multiple attachments match the name (should not happen in reality), the
     * first match will be deleted.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $name The name of the attachment to delete.
     * @return bool Indicating success.
     */
    function delete_attachment(&$object, $name)
    {
        $attachment = $object->get_attachment($name);

        if (!$attachment)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Tried to delete the attachment {$name} at the object " . get_class($object) . " {$object->guid}, but it did not exist. Failing silently.");
            debug_pop();
            return false;
        }

        if (   ! $_MIDCOM->auth->can_do('midgard:update', $object)
            || ! $_MIDCOM->auth->can_do('midgard:attachments', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to set parameters, midgard:update or midgard:attachments on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        return $attachment->delete();
    }

    /**
     * Creates a new attachment at the current object and returns it for usage.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $name The name of the attachment.
     * @param string $title The title of the attachment.
     * @param string $mimetype The MIME-Type of the attachment.
     * @return midcom_baseclasses_database_attachment The created attachment or false on failure.
     */
    function create_attachment(&$object, $name, $title, $mimetype)
    {
        if (! $object->id)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Cannot retrieve attachments on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (   ! $_MIDCOM->auth->can_do('midgard:update', $object)
            || ! $_MIDCOM->auth->can_do('midgard:attachments', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to set parameters, midgard:update or midgard:attachments on the " . get_class($object) . " {$object->guid} not granted for the current user.",
                MIDCOM_LOG_ERROR);
            return false;
        }

        $attachment = new midcom_baseclasses_database_attachment();
        $attachment->name = $name;
        $attachment->title = $title;
        $attachment->mimetype = $mimetype;
        $attachment->parentguid = $object->guid;
        $result = $attachment->create();

        if (   ! $result
            || ! $attachment->id)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Could not create the attachment {$name} at the object " . get_class($object) . " {$object->guid}: Creation failed.",
                MIDCOM_LOG_INFO);
            debug_add('Last Midgard error was: ' . mgd_errstr());
            debug_add('Return code was: ' . $result);
            debug_pop();
            return false;
        }

        return $attachment;
    }

    /**
     * Returns a prepared query builder that is already limited to the attachments of the given
     * object.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return MidgardQueryBuilder Prepared QueryBuilder or false on failure.
     */
    function get_attachment_qb(&$object)
    {
        if (!$object->id)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Cannot retrieve attachments on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_attachment');
        $qb->add_constraint('parentguid', '=', $object->guid);

        return $qb;
    }

    /**
     * Returns a complete list of attachments for the current object. If there are no
     * attachments, an empty array is returned.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return Array A list of midcom_baseclasses_database_attachment objects or false on failure.
     */
    function list_attachments(&$object)
    {
        if (! $object->id)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Cannot retrieve attachments on a non-persistant object.', MIDCOM_LOG_WARN);
            debug_pop();
            return array();
        }

        $qb = $object->get_attachment_qb();
        $result = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (   !$result
            || !is_array($result))
        {
            return array();
        }

        return $result;
    }

    /**
     * This helper function will create a new privilege object for the object in question.
     * It will initialize the privilege with the values given in the arguments, as outlined
     * below.
     *
     * This call requires the <i>midgard:privileges</i> privilege.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @param string $name The name of the privilege to add.
     * @param int $value The privilege value, this defaults to MIDCOM_PRIVILEGE_ALLOW.
     * @param mixed $assignee A valid assignee suitable for midcom_core_privilege::set_privilege(). This defaults to the currently
     *     active user if authenticated or to 'EVERYONE' otherwise.
     * @param string $classname An optional class name to which a SELF privilege gets restricted to. Only valid for SELF privileges.
     * @return midcom_core_privilege The newly created privilege record or false on failure.
     */
    function create_new_privilege_object(&$object, $name, $assignee = null, $value = MIDCOM_PRIVILEGE_ALLOW, $classname = '')
    {
        if (! $_MIDCOM->auth->can_do('midgard:privileges', $object))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Could not create a new privilege, permission denied.', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if ($assignee === null)
        {
            if ($_MIDCOM->auth->user === null)
            {
                $assignee = 'EVERYONE';
            }
            else
            {
                $assignee =& $_MIDCOM->auth->user;
            }
        }

        $privilege = new midcom_core_privilege();
        if (! $privilege->set_assignee($assignee))
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Failed to set the assignee, aborting.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        $privilege->set_object($object);
        $privilege->name = $name;
        $privilege->value = $value;
        $privilege->classname = $classname;

        if (! $privilege->validate())
        {
            debug_push_class($object, __FUNCTION__);
            debug_add('Failed to validate the newly created privilege.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }
        return $privilege;
    }

    /**
     * This is a metadata helper that maps to the metadata onsite visibility
     * check function, making checks against visibility far easier.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating visibility state.
     */
    function is_object_visible_onsite($object)
    {
        $metadata =& $object->get_metadata();
        if (! $metadata)
        {
            debug_push_class($object, __FUNCTION__);
            debug_add("Failed to load the metadata for the " . get_class($object) . " {$object->guid}, assuming invisible object.",
                MIDCOM_LOG_ERROR);
            return false;
        }

        return $metadata->is_object_visible_onsite();
    }

    /**
     * Returns the GUID of the parent object. Tries to utilize the Memcache
     * data, loading the actual information only if it is not cached.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating visibility state.
     * @see get_parent_guid_uncached()
     * @see midcom_services_cache_module_memcache::lookup_parent_guid()
     */
    function get_parent_guid($object)
    {
        return $_MIDCOM->dbfactory->get_parent_guid($object);
    }

    /**
     * Returns the the parent object. Tries to utilize the Memcache
     * data, loading the actual information only if it is not cached.
     *
     * @param MidgardObject $object A class inherited from one of the MgdSchema driven Midgard classes supporting the above callbacks.
     * @return bool Indicating visibility state.
     * @see get_parent_guid()
     */
    function get_parent($object)
    {
        static $parents = array();
        if (!isset($parents[$object->guid]))
        {
            $parents[$object->guid] = $_MIDCOM->dbfactory->get_object_by_guid($object->get_parent_guid());
        }
        return $parents[$object->guid];
    }
}
?>
