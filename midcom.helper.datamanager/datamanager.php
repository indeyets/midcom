<?php

/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * The main purpose of the Datamanager is to store arbitrary data in an
 * arbitrary Midgard object.
 *
 * The process of data storage and retrieval is completely automated and
 * controlled by the layout definition used for the object in question.
 *
 * It also provides a full form builder infrastructure which completely automates
 * both form handling and data output generation.
 *
 * <b>Layout database definition:</b>
 *
 * The following schema description uses a more-or-less BNF compatible syntax to
 * describe the structure of the layout schema Array.
 *
 * A layout database is a collection of layout definitions. The first layout will
 * be used as a default if the object in question has no layout specified. This is
 * the data structure that gets loaded by the Data Manager upon object creation.
 * The key for this array is used for indexing
 *
 * <pre>
 * &lt;layout database&gt; :== Array (
 *     &lt;layout line&gt;
 *     [ , &lt;layout line&gt; ... ]
 * );
 * &lt;layout line&gt; ::= &lt;layout name&gt; =&gt; &lt;layout definition&gt;,
 * &lt;layout name&gt; ::= &lt;string&gt;
 * &lt;string&gt; ::= Any PHP String
 * </pre>
 *
 * Each layout again consists of a descriptive string and the list of data
 * fields indexed by their name.
 *
 * <pre>
 * &lt;layout defintion&gt; ::= Array (
 *     "description" =&gt; &lt;string&gt;
 *     [, "locktimeout" =&gt; &lt;integer&gt; ]
 *     [, "lockoverride" =&gt; "all" | "poweruser" | "admin" ]
 *     [, "l10n_db" =&gt; &lt;string&gt; ]
 *     [, "save_text" =&gt; &lt;string&gt; ]
 *     [, "cancel_text" =&gt; &lt;string&gt; ]
 *     , "fields" =&gt;  &lt;field list&gt;
 * )
 *
 * &lt;field list&gt; ::= Array ( &lt;field name&gt; =&gt; &lt;field definition&gt;
 *   [ , &lt;field name&gt; =&gt; &lt;field definition&gt; ... ] )
 *
 * &lt;field name&gt; ::= [a-zA-Z0-9]*
 * </pre>
 *
 * The locktimeout directive will override the global article lock timeout of
 * 60 minutes and sets it to the specified amount in minutes. The lockoverride
 * in turn will specify who will be able to override an existing lock; all means
 * all users, powerusers includes only powerusers and admins, while admin will
 * restrict it to sg admins only. If you set the lock timeout to 0, you will
 * disable the locking system.
 *
 * The field definition is the core of the magic. Here you define the behaviour
 * of each field you want to store. "name" is used as an array index for the data
 * retrieval array, "description" is used in the generator logic as field name.
 * The datatype specifies the name of the object that manages the data. Optional
 * fields include the name of the widget used in the data/form generation logic.
 * The optional field "location" specifies an explicit requirement where to save
 * the field. The data manager is currently unable to verify this so make sure
 * that the destination you specify here can take the data of the datatype you
 * want to store. See the docs of the individual datatypes for further reference.
 *
 * The object names given for datatype will be prefixed with "midcom_helper_
 * datamanager_datatype_", while the widget object names use "midcom_helper_
 * datamanager_widget_" as prefix. If you want custom datatypes or widgets not
 * given by MidCOM, prefix them with "custom_", i.e. "midcom_helper_datamanager_
 * datatype_custom_mydatatype".
 *
 * If the value l10n_db is set, it overrides the default l10n database, possible
 * values are all valid component names, as the default component l10n DB is always
 * used (see below for details). Due to the current schema specification, it is
 * necessary to specify this on a per-schema level. Per-schema-db configuration is
 * not supported at this time.
 *
 * The save_text and cancel_text values will be used as labels for the save and cancel
 * buttons in the datamanager. They are translated using the rules outlined above.
 *
 * <pre>
 * &lt;field definition&gt; :== Array (
 *     "description" =&gt; &lt;string&gt;,
 *     "datatype" =&gt; &lt;object identifier&gt;
 *     [ , "widget" =&gt; &lt;object identifier&gt; ]
 *     [ , "location" =&gt; &lt;storage name&gt; ]
 *     [ , "hidden" =&gt; &lt;boolean&gt; ]
 *     [ , "aisonly" =&gt; &lt;boolean&gt; ]
 *     [ , "readonly" =&gt; &lt;boolean&gt; ]
 *     [ , "default" =&gt; &lt;field value&gt; ]
 *     [ , "required" =&gt; &lt;boolean&gt; ]
 *     [ , "start_fieldgroup" =&gt; &lt;fieldgroup definition&gt; ]
 *     [ , "end_fieldgroup" =&gt; &lt;integer&gt; ]
 *     [ , "config_domain" =&gt; &lt;string&gt; ]
 *     [ , "config_key" =&gt; &lt;string&gt; ]
 *     [ , "helptext" =&gt; &lt;string&gt; ]
 *     [ , &lt;option name&gt; =&gt; &lt;option value&gt; ... ]
 *     [ , "validation" =&gt; &lt;fieldgroup definition&gt; ]
 * )
 *
 * &lt;object identifier&gt; ::= Any valid PHP class name in the namespace of the
 *   Datamanager. (See above-)
 * &lt;storage name&gt; ::=   'parameter' | 'config' | 'attachment'
 *                    | &lt;storage object member name&gt;
 * &lt;storage object field name&gt; ::= Any valid member of the storage object.
 * &lt;option name&gt; ::= &lt;string&gt;
 * &lt;option value&gt; ::= Any valid PHP Datatype
 * &lt;field value&gt; ::= A value compatible to the datatype's value type
 * &lt;boolean&gt; ::= true|false
 * &lt;fieldgroup definition&gt; ::= Array (
 *     "title" =&gt; &lt;string&gt;
 *     [ , "css_group" =&gt; &lt;string&gt; ]
 *     [ , "css_title" =&gt; &lt;string&gt; ]
 * )
 * </pre>
 *
 * Note, that widget and location all have their defaults corresponding to the
 * datatype you use. Also note, that some datatypes (for example the blob
 * datatypes) do not allow you a choice of where to store your data.
 *
 * Note also, that the Datamanager adds another entry into this array internally:
 *
 * <pre>
 *     "name" =&gt; &lt;string&gt;
 * </pre>
 *
 * This is essentially the name of the field, which would not be available if you
 * only have the field definition available, as for example the Datatypes or
 * Widgets do.
 *
 * The special fields hidden and readonly affect the behavior of the form and view
 * generators. Hidden fields are ignored completely, nothing will be displayed
 * either in view- or in form-mode. Readonly fields are displayed in both views,
 * but instead of drawing the widget in form-mode, the datamanager draws the
 * regular view there. Both fields default to FALSE.
 *
 * The field aisonly is a special version of hidden, hiding the respective field
 * only in display_(view|form) calls outside of the AIS content admin. This too
 * defaults to FALSE.
 *
 * The default field is an indication what value should be used, if the field
 * in question is empty. It will automatically be automatically inserted by all
 * datatypes supporting default values upon extracting the fields from the
 * database.
 *
 * Setting the required flag on a field enforces an is_empty check before allowing
 * the user to save the object in question. The is_empty method is implemented on
 * a per-datatype basis, so you might want to check the specific datatypes in
 * what extent and with what meaning this operation is supported there.
 *
 * If the start_fieldgroup array is definied, it will start a new optical field
 * group before the currently defined field. The grouping is done through &lt;div&gt;
 * tags. The Title is printed, again enclosed by a &lt;div&gt; at the top of the
 * group. The optional css tags will be assigned to the opening div tags if
 * present. Each opened field group has to be closed using an end_fieldgroup
 * tag which will close the corresponding div tag. The end_fieldgroup tag takes
 * an integer indicating how many fieldgroups are to be closed (thus allowing
 * easier nesting of fieldgroups). For backwards compatibility only, end_fieldgroup
 * tags with an empty string as argument ('') are still supported, closing exactly
 * one fieldgroup.
 *
 * Note, that the schema writer
 * has to ensure that the fieldgroups are correctly paired, there is no checking
 * algorithm whatsoever which ensures the validity of the HTML to-be-generated.
 * It is possible, to have both a start and an end fieldgroup tag within the same
 * field, which will yield a group enclosing a single field.
 *
 * Note also, that the hidden
 * tag has precedence over the grouping algorithm. A field which is invisible
 * will not start or end a field group.
 *
 * The two options "config_domain" and "config_key" are both required for the
 * config storage method, which is in essence a way of setting any arbitrary
 * parameter, where config_domain is the parameter domain in question, and
 * config_key the parameter name. Apart from the extended configuration scheme,
 * this mode is otherwise identical to the storage method "parameter".
 *
 * The helptext parameter is there to allow for custom notes to the field.
 * Currently, the content of this field will be added as a tooltip to the field
 * in question. No HTML code is allowed in there yet.
 *
 * <b>Validation support:</b>
 *
 * The PEAR package HTML_Quickform must be installed to support validation.
 *
 * Validation is a new feature in MidCom 1.4.0. The implementation is based on
 * the Pear package HTML_Quickform so this must be installed for the
 * validation code to work. If HTML_Quickform is not installed the field will
 * just not be validated - MidCom will just save it.
 *
 * A larger manual on HTML_Quickform is found here:
 * http://pear.php.net/manual/en/package.html.html-quickform.intro-validation.php
 *
 * To add validation to a field, you add a validation keyword to the schema
 * like this:
 *
 * <pre>
 * "validation" =&gt; array (
 *     '&lt;type&gt;' =&gt; array (
 *         'message' =&gt; 'Some message to the user' ,
 *         ['format' =&gt; 'string' , ]
 *         ['function' =&gt; 'function name'
 *         [ 'object' =&gt; 'classname',] ]
 *     )
 * )
 * </pre>
 *
 * As you see, the variables follow HTML_Quickform quite closely.
 *
 * You may also write your own validation functions by setting the function
 * parameter. If you function is part of a class you also have to set the
 * class-parameter.
 *
 * <b>Localization support:</b>
 *
 * The description and helptext of each field and all
 * fieldgroup titles are automatically localized using the l10db of either the current
 * component or the component referenced by the l10n_db schema configuration key.
 *
 * If the string in
 * question does not exist in either the current or the default language, it will
 * fall back to the midcom core l10n db searching there.
 *
 * For backwards
 * compatibility with the existing l10n databases and schemas, all name strings are converted
 * to lower-case before being looked-up in the l10n databases.
 *
 * <b>Automatic Cache invalidation:</b>
 *
 * The Datamanager will automatically invalidate the cache during editing of an
 * existing object. When in creation mode, it will invalidate the currently active
 * node as well (for the current context of course). Of course, if you don't use
 * the creation mode, you have to invalidate the right topic yourself.
 *
 * <b>Object destruction:</b>
 *
 * Due to the complex nature of the Datamanager's object hierarchy, PHP cannot
 * reliably garbage collect Datamanager instances (it fails to resolve the cyclic
 * references between the datamanager class, its datatypes and the widgets). Therefore
 * you have to call the destroy method every time you do no longer need a datamanager
 * instance. Only then you can safely let a datamanager reference out of scope.
 * It will destroy all datatypes and widgets and clears all internal references.
 *
 * This is especially important in long running requests like reindexing or
 * bulk uploads.
 *
 *
 * @package midcom.helper.datamanager
 */

