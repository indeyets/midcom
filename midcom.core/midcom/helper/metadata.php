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
 * be instantinated directly, as a cache is in place to avoide duplicate metadata
 * objects for the same Midgard Object. So, basically, each of these object is
 * a singelton.
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
 * See also the schema in /midcom/config/metadata_default.dat
 *
 * - <b>timestamp schedule_start:</b> The time upon which the object should be made visible. 0 for no restriction.
 * - <b>timestamp schedule_end:</b> The time upon which the object should be made invisible. 0 for no restriction.
 * - <b>bool nav_noentry:</b> Set this to true if you do not want this object to appear in the navigation without it beeing completly hidden.
 * - <b>bool hide:</b> Set this to true to hide the object on-site, overriding scheduling.
 * - <b>string keywords:</b> The keywords for this object, should be used for META HTML headers.
 * - <b>string description:</b> A short description for this object, should be used for META HTML headers.
 * - <b>string robots:</b> Search engine crawler instructions, one of '' (unset), 'noindex', 'index', 'follow' and 'nofollow'.
 * 	 See the corresponding META HTML header.
 * - <b>timestamp created:</b> The creation time of the object, read-only unless an article is edited.
 * - <b>MidgardPerson creator:</b> The person that created the object, read-only.
 * - <b>timestamp edited:</b> The last-modified time of the object, read-only.
 * - <b>MidgardPerson editor:</b> The person that modified the object, read-only.
 * - <b>timestamp approved:</b> The time of approval of the object, or 0 if not approved. Set automatically through approve/unapprove.
 * - <b>MidgardPerson approver:</b> The person that approved/unapproved the object. Set automatically through approve/unapprove.
 *
 * <b>Example Usage, Metadata Retrival</b>
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
 * $article = mgd_get_article($my_article_created_id);
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
     * the outside, where neccessary.
     *
     * @var MidgardObject
     */
    var $object = null;

    /**
     * The guid of the object, it is cached for fast access to avoid repeated
     * database queries.
     *
     * @var GUID
     */
    var $guid = '';

    /**
     * Holds the values alread read from the database.
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
    var $_schemadb = null;

    /**
     * Datamanager instance for the given object.
     *
     * @access private
     * @var midcom_helper_datamanager
     */
    var $_datamanager = null;


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
     * @param mixed $object The MidgardObject ot attach to.
     * @param string $schemadb The URL of the schemadb to use.
     * @see midcom_helper_metadata::get()
     * @access private
     */
    function midcom_helper_metadata ($guid, $object, $schemadb)
    {
        $this->guid = $guid;
        $this->object = $object;
        $this->_schemadb = $schemadb;
    }


    /* ------- BASIC METADATA INTERFACE --------- */

    /**
     * This function will return a single metadata key from the object. Its return
     * type depends on the metadata key that is requested (see the class introduction).
     *
     * You will not get the data from the datamanager using this calls, but the only
     * slightly post-processed metadata values. See _retrieve_value for post processing.
     *
     * @see midcom_helper_metdata::_retrieve_value();
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
        /*
         * This can't work, _schemadb is no array but just an URL
         * 
        if ($this->object->__table__ == 'article')
        {
            // Patch schema in case of articles.
            $this->_schemadb['metadata']['fields']['created']['readonly'] = false;
        }
        // Patch approval until DM gets ACL awar
        if (! $_MIDCOM->auth->can_do('midcom:approve', $this->object))
        {
            $this->_schemadb['metadata']['fields']['approved']['readonly'] = true;
            $this->_schemadb['metadata']['fields']['approver']['readonly'] = true;
        }
         */

        $GLOBALS['midcom']->load_library("midcom.helper.datamanager");
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb);
        if (! $this->_datamanager)
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                'Failed to create the metadata datamanager instance, see the Debug Log for details.');
            // This will exit()
        }
        if (! $this->_datamanager->init($this->object, 'metadata'))
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                'Failed to initialize the metadata datamanager instance, see the Debug Log for details.');
            // This will exit()
        }
    }

    function release_datamanager()
    {
        if (! is_null($this->_datamanager))
        {
            $this->_datamanager->destroy();
            $this->_datamanager = null;
        }
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
    function set ($key, $value)
    {
         debug_push('midcom.helper.metadata::set');
        debug_print_r("We have to set {$key} to this value:", $value);

        switch ($key)
        {
            case 'created':
                // Special handling for articles:
                if ($this->object->__table__ == 'article')
                {
                     if (!mgd_update_article_created($this->object->id, $value))
                     {
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                            "Failed to set the created timestamp to {$value} on article ID {$this->object->id}.");
                        // This will exit.
                     }
                     $this->object = mgd_get_article($this->object->id);
                     $this->on_update($key);

                     // Explicitly return here, we mustn't set a parameter.
                     debug_pop();
                     return;
                }
                else
                {
                    $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                        "Cannot update the created fields of non-articles using the Metadata interface.");
                    // This will exit.
                }
                break;

            case 'creator':
            case 'edited':
            case 'editor':
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "Cannot update the creator, edited and editor fields using the Metadata interface.");
                // This will exit.
                break;

            case 'approver':
                // If we have a guid, we don't worry further.
                if (mgd_is_guid($value))
                {
                    break;
                }

                // ... otherwise we try to convert the passed argument to a GUID.
                if (is_object($value) && $value->__table__ == 'person')
                {
                    // This seems to be a MidgardPerson
                    $value = $value->guid();
                }
                else if (is_numeric($value))
                {
                    // This seems to be an ID
                    $person = mgd_get_person($value);
                    if (! $person)
                    {
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                            "Could not load the Person with the ID {$value} while setting the approver.");
                        // This will exit.
                    }
                    $value = $person->guid();
                }
                else
                {
                    $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                        "{$value} does not look like a GUID while setting the approver.");
                    // This will exit.
                }
                break;
        }
        $value = $this->object->parameter('midcom.helper.metadata', $key, $value);

        // Update the corresponding cache variable
        $this->on_update($key);
        debug_pop();
    }


    /**
     * This is the update event handler for the Metadata system. It must be called
     * whenever metadata changes to synchronize the various backwards-compatibility
     * values in place throughout the system.
     *
     * @param string $key The key that was updated. Leave empty for a complete update by the Datamanger.
     */
    function on_update($key = false)
    {
        if ($key && array_key_exists($key, $this->_cache))
        {
            unset ($this->_cache[$key]);
        }
        else
        {
            $this->_cache = Array();
        }

        if (! $key || $key == 'approved')
        {
            if ($this->object->__table__ == 'article')
            {
                mgd_approve_article($this->object->id, ($this->get('approved') ? true : false));
            }
        }

        // TODO: Add Caching Coide here, and do invalidation of the nap part manually.
        // so that we don't loose the cache of the metadata already in place.
        // Just be intelligent here :)

        $GLOBALS['midcom']->cache->invalidate($this->guid);
    }

    /* ------- METADATA I/O INTERFACE -------- */

    /**
     * Retrieves a given metadata key, postprocesses it where neccessary
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
     * Note, that we hide any errors from not existant properties explicitly,
     * as a few of the MidCOM objects do not support all of the predefined meta
     * data fields, PHP will default to "0" in these cases. For Person IDs, this
     * "0" is rewritten to "1" to use the MidgardAdministrator account instead.
     *
     * @param string $key The key to retrive.
     * @access private
     */
    function _retrieve_value($key)
    {
        switch ($key)
        {
            case 'created':
                $value = @$this->object->$key;
                if (   $value != ''
                    && !is_numeric($value))
                {
                    // This is a MgdSchema ISO Date String
                    $value = strtotime($value);
                }
                break;

            case 'creator':
                $value = @$this->object->$key;
                if ($value == 0)
                {
                    $value = 1;
                }
                break;

            case 'edited':
                $value = @$this->object->revised;
                if (   $value != ''
                    && !is_numeric($value))
                {
                    // This is a MgdSchema ISO Date String
                    $value = strtotime($value);
                }
                break;

            case 'editor':
                $value = @$this->object->revisor;
                if ($value == 0)
                {
                    $value = 1;
                }
                break;

            default:
                $varname = "midcom.helper.metadata_{$key}";
                if (! array_key_exists($varname, get_object_vars($this->object)))
                {
                    // Fall back to the parameter reader, this might be an MgdSchema object.
                    $value = $this->object->parameter('midcom.helper.metadata', $key);
                }
                else
                {
                    $value = $this->object->$varname;
                }
                break;
        }

        /* Special key handling for various keys goes here.
         * by default we just return the value directly.
         *
         * Empty keys stay untouched too.
         */
        if ($value)
        {
            switch ($key)
            {
                // Person GUIDs
                case 'approver':
                    $obj = mgd_get_object_by_guid($value);
                     if (! $obj)
                     {
                          debug_push_class(__CLASS__, __FUNCTION__);
                         debug_add("Could not load Approver {$value}:" . mgd_errstr(), MIDCOM_LOG_INFO);
                        debug_pop();
                        $obj = mgd_get_object_by_guid('f6b665f1984503790ed91f39b11b5392');
                     }
                     $value = $obj;
                     break;

                    // Person IDs
                  case 'creator':
                  case 'revisor':
                      $obj = mgd_get_person($value);
                        if (! $obj)
                        {
                            debug_push_class(__CLASS__, __FUNCTION__);
                            debug_add("Could not load {$key} {$value}:" . mgd_errstr(), MIDCOM_LOG_INFO);
                            debug_pop();
                            $obj = mgd_get_object_by_guid('f6b665f1984503790ed91f39b11b5392');
                        }
                        $value = $obj;
                        break;
             }
            }
         $this->_cache[$key] = $value;
    }


    /* ------- CONVENIENCE METADATA INTERFACE --------- */

    /**
     * Checks wether the article has been approved since its last editing.
     *
     * @return bool Indicating approval state.
     */
    function is_approved()
    {
        if (   $this->get('approved')
            && $this->get('approved') >= $this->get('edited'))
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
     * @return bool Indicatinv visibility state.
     */
    function is_visible()
    {
        if ($this->get('hide'))
        {
            return false;
        }

        $now = time();
        if (   $this->get('schedule_start')
            && $this->get('schedule_start') > $now)
        {
            return false;
        }
        if (   $this->get('schedule_end')
            && $this->get('schedule_end') < $now)
        {
            return false;
        }
        return true;
    }

    /**
     * This is a helper function which indicates wether a given object may be shown onsite
     * taking approval, scheduling and visibility settings into account. The important point
     * here is that it also checks the global configuration defaults, so that this is
     * basically the same base on which NAP decides wether to show an item or not.
     *
     * @return bool Indicating visibility.
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
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_do('midcom:approve', $this->object);
        $_MIDCOM->auth->require_do('midgard:update', $this->object);
        $_MIDCOM->auth->require_do('midgard:parameters', $this->object);

        $person = $_MIDCOM->auth->user->get_storage();
        $this->set('approved', time());
        $this->set('approver', $person->guid);
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

        $person = $_MIDCOM->auth->user->get_storage();
        $this->set('approved', '');
        $this->set('approver', $person->guid);
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
            $guid = $source->guid();
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
}







?>
