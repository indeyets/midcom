<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:dbclassloader.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class acts as an automated way of generating MidCOM level wrapper
 * classes for MgdSchema based database objects.
 *
 * <b>How to write database class definitions:</b>
 *
 * The class basically takes a list of class metadata declarations and transforms
 * them into stub classes usable within MidCOM.
 *
 * The classes are generated and loaded automatically on every change, you do not
 * have to worry about this.
 *
 * The general idea behind this loader is to provide MidCOM with a way to hook into
 * every database interaction between the component and the Midgard core. In MidCOM
 * versions released after 2.6.x direct access to the Midgard Database will be prohibited.
 *
 * Since PHP does not allow for multiple inheritance (which would be really useful here),
 * a code-generator is needed, which automatically generates intermediated classes between
 * the class you actually use in your component and the original MgdSchema class.
 *
 * For example, if you have the MgdSchema type midgard_article and the MidCOM article
 * base class called midcom_baseclasses_database_article, the schema automatically generates
 * an intermediate class called __midcom_baseclasses_database_article. The intermediate
 * class inherits from the MgdSchema class, while your class inherits from the intermediate
 * class.
 *
 * The class loader does not require much information when generating the intermediate classes:
 * An example declaration looks like this:
 *
 * <code>
 * Array
 * (
 *     'table' => 'article',
 *     'old_class_name' => 'MidgardArticle',
 *     'new_class_name' => 'midgard_article',
 *     'midcom_class_name' => 'midcom_baseclasses_database_article'
 * )
 * </code>
 *
 * As for the parameters:
 *
 * <i>table</i> denotes the database table that is use to store
 * this class. This is a compatibility value that will be deprecated on the long run but is
 * necessary for now to get a clean transition between legacy Midgard and MgdSchema. The
 * argument is checked for basic sanity (basically, only alphanumeric characters, underscores
 * and dashes are allowed).
 *
 * <i>old_class_name</i> is a compatibility value as well, and should take the name of the
 * original Midgard class that was used to work, with this type. If there is no such type,
 * set it to null which is the default if and only if this key is omitted in the declaration.
 * If specified the class is checked for existence when the file is being loaded.
 *
 * <i>new_class_name</i> is the MgdSchema class name from that you want to use. This argument
 * is mandatory, and the class specified must exist.
 *
 * <i>midcom_class_name</i> this is the name of the MidCOM base class you intend to create.
 * It is checked for basic validity against the PHP restrictions on symbol naming, but the
 * class itself is not checked for existence, naturally, as it is not declared at the time
 * of generating its base class. You <i>must</i> declare the class as listed at all times,
 * as typecasting and -detection is done using this metadata property in the core.
 *
 * As outlined above, the generated class will have two underscores appended to the
 * midcom_class_name you specify.
 *
 * It is possible to specify more than one class in a single class definition file, and it
 * is recommended that you take advantage of this feature for performance reasons:
 *
 * <code>
 * Array
 * (
 *     //...
 * ),
 * Array
 * (
 *     //...
 * ),
 * </code>
 *
 * Place a simple text file with exactly the declarations into the config directory of your
 * component or shared library, and add the files' name to the _autoload_class_definitions
 * member variable in your component interface base class (given that you inherited it of
 * midcom_baseclasses_component_interface as it is strongly recommended).
 *
 * <b>Inherited class requirements</b>
 *
 * The classes you inherit from the intermediate stub classes must at this time satisfy two
 * requirements: The constructor must call the base class constructor and you have to override
 * the get_parent method where applicable:
 *
 * The <i>constructor</i> part is relatively trivial. Consider the article example above, the
 * subclass constructor looks like this:
 *
 * <code>
 * class midcom_baseclasses_database_article
 *     extends __midcom_baseclasses_database_article
 * {
 *     function midcom_baseclasses_database_article($id = null)
 *     {
 *         parent::__midcom_baseclasses_database_article($id);
 *     }
 *
 *     // ...
 * }
 * </code>
 *
 * Be sure to take and pass the $id parameter to the parent class, it will automatically load
 * the object identified by the id <i>or</i> GUID passed.
 *
 * Then there is the (optional) <i>get_parent()</i> method: It is used in various places (for
 * example the ACL system) in MidCOM to find the logical parent of an object. By default this
 * method directly returns null indicating that there is no parent. You should override it
 * wherever you have a tree-like content structure so that MidCOM can correctly climb upwards.
 * If you have a parent only conditionally (e.g. there are root level objects), return NULL to
 * indicate no available parent.
 *
 * For example:
 *
 * <code>
 * class midcom_baseclasses_database_article
 *     extends __midcom_baseclasses_database_article
 * {
 *     // ...
 *
 *     function get_parent()
 *     {
 *         if ($this->up != 0)
 *         {
 *             $parent = new midcom_baseclasses_database_article($this->up);
 *             if (! $parent)
 *             {
 *                 // Handle Error
 *             }
 *         }
 *         else
 *         {
 *             $parent = new midcom_baseclasses_database_topic($this->topic);
 *             if (! $parent)
 *             {
 *                 // Handle Error
 *             }
 *         }
 *         return $parent;
 *     }
 * }
 * </code>
 *
 * As you can see, this is not that hard. The only rule is that you always have to return either
 * null (no parent) or a MidCOM DB type.
 *
 * The recommended way of handling inconsistencies as the ones shown above is to log an error with
 * at least MIDCOM_LOG_INFO and then return null. Depending on your application you could also
 * call generate_error instead, halting execution.
 *
 * <b>Caching</b>
 *
 * The phpscripts cache module is used to cache the created intermediate classes. Cache granularity
 * is per class definition file. We use the domain midcom.dba as namespace.
 *
 * <b>General design considerations and the original basic ideas:</b>
 *
 * http://www.nathan-syntronics.de/midcom-permalink-c77e1952f8079b8ce86be7911a09d750
 *
 * @todo Implement caching
 * @package midcom.services
 */