class midcom_helper_datamanager {

    /**
     * This MidgardObject is used for storing and retrieving the data. Any object
     * that is derived from them can also be used. Note that this member is populated
     * with a reference!
     *
     * @var MidgardObject
     * @access private
     */
    var $_storage;

    /**
     * This is the complete Layout database with which the system has been
     * initialized.
     *
     * @var Array
     * @access private
     */
    var $_layoutdb;

    /**
     * The layout currently in use, this is a reference into $_layoutdb.
     *
     * @var Array
     * @access private
     */
    var $_layout;

    /**
     * The index name of the layout currently in use.
     *
     * @var string
     * @access private
     */
    var $_layoutname;

    /**
     * The list of fields currently in use, this is a reference into $_layoutdb.
     *
     * @var Array
     * @access private
     */
    var $_fields;

    /**
     * The collection of datatypes corresponding to the fields out of the schema
     * file.
     *
     * @var Array
     * @access private
     */
    var $_datatypes;

    /**
     * This one holds the status of the process_form run. It is necessary to avoid
     * multiple invocations of this method, like it is sometimes done by not ideally
     * designed components. It is null if process_form wasn't called yet.
     *
     * @var int
     * @access private
     */
    var $_processing_result;

    /**
     * Creation mode callback object reference
     *
     * @var object
     * @access private
     */
    var $_creation_code_objref;

    /**
     * Creation mode callback method name
     *
     * @var string
     * @access private
     */
    var $_creation_code_objmethod;

    /**
     * Creation mode schema name
     *
     * @var string
     * @access private
     */
    var $_creation_schema;

    /**
     * If the datamanager should display js or not.
     * @access protected
     * @var boolean
     *
     */
    var $_show_js = false;
    /**
     * Creation mode flag
     *
     * @var boolean
     * @access private
     */
    var $_creation;

    /**
     * A simple collection of field names from the required fields check. It is used
     * for switching required fields to a seperate css class.
     *
     * @var Array
     * @access private
     */
    var $_missing_required_fields;

    /**
     * The URL of the help icon. This URL is complete, no prefixes need to be added.
     *
     * @var string
     * @access private
     */
    var $_url_help_icon;

    /**
     * The URL of the lock icon. This URL is complete, no prefixes need to be added.
     *
     * @var string
     * @access private
     */
    var $_url_lock_icon;

    /**
     * This one will contain the lock data if the current storage object is locked.
     * It is set either by a previous call of check_log or by set_log. It is null
     * if it is undefined, false if there is no lock or an array containing the lock
     * of another user otherwise.
     *
     * @var Array
     * @access private
     */
    var $_lock;

    /**
     * $_ourlock will be true if and only if we are the
     * owner of the current lock; this variable is only valid if $_lock is an array.
     *
     * @var boolean
     * @access private
     */
    var $_ourlock;

    /**
     * Pointer to a RuleRegistry singleton object.
     *
     * @var ???
     * @todo tarjei: Complete documentation
     * @access private
     */
    var $_rule_registry;

    /**
     * This array always holds a current snapshot of the data. Changes to this array
     * are not propagated into the object.
     *
     * Besides the values of all fields, the following keys are added to the array as well:
     *
     * - _schema contains the name of the data schema in use.
     * - _storage_type contains the name of the table in which we are stored (WARNING, this value will be deprecated
     *   during the DBA updates)
     * - _storage_id and _storage_guid hold the ID and GUID respectively of the storage object.
     *
     * @var Array
     */
    var $data;

    /**
     * Form field name prefix
     *
     * @var string
     */
    var $form_prefix;

    /**
     * Form URL prefix
     *
     * @var string
     */
    var $url_prefix;

    /**
     * The error string of the last call executed by the datamanager. The content of
     * this string is automatically appended to the content manager's processing
     * message and therefore needs only to be printed while in a non-AIS environment.
     * Note, that this string is HTML-capable, it should be enclosed in a <div> or
     * <p> upon printing, as those tags are not allowed in here.
     *
     * @var string
     */
    var $errstr;

    /**
     * URL prefix to the form
     *
     * @var string
     */
    var $url_me;

    /**
     * Datamanager L10n Database
     *
     * @access private
     * @var midcom_services__i18n_l10n
     */
    var $_l10n;

    /**
     * MidCOM L10n Database
     *
     * @access private
     * @var midcom_services__i18n_l10n
     */
    var $_l10n_midcom;

    /**
     * The primary L10n DB to use for schema translation.
     *
     * @var midcom_services__i18n_l10n
     * @access private
     */
    var $_l10n_schema = null;

    /* *************************** */
    /* ** Object Initialization ** */

    /**
     * The constructor loads the layout database, verifies its structure and
     * initializes the complete class for usage. No object gets loaded at this
     * point.
     *
     * The path to the schema database can be anything accepted by
     * midcom_get_snippet_content().
     *
     * @param mixed    $layoutdb    Either a string with the URL to a layoutDB or an Array containing the DB.
     * @see midcom_get_snippet_content()
     */
    function midcom_helper_datamanager ($layoutdb = null)
    {
        global $midcom_errstr;

        debug_push_class(__CLASS__, __FUNCTION__);

        if (is_null($layoutdb))
        {
            $x =& $this;
            $x = false;
            $midcom_errstr = "Default Constructor not allowed";
            debug_add($midcom_errstr);
            debug_pop();
            return false;
        }

        //$midgard = $GLOBALS['midgard'];

        $this->form_prefix = "midcom_helper_datamanager_";
        $this->url_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->url_me = $_SERVER['REQUEST_URI'];

        $this->_storage = null;
        $this->_layoutdb = null;
        $this->_layout = null;
        $this->_layoutname = "";
        $this->_fields = null;
        $this->_datatypes = null;
        $this->_processing_result = null;
        $this->_creation_code_objref = null;
        $this->_creation_code_objmethod = null;
        $this->_creation_schema = null;
        $this->_creation = false;
        $this->_missing_required_fields = Array();
        $this->errstr = "";
        $this->data = null;
        $this->_url_help_icon = MIDCOM_STATIC_URL . '/stock-icons/16x16/stock_help-agent.png';
        $this->_url_lock_icon = MIDCOM_STATIC_URL . '/stock-icons/24x24/lock.png';
        $this->_lock = null;
        $this->_ourlock = null;

        $i18n =& $_MIDCOM->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("midcom.helper.datamanager");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        if(array_key_exists("view_contentmgr", $GLOBALS)) {
            $this->_show_js = true;
        }
        $this->_load_schema_database($layoutdb);

        debug_pop ();
    }

