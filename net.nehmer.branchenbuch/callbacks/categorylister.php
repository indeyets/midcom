<?php
/**
 * @package net.nehmer.branchenbuch
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Branchenbuch Schema helper class.
 *
 * This class encaspulates a few function used to manage schemas within the
 * branchenbuch, which are all tied to the account component.
 *
 * @package net.nehmer.branchenbuch
 */

class net_nehmer_branchenbuch_callbacks_categorylister extends midcom_baseclasses_components_purecode
{
    /**
     * The DB type we're working together with.
     *
     * @var midcom_helper_datamanager2_type_select
     * @access private
     */
    var $_type = null;

    /**
     * The base category we should use for listing. This must be set, or startup will fail.
     *
     * @var string
     * @access private
     */
    var $_category = null;

    function net_nehmer_branchenbuch_callbacks_categorylister($category)
    {
        // Since this file could be loaded from the outside, we load our own component
        // so that we have all utility classes available.
        $_MIDCOM->componentloader->load('net.nehmer.branchenbuch');

        $this->_component = 'net.nehmer.branchenbuch';
        $this->_category = $category;

        if (! $this->_category)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Cannot create a categorylister instance without a base category selection.');
            // This will exit.
        }
        parent::midcom_baseclasses_components_purecode();
    }

    function set_type(&$type)
    {
        $this->_type = $type;
    }

    /**
     * Internal helper function incorporating a static caching infrastructure
     * for the loaded objects. Used to prevent multiple loaders of the same
     * object, which can slow down the system considerably on large category
     * lists.
     *
     * @param string $guid The object to return
     * @return net_nehmer_branchenbuch_branche The found object, or false on failure.
     */
    function _get_object_for_guid($guid)
    {
        static $_cache = Array();

        if (! array_key_exists($guid, $_cache))
        {
            $_cache[$guid] = new net_nehmer_branchenbuch_branche($guid);
        }
        return $_cache[$guid];

    }

    function get_name_for_key($key)
    {
        static $_cache = Array();

        if (! array_key_exists($key, $_cache))
        {
            $tmp = $this->_get_object_for_guid($key);
            $_cache[$key] = $tmp->get_full_name();
        }
        return $_cache[$key];
    }

    function key_exists($key)
    {
        $tmp = $this->_get_object_for_guid($key);
        if (! $tmp)
        {
            return false;
        }

        return ($tmp->type == $this->_category);
    }

    function list_all()
    {
        static $_cache = Array();

        if (! array_key_exists($this->_category, $_cache))
        {
            // two stage operation to maintain ordering over different levels.
            $root = net_nehmer_branchenbuch_branche::get_root_category_by_type($this->_category);
            $childs = $root->list_childs();
            $result = Array();

            if ($childs)
            {
                foreach ($childs as $child)
                {
                    $subchilds = $child->list_childs();
                    if ($subchilds)
                    {
                        foreach ($subchilds as $subchild)
                        {
                            $result[$subchild->guid] = "{$child->name}: {$subchild->name}";
                        }
                    }
                    else
                    {
                        $result[$child->guid] = $child->name;
                    }
                }
            }

            $_cache[$this->_category] = $result;
        }

        return $_cache[$this->_category];
    }

}

?>