class midcom_services_dbclassloader extends midcom_baseclasses_core_object
{
    /**
     * Internal version number, this is added to the generated classes for
     * informational purposes only (right now).
     *
     * @var string
     * @access private
     */
    var $_cvs_revision = '$Revision:3765 $';

    /**
     * Temporary variable during class construction, stores the
     * constructed code.
     *
     * @var string
     * @access private
     */
    var $_class_string = '';

    /**
     * The filename of the class definition currently being read.
     *
     * @var string
     * @access private
     */
    var $_class_definition_filename = '';

    /**
     * Temporary variable during class construction, stores the
     * class definition that is currently processed.
     *
     * @var Array
     * @access private
     */
    var $_class_definition = null;

    /**
     * List of all classes which have been loaded. 
     * 
     * This list only contains the class definitions that have been used to 
     * construct  the actual helper classes.
     *
     * @var Array
     * @access private
     */
    var $_loaded_classes = Array();

    /**
     * A mapping storing which component handles which class. 
     * 
     * This is used to ensure that all MidCOM DBA main classes are loaded when 
     * casting  MgdSchema objects to DBA objects. Especially important for the 
     * generic by-GUID object getter.
     *
     * @var Array
     * @access private
     */
    var $_mgdschema_class_handler = Array();

    /**
     * Initializes the class for usage.
     */
    function midcom_services_dbclassloader ()
    {
        parent::midcom_baseclasses_core_object();
    }

    /**
     * This is the main class loader function. It takes a component/filename pair as
     * arguments, the first specifying the place to look for the latter.
     *
     * For example, if you call load_classes('net.nehmer.static', 'my_classes.inc'), it will
     * look in the directory MIDCOM_ROOT/net/nehmer/static/config/my_classes.inc. The magic
     * component 'midcom' goes for the MIDCOM_ROOT/midcom/config directory and is reserved
     * for MidCOM core classes and compatibility classes.
     *
     * If the class definition file is invalid, false is returned.
     *
     * If this function completes successfully, all __xxx classes are loaded and present.
     *
     * @return boolean Indicating success
     */
    function load_classes($component, $filename)
    {
        $cache_identifier = $_MIDCOM->cache->phpscripts->create_identifier('midcom.dba', "{$component}-{$filename}");
        $this->_create_class_definition_filename($component, $filename);

        if (! $cache_identifier)
        {
            $cache_hit = false;
        }
        else
        {
            // Check the last modified stamps of both this script and the loaded
            // class definition file to get a hold on all API changes.
            $cache_hit = $_MIDCOM->cache->phpscripts->load
            (
                $cache_identifier,
                filemtime($this->_class_definition_filename),
                filemtime(__FILE__)
            );
        }

        if ($cache_hit)
        {
            //debug_add("We had a cache hit for {$component}/{$filename}.");
            return true;
        }

        $contents = $this->_read_class_definition_file();

        $definition_list = Array();
        $result = eval ("\$definition_list = Array ( {$contents} \n );");
        if ($result === false)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to parse the class definition file '{$this->_class_definition_filename}', see above for PHP errors.");
            // This will exit.
        }

        if (! $this->_validate_class_definition_list($definition_list))
        {
            return false;
        }
        $code = $this->_process_class_definition_list($definition_list, $contents, $component);

        if (! $_MIDCOM->cache->phpscripts->add($cache_identifier, $code))
        {
            debug_push_class(__CLASS__, __FUNCTION);
            debug_add("Could not add the generated classes for {$component}/{$filename} to the PHP script cache.", MIDCOM_LOG_ERROR);
            debug_add('We fall back to direct evaluation to keep MidCOM running.');
            debug_pop();
            eval($code);
        }

