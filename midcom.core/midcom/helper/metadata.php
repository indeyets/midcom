<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:metadata.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is an interface to the metadata of MidCOM objects. It is not to
 * be instantiated directly, as a cache is in place to avoid duplicate metadata
 * objects for the same Midgard Object. So, basically, each of these objects is
 * a singleton.
 *
 * It will use an internal mechanism to cache repeated accesses to the same
 * metadata key during its lifetime. (Invalidating this cache will be possible
 * though.)
 *
 * All metadata is identified by their string-based keys, the original MIDCOM_META_*
 * constants are mapped to these new keys. This has been done to allow for easier
 * extension.
 *
 * <b>Metadata Key Reference</b>
 *
 * See also the schema in /midcom/config/metadata_default.inc
 *
 * - <b>timestamp schedule_start:</b> The time upon which the object should be made visible. 0 for no restriction.
 * - <b>timestamp schedule_end:</b> The time upon which the object should be made invisible. 0 for no restriction.
 * - <b>boolean nav_noentry:</b> Set this to true if you do not want this object to appear in the navigation without it being completely hidden.
 * - <b>boolean hide:</b> Set this to true to hide the object on-site, overriding scheduling.
 * - <b>string keywords:</b> The keywords for this object, should be used for META HTML headers.
 * - <b>string description:</b> A short description for this object, should be used for META HTML headers.
 * - <b>string robots:</b> Search engine crawler instructions, one of '' (unset), 'noindex', 'index', 'follow' and 'nofollow'.
 *      See the corresponding META HTML header.
 * - <b>timestamp published:</b> The publication time of the object, read-only.
 * - <b>MidgardPerson publisher:</b> The person that published the object (i.e. author), read-only except on articles and pages.
 * - <b>timestamp created:</b> The creation time of the object, read-only unless an article is edited.
 * - <b>MidgardPerson creator:</b> The person that created the object, read-only.
 * - <b>timestamp edited:</b> The last-modified time of the object, read-only.
 * - <b>MidgardPerson editor:</b> The person that modified the object, read-only.
 * - <b>timestamp approved:</b> The time of approval of the object, or 0 if not approved. Set automatically through approve/unapprove.
 * - <b>MidgardPerson approver:</b> The person that approved/unapproved the object. Set automatically through approve/unapprove.
 *
 * <b>Example Usage, Metadata Retrieval</b>
 *
 * <code>
 * <?php
 * $nap = new midcom_helper_nav();
 * $node = $nap->get_node($nap->get_current_node());
 *
 * $meta =& midcom_helper_metadata::retrieve($node[MIDCOM_NAV_GUID]);
 * echo "Visible : " . $meta->is_visible() . "</br>";
 * echo "Approved : " . $meta->is_approved() . "</br>";
 * echo "Keywords: " . $meta->get('keywords') . "</br>";
 * ?>
 * </code>
 *
 * <b>Example Usage, Approval</b>
 *
 * <code>
 * <?php
 * $article = new midcom_db_article($my_article_created_id);
 *
 * $meta =& midcom_helper_metadata::retrieve($article);
 * $article->approve();
 * ?>
 * </code>
 *
 * @package midcom
 */
class midcom_helper_metadata
{
    /**
     * Object to which we are attached to. This object can be accessed from
     * the outside, where necessary.
     *
     * @var MidgardObject
     */
    var $object = null;

    /**
     * The guid of the object, it is cached for fast access to avoid repeated
     * database queries.
     *
     * @var string GUID
     */
    var $guid = '';

    /**
     * Holds the values already read from the database.
     *
     * @access private
     * @var Array
     */
    var $_cache = Array();

    /**
     * The schema database URL to use for this instance.
     *
     * @access private
     * @var string
     */
    var $_schemadb_path = null;

    /**
     * Datamanager instance for the given object.
     *
     * @access private
     * @var midcom_helper_datamanager2
     */
    var $_datamanager = null;

