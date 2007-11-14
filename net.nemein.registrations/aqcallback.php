<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration system: Additional Questions Schema selection dropdown
 *
 * This class is used as callback for the select dropdowns used to configure
 * the additional questions available.
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_aqcallback extends midcom_baseclasses_components_purecode
{
    /**
     * The current request data context. Used to access configuration.
     *
     * @var Array
     * @access private
     */
    var $_request_data = null;

    /**
     * An array of DM2 schemas, taken from the request data.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The option listing consists of a schema name => title mapping.
     *
     * var Array
     * @access private
     */
    var $_options = Array();

    /**
     * The constructor loads the current request configuration and prepares the option listing.
     */
    function net_nemein_registrations_aqcallback()
    {
        $this->_component = 'net.nemein.registrations';
        parent::midcom_baseclasses_components_purecode();

        $this->_request_data =& $_MIDCOM->get_custom_context_data('request_data');
        // Overwrite the local configuration data with one from the request data,
        // we're topic-specific this way.
        $this->_config = $this->_request_data['config'];
        $this->_schemadb = $this->_request_data['schemadb'];

        $this->_load_options();
    }

    /**
     * Loads the schema database and computes the available AQ schemas from it.
     */
    function _load_options()
    {
        $this->_options = Array ('' => $this->_l10n->get('no selection, first defined schema will be used by default'));
        foreach ($this->_schemadb as $name => $schema)
        {
            if (   $name == $this->_config->get('registrar_schema')
                || $name == $this->_config->get('event_schema'))
            {
                // Skip registar and event schemas
                continue;
            }
            $this->_options[$name] = $schema->description;
        }
    }

    /** @ignore Function unused. */
    function set_type(&$type) {}

    function get_name_for_key($key)
    {
        return $this->_options[$key];
    }

    function key_exists($key)
    {
        return array_key_exists($key, $this->_options);
    }

    function list_all()
    {
        return $this->_options;
    }


}

?>