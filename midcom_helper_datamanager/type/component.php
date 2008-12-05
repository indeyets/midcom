<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: select.php 14892 2008-02-12 10:18:30Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Simple select type. This allows the selection of one or more values
 * from a given list.
 *
 * @package midcom_helper_datamanager
 *
 */
class midcom_helper_datamanager_type_component extends midcom_helper_datamanager_type_select
{
    /**
     * A list of the currently selected keys. This is an array even for single select
     * types, in which case the validation limits it to one item. The values array
     * consists only of the object keys, use the resolver function to get the corresponding
     * values.
     *
     * @var array
     * @access public
     */
    public $selection = array();

    /**
     * The options available to the client. You should not access this variable directly,
     * as this information may be loaded on demand, depending on the types configuration.
     *
     * @see get_all_options()
     * @var array
     * @access public
     */
    public $options = array();
    
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
    public $others = array();
    
    /**
     * Set this to true if you want to allow selection of values not part of the regular
     * selection list. In this case you'll find the other options collected in the $others
     * member.
     *
     * @var boolean
     * @access public
     */
    public $allow_other = false;

    public $value = '';

    /**
     * This flag controls whether multiple selections are allowed, or not.
     *
     * @var boolean
     * @access public
     */
    protected $allow_multiple = false;

    protected $multiple_storagemode = 'serialized';

    /**
     * Glue that will be used for separating the keys
     * 
     * @var string
     * @access public
     */
    protected $multiple_separator = '|';

    /**
     * Initialize the class, if necessary, create a callback instance, otherwise
     * validate that an option array is present.
     */
    function on_initialize()
    {
        if (   !isset($this->options)
            || !is_array($this->options))
        {
            throw new midcom_helper_datamanager_exception_type('options is not an array');
        }

        foreach( $_MIDCOM->componentloader->manifests as $key => $val)
        {
            if( !isset($val['library']))
            {
                $this->options[$key] = "{$val['component']} {$val['version']}";
            }
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
        return $this->options[$key];
        /* Reminder 
        if ($this->option_callback === null)
        {
            return $this->options[$key];
        }
        return $this->_callback->get_name_for_key($key);
        */
    }

    /**
     * Checks, whether the given key is known.
     *
     * @param string $key The key index to look up.
     * @return boolean True if the key is known, false otherwise.
     */
    function key_exists($key)
    {
        $key = (string) $key;
        return array_key_exists($key, $this->options);

        /* Reminder
        if ($this->option_callback === null)
        {
            return array_key_exists($key, $this->options);
        }
        return $this->_callback->key_exists($key);
        */
    }

    /**
     * Returns the full listing of all available key/value pairs.
     *
     * @return array Listing of all keys, as an associative array.
     */
    function list_all()
    {
        return $this->options;
        /* reminder
        if ($this->option_callback === null)
        {
            return $this->options;
        }
        return $this->_callback->list_all();
        */
    }

    /**
     * Converts storage format to live format, all invalid keys are dropped, and basic validation
     * is done to ensure constraints like allow_multiple are met.
     */
    function convert_from_storage($source)
    {
        $this->selection = array();
        $this->others = array();

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

            $source = array($source);
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
     * @return array The storage information.
     */
    function convert_to_storage()
    {
        /**
          * Checking if a given component is available.
          * Fall back is empty string that is a midcom_core page.
          */
        if( array_key_exists($this->value, $this->options))
        {
            return $this->value;
        }
        else
        {
            return '';
        }
    }


     /**
     * The validation callback ensures that we dont't have an array or an object
     * as a value, which would be wrong.
     *
     * @return boolean Indicating validity.
     */
    function on_validate()
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
        $selection = array();
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
                $values = array_merge($selection, array($this->others));
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