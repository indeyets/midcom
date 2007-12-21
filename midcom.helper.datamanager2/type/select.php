<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Simple select type. This allows the selection of one or more values
 * from a given list. It is possible to enable adding "unreferenced" items in a "others"
 * listing, but those are outside the normal processing.
 *
 * <b>Available configuration options:</b>
 *
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
 * - <i>bool allow_other:</i> If this flag is set, the system allows the addition of
 *   values not in the option list. All unknown values will be merged into a single
 *   comma separated listing of unknown options during loading, which will be kept in
 *   that simple string representation. Otherwise, unknown keys will be forbidden, on
 *   validations they cause a validation error, on loading they are dropped silently.
 *   This option is set to false by default.
 * - <i>bool allow_multiple:</i> If this flag is set, you may select more then one
 *   option. This is disabled by default. If this feature is disabled, the loader
 *   code will drop all matches beyond the first match.
 * - <i>bool csv_export_key:</i> If this flag is set, the CVS export will store the
 *   field key instead of its value. This is only useful if the foreign tables referenced
 *   are available at the site of import. This flag is not set by default. Note, that
 *   this does not affect import, which is only available with keys, not values.
 * - <i>string multiple_storagemode:</i> Controls how multiple options are stored in
 *   a single field. See below "multiselect storagemodes". Defaults to "serialized".
 *
 * Keys should be alphanumeric only.
 *
 * <b>Multiselect storage modes</b>
 *
 * This type knows three ways of storing multiselect data:
 *
 * - 'serialized' will just store a serialized array
 * - 'imploded' will implode the keys using '|' as a separator
 * - 'imploded_wrapped' behaves like 'imploded' except that it will wrap the saved
 *   string again in '|'s thus yielding something like |1|2|3|...|. This is useful
 *   if you want to use like queries to look up values in such fields.
 *
 * Naturally, both 'imploded' storage modes don't allow a '|' being part of a key.
 * This is only checked during storage (due to performance reasons); if an invalid
 * element is found there, it will be skipped and logged. No error will be shown
 * on-site.
 *
 * <b>Option Callback class</b>
 *
 * These classes must follow a simple interface:
 *
 * <code>
 * class callback
 * {
 *     function callback($arg) {}
 *     function set_type(&$type) {}
 *     function get_name_for_key($key) { return $name; }
 *     function key_exists($key) { return $bool; }
 *     function list_all() { return $options; }
 * }
 * </code>
 *
 * Upon type startup, the set_type call is executed giving you a reference to the type
 * you are supplying with information. You may ignore this call (but it has to be defined
 * to satisfy PHP).
 *
 * The list_all option must use the same return format as the options array would normally
 * have. One instance of this class is created per type.
 *
 * You can safely assume that get_name_for_key receives only valid keys.
 *
 * The class is loaded using require_once by translating it to a path relative to midcom_root
 * prior to instantiation. If the class cannot be loaded from the filesystem but from a
 * snippet, you need to include that snippet previously, an auto-load from there is not
 * yet possible.
 *
 */
class midcom_helper_datamanager2_type_select extends midcom_helper_datamanager2_type
{
    /**
     * A list of the currently selected keys. This is an array even for single select
     * types, in which case the validation limits it to one item. The values array
     * consists only of the object keys, use the resolver function to get the corresponding
     * values.
     *
     * @var Array
     * @access public
     */
    var $selection = Array();

    /**
     * This member contains the other key, in case it is set. In case of multiselects,
     * the full list of unknown keys is collected here, in case of single select, this value
     * takes precedence from the standard selection.
     *
     * This is only valid if the allow_other flag is set.
     *
     * @var String
     * @access public
     */
    var $others = Array();

    /**
     * The options available to the client. You should not acecss this variable directly,
     * as this information may be loaded on demand, depending on the types configuration.
     *
     * @see get_all_options();
     * @var Array
     * @access public
     */
    var $options = null;

    /**
     * In case the options are returned by a callback, this member holds the name of the
     * class.
     *
     * @var string
     * @access public
     */
    var $option_callback = null;

    /**
     * The argument to pass to the option callback constructor.
     *
     * @var mixed
     * @access public
     */
    var $option_callback_arg = null;

    /**
     * Set this to true if you want to allow selection of values not part of the regular
     * selection list. In this case you'll find the other options collected in the $others
     * member.
     *
     * @var bool
     * @access public
     */
    var $allow_other = false;

    /**
     * This flag controls whether multiple selections are allowed, or not.
     *
     * @var bool
     * @access public
     */
    var $allow_multiple = false;

    /**
     * Set this to false to use with universalchooser, this skips making sure the key exists in option list
     * Mainly used to avoid unnecessary seeks to load all a ton of objects to the options list.
     *
     * @var bool
     * @access public
     */
     var $require_corresponding_option = true;

    /**
     * Set this to true if you want the keys to be exported to the csv dump instead of the
     * values. Note, that this does not affect import, which is only available with keys, not
     * values.
     *
     * @var bool
     * @access public
     */
    var $csv_export_key = false;