        return true;
    }

    /**
     * This helper function validates a class definition list for correctness. 
     * 
     * Any error will be logged and false is returned.
     *
     * Where possible, missing elements are completed with sensible defaults.
     *
     * @param Array $definition_list A reference to the definition list to verify.
     * @return boolean Indicating success
     */
    function _validate_class_definition_list(&$definition_list)
    {
        if (! is_array ($definition_list))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Validation failed: It was no Array.', MIDCOM_LOG_INFO);
            debug_pop();
            return false;
        }

        foreach ($definition_list as $key => $copy)
        {
            // Convenience Reference
            $definition =& $definition_list[$key];

            if (! is_array($definition))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} was no array.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }

            // Validate element count upper limit first, lower limits and defaults are caught by
            // The array_key_exists checks below.
            if (count($definition) > 4)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had too much elements.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }

            if (! array_key_exists('table', $definition))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had no table element.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
            if (preg_match('/^[a-zA-Z0-9_-]+$/', $definition['table']) == 0)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had an invalid table element.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }

            if (! array_key_exists('old_class_name', $definition))
            {
                $definition['old_class_name'] = null;
            }
            if (   ! is_null($definition['old_class_name'])
                && ! class_exists($definition['old_class_name']))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had an invalid old_class_name element.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }

            if (! array_key_exists('new_class_name', $definition))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had no new_class_name element.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
            if (! class_exists($definition['new_class_name']))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had an invalid new_class_name element: {$definition['new_class_name']}. Probably the required MgdSchema is not loaded.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }

            if (! array_key_exists('midcom_class_name', $definition))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had no midcom_class_name element.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $definition['midcom_class_name']) == 0)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Validation failed: Key {$key} had an invalid new_class_name element.", MIDCOM_LOG_INFO);
                debug_pop();
                return false;
            }
        }

        return true;
    }

    /**
     * Little helper which converts a component / filename combination into a fully
     * qualified path/filename. 
     * 
     * The filename is assigned to the $_class_definition_filename member variable of this class.
     *
     * @param string $component The name of the component for which the class file has to be loaded. The path must
     *     resolve with the component loader unless you use 'midcom' to load MidCOM core class definition files.
     */
    function _create_class_definition_filename($component, $filename)
    {
        if ($component == 'midcom')
        {
            $this->_class_definition_filename = MIDCOM_ROOT . "/midcom/config/{$filename}";
        }
        else
        {
            $this->_class_definition_filename = MIDCOM_ROOT . $_MIDCOM->componentloader->path_to_snippetpath($component) . "/config/{$filename}";
        }

    }

    /**
     * This helper function loads a class definition file from the disk and
     * returns its contents. 
     * 
     * The source must be stored in the $_class_definition_filename
     * member.
     * 
     * It will translate component and filename into a full path and delivers
     * the contents verbatim.
     *
     * @return string The contents of the file.
     */
    function _read_class_definition_file()
    {
        if (! file_exists($this->_class_definition_filename))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "DB Class Loader: Failed to access the file {$this->_class_definition_filename}: File does not exist.");
            // This will exit.
        }

        return file_get_contents($this->_class_definition_filename);
    }

    /**
     * Process an entire array of class definitions, collect the generated listings and return
     * a complete PHP file string for all classes in the list.
     *
     * @param Array $definition_list A list of classes to be defined.
     * @param string $definition_source The source which has been parsed to the definition list.
     *     (Used to cache the loaded class registrations as well.)
     * @param string $component The name of the component that is assigned to handle the
     *     classes.
     * @return string The code for these classes surrounded by php opening and closing tags.
     */
    function _process_class_definition_list($definition_list, $definition_source, $component)
    {
        $result = '';

        foreach ($definition_list as $definition)
        {
            $result .= $this->_generate_class($definition);
        }

        $result .= $this->_write_class_definition_registration($definition_source, $component);

        return $result;
    }

    /**
     * Generates a small block at the end of the script which registers the original definitions
     * with ourselves, so that we don't have to eval this all the time.
     *
     * @param string $definition_source The source which has been parsed to the definition list.
     * @param string $component The name of the component that is assigned to handle the
     *     classes.
     * @return string The generated registration code.
     * @access private
     */
    function _write_class_definition_registration($definition_source, $component)
    {
        $result = <<<EOF

// Register all classes which have been loaded with this file.

\$_MIDCOM->dbclassloader->_register_loaded_classes
(
    Array
    (
        'component' => '{$component}',
        'definition' => Array
        (
            {$definition_source}
        ),
    )
);

EOF;
        return $result;
    }

    /**
     * Simple helper that adds a list of classes to the loaded classes listing.
     * 
     * This function is called from the cached files, which contain a copy of the
     * class definition at their bottom to avoid eval'ing it again at each run.
     *
     * This does also create a mapping of which class is handled by which component.
     * The generic by-GUID loader and the class conversion tools in the dbfactory
     * require this information to be able to load the required components on-demand.
     *
     * @param Array &$data The list of classes which have been loaded along with the metainformation.
     */
    function _register_loaded_classes($data)
    {
        $this->_loaded_classes = array_merge($this->_loaded_classes, $data['definition']);
        foreach($data['definition'] as $entry)
        {
            $this->_mgdschema_class_handler[$entry['midcom_class_name']] = $data['component'];
        }
    }

    /**
     * Generates a complete class out of the definition passed to the method.
     *
     * @param Array $definition The class definition to use.
     * @return string The generated class without any php opening/closing tags.
     * @access private
     */
    function _generate_class($definition)
    {
        $this->_class_string = '';
        $this->_class_definition = $definition;
        $this->_write_header();
        $this->_write_class();
        $this->_write_footer();
        $result = $this->_class_string;
        $this->_class_string = '';
        $this->_class_definition = null;
        return $result;
    }

    /**
     * Writes the actual class definition, uses some helpers for this.
     *
     * @access private
     */
    function _write_class()
    {
        // We first produce the class header
        $this->_class_string .= "class __{$this->_class_definition['midcom_class_name']} extends {$this->_class_definition['new_class_name']}\n";
        $this->_class_string .= "{\n";
        $this->_class_string .= "    \n";

        // This includes the meta __blah__ properties related to this class builder.
        $this->_write_meta_members();

        // Write the class' constructor
        $this->_write_constructor();

        // We now add all event handlers first.
        $this->_write_event_handlers();

        // This dumps the various __exec handler functions.
        $this->_write_exec_handlers();

        // Write main API
        $this->_write_main_api();

        // Close the class.
        $this->_class_string .= "}\n\n";
    }

    /**
     * Helper, writes the constructor to the class.
     *
     * @access private
     */
    function _write_constructor()
    {
        $this->_class_string .= <<<EOF
    function __construct(\$id = null)
    {
        \$_id = null;
        
        if(is_object(\$id))
        {
            \$_id = \$id->guid;
        }
        else
        {
            \$_id = \$id;
        }

        try
        {
            parent::__construct(\$_id);
        }
        catch(midgard_error_exception \$e)
        {
            \$x =& \$this; 
            \$x = false; 
            return false; 
        }
          
        if (  \$this->guid
            && mgd_is_guid(\$this->guid))
        {
            midcom_baseclasses_core_dbobject::post_db_load_checks(\$this);
        }
    }
EOF;
        $this->_class_string .= "\n    \n";
    }

    /**
     * Helper, writes the main API to the class.
     *
     * @access private
     */
    function _write_main_api()
    {
        $this->_class_string .= <<<EOF
    // Main API
    function create() { return midcom_baseclasses_core_dbobject::create(\$this); }
    function create_attachment(\$name, \$title, \$mimetype) { return midcom_baseclasses_core_dbobject::create_attachment(\$this, \$name, \$title, \$mimetype); }
    function create_new_privilege_object(\$privilege, \$assignee = null, \$value = MIDCOM_PRIVILEGE_ALLOW, \$classname = '') { return midcom_baseclasses_core_dbobject::create_new_privilege_object(\$this, \$privilege, \$assignee, \$value, \$classname); }
    function delete() { return midcom_baseclasses_core_dbobject::delete(\$this); }
    function delete_attachment(\$name) { return midcom_baseclasses_core_dbobject::delete_attachment(\$this, \$name); }
    function delete_parameter(\$domain, \$name) { return midcom_baseclasses_core_dbobject::delete_parameter(\$this, \$domain, \$name); }
    function delete_tree() { return midcom_baseclasses_core_dbobject::delete_tree(\$this); }
    function get_attachment(\$name) { return midcom_baseclasses_core_dbobject::get_attachment(\$this, \$name); }
    function get_attachment_qb() { return midcom_baseclasses_core_dbobject::get_attachment_qb(\$this); }
    function get_by_guid(\$guid) { return midcom_baseclasses_core_dbobject::get_by_guid(\$this, \$guid); }
    function get_by_id(\$id) { return midcom_baseclasses_core_dbobject::get_by_id(\$this, \$id); }
    function get_by_path(\$path) { return midcom_baseclasses_core_dbobject::get_by_path(\$this, \$path); }  
    /* function get_by_path(\$path) { return parent::get_by_path(\$this, \$path); } */
    function & get_metadata() { return midcom_baseclasses_core_dbobject::get_metadata(\$this); }
    function get_parameter(\$domain, \$name) { return midcom_baseclasses_core_dbobject::get_parameter(\$this, \$domain, \$name); }
    function get_parent() { return midcom_baseclasses_core_dbobject::get_parent(\$this); }
    function get_parent_guid() { return midcom_baseclasses_core_dbobject::get_parent_guid(\$this); }
    function get_privilege(\$privilege, \$assignee, \$classname = '') { return midcom_baseclasses_core_dbobject::get_privilege(\$this, \$privilege, \$assignee, \$classname); }
    function get_privileges() { return midcom_baseclasses_core_dbobject::get_privileges(\$this); }
    function is_object_visible_onsite() { return midcom_baseclasses_core_dbobject::is_object_visible_onsite(\$this); }
    function is_owner(\$person = null) { return midcom_baseclasses_core_dbobject::is_owner(\$this, \$person); }
    function list_attachments() { return midcom_baseclasses_core_dbobject::list_attachments(\$this); }
    function list_parameters(\$domain = null) { return midcom_baseclasses_core_dbobject::list_parameters(\$this, \$domain); }
    static function new_query_builder() { return \$_MIDCOM->dbfactory->new_query_builder('{$this->_class_definition['midcom_class_name']}'); }
    static function new_collector(\$domain, \$value) { return \$_MIDCOM->dbfactory->new_collector('{$this->_class_definition['midcom_class_name']}', \$domain, \$value); }
    function open_attachment(\$name, \$mode = 'w') { return midcom_baseclasses_core_dbobject::open_attachment(\$this, \$name, \$mode); }
    function refresh() { return midcom_baseclasses_core_dbobject::refresh(\$this); }
    function set_parameter(\$domain, \$name, \$value) { return midcom_baseclasses_core_dbobject::set_parameter(\$this, \$domain, \$name, \$value); }
    function set_privilege(\$privilege, \$assignee = null, \$value = MIDCOM_PRIVILEGE_ALLOW, \$classname = '') { return midcom_baseclasses_core_dbobject::set_privilege(\$this, \$privilege, \$assignee, \$value, \$classname); }
    function unset_privilege(\$privilege, \$assignee = null, \$classname = '') { return midcom_baseclasses_core_dbobject::unset_privilege(\$this, \$privilege, \$assignee, \$classname); }
    function unset_all_privileges() { return midcom_baseclasses_core_dbobject::unset_all_privileges(\$this); }
    function update() { return midcom_baseclasses_core_dbobject::update(\$this); }

    // Legacy API
    function guid() { return \$this->guid; }
    function createattachment(\$name, \$title, \$mimetype) { return midcom_baseclasses_core_dbobject::createattachment(\$this, \$name, \$title, \$mimetype); }
    function deleteattachment(\$name) { return midcom_baseclasses_core_dbobject::delete_attachment(\$this, \$name); }
    function getattachment(\$name) { return midcom_baseclasses_core_dbobject::get_attachment(\$this, \$name); }
    function listattachments() { return midcom_baseclasses_core_dbobject::listattachments(\$this); }
    function openattachment(\$name, \$mode = 'w') { return midcom_baseclasses_core_dbobject::open_attachment(\$this, \$name, \$mode); }
    function parameter(\$domain, \$name)
    {
        if (func_num_args() == 2)
        {
            return \$this->get_parameter(\$domain, \$name);
        }
        else
        {
            \$value = func_get_arg(2);
            if (   \$value === false
                || \$value === null
                || \$value === '')
            {
                return \$this->delete_parameter(\$domain, \$name);
            }
            else
            {
                return \$this->set_parameter(\$domain, \$name, \$value);
            }
        }
    }
    function _parent_parameter(\$domain, \$name)
    {
        if (func_num_args() == 2)
        {
            return parent::parameter(\$domain, \$name);
        }
        else
        {
            \$value = func_get_arg(2);
            return parent::parameter(\$domain, \$name, \$value);
        }
    }

    // ACL Shortcuts
    function can_do(\$privilege, \$user = null) { return \$_MIDCOM->auth->can_do(\$privilege, \$this, \$user); }
    function can_user_do(\$privilege, \$user = null) { return \$_MIDCOM->auth->can_user_do(\$privilege, \$user, '{$this->_class_definition["midcom_class_name"]}'); }
    function require_do(\$privilege, \$message = null) { \$_MIDCOM->auth->require_do(\$privilege, \$this, \$message); }
    function require_user_do(\$privilege, \$message = null) { \$_MIDCOM->auth->require_user_do(\$privilege, \$message, '{$this->_class_definition["midcom_class_name"]}'); }

    // DBA API
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array(),
            'ANONYMOUS' => Array(),
            'USERS' => Array()
        );
    }
    function get_parent_guid_uncached()
    {
EOF;
        $reflector = new midgard_reflection_property($this->_class_definition['new_class_name']);
        $up_property = midgard_object_class::get_property_up($this->_class_definition['new_class_name']);
        if (!empty($up_property))
        {
            $target_property = $reflector->get_link_target($up_property);
            /**
             * Taken out from the generated code as this will cause infinite loop in ACL resolving, using direct QB in stead
             * (when instantiating the parent ACLs will be checked in any case)
             *
            \$mc = {$this->_class_definition['midcom_class_name']}::new_collector('{$target_property}', \$this->{$up_property});
            */
            $this->_class_string .= "\n";
            $this->_class_string .= <<<EOF
        // Up takes precedence over parent
        if (!empty(\$this->{$up_property}))
        {
            \$mc = new midgard_collector('{$this->_class_definition['new_class_name']}', '{$target_property}', \$this->{$up_property});
            \$mc->set_key_property('guid');
            \$mc->execute();
            \$guids = \$mc->list_keys();
            if (!is_array(\$guids))
            {
                unset(\$mc, \$guids);
                return null;
            }
            list (\$parent_guid, \$dummy) = each(\$guids);
            unset(\$mc, \$guids, \$dummy);
            return \$parent_guid;
        }
EOF;
        }
        $parent_property = midgard_object_class::get_property_parent($this->_class_definition['new_class_name']);
        if (!empty($parent_property))
        {
            $target_property = $reflector->get_link_target($parent_property);
            $target_class = $reflector->get_link_name($parent_property);
            /**
             * Taken out from the generated code as this will cause infinite loop in ACL resolving, using direct QB in stead
             * (when instantiating the parent ACLs will be checked in any case)
             *
            \$dummy_object = new {$target_class}();
            \$midcom_dba_classname = \$_MIDCOM->dbclassloader->get_midcom_class_name_for_mgdschema_object(\$dummy_object);
            if (empty(\$midcom_dba_classname))
            {
                return null;
            }
            \$mc = call_user_func(array(\$midcom_dba_classname, 'new_collector'), array(\$target_property, \$this->\$parent_property));
            */
            $this->_class_string .= "\n";
            $this->_class_string .= <<<EOF
        if (!empty(\$this->{$parent_property}))
        {
            \$mc = new midgard_collector('{$target_class}', '{$target_property}', \$this->{$parent_property});
            \$mc->set_key_property('guid');
            \$mc->execute();
            \$guids = \$mc->list_keys();
            if (!is_array(\$guids))
            {
                unset(\$mc, \$guids);
                return null;
            }
            list (\$parent_guid, \$dummy) = each(\$guids);
            unset(\$mc, \$guids, \$dummy);
            return \$parent_guid;
        }
EOF;
        }

        $this->_class_string .= "\n";
        $this->_class_string .= <<<EOF
        return null;
    }
    function get_parent_guid_uncached_static(\$object_guid)
    {
EOF;
        $reflector = new midgard_reflection_property($this->_class_definition['new_class_name']);
        $up_property = midgard_object_class::get_property_up($this->_class_definition['new_class_name']);
        if (!empty($up_property))
        {
            $target_property = $reflector->get_link_target($up_property);
            $this->_class_string .= "\n";
            $this->_class_string .= <<<EOF
        // Up takes precedence over parent
        \$mc = new midgard_collector('{$this->_class_definition['new_class_name']}', 'guid', \$object_guid);
        \$mc->set_key_property('{$up_property}');
        \$mc->execute();
        \$link_values = \$mc->list_keys();
        if (!empty(\$link_values))
        {
            list (\$link_value, \$dummy) = each(\$link_values);
            unset(\$mc, \$link_values, \$dummy);
            if (!empty(\$link_value))
            {
                \$mc2 = new midgard_collector('{$this->_class_definition['new_class_name']}', '{$target_property}', \$link_value);
                \$mc2->set_key_property('guid');
                \$mc2->execute();
                \$guids = \$mc2->list_keys();
                if (!is_array(\$guids))
                {
                    unset(\$mc2, \$guids, \$link_value);
                    return null;
                }
                list (\$parent_guid, \$dummy) = each(\$guids);
                unset(\$mc2, \$guids, \$link_value, \$dummy);
                return \$parent_guid;
            }
            else
            {
                unset(\$mc2, \$guids, \$link_value, \$dummy);
            }
        }
        else
        {
            unset(\$mc, \$link_values);
        }
EOF;
        }

        $parent_property = midgard_object_class::get_property_parent($this->_class_definition['new_class_name']);
        if (!empty($parent_property))
        {
            $target_property = $reflector->get_link_target($parent_property);
            $target_class = $reflector->get_link_name($parent_property);
            $this->_class_string .= "\n";
            $this->_class_string .= <<<EOF
        \$mc = new midgard_collector('{$this->_class_definition['new_class_name']}', 'guid', \$object_guid);
        \$mc->set_key_property('{$parent_property}');
        \$mc->execute();
        \$link_values = \$mc->list_keys();
        if (!empty(\$link_values))
        {
            list (\$link_value, \$dummy) = each(\$link_values);
            unset(\$mc, \$link_values, \$dummy);
            if (!empty(\$link_value))
            {
                \$mc2 = new midgard_collector('{$target_class}', '{$target_property}', \$link_value);
                \$mc2->set_key_property('guid');
                \$mc2->execute();
                \$guids = \$mc2->list_keys();
                if (!is_array(\$guids))
                {
                    unset(\$mc2, \$guids, \$link_value);
                    return null;
                }
                list (\$parent_guid, \$dummy) = each(\$guids);
                unset(\$mc2, \$guids, \$link_value, \$dummy);
                return \$parent_guid;
            }
            else
            {
                unset(\$mc2, \$guids, \$link_value, \$dummy);
            }
        }
        else
        {
            unset(\$mc, \$link_values);
        }
EOF;
        }

        $this->_class_string .= "\n";
        $this->_class_string .= <<<EOF
        return null;
    }
    function get_dba_parent_class()
    {
        // TODO: Try to figure this out via reflection (NOTE: this must return a midcom DBA class...)
        return null;
    }

    // PEAR API mapping
    function isError (\$data, \$msgcode = null) { return PEAR::isError(\$data, \$msgcode); }
    function raiseError(\$message = null, \$code = null, \$mode = null, \$options = null, \$userinfo = null, \$error_class = null, \$skipmsg = false)
    {
        return PEAR::raiseError(\$message, \$code, \$mode, \$options, \$userinfo, \$error_class, \$skipmsg);
    }

    // Private API
    function _load_from_database(\$id) { return midcom_baseclasses_core_dbobject::load(\$this, \$id); }
