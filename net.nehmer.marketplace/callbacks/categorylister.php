<?php
/**
 * @package net.nehmer.marketplace
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Marketplace Schema callback, post-processes the available categories and makes them
 * accessible. This callback can only be used from within the marketplace component, since
 * it relies on its component context to be correctly initialized.
 *
 * @package net.nehmer.marketplace
 */

class net_nehmer_marketplace_callbacks_categorylister extends midcom_baseclasses_components_purecode
{
    /**
     * The array with the data we're working on.
     *
     * @var array
     * @access private
     */
    var $_data = null;

    /**
     * Initializes the class to the category listing in the configuration. It does the necessary
     * postprocessing to move the configuration syntax to the rendering one.
     */
    function net_nehmer_marketplace_callbacks_categorylister()
    {
        $this->_component = 'net.nehmer.marketplace';

        parent::__construct();

        $data =& $_MIDCOM->get_custom_context_data('request_data');
        $this->_data = $data['config']->get('categories');
        foreach ($this->_data as $key => $copy)
        {
            $this->_data[$key] = str_replace('|', ': ', $copy);
        }
    }

    /** Ignored. */
    function set_type(&$type) {}

    function get_name_for_key($key)
    {
        return $this->_data[$key];
    }

    function key_exists($key)
    {
        return array_key_exists($key, $this->_data);
    }

    function list_all()
    {
        return $this->_data;
    }

}

?>