<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Data Manager controller base class.
 *
 * This class encapsulates a controlling instance of the Datamanager class system. You do not
 * need to use it, it is possible to implement your own, custom form processing solely on the
 * basis of form/datamanager classes. The controllers are intended to ease the integration work
 * and provide more advanced frameworks for example for multi-page forms or AJAX callbacks.
 *
 * The base class implements only a framework for controllers, along with a factory methods for
 * getting real instances which you need to initialize. For all instances, you have to set
 * the schema database using the load_schemadb() helper.
 *
 * See the individual
 * subclass documentations for details about the initialization procedure.
 *
 * <b>You cannot use this class directly, consider it as an abstract base class!</b>
 *
 * @package midcom.helper.datamanager2
 * @abstract
 */
class midcom_helper_datamanager2_controller extends midcom_baseclasses_components_purecode
{
    /**
     * The schemadb to handle by this controller. 
     *
     * This is a list of midcom_helper_datamanager2_schema instances, indexed 
     * by their name. Set this member using the load_schemadb or set_schemadb 
     * helpers unless you know what you're doing.
     *
     * @var Array
     */
    var $schemadb = Array();

    /**
     * The datamanager instance which is used for data I/O processing. 
     *
     * Set this member using the set_storage() helper function unless you 
     * definitely know what you're doing.
     *
     * @var midcom_helper_datamanager2
     */
    var $datamanager = null;

    /**
     * The form manager instance which is currently in use by this class. 
     *
     * This should always be the a single instance, even for multi-page forms. 
     * Usually, it is created by the controller class during initialization.
     *
     * @var midcom_helper_datamanager2_formmanager
     */
    var $formmanager = null;

    /**
     * Lock timeout defines the length of lock in seconds.
     * 
     * @access public
     * @var integer
     */
    var $lock_timeout = null;

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function midcom_helper_datamanager2_controller()
    {
         $this->_component = 'midcom.helper.datamanager2';
         parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Empty default implementation, this calls won't do much.
     *
     * @return boolean Indicating success.
     */
    function initialize()
    {
        if (is_null($this->lock_timeout))
        {
            $this->lock_timeout = (int) $this->_config->get('lock_timeout');
        }
        
        return true;
    }

    /**
     * Loads a schema definition from disk and creates the corresponding schema
     * class instances.
     *
     * If you have an array of schema classes already, use set_schemadb() instead.
     *
     * @param mixed $schemapath A schema database source suitable for use with
     *     midcom_helper_datamanager2_schema::load_database()
     * @see midcom_helper_datamanager2_schema::load_database()
     */
    function load_schemadb($schemapath)
    {
        // We were unable to shortcut, so we try to load the schema database now.
        $this->schemadb = midcom_helper_datamanager2_schema::load_database($schemapath);
    }

    /**
     * Uses an already loaded schema database. If you want to load a schema database
     * from disk, use the load_schemadb method instead.
     *
     * @param array &$schemadb The schema database to use, this must be an array of midcom_helper_datamanager2_schema
     *     instances, which is taken by reference.
     * @see load_schemadb()
     */
    function set_schemadb(&$schemadb)
    {
        foreach ($schemadb as $key => $value)
        {
            if (! is_a($value, 'midcom_helper_datamanager2_schema'))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('The database passed was:', $schemadb);
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'An invalid schema database has been passed to the midcom_helper_datamanager2_controller::set_schemadb method.');
                // This will exit.
            }
        }