    /**
     * Initializes the datamanager for object creation.
     *
     * In the creation mode, special rules apply. First, there must be a callback from
     * the application itself, that is executed when the dm need to create the true
     * storage object (after the user first clicks save, that is). The function must
     * return an array: The parameter "strorage" is mandatory and holds a reference(!)
     * to the storage object to be used. Note, that this object is passed by reference
     * to the complete datamanager system, so that all changes propagate accordingly.
     * The parameter "success" is optional, setting it to false tells the datamanager
     * to stay in the edit loop rather then complete it. Note, that this function must
     * create an empty object without any references to schema names and so on.
     *
     * The callback function receives only one parameter, which is a reference (!!)
     * to the datamanager object itself.
     *
     * Note, that the callback should absolutly try to create an empty record somehow,
     * telling success=false on all minor errors along with an appropriate error
     * message through the append_error method of the datamanager. If no storage
     * object can be created, NULL should be returned instead with an appropriate
     * error message through append_error. Know, that it is not possible, to retain
     * the user's input in the form in that case!
     *
     * The datamanager internally keeps track of the schema to be used for the new
     * record. The parameter $schema therefor is only relevant on the first call of
     * the function. Its value is tracked through a hidden variable within the input
     * form to save the application from keeping track of this variable over the
     * requests.
     *
     * @param string $schema    The schema name which should be used for creation of the new object.
     * @param object &$object    The callback object containing the creation code.
     * @param string $callback    The method name that should be used to create the new object.
     * @return boolean Indicating success
     */
    function init_creation_mode ($schema, &$object, $callback = "_dm_create_callback") {
        $this->_creation_code_objref =& $object;


        $this->_creation_code_objmethod = $callback;
        if (array_key_exists("midcom_helper_datamanager_creation_schema", $_REQUEST))
            $this->_creation_schema = $_REQUEST["midcom_helper_datamanager_creation_schema"];
        else
            $this->_creation_schema = $schema;
        $this->_creation = true;
        $this->_storage = null;
        return $this->_true_init(null);
    }

    /**
     * This method is responsible for the initialization of the datamanager to a
     * given object.
     *
     * The datamanager loads the object and
     * initializes all local fields accordingly. All datatypes get instantiated
     * and the data array gets populated. If the object has no schema associated with
     * it, it defaults to the first layout in the database.
     *
     * If the passed object is not a MgdSchema type, it gets converted silently.
     * The DM tries to propagate this change to the outside, to stay compatible with
     * existing code.
     *
     * @param MidgardObject $storage    The storage object to which the DM should be linked to.
     * @param string $schema    Do not autodetect the schema but use the one given here.
     *           This is useful if you want to edit the same object with more then one schema.
     *        Can be omitted.
     * @return boolean    True on success, false on failure, errors go to the debug log.
     */
    function init (&$storage, $schema = null)
    {
        if (! $_MIDCOM->dbclassloader->is_midcom_db_object($storage))
        {
            // Propagate the new object back to the called (HACKY!)
            $storage = $_MIDCOM->dbfactory->convert_midgard_to_midcom($storage);
        }
        $this->_storage =& $storage;
        return $this->_true_init($schema);
    }

    /**
     * This is the common initialization work shared between the existing-object
     * and the new-object ("creation mode") init procedure.
     *
     * It will translate all descriptions and helptexts automatically.
     *
     * Note, that the string is translated to <i>lower case</i> before
     * translation, as this is the usual form how strings are in the
     * l10n database. (This is for backwards compatibility mainly.)
     *
     * @param string $schema    Do not autodetect the schema but use the one given here.
     *           This is useful if you want to edit the same object with more then one schema.
     * @return boolean    Indicating success
     * @access private
     */
    function _true_init ($schema) {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->errstr = "";

        if (   ! is_object($this->_storage)
            && ! $this->_creation
           )
        {
            debug_add ("No object given!", MIDCOM_LOG_ERROR);
            debug_pop ();
            return false;
        }

        // get the layout of article, use the first defined layout if
        // none specified

        if ($this->_creation)
        {
            $this->_layoutname = $this->_creation_schema;
        }
        else if (is_null($schema))
        {
            $this->_layoutname = $this->_storage->parameter ("midcom.helper.datamanager", "layout");
            if (! $this->_layoutname)
            {
                $layouts = array_keys ($this->_layoutdb);
                $this->_layoutname = $layouts[0];
                debug_add ("Object has no schema, trying to use default: {$this->_layoutname}", MIDCOM_LOG_INFO);
            }
        }
        else
        {
            $this->_layoutname = $schema;
        }

        if (array_key_exists($this->_layoutname, $this->_layoutdb))
        {
            $this->_layout =& $this->_layoutdb[$this->_layoutname];
            $this->_fields =& $this->_layout["fields"];
        }
        else
        {
            $GLOBALS["midcom_errstr"] = "Layout does not exist!";
            debug_add ($GLOBALS["midcom_errstr"], MIDCOM_LOG_ERROR);
            debug_pop ();
            return false;
        }


        if (array_key_exists('l10n_db', $this->_layout))
        {
            $comp = $this->_layout['l10n_db'];
        }
        else
        {
            $comp = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT);
        }

        $i18n =& $_MIDCOM->get_service('i18n');
        $this->_l10n_schema = $i18n->get_l10n($comp);

        $this->_translate_schema_field($this->_layout['description']);

        // Complete Field Defaults

        if (! array_key_exists("locktimeout", $this->_layout))
        {
            $this->_layout["locktimeout"] = 60;
        }

        if (! array_key_exists("lockoverride", $this->_layout))
        {
            $this->_layout["lockoverride"] = "poweruser";
        }

        if (! array_key_exists('save_text', $this->_layout))
        {
            $this->_layout['save_text'] = 'save';
        }

        if (! array_key_exists('cancel_text', $this->_layout))
        {
            $this->_layout['cancel_text'] = 'cancel';
        }

        $this->_translate_schema_field($this->_layout['save_text']);
        $this->_translate_schema_field($this->_layout['cancel_text']);

        foreach ($this->_fields as $name => $field)
        {
            $this->_fields[$name]["name"] = $name;

            if (!array_key_exists("helptext",$field))
            {
                $this->_fields[$name]["helptext"] = "";
            }

            if (!array_key_exists("hidden",$field))
            {
                $this->_fields[$name]["hidden"] = false;
            }

            if (!array_key_exists("readonly",$field))
            {
                $this->_fields[$name]["readonly"] = false;
            }

            if (!array_key_exists("required",$field))
            {
                $this->_fields[$name]["required"] = false;
            }

            if (!array_key_exists("aisonly",$field))
            {
                $this->_fields[$name]["aisonly"] = false;
            }

            // Translate the field
            $this->_translate_schema_field($this->_fields[$name]['description']);
            $this->_translate_schema_field($this->_fields[$name]['helptext']);
            if (array_key_exists('start_fieldgroup', $field))
            {
                $this->_translate_schema_field($this->_fields[$name]['start_fieldgroup']['title']);
            }

            if (   array_key_exists("location", $field)
                && $field["location"] == "config"
                && (   ! array_key_exists("config_domain", $field)
                    || ! array_key_exists("config_key", $field)))
            {
                $GLOBALS["midcom_errstr"] = "Config field detected without config_domain or config_key";
                debug_add ($GLOBALS["midcom_errstr"], MIDCOM_LOG_ERROR);
                debug_print_r ("Field $name was defined as: ", $field);
                debug_pop();
                return false;
            }
        }


        // If we have a datatype listing, we kill them before creating the new one
        // This covers the cases where we re-init the datamanager.
        if (! is_null($this->_datatypes))
        {
            $this->_destroy_types();
        }

        // Instantiate all Datatype Concepts
        $this->_datatypes = Array();