    /**
     * In case the options are returned by a callback, this member holds the callback
     * instance.
     *
     * @var string
     * @access public
     */
    var $_callback = null;

    /**
     * The storage mode used when multiselect is enabled, see the class' introduction for
     * details.
     *
     * @var string
     * @access public
     */
    var $multiple_storagemode = 'serialized';

    /**
     * Initialize the class, if necessary, create a callback instance, otherwise
     * validate that an option array is present.
     */
    function _on_initialize()
    {
        if (   $this->options === null
            && $this->option_callback === null)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Either 'options' or 'option_callback' must be defined for the field {$this->name}.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   $this->options !== null
            && $this->option_callback !== null)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Both 'options' and 'option_callback' was defined for the field {$this->name}, go for one of them.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        if ($this->option_callback !== null)
        {
            $classname = $this->option_callback;

            if (! class_exists($classname))
            {
                // Try auto-load.
                $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $classname) . '.php';
                if (! file_exists($path))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Auto-loading of the class {$classname} from {$path} failed: File does not exist.", MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                require_once($path);
            }

            if (! class_exists($classname))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The class {$classname} was defined as option callback for the field {$this->name} but did not exist.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $this->_callback = new $classname($this->option_callback_arg);
            $this->_callback->set_type($this);
        }

        // Activate serialized storage format if we are in multiselect-mode.
        if (   $this->allow_multiple
            && $this->multiple_storagemode == 'serialized')
        {
            $this->serialized_storage = true;
        }
        else
        {
            $this->serialized_storage = false;
        }

        return true;
    }

    /**
     * Returns the full name for a given key. This value is not localized in any way.
     *
     * @param string $key The key index to look up.
     * @return string The name of the key in clear-text, or null, if the key was not found.
     */
    function get_name_for_key($key)
    {
        $key = (string) $key;

        if (! $this->key_exists($key))
        {
            if ($this->require_corresponding_option)
            {
                return null;
            }
            else
            {
                // This is probably universal chooser
                // FIXME: This is not exactly an elegant way to do this
                if ($this->storage->_schema->fields[$this->name]['widget'] != 'universalchooser')
                {
                    return null;
                }
                $class = $this->storage->_schema->fields[$this->name]['widget_config']['class'];
                $titlefield = $this->storage->_schema->fields[$this->name]['widget_config']['titlefield'];

                if (!$key)
                {
                    return null;
                }

                $object = new $class($key);

                if (!$object)
                {
                    return null;
                }

                if (is_array($titlefield))
                {
                    foreach($titlefield as $field)
                    {
                        if ($object->$field)
                        {
                            $titlefield = $field;
                            break;
                        }
                    }
                }
                return $object->$titlefield;
            }
        }

        if ($this->option_callback === null)
        {
            return $this->options[$key];
        }
        else
        {
            return $this->_callback->get_name_for_key($key);
        }
    }

    /**
     * Checks, whether the given key is known.
     *
     * @param string $key The key index to look up.
     * @return bool True if the key is known, false otherwise.
     */
    function key_exists($key)
    {
        $key = (string) $key;

        if ($this->option_callback === null)
        {
            return array_key_exists($key, $this->options);
        }
        else
        {
            return $this->_callback->key_exists($key);
        }
    }

    /**
     * Returns the full listing of all available key/value pairs.
     *
     * @return Array Listing of all keys, as an associative array.
     */
    function list_all()
    {
        if ($this->option_callback === null)
        {
            return $this->options;
        }
        else
        {
            return $this->_callback->list_all();
        }

    }

    /**
     * Converts storage format to live format, all invalid keys are dropped, and basic validation
     * is done to ensure constraints like allow_multiple are met.
     */
    function convert_from_storage($source)
    {
        $this->selection = Array();
        $this->others = Array();

        if (   $source === false
            || $source === null)
        {
            // We are fine at this point.
            return;
        }
        if ($this->allow_multiple)
        {
            // In multiselect mode, we need to convert as per type setting.
            $source = $this->_convert_multiple_from_storage($source);
        }
        else
        {
            // If we aren't in multiselect mode, we don't get an array by default (to have
            // plain storage), therefore we typecast here. This is easier to do then having
            // the same code below twice thus unifying allow_other handling mainly.

            $source = Array($source);
        }

        foreach ($source as $key)
        {
            $key = (string) $key;
            if ($this->key_exists($key))
            {
                $this->selection[] = $key;
                if (! $this->allow_multiple)
                {
                    // Whatever happens, in this mode we only have one key.
                    return;
                }
            }
            // Done as separate check instead of || because I'm not 100% sure this is the correct place for it (Rambo)
            else if (!$this->require_corresponding_option)
            {
                $this->selection[] = $key;
                if (! $this->allow_multiple)
                {
                    // Whatever happens, in this mode we only have one key.
                    return;
                }
            }
            else if ($this->allow_other)
            {
                $this->others[] = $key;

                if (! $this->allow_multiple)
                {
                    // Whatever happens, in this mode we only have one key.
                    return;
                }
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
     * Merges selection and others arrays, the validation cycle ensures that they are
     * right.
     *
     * @return Array The storage information.
     */
    function convert_to_storage()
    {
        if ($this->allow_multiple)
        {
            return $this->_convert_multiple_to_storage();
        }
        else
        {
            if (   $this->allow_other
                && !empty($this->others))
            {
                return $this->others[0];
            }
            else
            {
                if (count($this->selection) == 0)
                {
                    return '';
                }
                else
                {
                    return $this->selection[0];
                }
            }
        }
    }

    /**
     * Converts the selected options according to the multiple_storagemode setting.
     *
     * @param mixed The stored data.
     * @return Array The stored data converted back to an Array.
     */
    function _convert_multiple_from_storage($source)
    {
        $glue = '|';

        switch ($this->multiple_storagemode)
        {
            case 'serialized':
            case 'array':
                if (   !is_array($source)
                    && empty($source))
                {
                    $source = array();
                }
                return $source;

            case 'imploded':
                if (!is_string($source))
                {
                    return array();
                }
                return explode($glue, $source);

            case 'imploded_wrapped':
                if (!is_string($source))
                {
                    return array();
                }
                return explode($glue, substr($source, 1, -1));

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "The multiple_storagemode '{$this->multiple_storagemode}' is invalid, cannot continue.");
                // This will exit.
        }
    }

    /**
     * Converts the selected options according to the multiple_storagemode setting.
     *
     * @return mixed The data converted to the final data storage.
     */
    function _convert_multiple_to_storage()
    {
        switch ($this->multiple_storagemode)
        {
            case 'array':
                return $this->selection;

            case 'serialized':
                if ($this->others)
                {
                    return array_merge($this->selection, $this->others);
                }
                else
                {
                    return $this->selection;
                }

            case 'imploded':
                $options = $this->_get_imploded_options();
                return $options;

            case 'imploded_wrapped':
                $glue = '|';
                $options = $this->_get_imploded_options();
                return "{$glue}{$options}{$glue}";

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "The multiple_storagemode '{$this->multiple_storagemode}' is invalid, cannot continue.");
                // This will exit.
        }
    }

    /**
     * Prepares the imploded storage string. All entries containing the pipe char (used as glue)
     * will be logged and skipped silently.
     *
     * @return string The imploded data string.
     */
    function _get_imploded_options()
    {
        $glue = '|';

        if ($this->others)
        {
            if (is_string($this->others))
            {
                $this->others = array
                (
                    $this->others => $this->others,
                );
            }
            $options = array_merge($this->selection, $this->others);
        }
        else
        {
            $options = $this->selection;
        }

        $result = Array();
        foreach ($options as $key)
        {
            if (strpos($key, $glue) !== false)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The option key '{$key}' contained a pipe (|) char, which is not allowed for imploded storage targets. ignoring silently.",
                    MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }

            $result[] = $key;
        }
        return implode($glue, $result);
    }

    /**
     * CSV conversion works from the storage representation, converting the arrays
     * into simple text lists.
     */
    function convert_from_csv ($source)
    {
        $source = explode(',', $source);
        $this->convert_from_storage($source);
    }

    /**
     * CSV conversion works from the storage representation, converting the arrays
     * into simple text lists.
     */
    function convert_to_csv()
    {
        if ($this->csv_export_key)
        {
            $data = $this->convert_to_storage();
            if (is_array($data))
            {
                return implode(',', $data);
            }
            else
            {
                return $data;
            }
        }
        else
        {
            $selection = Array();
            foreach($this->selection as $item)
            {
                $selection[] = $this->get_name_for_key($item);
            }
            if ($this->others)
            {
                $values = array_merge($selection, Array($this->others));
            }
            else
            {
                $values = $selection;
            }
            return implode($values, ', ');
        }
    }

    /**
     * The validateion callback ensures that we dont't have an array or an object
     * as a value, which would be wrong.
     *
     * @return bool Indicating validity.
     */
    function _on_validate()
    {
        if (   ! $this->allow_other
            && $this->others)
        {
            $this->validation_error = $this->_l10n->get('type select: other selection not allowed');
            return false;
        }

        if (   ! $this->allow_multiple
            && count($this->selection) > 1)
        {
            $this->validation_error = $this->_l10n->get('type select: multiselect not allowed');
            return false;
        }

        return true;
    }

    function combine_values()
    {
        $selection = Array();
        foreach($this->selection as $item)
        {
            $selection[] = $this->get_name_for_key($item);
        }
        if ($this->others)
        {
            if (is_array($this->others))
            {
                $values = array_merge($selection, $this->others);
            }
            else
            {
                $values = array_merge($selection, Array($this->others));
            }
        }
        else
        {
            $values = $selection;
        }
        return $values;
    }

    function convert_to_html()
    {
        $values = $this->combine_values();
        return implode($values, ', ');
    }
}
?>