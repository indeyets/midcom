<?php
/**
 * @package net.nehmer.jobmarket
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Job Market Schema callback, takes the configuration of the current request context
 * and works with the arrays in there. Cannot work outside the component scope.
 *
 * @package net.nehmer.jobmarket
 */

class net_nehmer_jobmarket_callbacks_configlists extends midcom_baseclasses_components_purecode
{
    /**
     * The information we should list, one of 'sector' or 'location'.
     *
     * @var string
     * @access private
     */
    var $_mode = null;

    /**
     * The array with the data we're working on.
     *
     * @var array
     * @access private
     */
    var $_data = null;

    /**
     * Initializes the class to a certain _list element from the current context configuraiton,
     * not much magic.
     *
     * @param string $mode The config key to bind to, without the _list suffix.
     */
    function net_nehmer_jobmarket_callbacks_configlists($mode)
    {
        $this->_component = 'net.nehmer.jobmarket';
        $this->_mode = $mode;

        parent::midcom_baseclasses_components_purecode();

        $data =& $_MIDCOM->get_custom_context_data('request_data');
        $this->_data = $data['config']->get("{$mode}_list");
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