        $this->schemadb =& $schemadb;
    }

    /**
     * Sets the current datamanager instance to the storage object given, which may either
     * be a MidCOM DBA object (which is encapsulated by a midgard datamanager storage instance).
     *
     * You must load a schema database before actually
     *
     * @param object &$storage A reference to either an initialized datamanager, an initialized
     *     storage backend or to a DBA compatible class instance.
     * @param string $schema This is an optional schema name that should be used to edit the
     *     storage object. If it is null, the controller will try to autodetect the schema
     *     to use by using the datamanager's autoset_storage interface.
     */
    function set_storage(&$storage, $schema = null)
    {
        if (count($this->schemadb) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You cannot set a storage object for a DM2 controller object without loading a schema database previously.');
            // This will exit.
        }

        if (is_a($storage, 'midcom_helper_datamanager2_datamanager'))
        {
            $this->datamanager =& $storage;
        }
        else if (   is_a($storage, 'midcom_helper_datamanager2_storage')
                 || $_MIDCOM->dbclassloader->is_midcom_db_object($storage))
        {
            $this->datamanager = new midcom_helper_datamanager2_datamanager($this->schemadb);
            if ($schema === null)
            {
                if (! $this->datamanager->autoset_storage($storage))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_print_r('We got this storage object:', $storage);
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        'Failed to automatically create a datamanager instance for a storage object or a MidCOM type. See the debug level log for more information.');
                    // This will exit().
                }
            }
            else
            {
                if (! $this->datamanager->set_schema($schema))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Tried to set the schema {$schema}");
                    debug_print_r('We got this storage object:', $storage);
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        'Failed to set the autocreated datamanager\'s schema. See the debug level log for more information.');
                    // This will exit().
                }
                if (! $this->datamanager->set_storage($storage))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Tried to set the schema {$schema}");
                    debug_print_r('We got this storage object:', $storage);
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                        'Failed to set the autocreated datamanager\'s storage object. See the debug level log for more information.');
                    // This will exit().
                }
            }
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Storage object passed was:', $storage);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'You must pass either a datamanager subclass, an initialized storage encapsulation or a MidCOM DBA object to datamanager2_controller::set_storage()');
            // This will exit.
        }
    }

    /**
     * This is a static factory method which lets you dynamically create controller instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * On any error (class not found etc.) the factory method will call generate_error.
     *
     * <b>This function must be called statically.</b>
     *
     * @param string $type The type of the controller (the file name from the controller directory).
     * @return midcom_helper_datamanager2_controller A reference to the newly created controller instance.
     * @static
     */
    function & create($type)
    {
        $filename = MIDCOM_ROOT . "/midcom/helper/datamanager2/controller/{$type}.php";
        $classname = "midcom_helper_datamanager2_controller_{$type}";
        require_once($filename);
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }

    /**
     * This function should process the form data sent to the server. Its behavior is dependant
     * on the controller used, see the individual class documentations for details.
     *
     * @return string The exitcode of the form processing, usually related to the formmanager
     *     result constants.
     */
    function process_form()
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be implemented in subclasses.');
    }

    /**
     * This function invokes the display_form() hook on the form manager class.
     */
    function display_form()
    {
        if (midcom_helper_datamanager2_controller::is_locked($this->datamanager->storage->object, $this->lock_timeout))
        {
            $this->show_remove_lock();
            return;
        }
        
        $this->formmanager->display_form();
    }
    
    /**
     * Show the lock status
     * 
     * @access public
     */
    function show_remove_lock()
    {
        if (   function_exists('mgd_is_element_loaded')
            && mgd_is_element_loaded('midcom_helper_datamanager2_remove_lock'))
        {
            mgd_show_element('midcom_helper_datamanager2_remove_lock');
        }
        else
        {
            $user = $this->datamanager->storage->object->get_parameter('midcom.helper.datamanager2.lock', 'user');
            $expires = strtotime($this->datamanager->storage->object->get_parameter('midcom.helper.datamanager2.lock', 'expires'));
            
            $person = new midcom_db_person($user);
            ?>
                <div class="midcom_helper_datamanager2_remove_lock">
                    <h2><?php echo $this->_l10n->get('object locked'); ?></h2>
                    <p>
                        <?php echo sprintf($this->_l10n->get('this object was locked by %s'), $person->name); ?>.
                        <?php echo sprintf($this->_l10n->get('lock will expire on %s'), strftime('%x %X', $expires)); ?>.
                    </p>
            <?php
            if ($_MIDCOM->auth->can_user_do('midcom.helper.datamanager2:break_lock', null, 'midcom_helper_datamanager2_controller', 'midcom.helper.datamanager2'))
            {
                echo "<form method=\"post\">\n";
                echo "    <p class=\"break_lock\">\n";
                echo "        <input type=\"hidden\" name=\"midcom_helper_datamanager2_object\" value=\"{$this->datamanager->storage->object->guid}\" />\n";
                echo "        <input type=\"submit\" name=\"midcom_helper_datamanager2_remove_lock\" value=\"" . $this->_l10n->get('break the lock') . "\" class=\"break_lock\" />\n";
                echo "    </p>\n";
                echo "</form>\n";
            }
            ?>
                </div>
            <?php
        }
    }
    
    /**
     * Check if the object is being edited elsewhere
     * 
     * @static
     * @access public
     * @param mixed $guid      Object or GUID of the object
     * @return boolean         True if the object is locked
     */
    function is_locked($target, $timeout = 0)
    {
        // Remove the object lock if applicable
        if (   isset($_REQUEST['midcom_helper_datamanager2_remove_lock'])
            && isset($_REQUEST['midcom_helper_datamanager2_object']))
        {
            // Get the object
            $object = $_MIDCOM->dbfactory->get_object_by_guid($_POST['midcom_helper_datamanager2_object']);
            
            // Remove the lock, if permission is granted
            if ($_MIDCOM->auth->can_user_do('midcom.helper.datamanager2:break_lock', null, 'midcom_helper_datamanager2_controller', 'midcom.helper.datamanager2'))
            {
                midcom_helper_datamanager2_controller::set_lock($object, $timeout);
            }
            else
            {
                $_MIDCOM->uimessages->add($_MIDCOM->i18n->get_string('midcom.helper.datamanager2', 'midcom.helper.datamanager2'), $_MIDCOM->i18n->get_string('permission denied', 'midcom'), 'error');
            }
        }
        
        // Check that we have an object at disposal
        if (is_object($target))
        {
            $object =& $target;
        }
        else
        {
            $object = $_MIDCOM->dbfactory->get_object_by_guid($target);
        }
        
        // Couldn't get the object. Different error handling here? Controller should have already alarmed the user, though...
        if (   !$object
            || !$object->guid)
        {
            return false;
        }
        
        $expires = (int) strtotime($object->get_parameter('midcom.helper.datamanager2.lock', 'expires'));
        
        // Object not locked, allow editing
        if (!$expires)
        {
            return false;
        }
        
        // Get the person who locked the object
        $user = $object->get_parameter('midcom.helper.datamanager2.lock', 'user');
        
        // Lock was created by the user, allow unquestionable editing
        if (   isset($_MIDCOM->auth->user)
            && isset($_MIDCOM->auth->user->guid)
            && $user === $_MIDCOM->auth->user->guid)
        {
            return false;
        }
        
        // Object lock is no longer valid
        if (time() > $expires)
        {
            return false;
        }
        
        // Lock checked, object locked
        return true;
    }
    
    /**
     * Set the object lock
     * 
     * @static
     * @access public
     * @param mixed $object    Object that should be locked
     * @param int $timeout     Length of the lock timeout
     */
    function set_lock($object, $timeout)
    {
        if ((int) $timeout < 1)
        {
            $object->set_parameter('midcom.helper.datamanager2.lock', 'user', '');
            $object->set_parameter('midcom.helper.datamanager2.lock', 'expires', '');
            return true;
        }
        
        $object->set_parameter('midcom.helper.datamanager2.lock', 'user', $_MIDCOM->auth->user->guid);
        $object->set_parameter('midcom.helper.datamanager2.lock', 'expires', strftime('%Y-%m-%d %H:%M:%S', time() + $timeout));
        
        return true;
    }
}
?>