EOF;
        $this->_class_string .= "\n    \n";
    }

    /**
     * Helper, writes the exec handlers to the class.
      *
      * @access private
      */
    function _write_exec_handlers()
    {
        $this->_class_string .= <<<EOF
    // Exec handlers
    function __exec_create() { return @parent::create(); }
    function __exec_update() { return @parent::update(); }
    function __exec_delete() { return @parent::delete(); }
    function __exec_get_by_id(\$id) { return parent::get_by_id(\$id); }
    function __exec_get_by_guid(\$guid) { return parent::get_by_guid(\$guid); }
    function __exec_get_by_path(\$path) { return parent::get_by_path(\$path); }

    // Legacy API exec handlers
    function __exec_listattachments() { return parent::listattachments(); }

EOF;
        $this->_class_string .= "\n    \n";
    }

    /**
     * This helper adds all definition properties as __$key__ = '$value' members.
     * Objects and arrays are skipped.
     *
     * Assumes safe contents of $key and $value already.
     *
     * @access private
     */
    function _write_meta_members()
    {
        foreach ($this->_class_definition as $key => $value)
        {
            if (   is_object($value)
                || is_array($value))
            {
                continue;
            }
            else if (is_null($value))
            {
                $this->_class_string .= "    var \$__{$key}__ = null;\n";
            }
            else if (is_bool($value))
            {
                if ($value)
                {
                    $this->_class_string .= "    var \$__{$key}__ = true;\n";
                }
                else
                {
                    $this->_class_string .= "    var \$__{$key}__ = false;\n";
                }
            }
            else
            {
                $this->_class_string .= "    var \$__{$key}__ = '{$value}';\n";
            }
        }

        // Add the generator metadata revision
        $revision = substr($this->_cvs_revision, 11, -2);
        $this->_class_string .= "    var \$__midcom_generator__ = 'midcom_services_dbclassloader';\n";
        $this->_class_string .= "    var \$__midcom_generator_version__ = '{$revision}';\n";

        $this->_class_string .= "    \n";
    }

    /**
     * Helper, writes the event handlers to the class.
     *
     * @access private
     */
    function _write_event_handlers()
    {
        $this->_class_string .= <<<EOF
    // Event handlers
    function _on_created() {}
    function _on_creating() { return true; }
    function _on_deleted() {}
    function _on_deleting() { return true; }
    function _on_loaded() { return true; }
    function _on_prepare_exec_query_builder(&\$qb) { return true; }
    function _on_prepare_new_query_builder(&\$qb) {}
    function _on_process_query_result(&\$result) {}
    function _on_prepare_new_collector(&\$mc) {}
    function _on_prepare_exec_collector(&\$mc) { return true; }
    function _on_process_collector_result(&\$result) {}
    function _on_updated() {}
    function _on_updating() { return true; }
    function _on_imported() {}
    function _on_importing() { return true; }

    // functions related to the rcs service.
    var \$_use_rcs = true;
    var \$_rcs_message = false;
    function disable_rcs() { \$this->_use_rcs = false; }
    function enable_rcs() { \$this->_use_rcs  = true; }
    function set_rcs_message(\$msg) { \$this->_rcs_message = \$msg; }
    function get_rcs_message() { return \$this->_rcs_message; }



EOF;
        $this->_class_string .= "\n    \n";
    }

    /**
     * Writes the header to the class, this includes a dump of the
     * definition used and the generation timestamp.
     *
     * @access private
     */
    function _write_header()
    {
        $this->_class_string .= "/* Autogenerated MidCOM Database Interface Class\n";
        $this->_class_string .= " *\n";
        $this->_class_string .= " * Description used:\n";
        foreach ($this->_class_definition as $key => $value)
        {
            $this->_class_string .= " * {$key} => {$value}\n";
        }
        $this->_class_string .= " *\n";
        $timestamp = time();
        $this->_class_string .= ' * File created: ' . gmstrftime('%Y-%m-%dT%T GMT', $timestamp) . " ({$timestamp})\n";
        $this->_class_string .= " */\n\n";
    }

    /**
     * Writes the footer to the class, currently empty.
     *
     * @access private
     */
    function _write_footer()
    {
        // Nothing to do yet.
    }

    /**
     * Returns a list of loaded classes that operate on the given table name. The
     * results are returned in the order the classes were registered, so it is rather
     * arbitrary. The only behavior that can usually be counted on is to get the
     * MidCOM core baseclasses first, as they are already registered during framework
     * startup. Especially the wrappers for the legacy MidgardXXX Classes are the very
     * first classes registered.
     *
     * @param string $tablename The table to look up.
     * @return Array List of class definitions that match the given table.
     */
    function get_classes_for_table($tablename)
    {
        $result = Array();
        foreach ($this->_loaded_classes as $class_definition)
        {
            if ($class_definition['table'] == $tablename)
            {
                $result[] = $class_definition;
            }
        }
        return $result;
    }

    /**
     * Simple helper to check whether we are dealing with a legacy midgard object
     * or a subclass thereof.
     *
     * @param object &$object The object to check
     * @return boolean true if this is a Legacy Midgard object, false otherwise.
     */
    function is_legacy_midgard_object(&$object)
    {
        /* TODO, remove this method and all its references */
        $classname = get_class($object);    
        foreach ($this->_loaded_classes as $class_definition)
        {
            if (is_a($object, $class_definition['old_class_name']))
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Get a MidCOM DB class name for a legacy Midgard class.
     *
     * @param object $classname The class to check
     * @return string The corresponding MidCOM DB class name, false otherwise.
     */
    function get_midcom_class_name_for_legacy_midgard_class($classname)
    {
        foreach ($this->_loaded_classes as $class_definition)
        {
            if ($classname == $class_definition['new_class_name'])
            {
                return $class_definition['midcom_class_name'];
            }
        }
        
        // We don't have the class loaded, try to load it
        if ($this->load_component_for_class($classname))
        {
            foreach ($this->_loaded_classes as $class_definition)
            {
                if ($classname == $class_definition['new_class_name'])
                {
                    return $class_definition['midcom_class_name'];
                }
            }
        }

        return false;
    }

    /**
     * Get a MidCOM DB class name for a legacy Midgard Object.
     *
     * @param object &$object The object to check
     * @return string The corresponding MidCOM DB class name, false otherwise.
     */
    function get_midcom_class_name_for_legacy_midgard_object(&$object)
    {
        $classname = get_class($object);
        foreach ($this->_loaded_classes as $class_definition)
        {
            if (is_a($object, $class_definition['old_class_name']))
            {
                return $class_definition['midcom_class_name'];
            }
        }
        
        // We don't have the class loaded, try to load it
        if ($this->load_component_for_class($classname))
        {
            foreach ($this->_loaded_classes as $class_definition)
            {
                if (is_a($object, $class_definition['old_class_name']))
                {
                    return $class_definition['midcom_class_name'];
                }
            }
        }

        return false;
    }

    /**
     * Simple helper to check whether we are dealing with a MgdSchema object
     * or a subclass thereof.
     *
     * @param object &$object The object to check
     * @return boolean true if this is a MgdSchema object, false otherwise.
     */
    function is_mgdschema_object(&$object)
    {
        $classname = get_class($object);
        
        foreach ($this->_loaded_classes as $class_definition)
        {
            if (is_a($object, $class_definition['new_class_name']))
            {
                return true;
            }
        }

        // We might not have the class loaded, try to load it
        $this->load_component_for_class($classname);
        foreach ($this->_loaded_classes as $class_definition)
        {
            if (is_a($object, $class_definition['new_class_name']))
            {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Load a component associated with a class name to get its DBA classes defined
     *
     * @param string $classname Class name to load a component for
     * @return boolean true if a component was found for the class, false otherwise
     */
    function load_component_for_class($classname)
    {
        $class_parts = explode('_', $classname);
        $component = '';
        foreach ($class_parts as $part)
        {
            if (empty($component))
            {
                $component = $part;
            }
            else
            {
                $component .= ".{$part}";
            }
            
            // Fix for incorrectly named class
            if ($component == 'net.nehmer.accounts')
            {
                $component = 'net.nehmer.account';
            }
            
            if (   !empty($component)
                && isset($_MIDCOM->componentloader->manifests[$component]))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Loading component {$component} to get DBA class {$classname}.", MIDCOM_LOG_INFO);
                $_MIDCOM->componentloader->load_graceful($component);
                debug_pop();
                return true;
            }
        }
        return false;
    }

    /**
     * Get a MidCOM DB class name for a MgdSchema Object.
     *
     * @param object &$object The object to check
     * @return string The corresponding MidCOM DB class name, false otherwise.
     */
    function get_midcom_class_name_for_mgdschema_object(&$object)
    {
        $classname = get_class($object);
        foreach ($this->_loaded_classes as $class_definition)
        {
            if (is_a($object, $class_definition['new_class_name']))
            {
                return $class_definition['midcom_class_name'];
            }
        }
        
        // We don't have the class loaded, try to load it
        if ($this->load_component_for_class($classname))
        {
            foreach ($this->_loaded_classes as $class_definition)
            {
                if (is_a($object, $class_definition['new_class_name']))
                {
                    return $class_definition['midcom_class_name'];
                }
            }
        }
        
        return false;
    }

    /**
     * This function is required by the DBA interface layer and should normally not be used
     * outside of it.
     *
     * Its purpose is to ensure that the component providing a certain DBA class instance is
     * actually loaded. This is necessary, as the intermediate classes along with the class
     * descriptions are loaded during system startup now, but the full-blown DBA class
     * is not available at that point (for performance reasons). It will load the components
     * in question when requested by any operation in the system that might have to convert
     * to a yet unloaded class, mainly this covers the type conversion of arbitrary objects
     * retrieved by the GUID object getter.
     *
     * @param string $classname The name of the MidCOM DBA class that must be available.
     * @return boolean Indicating success. False is returned only if you are requesting unknown
     *        classes and the like. Component loading failure will result in an HTTP 500, as
     *     always.
     */
    function load_mgdschema_class_handler($classname)
    {
        if (!is_string($classname))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Requested to load the classhandler for class name which is not a string.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if (! array_key_exists($classname, $this->_mgdschema_class_handler))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Requested to load the classhandler for {$classname} which is not known.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $component = $this->_mgdschema_class_handler[$classname];

        if ($component == 'midcom')
        {
            // This is always loaded.
            return true;
        }

        if ($_MIDCOM->componentloader->is_loaded($component))
        {
            // Already loaded, so we're fine too.
            return true;
        }

        // This generate_error's on any problems.
        $_MIDCOM->componentloader->load($component);

        return true;
    }

    /**
     * Simple helper to check whether we are dealing with a MidCOM Database object
     * or a subclass thereof.
     *
     * @param object &$object The object to check
     * @return boolean true if this is a MidCOM Database object, false otherwise.
     */
    function is_midcom_db_object(&$object)
    {
        $classname = get_class($object);
        foreach ($this->_loaded_classes as $class_definition)
        {
            if (is_a($object, $class_definition['midcom_class_name']))
            {
                return true;
            }
        }
        return false;
    }

}

?>