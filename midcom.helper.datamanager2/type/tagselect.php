<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: text.php 3858 2006-08-23 16:18:26Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require_once(MIDCOM_ROOT . '/midcom/helper/datamanager2/type/select.php');

/**
 * Datamanager 2 tag datatype. The values encapsulated by this type are
 * passed to the net.nemein.tag library and corresponding tag objects and
 * relations will be handled there or to callback functions if set.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_type_tagselect extends midcom_helper_datamanager2_type_select
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
    var $selection = array();

    /**
     * This flag controls whether multiple selections are allowed, or not.
     *
     * @var boolean
     * @access public
     */
    var $enable_saving_to_callback = false;

    /**
     * This flag controls whether we use net_nehmer_tag or not
     *
     * @var boolean
     * @access public
     */
    var $use_tag_library = true;

    /**
     * This flag controls whether we force net_nehmer_tag to be used as the saving location
     * or should we use callback or storage
     *
     * @var boolean
     * @access public
     */
    var $force_saving_to_tag_library = false;

    /**
     * This flag controls whether we force net_nehmer_tag to be used as the location
     * to read tags data on rendering or should we use callback or storage
     *
     * @var boolean
     * @access public
     */
    var $force_rendering_from_tag_library = false;

    /**
     * This flag controls whether we require tags found with net_nehmer_tag
     * to exist in callback also (we check with key_exists with second argument set as true). This is only applied if we have callback defined.
     *
     * @var boolean
     * @access public
     */
    var $must_exist_also_in_callback = false;

    /**
     * The arguments to pass to the option callback constructor.
     *
     * @var mixed
     * @access public
     */
    var $option_callback_args = null;

    /**
     *
     * @var Array
     * @access private
     */
    var $_data_template = array();

    /**
     * This event handler is called after construction, so passing references to $this to the
     * outside is safe at this point.
     *
     * @return boolean Indicating success, false will abort the type construction sequence.
     * @access protected
     */
    function _on_initialize()
    {
        if (   $this->options === null
            && $this->option_callback === null
            && $this->use_tag_library == false)
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

            $this->_callback = new $classname($this->option_callback_args);
            $this->_callback->set_type($this);

            debug_add("classname: {$classname}");

            $this->use_tag_library = false;
        }
        else
        {
            $this->use_tag_library = true;

            $this->_data_template = array(
                'id' => '',
                'name' => '',
                'color' => '8596b6'
            );

            $_MIDCOM->load_library('net.nemein.tag');
        }

        $this->allow_multiple = true;
        $this->multiple_storagemode = 'array';

        if (!is_array($this->option_callback_args))
        {
            $this->option_callback_args = array();
        }

        // debug_add("this->allow_multiple: {$this->allow_multiple}");
        // debug_add("this->multiple_storagemode: {$this->multiple_storagemode}");
        // debug_add("this->enable_saving_to_callback: {$this->enable_saving_to_callback}");
        // debug_add("this->use_tag_library: {$this->use_tag_library}");
        // debug_add("this->force_saving_to_tag_library: {$this->force_saving_to_tag_library}");

        debug_pop();
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
        debug_push_class(__CLASS__, __FUNCTION__);
        $key = (string) $key;

        debug_add("key: {$key}");

        if (! $this->key_exists($key))
        {
            if ($this->require_corresponding_option)
            {
                debug_pop();
                return null;
            }
        }

        if (   $this->use_tag_library
            || $this->force_rendering_from_tag_library)
        {
            debug_pop();
            return $key;
        }

        if ($this->option_callback === null)
        {
            debug_add("Use options");
            debug_pop();
            return $this->options[$key]['name'];
        }
        else
        {
            debug_add("get from callback");
            debug_pop();
            return $this->_callback->get_name_for_key($key);
        }
    }

    /**
     * Returns the data for a given key.
     *
     * @param string $key The key index to look up.
     * @return array Data associated with the key, or null, if the key was not found.
     */
    function get_data_for_key($key)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $key = (string) $key;

        debug_add("key: {$key}");

        if (! $this->key_exists($key))
        {
            if ($this->require_corresponding_option)
            {
                debug_add("Key not found and corr.opt enabled");

                debug_pop();
                return null;
            }
        }

        if (   $this->use_tag_library
            || $this->force_rendering_from_tag_library)
        {
            $data = $this->_data_template;

            $data['id'] = $key;
            $data['name'] = $key;

            debug_print_r("tag_lib data:", $data);

            debug_pop();
            return $data;
        }

        if ($this->option_callback === null)
        {
            debug_add("this->options[{$key}]: {$this->options[$key]}");
            debug_pop();
            return $this->options[$key];
        }
        else
        {
            debug_add("get from callback");

            $data = $this->_callback->get_data_for_key($key);
            debug_print_r('got data from callback',$data);

            debug_pop();
            return $data;
        }
    }

    /**
     * Checks, whether the given key is known.
     *
     * @param string $key The key index to look up.
     * @return boolean True if the key is known, false otherwise.
     */
    function key_exists($key)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $key = (string) $key;

        debug_add("key: {$key}");

        if (   $this->use_tag_library
            || $this->force_saving_to_tag_library)
        {
            debug_add("use tag lib");

            if (! $this->storage->object)
            {
                debug_add("no storage available");
                debug_pop();
                return false;
            }

            $tags = net_nemein_tag_handler::get_object_tags($this->storage->object);
            if (array_key_exists($key, $tags))
            {
                debug_pop();
                return true;
            }
        }

        if ($this->option_callback === null)
        {
            debug_add("use options");
            debug_pop();
            return array_key_exists($key, $this->options);
        }
        else
        {
            debug_add("use callback");
            debug_pop();
            return $this->_callback->key_exists($key);
        }
    }

    /**
     * Returns the full listing of all available key/data pairs.
     *
     * @return Array Listing of all keys, as an associative array.
     */
    function list_all()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (   empty($this->options)
            && (   $this->use_tag_library
                || $this->force_saving_to_tag_library)
            )
        {
            debug_add("use tag lib");

            $all_tags = array();

            if (! $this->storage->object)
            {
                debug_pop();
                return $all_tags;
            }

            $tags = net_nemein_tag_handler::get_object_tags($this->storage->object);
            foreach ($tags as $name => $url)
            {
                $all_tags[$name] = $name;
            }

            debug_print_r('all_tags',$all_tags);

            debug_pop();
            return $all_tags;
        }

        if ($this->option_callback === null)
        {
            debug_add("use options");
            debug_pop();
            return $this->options;
        }
        else
        {
            debug_add("get from callback");
            debug_pop();
            return $this->_callback->list_all();
        }
    }

    /**
     * Converts storage format to live format, all invalid keys are dropped, and basic validation
     * is done to ensure constraints like allow_multiple are met.
     */
    function convert_from_storage($source)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_print_r("source",$source);

        $this->selection = Array();

        if ($this->option_callback !== null)
        {
            $source = $this->_callback->list_all();
        }

        if (   $this->use_tag_library
            || $this->force_saving_to_tag_library)
        {
            if (! $this->storage->object)
            {
                $source = null;
            }
            else
            {
                $tags = net_nemein_tag_handler::get_object_tags($this->storage->object);
                $source = net_nemein_tag_handler::tag_array2string($tags);
            }
            debug_add("tag lib single source: {$source}");
        }

        if (   $source === false
            || $source === null)
        {
            // We are fine at this point.
            debug_pop();
            return;
        }
        if ($this->allow_multiple)
        {
            // In multiselect mode, we need to convert as per type setting.
            $source = $this->_convert_multiple_from_storage($source);
        }
        else
        {
            if (! is_array($source))
            {
                $source = array($source);
            }
        }

        debug_add("final source: {$source}");

        foreach ($source as $key)
        {
            $key = (string) $key;
            if ($this->key_exists($key))
            {
                $this->selection[] = $key;
                if (! $this->allow_multiple)
                {
                    // Whatever happens, in this mode we only have one key.
                    debug_pop();
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
                    debug_pop();
                    return;
                }
            }
            else if ($this->allow_other)
            {
                $this->others[] = $key;

                if (! $this->allow_multiple)
                {
                    // Whatever happens, in this mode we only have one key.
                    debug_pop();
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

    function convert_to_raw()
    {
        return $this->selection;
    }

    /**
     * Merges selection and others arrays, the validation cycle ensures that they are
     * right.
     *
     * @return Array The storage information.
     */
    function convert_to_storage()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if ($this->allow_multiple)
        {
            debug_pop();
            return $this->_convert_multiple_to_storage();
        }
        else
        {
            if (   $this->use_tag_library
                || $this->force_saving_to_tag_library)
            {
                debug_add("use tag lib");

                if (   $this->allow_other
                    && !empty($this->others))
                {
                        $tags[$this->others[0]] = '';
                }
                else
                {
                    if (count($this->selection) == 0)
                    {
                        debug_pop();
                        return '';
                    }
                    else
                    {
                        $tags[$this->selection[0]] = '';
                    }
                }

                debug_print_r('new tags to be saved to n.n.tag',$tags);

                $status = net_nemein_tag_handler::tag_object($this->storage->object, $tags);
                if (!$status)
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_print_r('Tried to save the tags',$tags);
                    debug_add("for field {$this->name}, but failed. Ignoring silently.", MIDCOM_LOG_WARN);
                    debug_pop();
                }

                $tmp_tags = net_nemein_tag_handler::get_object_tags($this->storage->object);
                $tags = array();
                foreach ($tmp_tags as $name => $url)
                {
                    $tags[$name] = $name;
                }

                debug_add("new tags: {$tags}");

                debug_pop();
                return null;
            }

            if (   $this->option_callback !== null
                && $this->enable_saving_to_callback)
            {
                if (   $this->allow_other
                    && !empty($this->others))
                {
                    $tags = $this->others[0];
                }
                else
                {
                    if (count($this->selection) == 0)
                    {
                        debug_pop();
                        return '';
                    }
                    else
                    {
                        $tags = $this->selection[0];
                    }
                }

                debug_print_r('new tags to be saved to callback',$tags);

                $this->_callback->save_values($tags);
                debug_pop();
                return null;
            }

            if (   $this->allow_other
                && !empty($this->others))
            {
                debug_pop();
                return $this->others[0];
            }
            else
            {
                if (count($this->selection) == 0)
                {
                    debug_pop();
                    return '';
                }
                else
                {
                    debug_pop();
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
        debug_push_class(__CLASS__, __FUNCTION__);

        $glue = '|';

        if (   $this->use_tag_library
            || $this->force_saving_to_tag_library)
        {
            debug_add("use tag lib");

            $tags = net_nemein_tag_handler::string2tag_array($source);
            $source = array();
            foreach ($tags as $name => $url)
            {
                if ($this->must_exist_also_in_callback)
                {
                    if ($this->key_exists($name,true))
                    {
                        $source[$name] = $name;
                    }
                }
                else
                {
                    $source[$name] = $name;
                }
            }

            debug_print_r('source',$source);

            debug_pop();
            return $source;
        }

        switch ($this->multiple_storagemode)
        {
            case 'serialized':
            case 'array':
                if (   !is_array($source)
                    && empty($source))
                {
                    $source = array();
                }
                debug_print_r("array source:", $source);
                debug_pop();
                return $source;

            case 'imploded':
                debug_pop();
                return explode($glue, $source);

            case 'imploded_wrapped':
                debug_pop();
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
        debug_push_class(__CLASS__, __FUNCTION__);

        if (   $this->option_callback !== null
            && $this->enable_saving_to_callback)
        {
            if (   $this->allow_other
                && !empty($this->others))
            {
                $tags = array_merge($this->selection, $this->others);
            }
            else
            {
                if (count($this->selection) == 0)
                {
                    debug_pop();
                    return null;
                }
                else
                {
                    $tags = $this->selection;
                }
            }

            debug_print_r('new tags to be saved to callback',$tags);

            $this->_callback->save_values($tags);
            debug_pop();
            return null;
        }

        if (   $this->use_tag_library
            || $this->force_saving_to_tag_library)
        {
            debug_add("use tag lib");

            if (   $this->allow_other
                && !empty($this->others))
            {
                $merged = array_merge($this->selection, $this->others);
                foreach ($merged as $k => $tag)
                {
                    $tags[$tag] = '';
                }
            }
            else
            {
                if (count($this->selection) == 0)
                {
                    debug_pop();
                    return null;
                }
                else
                {
                    foreach ($this->selection as $k => $tag)
                    {
                        $tags[$tag] = '';
                    }
                }
            }

            debug_print_r('new tags to be saved to n.n.tag',$tags);

            $status = net_nemein_tag_handler::tag_object($this->storage->object, $tags);
            if (!$status)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('Tried to save the tags',$tags);
                debug_add("for field {$this->name}, but failed. Ignoring silently.", MIDCOM_LOG_WARN);
                debug_pop();
            }

            $tmp_tags = net_nemein_tag_handler::get_object_tags($this->storage->object);
            $tags = array();
            foreach ($tmp_tags as $name => $url)
            {
                $tags[$name] = $name;
            }

            debug_print_r("new tags:",$tags);

            debug_pop();
            return null;
        }

        switch ($this->multiple_storagemode)
        {
            case 'array':
            case 'serialized':
                if ($this->others)
                {
                    debug_pop();
                    return array_merge($this->selection, $this->others);
                }
                else
                {
                    debug_pop();
                    return $this->selection;
                }

            case 'imploded':
                $options = $this->_get_imploded_options();
                debug_pop();
                return $options;

            case 'imploded_wrapped':
                $glue = '|';
                $options = $this->_get_imploded_options();
                debug_pop();
                return "{$glue}{$options}{$glue}";

            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "The multiple_storagemode '{$this->multiple_storagemode}' is invalid, cannot continue.");
                // This will exit.
        }
    }

    /**
     * The validation callback ensures that we don't have an array or an object
     * as a value, which would be wrong.
     *
     * @return boolean Indicating validity.
     */
    function _on_validate()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (   ! $this->allow_other
            && $this->others)
        {
            $this->validation_error = $this->_l10n->get('type select: other selection not allowed');
            debug_pop();
            return false;
        }

        if (   ! $this->allow_multiple
            && count($this->selection) > 1)
        {
            $this->validation_error = $this->_l10n->get('type select: multiselect not allowed');
            debug_pop();
            return false;
        }

        debug_pop();
        return true;
    }

}

?>