    /**
     * Translation array for the object
     *
     * @access private
     * @var array
     */
    var $_translations = null;

    /**
     * This will construct a new metadata object for an existing content object.
     *
     * You must never use this constructor directly, it is considered private
     * in this respect. Instead, use the get method, which may be called as a
     * class method.
     *
     * You may use objects derived from any MidgardObject will do as well as long
     * as the parameter call is available normally.
     *
     * @param GUID $guid The GUID of the object as it is in the global metadata object cache.
     * @param mixed $object The MidgardObject to attach to.
     * @param string $schemadb The URL of the schemadb to use.
     * @see midcom_helper_metadata::get()
     * @access private
     */
    function midcom_helper_metadata ($guid, $object, $schemadb)
    {
        $this->guid = $guid;
        $this->object = $object;
        $this->_schemadb_path = $schemadb;
    }


    /* ------- BASIC METADATA INTERFACE --------- */

    /**
     * This function will return a single metadata key from the object. Its return
     * type depends on the metadata key that is requested (see the class introduction).
     *
     * You will not get the data from the datamanager using this calls, but the only
     * slightly post-processed metadata values. See _retrieve_value for post processing.
     *
     * @see midcom_helper_metdata::_retrieve_value()
     * @param string $key The key to retrieve
     * @return mixed The key's value.
     */
    function get ($key)
    {
        if (! array_key_exists($key, $this->_cache))
        {
            $this->_retrieve_value($key);
        }
        return $this->_cache[$key];
    }

    /**
     * Return a Datamanager instance for the current object.
     *
     * This is returned by reference, which must be honored, as usual.
     *
     * Also, whenever the containing datamanager stores its data, you
     * <b>must</b> call the on_update() method of this class. This is
     * very important or backwards compatibility will be broken.
     *
     * @return midcom_helper_datamanager A initialized Datamanager instance for the selected object.
     * @see midcom_helper_metadata::on_update()
     */
    function & get_datamanager()
    {
        if (is_null($this->_datamanager))
        {
            $this->load_datamanager();
        }
        return $this->_datamanager;
    }

    /**
     * Loads the datamanager for this instance. This will patch the schema in case we
     * are dealing with an article.
     */
    function load_datamanager()
    {
        $_MIDCOM->load_library('midcom.helper.datamanager2');
        
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_schemadb_path);
        
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        if (! $this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create the metadata datamanager instance, see the Debug Log for details.');
            // This will exit()
        }