        foreach ($this->_fields as $name => $field)
        {
            $classname = "midcom_helper_datamanager_datatype_" . $field["datatype"];
            $this->_datatypes[$name] =& new $classname ($this, $this->_storage, $field);
            if (! $this->_datatypes[$name])
            {
                $GLOBALS["midcom_errstr"] = "Could not instantiate {$name} Datatype Class.";
                debug_add($GLOBALS["midcom_errstr"], MIDCOM_LOG_ERROR);
                debug_print_r('Field was:', $field);
                debug_pop();
                return false;
            }
        }

        $this->_populate_data();

        debug_pop();
        return true;
    }


    /*********************************/
    /*** Form Processing Functions ***/

    /**
     * This method does all processing related to datamanager-generated forms. You
     * must call this method during your handle phase and act according to the
     * constant returned:
     *
     * - <b>MIDCOM_DATAMGR_FAILED:</b> Something critical occurred, processing failed.
     * - <b>MIDCOM_DATAMGR_CANCELLED:</b> Editing has been cancelled by the user, you
     *   should return to view-mode, no changes made. In case of a creation loop,
     *   the actual record has already been created and must be deleted by the
     *   callee manually.
     * - <b>MIDCOM_DATAMGR_SAVED:</b> Data has been save, you should return to
     *   view-mode.
     * - <b>MIDCOM_DATAMGR_EDITING:</b> We are still editing the data, keep calling the
     *   form-mode. If we are in creation mode, this means that object creation
     *   happened, but there was some validation error or the such, consult the
     *   datamanager's error messages for further details; an object has been
     *   created.
     * - <b>MIDCOM_DATAMGR_CREATEFAILED:</b> The creation callback could not create an
     *   empty record for use with the datamanager. This is serious. No new
     *   record has been created yet.
     * - <b>MIDCOM_DATAMGR_CREATING:</b> This is a variant of EDITING, the callee should show
     *   the edit form now. It is the first time, the edit interface is shown, no
     *   data has been stored yet.
     * - <b>MIDCOM_DATAMGR_CANCELLED_NONECREATED:</b> The user aborted the creation of a new
     *   object before any new object could have been created. The application
     *   should revert to some welcome screen, nothing has to be deleted.
     *
     * This method does its work only once. Consecutive calls will only yield the
     * same result as the first call to avoid trouble with multiple updated runs
     * in the datatypes.
     *
     * It will also call the _update_nemein_rcs helper to utilize its RCS mechanism
     * if available on any successful storage to the storage object.
     *
     * @return int Status code of the form processing run.
     */
    function process_form ()
    {
        // Make sure we have CSS loaded
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/midcom.helper.datamanager/datamanager.css",
            )
        );
        global $midcom_errstr;

        $this->errstr = "";

        debug_push ("midcom_helper_datamanager::process_form");

        if (!is_null($this->_processing_result)) {
            debug_add("We already had a process_form run, result was $this->_processing_result, returning it and aborting.");
            debug_pop();
            return $this->_processing_result;
        }

        if (   (   is_null($this->_storage)
                && $this->_creation == false
               )
            || is_null($this->_layout)
           )
        {
            $this->append_error($this->_l10n->get("storage object or data schema not set"));
            $this->_processing_result = MIDCOM_DATAMGR_FAILED;
            debug_add ("storage object or data schema not set.", MIDCOM_LOG_WARN);
            debug_pop ();
            return MIDCOM_DATAMGR_FAILED;
        }

        debug_add ("Processing Form Data");

        if ($this->_check_lock()) {

            /*** EDIT FORM: CLEAR LOCK ***/
            if (array_key_exists ($this->form_prefix . "clearlock", $_REQUEST))
            {

                debug_add("DATAMANAGER: Clear lock request due to user override, trying...");

                if (! is_array($this->_lock)) {
                    debug_add("Tried to clear a nonexistent lock. Ignoring and redirecting to the edit page.");
                    $this->_processing_result = MIDCOM_DATAMGR_EDITING;
                    debug_pop();
                    return $this->_processing_result;
                }

                if (! $this->_clear_lock()) {
                    $this->append_error($this->_l10n->get("could not clear lock: insufficient privilegs"));
                    debug_add("Could not clear lock, in case you need it, the last Midgard error was: "
                              . mgd_errstr());
                } else {
                    $this->_set_lock();
                    $this->_processing_result = MIDCOM_DATAMGR_EDITING;
                    debug_pop();
                    return $this->_processing_result;
                }
            }

            debug_add("DATAMANAGER: This article is locked, telling the callee that we are in edit mode anyway, display_handlers will know what to do.");
            $this->_processing_result = MIDCOM_DATAMGR_EDITING;
            $error = $this->_l10n->get("object locked by %s from %s at %s, cannot edit.");
            $error = sprintf($error, $this->_lock["user_name"], $this->_lock["client_ip"],
                             strftime("%x %X", $this->_lock["time"]));
            $this->append_error("<img src=\"" . $this->_url_lock_icon . "\" ALT=\"locked\">&nbsp;$error<br>\n");
            debug_pop();
            return $this->_processing_result;
        }

        /*** EDIT FORM: CANCEL ***/
        if (array_key_exists ($this->form_prefix . "cancel", $_REQUEST))
        {
            debug_add("CANCEL: Editing aborted.");
            if (! $this->_creation)
                foreach ($this->_fields as $name => $field) {
                    $object =& $this->_datatypes[$name];
                    $object->sync_widget_with_data();
                }
            debug_pop();
            if ($this->_creation) {
                $this->_processing_result = MIDCOM_DATAMGR_CANCELLED_NONECREATED;
            } else {
                $this->_processing_result = MIDCOM_DATAMGR_CANCELLED;
                if (is_array($this->_lock) && $this->_ourlock) {
                    debug_add("DATAMANAGER: Cleared lock due toDatamanager cancel click");
                    $this->_clear_lock();
                }
            }
            debug_pop();
            return $this->_processing_result;
        }

        /*** EDIT FORM: SAVE ***/
        if (array_key_exists ($this->form_prefix . "submit", $_REQUEST))
        {
            $success = true;
            if ($this->_creation)
            {
                debug_add ("will execute \$this->_creation_code_objref->{$this->_creation_code_objmethod}(\$this);");
                $result = $this->_creation_code_objref->{$this->_creation_code_objmethod}($this);
                debug_print_r("Got this result:", $result);

                if (    is_null ($result)
                     || (   is_array($result)
                         && ! array_key_exists("storage", $result)
                        )
                   )
                {
                    debug_print_r("DATAMANAGER: Create callback failed, errstr is:", $this->errstr);
                    debug_pop ();
                    return MIDCOM_DATAMGR_CREATEFAILED;
                }

                if (! $_MIDCOM->dbclassloader->is_midcom_db_object($result['storage']))
                {
                    // Propagate the new object back to the called (HACKY!)
                    $result['storage'] = $_MIDCOM->dbfactory->convert_midgard_to_midcom($result['storage']);
                }
                $this->_storage =& $result['storage'];
                $this->_storage->parameter("midcom.helper.datamanager", "layout", $this->_creation_schema);

                /* Tell the datatypes where to store data and load all readonly
                 * and hidden fields now, so that their content is right.
                 */
                foreach ($this->_fields as $name => $field) {
                    $object =& $this->_datatypes[$name];
                    $object->_datamanager_set_storage($this->_storage);
                    if (   $field["readonly"] === true || $field["hidden"] === true
                        || $field["aisonly"] === true && ! array_key_exists("view_contentmgr", $GLOBALS)

                       )
                    {
                        $object->load_from_storage();
                        $object->sync_widget_with_data();
                    }
                }

                if (   array_key_exists("success", $result)
                    && $result["success"] == false)
                {
                    $success = false;
                }

                debug_add ("DATAMANAGER: Set a lock after creating an object (Creation Mode active)");
                $this->_set_lock();

                // Invalidate the current content topic so that the NAP cache is correct again.
                $nav = new midcom_helper_nav();
                $node = $nav->get_node($nav->get_current_node());
                $_MIDCOM->cache->invalidate($node[MIDCOM_NAV_GUID]);

                /* Ok, we do now have a storage object to work with. Note, that
                 * if the DM returns MIDCOM_DATAMGR_EDITING, you must honor this
                 * by no longer entering the creation mode, you'll have to fall
                 * back to the original bahvoir for future runs in that case.
                 * See the data array for a guid/id off the content object.
                 */
            }

            /* Frist, synchronize all data and check for required fields.
             * Note, that this place could be used for validation as well.
             * For readonly/hidden fields, do the opposit, resync the widget
             * with the datatype, just to be on the safe side.
             */
            $this->_missing_required_fields = Array();
            foreach ($this->_fields as $name => $field) {
                $object =& $this->_datatypes[$name];
                if (   $field["readonly"] === true || $field["hidden"] === true
                    || $field["aisonly"] === true && ! array_key_exists("view_contentmgr", $GLOBALS)
                   )
                    $object->sync_widget_with_data();
                else
                    $object->sync_data_with_widget();
                if (   $field["required"] === true
                    && $object->is_empty() == true
                   )
                {
                    $this->_missing_required_fields[] = $name;
                    $msg = sprintf($this->_l10n->get("required field missing"), $field["description"]);
                    $this->append_error("$msg<br>\n");
                    $success = false;
                }
                /* input validation  */
                if ($success && array_key_exists('validation',$field)) {
                    if (!is_object($this->_rule_registry)) {
                        /* if quickform is not installed, return true.  */
                        if (include_once ('HTML/QuickForm/RuleRegistry.php')) {
                            $this->_rule_registry =& HTML_QuickForm_RuleRegistry::singleton();
                        } else {
                            debug_pop();
                            return true;
                        }
                    }
                    $success = $object->validate($field['description'],$field['validation']);
                }

            }

            /* If the previous checks were successful, save the data, if not
             * exit here indicating that we are still in the edit loop.
             * Any readonly or hidden fields will be skipped to preserve
             * their contents.
             */
            $dm_save_required = false;

            foreach ($this->_fields as $name => $field) {
                $object =& $this->_datatypes[$name];

                if (   $field["readonly"] === true || $field["hidden"] === true
                    || $field["aisonly"] === true && ! array_key_exists("view_contentmgr", $GLOBALS)
                   )
                    continue;
                $result = $object->save_to_storage();
                if ($result == MIDCOM_DATAMGR_SAVE_DELAYED)
                {
                    // Call $object->update() once
                    $dm_save_required = true;
                }
                elseif ($result == MIDCOM_DATAMGR_FAILED)
                {
                    $success = false;
                    debug_add("Failed to save field $name - save_to_storage returned $result.", MIDCOM_LOG_ERROR);
                }
            }

            if (   $success
                && $dm_save_required)
            {
                debug_print_r('We have to save the object, calling update on:', $this->_storage);
                $success = $this->_storage->update();
                if (! $success)
                {
                    debug_add('Failed to update the storage object: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                    $this->append_error($this->_l10n->get('failed to update the storage object') . "<br>\n");

                    debug_add('Keeping the lock in place, but returning FAILED to allow components to think about this.');
                    debug_pop();
                    return MIDCOM_DATAMGR_FAILED;
                }
            }
            else
            {
                debug_add('We do not have to update the object.');
            }

            // On Success, checkin an RCS revision, if NemeinRCS is available
            // also invalidate the cache.
            if ($success)
            {
                $this->_update_nemein_rcs();
                $_MIDCOM->cache->invalidate($this->_storage->guid());
                debug_add("Invalidated MidCOM Cache.");
            }

            // rebuild layout array, this is done even in the case of an error
            // as a part of the updates might have already commenced.
            $this->_populate_data ();

            debug_pop();
            if ($success)
            {
                $this->_processing_result = MIDCOM_DATAMGR_SAVED;
                debug_add("DATAMANAGER: Cleared lock after successful save");
                $this->_clear_lock();
            }
            else
            {
                $this->_processing_result = MIDCOM_DATAMGR_EDITING;
            }
            debug_pop();
            return $this->_processing_result;
        }

        // No State has been processed, Default:

        debug_add ("state unknown -> usually this means, no formdata has been received yet.");
        if ($this->_creation) {
            $this->_processing_result = MIDCOM_DATAMGR_CREATING;
        } else {
            if ($this->_lock === false)
                $this->_set_lock();
            $this->_processing_result = MIDCOM_DATAMGR_EDITING;
        }
        debug_pop();
        return $this->_processing_result;
    }

    /*****************************/
    /*** Data Output Functions ***/

    /**
     * This function will display the current data of the object in a non-editable
     * way. It will not display edit/delete Links for the object. This view can
     * be customized by CSS commands, see below.
     *
     * This command will not work in creation mode.
     */
    function display_view ()
    {
        global $midcom_errstr;
        $this->errstr = "";

        debug_push ("midcom_helper_datamanager::display_view");

        if (is_null($this->_storage) || is_null($this->_layout))
        {
            $this->append_error($this->_l10n->get("storage object or data schema not set"));
            $this->_processing_result = MIDCOM_DATAMGR_FAILED;
            debug_add ("storage object or data schema not set.", MIDCOM_LOG_WARN);
            debug_pop ();
            return false;
        }

        debug_add ("Generating view");


        foreach ($this->_fields as $name => $field)
        {
            if ($field["hidden"] === true)
            {
                debug_add("Skipping hidden field $name");
                continue;
            }
            if ($field["aisonly"] === true && ! array_key_exists("view_contentmgr", $GLOBALS))
            {
                debug_add("Skipping AIS-only field $name");
                continue;
            }

            if (array_key_exists("start_fieldgroup", $field))
            {
                $group = $field["start_fieldgroup"];
                debug_add("Starting fieldgroup before $name");
                if (array_key_exists("css_group", $group))
                {
                    ?><div class="<?echo htmlspecialchars($group['css_group']);?>"><?php
                }
                else
                {
                    ?><div class="form_fieldgroup"><?php
                }
                if (array_key_exists("css_title", $group))
                {
                    ?><div class="<?echo htmlspecialchars($group['css_title']);?>"><?php
                }
                else
                {
                    ?><div class="form_fieldgroup_title"><?php
                }
                ?><?echo htmlspecialchars($group['title']);?></div><?php
            }

            ?><div class="form_description"><?echo htmlspecialchars($field["description"]);?></div><?php
            $widget =& $this->_datatypes[$name]->get_widget();
            ?><div class="form_viewfield"><?php
            debug_add("Drawing widget for $name, typeof datatype = " . get_class($this->_datatypes[$name])
                . "; typeof widget = " . get_class($widget));
            $widget->draw_view();
            ?></div><?php

            if (array_key_exists("end_fieldgroup", $field))
            {
                $count = (int) $field['end_fieldgroup'];
                if ($count < 1)
                {
                    $count = 1;
                }
                debug_add("Ending fieldgroup at {$name}, closing {$count} levels.");
                for ($i = 0; $i < $count; $i++)
                {
                    ?></div><?php
                }
            }
        }

        debug_pop();
        return true;
    }

    /**
     * Here is the root of the all-mighty form generator. Calling this function will
     * result in a complete editing form to be put out to the client. Remebmer to call
     * the process_form function before any of this form operations, even upon
     * displaying the form the first time. This view can be customized by CSS
     * commands.
     *
     * The form's action will point to the same URL as the current page, including all
     * HTTP GET parameters
     */
    function display_form ($form_id = '', $skip_submit = false, $display_hidden = false) {
        global $midcom_errstr;
        $this->errstr = "";

        debug_push ("midcom_helper_datamanager::display_form");

        $i18n =& $_MIDCOM->get_service("i18n");
        $l10n_midcom = $i18n->get_l10n("midcom");

        if (   (   is_null($this->_storage)
                && $this->_creation == false
               )
            || is_null($this->_layout)

           )
        {
            $this->append_error($this->_l10n->get("storage object or data schema not set"));
            $this->_processing_result = MIDCOM_DATAMGR_FAILED;
            debug_add ("storage object or data schema not set.", MIDCOM_LOG_WARN);
            debug_pop ();
            return false;
        }

        debug_add ("Generating form", MIDCOM_LOG_DEBUG);

        if ($form_id)
        {
            $form_id = " id='{$form_id}'";
        }
        $form_prefix = htmlspecialchars($this->form_prefix);

        if ($skip_submit)
        {
            // Some developers prefer to submit their form via AJAX
            echo "<form name='{$form_prefix}_form' enctype='multipart/form-data' class='datamanager'{$form_id}>\n";
        }
        else
        {
            // Regular, submittable form
            echo "<form name='{$form_prefix}_form' action='{$this->url_me}' "
                . "enctype='multipart/form-data' method='post' class='datamanager'{$form_id}>\n";
        }

        if (is_array($this->_lock) && ! $this->_ourlock) {
            $can_break = false;

            $midgard = $_MIDCOM->get_midgard();
            $user = mgd_get_person($midgard->user);

            if ($user != false)
            {
                if ($midgard->admin)
                {
                    $can_break = true;
                }
                else if ($user != false && $this->_layout["lockoverride"] == "poweruser")
                {
                    if ($user->parameter("Interface", "Power_User") == "YES")
                    {
                        $can_break = true;
                    }
                }
                else if ($user != false && $this->_layout["lockoverride"] == "all")
                {
                    $can_break = true;
                }
            }

            // Print Locked Message
?>
<div class='processing_msg'>
    <img src='<?php echo $this->_url_lock_icon; ?>' ALT='locked' />
    <?php echo $this->_l10n->get("object locked"); // Keep the space behind this closing tag so that we get a linebreak ?>
</div>
<?php
            // Generate the fieldgroup with the try again / break lock buttons

            echo "<fieldset class='locking'>\n";

            $caption = $this->_l10n->get('tryagain');
            echo "    <input type='submit' name='{$form_prefix}tryagain' value='{$caption}' />\n";

            if ($can_break)
            {
                $caption = $this->_l10n->get('break lock');
                echo "    <input type='submit' name='{$form_prefix}clearlock' value='{$caption}' />\n";
            }

            $caption = $this->_l10n->get('cancel');
            echo "    <input type='submit' name='{$form_prefix}cancel' value='{$caption}' />\n";

            echo "</fieldset>\n";
        }

        foreach ($this->_fields as $name => $field)
        {

            // Check for the start of a fieldset (aka "fieldgroup")
            if (array_key_exists("start_fieldgroup", $field))
            {
                $group = $field["start_fieldgroup"];

                echo "<div class='midcom_datamanager_fieldgroup' id='midcom_datamanager_fieldgroup_{$group['title']}'><fieldset";
                if (array_key_exists("css_group", $group))
                {
                    echo " class='{$group['css_group']}'";
                }
                echo ">\n";

                echo '    <legend';
                if (array_key_exists("css_title", $group))
                {
                    echo " class='{$group['css_title']}'";
                }
                echo '>' . htmlspecialchars($group['title']) . "</legend>\n";
            }
            // Don't print if we are either hidden or ais-only and not in AIS.
            if (   $display_hidden == false
                && (($field["hidden"] === true)
                     || (   $field["aisonly"] === true
                         && !array_key_exists("view_contentmgr", $GLOBALS))))
            {
               if (array_key_exists("end_fieldgroup", $field))
                    {
                    $count = (int) $field['end_fieldgroup'];
                    if ($count < 1)
                    {
                        $count = 1;
                    }
                    debug_add("Ending fieldgroup at {$name}, closing {$count} levels.");
                    for ($i = 0; $i < $count; $i++)
                    {
                        echo "</fieldset></div>\n";
                    }
                }
                continue;
            }


            // Render the field
            if (   $field["readonly"] === true
                || (is_array($this->_lock) && ! $this->_ourlock))
            {
                // We are read-only or the object is locked, render the view.
                // TODO: this is not yet WAI transformed!
                ?><div class="form_description"><?echo htmlspecialchars($field["description"]);?><?php
                if (strlen($field["helptext"]) > 0) {
                    ?>&nbsp;&nbsp;<img src="<?echo htmlspecialchars($this->_url_help_icon);?>" ALT="<?echo htmlspecialchars($field["helptext"]);?>" TITLE="<?echo htmlspecialchars($field["helptext"]);?>"><?php
                }
                ?></div><?php
                $widget =& $this->_datatypes[$name]->get_widget();
                $widget->draw_view();
            }
            else
            {
                $widget =& $this->_datatypes[$name]->get_widget();

                // Render the edit widget.
                if ($field["required"] === true)
                {
                    $widget->required = true;
                    if (in_array($name, $this->_missing_required_fields))
                    {
                        $widget->missingrequired = true;
                    }
                }

                if (   $display_hidden == true
                    && $field["hidden"] == true)
                {
                    echo '<fieldset style="display: none;">';
                }

                $widget->draw_widget_start();

                /*
                 * Adds a paragraph to the start of the field's content.
                 */
                if (isset($field['widget_content_start']))
                {
                    echo '<p>' . htmlspecialchars($field['widget_content_start']) . '</p>\n';
                }

                $widget->draw_widget();

                /*
                 * Adds a paragraph to the end of the field's content.
                 */
                if (isset($field['widget_content_end']))
                {
                    echo '<p>' . htmlspecialchars($field['widget_content_end']) . '</p>\n';
                }

                $widget->draw_widget();
                $widget->draw_widget_end();

                if (   $display_hidden == true
                    && $field["hidden"] == true)
                {
                    echo '</fieldset>';
                }
            }


            if (array_key_exists("end_fieldgroup", $field))
            {
                $count = (int) $field['end_fieldgroup'];
                if ($count < 1)
                {
                    $count = 1;
                }
                debug_add("Ending fieldgroup at {$name}, closing {$count} levels.");
                for ($i = 0; $i < $count; $i++)
                {
                    echo "</fieldset></div>\n";
                }
            }
        }

        // Display the submit buttons if we are not locked.
        // TODO: this is not yet WAI transformed!
        if (   !$skip_submit
            && (   $this->_layout["locktimeout"] == 0
                || (is_array($this->_lock) && $this->_ourlock)
                || is_null($this->_storage)))
        {
?>
<div class="form_toolbar">
    <input type="submit" name="<?echo htmlspecialchars($this->form_prefix);?>submit" accesskey="s" class="save" value="<?php echo $this->_layout['save_text']; ?>" />
    <input type="submit" name="<?echo htmlspecialchars($this->form_prefix);?>cancel" class="cancel" value="<?php echo $this->_layout['cancel_text']; ?>" />
</div>
<?php
            if ($this->_creation)
            {
                echo "<input type=\"hidden\" name=\"midcom_helper_datamanager_creation_schema\" value=\""
                     . $this->_creation_schema . "\" />\n";
            }
        }
        else
        {
            debug_add("Ommiting buttons as this object is locked. Lock info:");
            debug_add("Lock timeout: " . $this->_layout["locktimeout"]
                . ", storage is " . (is_null($this->_storage) ? "null" : "not null"));
            debug_print_r("Lock record is:", $this->_lock);
        }

        echo "</form>\n";

        debug_pop();
        return true;
    }

    /**
     * This is a small version of the display_view method above for use outside of
     * the datamanager. It calls the draw_view method of the corresponding widget
     * which essentially renders the data in the datamanagers default view
     * representation. It does not display a headings or the like, so it can be
     * easily used to created custom database applications without having to
     * reimplement the view logic for each datatype again and again.
     *
     * @param string $name    The name of the field that should be shown
     */
    function display_view_field ($name) {
        global $midcom_errstr;
        $this->errstr = "";

        debug_push ("midcom_helper_datamanager::display_view_field");

        if (is_null($this->_storage) || is_null($this->_layout))
        {
            $this->append_error($this->_l10n->get("storage object or data schema not set"));
            $this->_processing_result = MIDCOM_DATAMGR_FAILED;
            debug_add ("storage object or data schema not set.", MIDCOM_LOG_WARN);
            debug_pop ();
            return false;
        }

        if (! array_key_exists ($name, $this->_fields)) {
            $midcom_errstr = "field $name is not known";
            debug_add ($midcom_errstr, MIDCOM_LOG_WARN);
            debug_pop ();
            return false;
        }

        debug_add ("Generating view output for field $name");

        $widget =& $this->_datatypes[$name]->get_widget();
        $widget->draw_view();

        debug_pop();


        return true;
    }

    /**
     * get_csv_line function will transform the data array into a CSV compatible form.
     * The actual CSV representation of the data is determined by the individual
     * datatypes. Data will be separated by the given separator and ends with the
     * given newline characters. The internal helper _csv_encode is used
     * to encode the actual data from the components.
     *
     * @param string $separator    Separator to use between fields.
     * @param string $newline    Newline character to use.
     * @return string CSV line
     * @see midcom_helper_datamanager::get_csv_header_line()
     * @see midcom_helper_datamanager::_csv_encode()
     */
    function get_csv_line ($separator = ",", $newline = "\r\n") {
        global $midcom_errstr;
        $this->errstr = "";

        debug_push ("midcom_helper_datamanager::get_csv_line");

        if (is_null($this->_storage) || is_null($this->_layout))
        {
            $this->append_error($this->_l10n->get("storage object or data schema not set"));
            $this->_processing_result = MIDCOM_DATAMGR_FAILED;
            debug_add ("storage object or data schema not set.", MIDCOM_LOG_WARN);
            debug_pop ();
            return false;
        }

        $result = "";
        $first = true;

        foreach ($this->_fields as $name => $field) {
            $data = $this->_csv_encode($this->_datatypes[$name]->get_csv_data(), $separator);
            if ($first) {
                $result .= $data;
                $first = false;
            } else {
                $result .= $separator . $data;
            }
        }
        $result .= $newline;
        debug_pop();
        return $result;
    }

    /**
     * get_csv_header_line will yield a line containing all the field descriptions to
     * be used as column headers.
     *
     * @param string $separator    Separator to use between fields.
     * @param string $newline    Newline character to use.
     * @return string CSV line
     * @see midcom_helper_datamanager::get_csv_header_line()
     * @see midcom_helper_datamanager::_csv_encode()
     */
    function get_csv_header_line ($separator = ",", $newline = "\r\n") {
        global $midcom_errstr;
        $this->errstr = "";

        debug_push ("midcom_helper_datamanager::get_csv_line");

        if (is_null($this->_storage) || is_null($this->_layout))
        {
            $this->append_error($this->_l10n->get("storage object or data schema not set"));
            $this->_processing_result = MIDCOM_DATAMGR_FAILED;
            debug_add ("storage object or data schema not set.", MIDCOM_LOG_WARN);
            debug_pop ();
            return false;
        }

        $result = "";
        $first = true;

        foreach ($this->_fields as $name => $field) {
            $data = $this->_csv_encode($field["description"], $separator);
            if ($first) {
                $result .= $data;
                $first = false;
            } else {
                $result .= $separator . $data;
            }
        }
        $result .= $newline;
        debug_pop();
        return $result;
    }

    /**********************************/
    /*** Meta-Data Access Functions ***/

    /**
     * Return the data array (a copy of the data member).
     *
     * @return Array    Current object data
     * @see midcom_helper_datamanager::data
     */
    function get_array () {
        return $this->data;
    }

    /**
     * Return a copy of the schema database.
     *
     * @return Array
     */
    function get_layout_database () {
        return $this->_layoutdb;
    }

    /**
     * Return a list of the available schemas.
     *
     * @return Array
     */
    function list_layouts () {
        return array_keys ($this->_layoutdb);
    }

    /**
     * Return the name of the currently active schema.
     *
     * @return string
     */
    function get_schema_name ()
    {
        return $this->_layoutname;
    }



    /**
     * Compatibility function for get_schema_name().
     *
     * @deprecated This function is deprecated and will be removed after MidCOM 2.6.
     * @see get_schema_name()
     */
    function get_layout_name()
    {
        return $this->get_schema_name();
    }

    /**
     * Return a list of fields in the current schema.
     *
     * @return Array
     */
    function get_fieldnames () {
        $result = Array ();
        foreach ($this->_fields as $name => $field)
            $result[$name] = $field["description"];
        return $result;
    }

    /**
     * Return a list of fieldgroups in the current schema
     * @return Array
     *
     */
    function get_fieldgroups() {
        $result = Array ();
        foreach ($this->_fields as $name => $field) {
            if (array_key_exists('start_fieldgroup', $field)) {
                $result[] = $field['start_fieldgroup']['title'] ;
            }
        }

        return $result;
    }
    /**
     * Set the _show_js flag so that javascript is included with the widgets.
     * IMPORTANT: This function must be called before calling
     * midcom_helper_datamanager::init.
     *
     * @access public
     * @author tarjei
     * @param boolean show_js
     */
    function set_show_javascript($show_js = true){
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_null($this->_fields)) {
            debug_add("set_show_javascript() called  after init() has been called on the datamanager object." .
                    "This call will fail", MIDCOM_LOG_WARN);
        }
        $this->_show_js = $show_js;

        debug_pop();
    }

    /*********************************/
    /*** internal Helper Functions ***/

    /**
     * This helper function will load the schema database identified by
     * $source, which can either be a path to a snippet/file or an already
     * loaded database array.
     *
     * @see midcom_helper_datamanager::_translate_schema_field()
     * @param mixed $source A schemadb-path or -array.
     * @access private
     */
    function _load_schema_database($source)
    {
        debug_push('midcom_helper_datamanager::_load_schema_database');
        // Load Layouts
        if (is_string($source))
        {
            debug_add("Trying to load Layout database from the snippet $source");
            $this->_layoutdb = $this->_get_layout_from_snippet($source);
        }
        else
        {
            debug_add("Got a complete layout database, using it.");
            $this->_layoutdb = $source;
        }

        if (! is_array($this->_layoutdb))
        {
            debug_print_type("Got this type as layout database:", $source);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Layout database was not found, aborting.');
            // this will exit()
        }

        debug_pop();
    }


    /**
     * This helper translates a field of the schema. Strings are
     * translated in-place.
     *
     * @see midcom_helper_datamanager::_load_schema_database()
     * @see midcom_helper_datamanager::translate_schema_string()
     * @param string $field A reference to the string to be translated
     * @access private
     */
    function _translate_schema_field (&$field)
    {
        $field = $this->translate_schema_string($field);
    }


    /**
     * Schema translation helper, usable by components from the outside.
     *
     * The l10n db from the schema is used first, the MidCOM core l10n db second.
     * If the string is not found in both databases, the string is returned unchanged.
     *
     * Note, that the string is translated to <i>lower case</i> before
     * translation, as this is the usual form how strings are in the
     * l10n database. (This is for backwards compatibility mainly.)
     *
     * @param string $string The string to be translated.
     * @return string The translated string.
     */
    function translate_schema_string ($string)
    {
        $translate_string = strtolower($string);
        if (! is_null($this->_l10n_schema) && $this->_l10n_schema->string_available($translate_string))
        {
            return $this->_l10n_schema->get($translate_string);
        }
        else if ($this->_l10n_midcom->string_available($translate_string))
        {
            return $this->_l10n_midcom->get($translate_string);
        }
        return $string;
    }

    /**
     * This member will append the given string both to the internal error string
     * and, if available, to the current content manager's processing message string.
     * Note, that this function is public, but should only be called by widgets and
     * datatypes.
     *
     * @param string $string    The string to append to the error.
     */
    function append_error ($string) {
        if (array_key_exists("view_contentmgr", $GLOBALS))
            $GLOBALS["view_contentmgr"]->msg .= $string;
        $this->errstr .= $string;
    }

    /**
     * This method populates the Array $data with the current field information.
     *
     * @see $data
     * @access private
     */
    function _populate_data ()
    {
        debug_push ("midcom_helper_datamanager::_populate_data");

        $this->data = array ();
        $this->data["_schema"] = $this->_layoutname;
        if (! is_null ($this->_storage))
        {
            $this->data["_storage_type"] = $this->_storage->__table__;
            $this->data["_storage_id"] = $this->_storage->id;
            $this->data["_storage_guid"] = $this->_storage->guid();
        }
        else
        {
            $this->data["_storage_type"] = null;
            $this->data["_storage_id"] = null;
            $this->data["_storage_guid"] = null;
        }

        foreach ($this->_fields as $name => $field)
        {
            $this->data[$name] = $this->_datatypes[$name]->get_value();
            // debug_print_r ("Updating field {$name} with this:", $this->data[$name]);
        }

        debug_print_r ("Array Dump:", $this->data);
        debug_pop ();
        return true;
    }

    /**
     * This funtion extracts the Layout Database from the snippet referenced by
     * $path. The snippet must only contain the layout definitions without the
     * surrounding array definition, this corresponds to this part of the schema
     * definition:
     *
     * <pre>
     * <layout line> [ , <layout line> ... ]
     * </pre>
     *
     * @param string $path    The path to the schema, as passed to midcom_get_snippet_content.
     * @return Array        The parsed schema.
     * @see midcom_get_snippet_content()
     * @access private
     */
    function _get_layout_from_snippet($path) {
        $data = midcom_get_snippet_content($path);
        eval ("\$layout = Array(\n" . $data . "\n);");
        return $layout;
    }

    /**
     * Calls midcom_update_nemein_rcs to update the Nemein RCS store if configured.
     *
     * @return boolean    Indicating success.
     * @see midcom_update_nemein_rcs()
     * @access private
     */
    function _update_nemein_rcs()
    {
        return true;
        /*
         * Disabled, does not fail gracefully if nemein rcs isn't installed/setup properly:
         *
         * Warning: chmod(): File or directory not found in
         * /home/torben/XPDATA/FILES/Eclipse Workbench/MidCOM/lib/no/bergfald/rcs/backends/aegirrcs.php
         * on line 501
         *
        return midcom_update_nemein_rcs($this->_storage);
         */
    }

    /**
     * Encodes the given string into CSV according to these rules: Any appearance
     * of the separator or one of the two newline characters \n and \r will trigger
     * quoting. In quoting mode, the entire string will be enclosed in double-quotes.
     * Any occurence of a double quote in the original string will be transformed
     * into two double quotes. Any leading or trailing whitespace around the data
     * will be eliminated.
     *
     * @param string $string    The string to encode.
     * @param string $separator    Separator to use between fields.
     * @return string            The encoded string.
     * @access private
     */
    function _csv_encode ($string, $separator) {
        // Quote the whole line if the separator or any of the
        // newline characters \n or \r is within the data.


        $pattern='/[\n\r]|' . str_replace(Array("/","|"), Array("\/","\|"), $separator) . '/';
        $data = trim($string);

        // debug_add("Encoding [$data] (HEX: [" . bin2hex($data) . "]) with Pattern [$pattern].");

        if (preg_match($pattern, $data) != 0) {
            // Qutoed operation required: Escape quotes
            return '"' . str_replace('"','""',$data) . '"';
        } else {
            // Unquoted operation required
            return $data;
        }
    }

    /**
     * This function will check if the current object does have a still valid lock which prevents
     * us from editing. It returns true, if the storage object is locked for us, false
     * otherwise.
     *
     * @return boolean    Indicating locking state.
     * @access private
     */
    function _check_lock() {
        debug_add("Checking for lock");

        if (is_null($this->_storage) || $this->_layout["locktimeout"] == 0) {
            debug_add("No lock: Storage is null or locktimeout is 0");
            return false;
        }

        $lockdata = $this->_storage->parameter("midcom.helper.datamanager", "lock");
        if ($lockdata == false) {
            $this->_lock = false;
            debug_add("Lockdata did not exist.");
            return false;
        }

        $lock = unserialize($lockdata);
        debug_print_r("Got this lock data:", $lock);

        if (! $lock['user'])
        {
            debug_add('Warning, the user field of the lock was invalid, perhaps due to an expired session.');
            debug_add('Deleting lock.');
            $this->_storage->parameter('midcom.helper.datamanager', 'lock', '');
            return false;
        }

        $midgard = $_MIDCOM->get_midgard();
        $user = mgd_get_person($midgard->user);

        if (($lock["time"] + $this->_layout["locktimeout"] * 60) < time()) {
            // Lock Timeout
            debug_print_r ("DATAMANAGER: Lock timeout, we have now " . time(), $lock);
            $this->_clear_lock();
            return false;
        }

        $this->_lock = $lock;
        if ($user) {
            $this->_lock["user_record"] = $user;
            $locker = mgd_get_person($lock["user"]);
            if (! $locker)
          $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "CRITICAL FAILURE: Locker person record does not exist: " . mgd_errstr());
            $this->_lock["user_name"] = $locker->name;
            $this->_lock["user_record"] = $locker;
        } else {
            $this->_lock["user_record"] = null;
            $this->_lock["user_name"] = $this->_l10n->get("anonymous user");
        }

        debug_print_r("Loaded this lock:", $this->_lock);

        if (
              (
                  ($user == false && $lock["user"] == false)
               || ($user->id == $lock["user"])
              )
            && $lock["client_ip"] == $_SERVER["REMOTE_ADDR"]
           )
        {
            $this->_ourlock = true;
            debug_add("This is our lock, so record is clear");
            return false;
        } else {
            $this->_ourlock = false;
            debug_add("This is not our lock, we have to wait.");
            return true;
        }
    }

    /**
     * This tries to set a lock at the current storage object. If it returns false,
     * this failed due to an existing lock.
     *
     * @return boolean Indicating success.
     * @access private
     */
    function _set_lock()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("We should set a lock.");

        if (   is_null($this->_storage)
            || $this->_check_lock())
        {
            debug_add("Denied, storage is null or check_lock returned true");
            debug_pop();
            return false;
        }

        if ($this->_layout["locktimeout"] == 0)
        {
            debug_add("Locktimeout is 0");
            debug_pop();
            return true;
        }

        /*
        $owner = false;
        $owner_function = "mgd_is_{$this->_storage->__table__}_owner";
        if (function_exists($owner_function))
        {
            $owner = $owner_function($this->_storage->id);
        }
        else
        {
            debug_add("We have to do a can_do");
            $owner = $_MIDCOM->auth->can_do('midgard:update', $this->_storage);
        }

        if (   ! $midgard->admin
            && ! $owner)
        */
        if (! $_MIDCOM->auth->can_do('midgard:update', $this->_storage))
        {
            debug_add("Denied, not owner of the target object");
            debug_pop();
            return false;
        }

        $user = mgd_get_person($_MIDGARD['user']);
        $lock = Array
        (
            "user" => (($user == false) ? null : $user->id),
            "time" => time(),
            "client_ip" => $_SERVER["REMOTE_ADDR"]
        );

        $this->_storage->parameter("midcom.helper.datamanager", "lock", serialize($lock));
        debug_print_r("DATAMANAGER: set lock", $lock);

        $this->_lock = $lock;
        if ($user)
        {
            $this->_lock["user_record"] = $user;
            $this->_lock["user_name"] = $user->name;
        }
        else
        {
            $this->_lock["user_record"] = null;
            $this->_lock["user_name"] = $this->_l10n->get("anonymous user");
        }
        $this->_ourlock = true;

        debug_pop();
        return true;
    }

    /**
     * This will clear any existing lock on the storage object. Note, that it will
     * check the permissions of the user and the lock. Returns true if the lock was
     * successfully cleared.
     *
     * @return boolean Indicating success.
     * @access private
     */
    function _clear_lock() {
        if (is_null($this->_storage))
            return false;

        $lockdata = $this->_storage->parameter("midcom.helper.datamanager", "lock");
        if ($lockdata == false) {
            $this->_lock = false;
            return true;
        }

        $lock = unserialize($lockdata);

        if (($lock["time"] + $this->_layout["locktimeout"] * 60) < time()) {
            debug_add ("DATAMANAGER: Lock timeout, deleting it");
            $this->_lock = false;
            return $this->_storage->parameter("midcom.helper.datamanager", "lock", "");
        }

        $midgard = $_MIDCOM->get_midgard();
        $user = mgd_get_person($midgard->user);
        if ($midgard->admin) {
            debug_add("DATAMANAGER: Cleared lock due to admin privileges");
            $this->_lock = false;
            return $this->_storage->parameter("midcom.helper.datamanager", "lock", "");

        } else if ($user != false && $this->_layout["lockoverride"] == "poweruser") {
            if ($user->parameter("Interface","Power_User") == "YES") {

                debug_add("DATAMANAGER: Cleared lock due to poweruser privileges");
                $this->_lock = false;
                return $this->_storage->parameter("midcom.helper.datamanager", "lock", "");
            }

        } else if ($user != false && $this->_layout["lockoverride"] == "all") {
            debug_add("DATAMANAGER: Cleared lock due to alluser privileges");
            $this->_lock = false;
            return $this->_storage->parameter("midcom.helper.datamanager", "lock", "");
        }

        // We can't delete the lock due to special privileges, so we check if we
        // own the lock.

        if (
              (
                  ($user == false && $lock["user"] == false)
               || ($user->id == $lock["user"])
              )

            && $lock["client_ip"] == $_SERVER["REMOTE_ADDR"]
           )
        {
            debug_add ("Clearing our own lock.");
            $this->_lock = false;
            return $this->_storage->parameter("midcom.helper.datamanager", "lock", "");
        } else {
            debug_add ("Denied clearing lock due to insufficient privileges.");
            $this->_lock = $lock;
            return false;
        }
    }

    /**
     * Reindexes all blobs (or subtypes thereof) set to autoindex mode.
     *
     * The calls are relayed to the blob method autoreindex.
     *
     * @see midcom_helper_datamanager_datatype_blob::autoindex()
     */
    function reindex_autoindex_blobs()
    {
        foreach ($this->_fields as $name => $field)
        {
            if (   is_a($this->_datatypes[$name], 'midcom_helper_datamanager_datatype_blob')
                || is_a($this->_datatypes[$name], 'midcom_helper_datamanager_datatype_collection'))
            {
                $object =& $this->_datatypes[$name];
                $object->autoindex();
            }
        }
    }

    /**
     * Call this function if you no longer need the DM instance. It will drop
     * all instantiated classes and resolve the cyclic references which prevent
     * a DM instance to be garbage collected by PHP.
     */
    function destroy()
    {
        $this->_destroy_types();
    }

    /**
     * Internal helper which destroys all datatypes.
     *
     * @access private
     */
    function _destroy_types()
    {
        foreach ($this->_datatypes as $key => $copy)
        {
            $this->_datatypes[$key]->destroy();
            unset ($this->_datatypes[$key]);
        }
        $this->_datatypes = null;
    }
}

?>