<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications Schema callback, retrieves a category listing from the database and makes it
 * availalble in a way compatible for the select DM2 type.
 *
 * It is only geared for places where you use fixed listings in the 'options' entry of the
 * corresponding category. It does <em>not</em> wrap any callbacks.
 *
 * The keys will automatically be merged with the category group identifier so that valid keys
 * for the cateogry field of the categorymap class are available.
 *
 * This call supports both callback-style and directly configured category list entries using
 * the configuration options 'options', 'options_callback' and 'options_callback_args'. 
 * 
 * Optionally, you can have separate callbacks used for the category index on-site. This is
 * useful if you want to hide categories in the index but have them available for assignment 
 * in DM2. If such prefixed keys are not found, the system reverts to the standard callbacks.
 * 
 * Note, that option_callback and option_callback_args always have to come in matching
 * pairs (even for site_ mode). There is no default for option_callback_args.
 *
 * @package net.nehmer.publications
 */

class net_nehmer_publications_callbacks_categorylister extends midcom_baseclasses_components_purecode
{
    /**
     * The array with the data we're working on. Always contains fully qualified category identifiers.
     *
     * @var array
     * @access private
     */
    var $_data = null;

    /**
     * The callback class instance, a callback matching the signature required for the DM2 select
     * type callbacks.
     *
     * @var object
     * @access private
     */
    var $_callback = null;

    /**
     * The category group being listed.
     *
     * @var int
     * @access private
     */
    var $_group = null;

    /**
     * The number of characters which have to be stripped off to transform a fully qualified#
     * category identifier to a local one. Thus, one can substr($key, $this->_group_prefix_length)
     * at all times.
     *
     * @var int
     * @access private
     */
    var $_group_prefix_length = null;

    /**
     * Initializes the class to the category listing in the configuration. It does the necessary
     * postprocessing to move the configuration syntax to the rendering one.
     *
     * If $sitelisting is true, the component is requesting the listing for the site category 
     * index. In that case "site_" prefixed config options take precedence over the standard
     * option names to allow you to have limited category listings on-site.
     *
     * @param int $group The category group to list.
     * @param bool $sitelisting The callback is used to display the onsite listing instead of
     *     the standard DM2 interface 
     */
    function net_nehmer_publications_callbacks_categorylister($group, $sitelisting = false)
    {
        $this->_component = 'net.nehmer.publications';

        parent::midcom_baseclasses_components_purecode();

        $this->_group = $group;
        $this->_group_prefix_length = strlen($this->_group) + 1;
        $data =& $_MIDCOM->get_custom_context_data('request_data');
        $category_config = $data['config']->get('categories');

        if (! array_key_exists($this->_group, $category_config))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The category group {$this->_group} is invalid, cannot load categories.");
            // This will exit.
        }

        $category_group_config = $category_config[$this->_group];

        if (   $sitelisting
            && array_key_exists('site_option_callback', $category_group_config))
        {
            $this->_initialize_from_callback($category_group_config['site_option_callback'], 
                $category_group_config['site_option_callback_args']);
        }
        else if (array_key_exists('option_callback', $category_group_config))
        {
            $this->_initialize_from_callback($category_group_config['option_callback'], 
                $category_group_config['option_callback_args']);
        }
        else if (   $sitelisting
                 && array_key_exists('site_options', $category_group_config)) 
        {
            $this->_initialize_from_options($category_group_config['site_options']);
        }
        else
        {
            $this->_initialize_from_options($category_group_config['options']);
        }

    }

    /**
     * Initializes the data list from a static option list. Merges the options with the category key.
     *
     * It will try to auto-load the callback according to the same rules as the DM2 select type
     * callback loader.
     *
     * @param string $classname The name of the callback.
     */
    function _initialize_from_callback($classname, $args)
    {
        if (! class_exists($classname))
        {
            // Try auto-load.
            $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $classname) . '.php';
            if (! file_exists($path))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Auto-loading of the class {$classname} from {$path} failed: File does not exist.");
                // This will exit.
            }
            require_once($path);
        }

        if (! class_exists($classname))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The class {$classname} was defined as option callback for the field {$this->name} but did not exist.");
            // This will exit.
        }

        $this->_callback = new $classname($args);
    }

    /**
     * Initializes the data list from a static option list. Merges the options with the category key.
     *
     * @param Array $categories The options to use.
     */
    function _initialize_from_options($categories)
    {
        if (   ! $categories
            || ! is_array($categories))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Could not load the publications category {$group}, the key was either not found or empty or no array.");
            // This will exit.
        }

        $this->_data = Array();
        foreach ($categories as $key => $value)
        {
            $this->_data["{$this->_group}-{$key}"] = $value;
        }
    }

    /** Ignored. */
    function set_type(&$type) {}

    function get_name_for_key($key)
    {
        if ($this->_callback)
        {
            return $this->_callback->get_name_for_key(substr($key, $this->_group_prefix_length));
        }
        else
        {
            return $this->_data[$key];
        }
    }

    function key_exists($key)
    {
        if ($this->_callback)
        {
            return $this->_callback->key_exists(substr($key, $this->_group_prefix_length));
        }
        else
        {
            return array_key_exists($key, $this->_data);
        }
    }

    function list_all()
    {
        if ($this->_callback)
        {
            $data = $this->_callback->list_all();
            $result = Array();
            foreach ($data as $key => $value)
            {
                $result["{$this->_group}-{$key}"] = $value;
            }
            return $result;
        }
        else
        {
            return $this->_data;
        }
    }

}
?>