        // Check if we have metadata schema defined in the schemadb specific for the object's schema or component
        $object_schema = $this->object->get_parameter('midcom.helper.datamanager2', 'schema_name');
        $component_schema = str_replace('.', '_', $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT));
        if (   $object_schema == ''
            || !isset($this->_schemadb[$object_schema]))
        {
            if (isset($this->_schemadb[$component_schema]))
            {
                // No specific metadata schema for object, fall back to component-specific metadata schema
                $object_schema = $component_schema;
            }
            else
            {
                // No metadata schema for component, fall back to default
                $object_schema = 'metadata';
            }
        }        
        $this->_datamanager->set_schema($object_schema);
        if (! $this->_datamanager->set_storage($this->object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to initialize the metadata datamanager instance, see the Debug Log for details.');
            // This will exit()
        }
    }

    function release_datamanager()
    {
        if (! is_null($this->_datamanager))
        {
            $this->_datamanager = null;
        }
    }

    /**
     * Frontend for setting a single metadata option
     *
     * @param string $key The key to set.
     * @param mixed $value The value to set.
     */
    function set ($key, $value)
    {
      $return = false;
      if ($this->_set_property($key, $value))
    {
      $return = $this->object->update();
      // Update the corresponding cache variable
      $this->on_update($key);
    }
      return $return;
    }

    /**
     * Frontend for setting multiple metadata options
     *
     * @param Array $properties Array of key => value properties.
     */
    function set_multiple ($properties)
    {
      $return = false;
      foreach ($properties as $key => $value)
    {
      if (!$this->_set_property($key, $value))
        {
          return false;
          // this will exit
        }
    }
      if ($this->object->update())
    {
      $return = true;
      // Update the corresponding cache variables
      foreach ($properties as $key => $value)
        {
          $this->on_update($key);
        }
    }
      return $return;
    }

    /**
     * Directly set a metadata option.
     *
     * The passed value will be stored using the follow transformations:
     *
     * - Storing into the approver field will automatically recognize Person Objects and simple
     *   IDs and transform them into a GUID.
     * - created can only be set with articles.
     * - creator, editor and edited cannot be set.
     *
     * Any error will trigger generate_error.
     *
     * @param string $key The key to set.
     * @param mixed $value The value to set.
     */
    function _set_property ($key, $value)
    {
        // Store the RCS mode
        $rcs_mode = $this->object->_use_rcs;

        switch ($key)
        {
            // Read-only properties
            case 'creator':
            case 'created':
            case 'revisor':
            case 'revised':
            case 'revision':
            case 'size':
            case 'deleted':
            case 'exported':
            case 'imported':
                mgd_set_errno(MGD_ERR_ACCESS_DENIED);
                return false;

            // Writable properties
            case 'locker':
            case 'locked':
            case 'approver':
            case 'approved':
                // Prevent lock changes from creating new revisions
                $this->object->_use_rcs = false;
                // Fall through
            
            case 'authors':
            case 'owner':
            case 'published':
            case 'schedulestart':
            case 'scheduleend':
            case 'hidden':
            case 'navnoentry':
            case 'score':
                $this->object->metadata->$key = $value;
                $value = true;
                break;

            // Fall-back for non-core properties
            default:
                $value = $this->object->set_parameter('midcom.helper.metadata', $key, $value);
                break;
        }

        debug_pop();

        // Return the original RCS mode
        $this->object->_use_rcs = $rcs_mode;
        
        return $value;
    }



    /**
     * This is the update event handler for the Metadata system. It must be called
     * whenever metadata changes to synchronize the various backwards-compatibility
     * values in place throughout the system.
     *
     * @param string $key The key that was updated. Leave empty for a complete update by the Datamanager.
     */
    function on_update($key = false)
    {
        if (   $key
            && array_key_exists($key, $this->_cache))
        {
            unset ($this->_cache[$key]);
        }
        else
        {
            $this->_cache = Array();
        }

        // TODO: Add Caching Code here, and do invalidation of the nap part manually.
        // so that we don't loose the cache of the metadata already in place.
        // Just be intelligent here :)

        $_MIDCOM->cache->invalidate($this->guid);
    }

    /* ------- METADATA I/O INTERFACE -------- */

    /**
     * Retrieves a given metadata key, postprocesses it where necessary
     * and stores it into the local cache.
     *
     * - Person references (both guid and id) get resolved into the corresponding
     *   Person object.
     * - created, creator, edited and editor are taken from the corresponding
     *   MidgardObject fields.
     * - Parameters are accessed using their midgard-created member variables
     *   instead of accessing the database using $object->parameter directly for
     *   performance reasons (this will implicitly use the NAP cache for these
     *   values as well. (Implementation note: Variable variables have to be
     *   used for this, as we have dots in the member name.)
     *
     * Note, that we hide any errors from not existent properties explicitly,
     * as a few of the MidCOM objects do not support all of the predefined meta
     * data fields, PHP will default to "0" in these cases. For Person IDs, this
     * "0" is rewritten to "1" to use the MidgardAdministrator account instead.
     *
     * @param string $key The key to retrieve.
     * @access private
     */
    function _retrieve_value($key)
    {
        switch ($key)
        {
            // Time-based properties
            case 'created':
            case 'revised':
            case 'published':
            case 'locked':
            case 'approved':
            case 'schedulestart':
            case 'scheduleend':
            case 'exported':
            case 'imported':
            case 'revised':
                $value = $this->object->metadata->$key;
                break;

            case 'nav_noentry':
                $value = $this->get('navnoentry');
                break;

            case 'edited':
                $value = $this->get('revised');
                break;

            // Person properties
            case 'creator':
            case 'revisor':
            case 'locker':
            case 'approver':
            case 'authors':
                $value = $this->object->metadata->$key;
                if (!$value)
                {
                    // Fall back to "Midgard admin" if person is not found
                    $value = 1;
                }
                break;

            // Group property
            case 'owner':
                $value = $this->object->metadata->$key;
                if (!$value)
                {
                    // Fall back to SG admin group if owner is not found
                    static $sg = null;
                    if (is_null($sg))
                    {
                        $sg = mgd_get_sitegroup($_MIDGARD['sitegroup']);
                    }
                    $value = $sg->admingroup;
                }
                break;

            // Old, renamed MidCOM metadata properties
            case 'author':
                $value = $this->get('authors');
                break;
            case 'editor':
                $value = $this->get('revisor');
                break;
            case 'publisher':
                if (   $this->object->__table__ == 'article'
                    || $this->object->__table__ == 'page')
                {
                    $value = $this->object->author;
                }
                else
                {
                    $value = $this->get('authors');
                }
                break;
            case 'hide':
                $value = $this->get('hidden');
                break;
            case 'schedule_start':
                $value = $this->get('schedulestart');
                break;
            case 'schedule_end':
                $value = $this->get('scheduleend');
                break;

            // Other midgard_metadata properties
            case 'revision':
            case 'hidden':
            case 'navnoentry':
            case 'size':
            case 'deleted':
            case 'score':
                $value = $this->object->metadata->$key;
                break;

            // Fall-back for non-core properties
            default:
                $dm = $this->get_datamanager();
                if (!isset($dm->types[$key]))
                {
                    // Fall back to the parameter reader to get legacy MidCOM metadata params
                    $value = $this->object->get_parameter('midcom.helper.metadata', $key);
                }
                else
                {
                    $value = $dm->types[$key]->convert_to_csv();
                }

                break;
        }

        $this->_cache[$key] = $value;
    }


    /* ------- CONVENIENCE METADATA INTERFACE --------- */

    /**
     * Checks whether the article has been approved since its last editing.
     *
     * @return boolean Indicating approval state.
     */
    function is_approved()
    {
        if (   $this->get('approved')
            && $this->get('approved') >= $this->get('revised'))
        {
            return true;
        }
        return false;
    }

    /**
     * Checks the object's visibility regarding scheduling and the hide flag.
     *
     * This does not check approval, use is_approved for that.
     *
     * @see midcom_helper_metadata::is_approved()
     * @return boolean Indicatinv visibility state.
     */
    function is_visible()
    {
        if ($this->get('hidden'))
        {
            return false;
        }

        $now = time();
        if (   $this->get('schedulestart')
            && $this->get('schedulestart') > $now)
        {
            return false;
        }
        if (   $this->get('scheduleend')
            && $this->get('scheduleend') < $now)
        {
            return false;
        }
        return true;
    }

    /**
     * This is a helper function which indicates whether a given object may be shown onsite
     * taking approval, scheduling and visibility settings into account. The important point
     * here is that it also checks the global configuration defaults, so that this is
     * basically the same base on which NAP decides whether to show an item or not.
     *
     * @return boolean Indicating visibility.
     */
    function is_object_visible_onsite()
    {
        return
        (   (   $GLOBALS['midcom_config']['show_hidden_objects']
             || $this->is_visible())
         && (   $GLOBALS['midcom_config']['show_unapproved_objects']
             || $this->is_approved())
        );
    }

    /**
     * Approves the object.
     *
     * This sets the approved timestamp to the current time and the
     * approver person GUID to the GUID of the person currently
     * authenticated.
     */
    function approve()
    {
        //$_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_do('midcom:approve', $this->object);
        $_MIDCOM->auth->require_do('midgard:update', $this->object);
        $_MIDCOM->auth->require_do('midgard:parameters', $this->object);

        if (!$_MIDCOM->auth->user)
        {
            $approver = 'f6b665f1984503790ed91f39b11b5392';
        }
        else
        {
            $approver = $_MIDCOM->auth->user->guid;
        }
        $this->set('approver', $approver);
        $this->set('approved', time());
    }

    /**
     * Unapproves the object.
     *
     * This resets the approved timestamp and sets the
     * approver person GUID to the GUID of the person currently
     * authenticated.
     */
    function unapprove()
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_do('midcom:approve', $this->object);
        $_MIDCOM->auth->require_do('midgard:update', $this->object);
        $_MIDCOM->auth->require_do('midgard:parameters', $this->object);

        if (!$_MIDCOM->auth->user)
        {
            $approver = 'f6b665f1984503790ed91f39b11b5392';
        }
        else
        {
            $person = $_MIDCOM->auth->user->get_storage();
            $approver = $person->guid;
        }

        $this->set('approved', 0);
        $this->set('approver', $approver);
    }


    /* ------- CLASS MEMBER FUNCTIONS ------- */

    /**
     * Returns a metadata object for a given content object.
     *
     * You may bass any one of the following arguments to the function:
     *
     * - Any class derived from MidgardObject, you must only ensure, that the parameter
     *   and guid member functions stays available.
     * - Any valid GUID
     * - Any NAP object structure, the content object is deduced from MIDCOM_NAV_GUID in
     *   this case.
     *
     * <b>Important note:</b> The metadata object is returned by reference. You are very
     * much encouraged to honor this reference, otherwise, the internal metadata value cache
     * won't really help.
     *
     * @param mixed $source The object to attach to, this may be either a MidgardObject, a GUID or a NAP data structure (node or leaf).
     * @return midcom_helper_metadata A reference to the created metadata object.
     */
    function & retrieve ($source)
    {
        // The object cache, indexed by GUID.
        static $_object_cache = Array();

        $object = null;
        $guid = '';

        if (is_object($source))
        {
            $object = $source;
            $guid = $source->guid;
        }
        else
        {
            if (is_array($source))
            {
                if (   array_key_exists(MIDCOM_NAV_GUID, $source)
                    && ! is_null($source[MIDCOM_NAV_GUID]))
                {
                    $guid = $source[MIDCOM_NAV_GUID];
                    $object = $source[MIDCOM_NAV_OBJECT];
                }
                else
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_print_r('We got an invalid input, cannot return metadata:', $source);
                    debug_pop();
                    $result = false;
                    return $result;
                }

            }
            else
            {
                $guid = $source;
            }
        }

        // Validate everything.
        if (! mgd_is_guid($guid))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The GUID '{$guid}' is invalid, cannot retrieve a metadata object reliably therefore.", MIDCOM_LOG_WARN);
            debug_pop();
            $result = false;
            return $result;
        }

        // $guid is now populated, check the cache.
        if (array_key_exists($guid, $_object_cache))
        {
            // This is a hit :-)
            return $_object_cache[$guid];
        }

        // We don't have a cache hit, return a newly constructed object.
        if (is_null($object))
        {
            $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
            if (! $object)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to create a metadata instance for the GUID {$guid}: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_print_r("Source was:", $source);
                debug_pop();
                $result = false;
                return $result;
            }
        }

        // $object is now populated too
        $meta = new midcom_helper_metadata($guid, $object, $GLOBALS['midcom_config']['metadata_schema']);
        if (! $meta)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to create a metadata object for {$guid}, last error was: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_print_r('Object used was:', $object);
            debug_pop();
            $result = false;
            return $result;
        }

        if (count($_object_cache) >= $GLOBALS['midcom_config']['cache_module_nap_metadata_cachesize'])
        {
            array_shift($_object_cache);
        }
        $_object_cache[$guid] =& $meta;

        return $meta;
    }

    function get_languages()
    {
        if (is_null($this->_translations))
        {
            $this->_translations = array();

            $languages = @$this->object->get_languages();
            if (   !$languages
                || count($languages) == 0)
            {
                return $this->_translations;
            }

            $language_hosts = $_MIDCOM->i18n->get_language_hosts();

            foreach ($languages as $language)
            {
                if (!array_key_exists($language->id, $language_hosts))
                {
                    // No host for this language, skip
                    continue;
                }

                $this->_translations[$language->id] = array
                (
                    'code' => $language->code,
                    'name' => $language->name,
                    'native' => $language->native,
                    'host' => $language_hosts[$language->id],
                    'url' => $_MIDCOM->generate_host_url($language_hosts[$language->id]),
                );
            }
        }

        return $this->_translations;
    }
    
    /**
     * Check if the requested object is locked
     * 
     * @static
     * @access public
     * @param mixed &$object    MgdSchema object
     * @return boolean          True if the object is locked, false if it isn't
     */
    function is_locked()
    {
        // Object hasn't been marked to be edited
        if ($this->get('locked') == 0)
        {
            return false;
        }

        if (($this->get('locked') + ($GLOBALS['midcom_config']['metadata_lock_timeout'] * 60)) < time())
        {
            // lock expired, explicitly clear lock
            $this->set_multiple(Array('locked' => 0, 'locker' => ''));
            return false;
        }

        // Lock was created by the user, return "not locked"
        if (   isset($_MIDCOM->auth->user)
            && isset($_MIDCOM->auth->user->guid)
            && $this->get('locker') === $_MIDCOM->auth->user->guid)
        {
            return false;
        }

        // Unlocked states checked and none matched, consider locked
        return true;
    }

    /**
     * Set the object lock
     * 
     * @access public
     * @param int $timeout   Length of the lock timeout
     * @param String $user   GUID of the midgard_person object
     * @return boolean       Indicating success
     */
    function lock($timeout = null, $user = null)
    {
        if (!$timeout)
        {
            $timeout = $GLOBALS['midcom_config']['metadata_lock_timeout'];
        }
        
        if (!$user)
        {
            $user = $_MIDCOM->auth->user->guid;
        }
        
        if (   is_object($this->object)
            && method_exists($this->object, 'lock'))
        {
            // This is the Midgard 8.09+ way for locking
            if ($this->object->is_locked())
            {
                return true;
            }
            return $this->object->lock();
        }
        
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Using legacy update() method for locking object, please update to Midgard 8.09", MIDCOM_LOG_WARN);
        debug_pop();

        $lock = Array
        (
            'locker' => $user, 
            'locked' => time() + $timeout * 60
        );

        return $this->set_multiple($lock);
    }

    /**
     * Check whether current user can unlock the object
     *
     * @return boolean indicating privileges
     * @todo enable specifying user ?
     */
    function can_unlock()
    {
        if (   !$this->object->can_do('midcom:unlock')
            && !$_MIDCOM->auth->can_user_do('midcom:unlock', null, 'midcom_services_auth', 'midcom'))
        {
            return false;
        }
        return true;
    }

    /**
     * Unlock the object
     * 
     * @access public
     * @param boolean $soft_unlock If this is true, the changes are not written to disk
     * @return boolean    Indicating success
     */
    function unlock($soft_unlock = false)
    {
        if (!$this->can_unlock())
        {
            return false;
        }

        if (   is_object($this->object)
            && method_exists($this->object, 'unlock'))
        {
            // This is the Midgard 8.09+ way for locking
            // TODO: Should we support soft unlock somehow?
            if (!$this->object->is_locked())
            {
                return true;
            }
            
            return $this->object->unlock();
        }
        
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Using legacy update() method for unlocking object, please update to Midgard 8.09", MIDCOM_LOG_WARN);
        debug_pop();

        if ($soft_unlock)
        {
            $this->object->metadata->locked = 0;
            $this->object->metadata->locker = '';
            return true;
        }

        if (!$this->set_multiple(Array('locked' => 0, 'locker' => '')))
        {
            return false;
        }

        return true;
    }
}
?>