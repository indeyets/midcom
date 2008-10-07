<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore Baseclass include */
require_once('select.php');

/**
 * Datamanager 2 m:n membership management type.
 *
 * This subclass provides specialized I/O procedures which allow implicit management of
 * m:n object mappings. For example this can cover the person member assignments of a
 * midgard_group. The mapping class used is configurable, thus it should be adaptable to
 * any standard m:n relationship.
 *
 * The member objects used to construct this mapping must be fully
 * qualified DBA objects where the user owning the master object has full control So
 * that the objects can be updated accordingly. It is recommended to make the member
 * objects children of the master objects. In addition, edit, delete and create rights
 * shoudl always go together.
 *
 * To work properly, this class needs various information: First, there is the name of the
 * member class used to do the mapping. In addition to this, two fieldnames of that class
 * must be supplied, one for the GUID of the master object, the other for the identifier
 * of the membership.
 *
 * Optionally, you can set the class to use the master object ID in case of the GUID,
 * this is there for legacy code ("midgard_members") which do not use GUIDs for linking
 * yet. The linked object ("membership") is always referenced by the key selected in the
 * corresponding widget.
 *
 * An additional option allows you to limit the "visible" member key space: You specify
 * a SQL LIKE compatible expression. When updating members, only member records matching
 * this constraint will be taken into account. This is quite useful in case you want to
 * split up a single selection into multiple "categories" for better usability. This
 * constraint is taken into account even when saving new keys so that all load and save
 * stays symmetrical. If you use this feature to separate multiple key namespaces from
 * each other, make sure that the various types do not overlap, otherwise one type will
 * overwrite the assignments of the other.
 *
 * Quick SQL LIKE cheatsheet: '%' matches any number of characters, even zero characters,
 * '_' matches exactly one character.
 *
 * When starting up, the type will only validate the existence of the mapping class. The
 * members specified will not be checked for performance reasons. In case something
 * wrong is specified there, it will surface during runtime, as invalid mapping entries
 * will be silently ignored (and thus saving won't work).
 *
 * This type should be set to a null storage location, as the m:n mapping entries do not
 *
 *
 * <b>Available configuration options:</b>
 *
 * - <i>string mapping_class_name:</i> Mandatory option. Holds the name of the DBA
 *   class used for the mapping code. The class must satisfy the above rules.
 * - <i>string master_fieldname:</i> Mandatory option. Holds the fieldname containing
 *   the (GU)ID of the master object in the mapping table.
 * - <i>string member_fieldname:</i> Mandatory option. Holds the fieldname containing
 *   the membership keys in the mapping table.
 * - <i>boolean master_is_id:</i> Set this to true if you want the ID instead of the GUID
 *   to be used for mapping purposes. Defaults to false.
 * - <i>string member_limit_like:</i> This SQL LIKE compatible constraint limits the
 *   number of valid member keys if set (see above). It defaults to null meaning no limit.
 * - <i>Array options:</i> The allowed option listing, a key/value map. Only the keys
 *   are stored in the storage location, using serialized storage. If you set this to
 *   null, <i>option_callback</i> has to be defined instead. You may not define both
 *   options.
 * - <i>string option_callback:</i> This must be the name of an available class which
 *   handles the actual option listing. See below how such a class has to look like.
 *   If you set this to null, <i>options</i> has to be defined instead. You may not
 *   define both options.
 * - <i>mixed option_callback_arg:</i> An additional argument passed to the constructor
 *   of the option callback, defaulting to null.
 * - <i>boolean csv_export_key:</i> If this flag is set, the CVS export will store the
 *   field key instead of its value. This is only useful if the foreign tables referenced
 *   are available at the site of import. This flag is not set by default. Note, that
 *   this does not affect import, which is only available with keys, not values.
 * - <i>boolean sortable:</i> Switch for determining if the order selected by the widget
 *   should be stored to the metadata object
 * - <i>string sortable_sort_order:</i> Direction that metadata.score should go. If this
 *   is set to `ASC`, lower scores will be displayed first. If this is set to `DESC`, higher
 *   scores will be displayed first. `DESC` is default, since then new member objects will
 *   be left at the end of the line rather than appearing first. This field is not case
 *   sensitive and string can be extended e.g. to `ascend`.
 * - <i>array additional_fields:</i> Additional fields that should be set on the mnrelation object
 *
 * (These list is complete, including all allowed options from the base type. Base type
 * options not listed here may not be used.)
 *
 * <b>Option Callback class</b>
 *
 * See base type.
 *
 * <b>Implementation notes</b>
 *
 * This class essentially extends the select type, rewriting its I/O code to suite the
 * needs of a member management type.
 *
 * Therefore, we force-override a few settings to ensure operability: allow_other
 * will always be false, while allow_multiple always be true.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_mnrelation extends midcom_helper_datamanager2_type_select
{
    /**
     * Mandatory option. Holds the name of the DBA
     * class used for the mapping code. The class must satisfy the above rules.
     *
     * @var string
     */
    var $mapping_class_name = null;

    /**
     * Mandatory option. Holds the fieldname containing
     * the (GU)ID of the master object in the mapping table.
     *
     * @var string
     */
    var $master_fieldname = null;

    /**
     * Mandatory option. Holds the fieldname containing
     * the membership keys in the mapping table.
     *
     * @var string
     */
    var $member_fieldname = null;

    /**
     * Set this to true if you want the ID instead of the GUID
     * to be used for mapping purposes.
     *
     * @var boolean
     */
    var $master_is_id = false;

    /**
     * This SQL LIKE combatibel constraint limits the number of valid member keys if set
     * (see class introduction).
     *
     * @var string
     */
    var $member_limit_like = null;
    
    /**
     * Set this to false to use with universalchooser, this skips making sure the key exists in option list
     * Mainly used to avoid unnecessary seeks to load all a ton of objects to the options list. This is false
     * by default for mn relations, since by its nature this is intended for dynamic searches.
     *
     * @var boolean
     * @access public
     */
     var $require_corresponding_option = false;

    /**
     * This is a regular expression pattern constructed from $member_limit_like to verify
     * active records against the regex. Tied to $member_limit_like and only valid during
     * convert_to_storage.
     *
     * @var string
     */
    var $_member_limit_regex = null;

    /**
     * This is a QB resultset of all membership objects currently constructed. It is indexed
     * by membership record guid. It will be populated during startup, when the stored data is
     * loaded. During save, this list will be used to determine the objects that have to be
     * deleted.
     *
     * Only objects matching the member_limit_regex will be memorized.
     *
     * @var Array
     * @access private
     */
    var $_membership_objects = null;
    
    /**
     * Should the sorting feature be enabled. This will affect the way chooser widget will act
     * and how the results will be presented. If the sorting feature is enabled, 
     *
     * @access public
     * @var boolean
     */
    var $sortable = false;
    
    /**
     * Sort order. Which direction should metadata.score force the results. This should be either
     * `ASC` or `DESC`
     *
     * @access public
     * @var string
     */
    var $sortable_sort_order = 'DESC';
    
    /**
     * Sorted order, which is returned by the widget.
     * 
     * @access public
     * @var Array
     */
    var $sorted_order = array();

    /**
     * Additional fields to set on the object
     * 
     * @access public
     * @var Array
     */
    var $additional_fields = array();

    /**
     * Initialize the class, if necessary, create a callback instance, otherwise
     * validate that an option array is present.
     */
    function _on_initialize()
    {
        if (   ! $this->mapping_class_name
            || ! $this->master_fieldname
            || ! $this->member_fieldname)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The configuration options mapping_class_name, master_filename and member_fieldname ' .
                'must be defined for  any mnselect type.');
            // This will exit.
        }

        if (! class_exists($this->mapping_class_name))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The mapping class {$this->mapping_class_name} does not exist.");
            // This will exit.
        }

        $this->allow_other = false;
        $this->allow_multiple = true;
        parent::_on_initialize();

        return true;
    }

    /**
     * Returns the foreign key of the master object. This is either the ID or the GUID of
     * the master object, depending on the $master_is_id member.
     *
     * @var string Foreign key for the master field in the mapping table.
     * @access protected
     */
    function _get_master_foreign_key()
    {
        if ($this->master_is_id)
        {
            return $this->storage->object->id;
        }
        else
        {
            return $this->storage->object->guid;
        }
    }

    /**
     * Synchronizes the member list regex with the given LIKE query.
     *
     * @access private
     */
    function _update_member_limit_regex()
    {
        if ($this->member_limit_like)
        {
            $this->_member_limit_regex =
                '/^' .
                str_replace(Array('%', '_'), Array('.*', '.'), $this->member_limit_like) .
                '$/';
        }
        else
        {
            $this->_member_limit_regex = null;
        }
    }

    /**
     * Loads all membership records from the database. May only be called if a storage object is
     * defined.
     */
    function _load_membership_objects()
    {
        $qb = $_MIDCOM->dbfactory->new_query_builder($this->mapping_class_name);
        $qb->add_constraint($this->master_fieldname, '=', $this->_get_master_foreign_key());
        
        if (   $this->sortable
            && preg_match('/^(ASC|DESC)/i', $this->sortable_sort_order, $regs))
        {
            $order = strtoupper($regs[1]);
            $qb->add_order('metadata.score', $order);
        }
        
        if ($this->member_limit_like)
        {
            $qb->add_constraint($this->member_fieldname, 'LIKE', $this->member_limit_like);
        }

        if (!empty($this->additional_fields))
        {
            foreach ($this->additional_fields as $fieldname => $value)
            {
                $qb->add_constraint($fieldname, '=', $value);
            }
        }

        $this->_membership_objects = $qb->execute();
    }

    /**
     * Reads all entries from the mapping table. This overrides the base types I/O code completely.
     *
     * @var mixed $source UNUSED.
     */
    function convert_from_storage ($source)
    {
        $this->selection = Array();
        
        // Check for the defaults section first
        if (   isset($this->storage->_defaults)
            && isset($this->storage->_defaults[$this->name]))
        {
            foreach ($this->storage->_defaults[$this->name] as $id)
            {
                if (is_object($id))
                {
                    if ($this->master_is_id)
                    {
                        $this->selection[] = $id->id;
                    }
                    else
                    {
                        $this->selection[] = $id->guid;
                    }
                }
                else
                {
                    $this->selection[] = $guid;
                }
            }
        }
        
        if (!$this->storage->object)
        {
            // That's all folks, no storage object, thus we cannot continue.
            return;
        }
        
        $this->_load_membership_objects();
        
        foreach ($this->_membership_objects as $member)
        {
            $key = $member->{$this->member_fieldname};
            if ($this->key_exists($key))
            {
                $this->selection[] = $key;
            }
            else if (!$this->require_corresponding_option)
            {
                $this->selection[] = $key;
            }
            else
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Encountered unknown key {$key} for field {$this->name}, skipping it.", MIDCOM_LOG_INFO);
                debug_pop();
            }
        }
    }
    
    /**
     * Updates the mapping table to match the current selection.
     *
     * @return Returns null.
     */
    function convert_to_storage()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (! $this->storage->object)
        {
            // That's all folks, no storage object, thus we cannot continue.
            // We log a warning here (as opposed to convert_from_storage).
            debug_add("Tried to save the membership info for field {$this->name}, but no storage object was set. Ignoring silently.",
                MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        $this->_update_member_limit_regex();

        // Build a reverse lookup map for the existing membership objects.
        // We map keys to _membership_object indexes.
        // If we have duplicate keys, the latter will overwrite the former, leaving the dupe for deletion.
        $todelete = $this->_membership_objects;
        $existing_members = Array();
        foreach ($this->_membership_objects as $index => $member)
        {
            $key = $member->{$this->member_fieldname};
            $existing_members[$key] = $index;
        }
        
        // Cache the total quantity of items and get the order if the field is supposed to store the member order
        if (   $this->sortable
            && isset($this->sorted_order))
        {
            $count = count($this->sorted_order);
            
            if (preg_match('/ASC/i', $this->sortable_sort_order))
            {
                $direction = 'asc';
            }
            else
            {
                $direction = 'desc';
            }
        }
        
        $i = 0;
        
        $new_membership_objects = Array();
        foreach ($this->selection as $key)
        {
            // Validation
            if (   $this->member_limit_like
                && ! preg_match($this->_member_limit_regex, $key))
            {
                debug_add("The key {$key} does not match the LIKE constraint {$this->member_limit_like}, skipping it.",
                    MIDCOM_LOG_INFO);
                debug_add("Used Regex: {$this->_member_limit_regex}");
                continue;
            }

            // Do we have this key already? If yes, move it to the new list, otherwise create it.
            if (array_key_exists($key, $existing_members))
            {
                // Update the existing member
                if ($this->sortable)
                {
                    $index = $existing_members[$key];
                    
                    if ($direction === 'asc')
                    {
                        $this->_membership_objects[$index]->metadata->score = $i;
                    }
                    else
                    {
                        $this->_membership_objects[$index]->metadata->score = $count - $i;
                    }
                    
                    if (!$this->_membership_objects[$index]->update())
                    {
                        debug_add("Failed to update the member record for key {$key}. Couldn't store the order information", MIDCOM_LOG_ERROR);
                        debug_add('Last Midgard error was ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                        debug_print_r('Tried to update this object', $this->_membership_objects[$index]);
                    }
                    
                    $i++;
                }
                
                $index = $existing_members[$key];
                $new_membership_objects[] = $this->_membership_objects[$index];
                unset ($this->_membership_objects[$index]);
            }
            else
            {
                // Create new member
                $member = new $this->mapping_class_name();
                $member->{$this->master_fieldname} = $this->_get_master_foreign_key();
                $member->{$this->member_fieldname} = $key;
                
                // Set the score if requested
                if ($this->sortable)
                {
                    if ($direction === 'asc')
                    {
                        $member->metadata->score = $i;
                    }
                    else
                    {
                        $member->metadata->score = $count - $i;
                    }
                    
                    $i++;
                }

                if (!empty($this->additional_fields))
                {
                    foreach ($this->additional_fields as $fieldname => $value)
                    {
                        $member->{$fieldname} = $value;
                    }
                }

                if (!$member->create())
                {
                    debug_add("Failed to create a new member record for key {$key}, skipping it. " .
                        'Last Midgard error was: ' .
                        mgd_errstr(),
                        MIDCOM_LOG_ERROR);
                    debug_print_r('Tried to create this object:', $member);
                    continue;
                }
                $new_membership_objects[] = $member;
            }
        }

        // Delete all remaining objects, then update the membership_objects list
        foreach ($this->_membership_objects as $member)
        {
            if (!$member->delete())
            {
                debug_add("Failed to delete a no longer needed member record #{$member->id}, ignoring silently. " .
                    'Last Midgard error was: ' .
                    mgd_errstr(),
                    MIDCOM_LOG_ERROR);
                debug_print_r('Tried to delete this object:', $member);
            }
        }

        $this->_membership_objects = $new_membership_objects;

        debug_pop();
        return null;
    }

    function convert_to_raw()
    {
        return null;
    }
}

?>