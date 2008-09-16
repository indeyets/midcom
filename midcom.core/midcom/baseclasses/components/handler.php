<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:handler.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Execution handler subclass, to be used with the request switch
 * in midcom_baseclasses_components_request.
 *
 * Use the various event handlers to customize startup.
 *
 * The basic idea is that you have separate instances of this type for the various
 * operations in your main viewer class. This avoids cluttering up the viewer class
 * and gives you better maintainability due to smaller code files.
 *
 * Under normal operation within the same component you don't need to think about any
 * specialties, the member variables are just references to the main request class
 * (also known as "viewer class").
 *
 * Noteworthy is the ability to export handlers for usage in other components in
 * both libraries and full components. To make the exported handler work correctly,
 * you need to set $this->_component to the corresponding value of the <i>exporting</i>
 * component. In this case, the startup code will take the main l10n instance, the
 * component data storage and the configuration <i>from the exporting component.</i>
 * The configuration in this case is merged from the global defaults (constructed
 * during component/library startup) and the configuration parameters set on the topic
 * <i>where it is invoked.</i>
 *
 * Note, that the export "mode" is only invoked <i>if and only if</i> the component of
 * the handler is <i>different</i> of the component of the main request class.
 *
 * @package midcom.baseclasses
 */
class midcom_baseclasses_components_handler
{
    /**#@+
     * Request state variable, set during startup. There should be no need to change it
     * in most cases.
     *
     * @access protected
     */

    /**
     * The topic for which we are handling a request.
     *
     * @var MidgardTopic
     */
    var $_topic = null;

    /**
     * The current configuration.
     *
     * @var midcom_helper_configuration
     */
    var $_config = null;

    /**
     * A handle to the i18n service.
     *
     * @var midcom_services_i18n
     */
    var $_i18n = null;

    /**
     * The components' L10n string database
     *
     * @var midcom_services__i18n_l10n
     */
    var $_l10n = null;

    /**
     * The global MidCOM string database
     *
     * @var midcom_services__i18n_l10n
     */
    var $_l10n_midcom = null;

    /**
     * Component data storage area.
     *
     * @var Array
     */
    var $_component_data = null;

    /**
     * Request specific data storage area. Registered in the component context
     * as ''.
     *
     * @var Array
     */
    var $_request_data = Array();

    /**
     * Internal helper, holds the name of the component. Should be used whenever the
     * components' name is required instead of hardcoding it.
     *
     * @var string
     */
    var $_component = null;

    /**
     * A reference to the request class that has invoked this handler instance.
     *
     * @var midcom_baseclasses_components_request
     */
    var $_master = null;

    /**
     * The node toolbar for the current request context. Not available during the can_handle
     * phase.
     *
     * @var midcom_helper_toolbar
     * @see midcom_services_toolbars
     */
    var $_node_toolbar = null;

    /**
     * The view toolbar for the current request context. Not available during the can_handle
     * phase.
     *
     * @var midcom_helper_toolbar
     * @see midcom_services_toolbars
     */
    var $_view_toolbar = null;

    /**#@-*/


    /**
     * Main constructor does not do much yet, it shouldn't be overridden though,
     * use the _on_initilize event handler instead.
     */
    function __construct()
    {
    }


    /**
     * Initializes the request handler class, called by the component interface after
     * instantiation. Required to allow safe $this references during startup.
     *
     * Be aware that it is possible that a handler can come from a different component
     * (or library) then the master class. Take this into account when getting the
     * component data storage, configuration and l10n instances. Configuration is merged
     * during runtime based on the system defaults and all parameters attached to the
     * topic <i>we're currently operating on.</i>
     *
     * @param midcom_baseclasses_components_request &$master A reference to the request class
     *     handling the request.
     */
    function initialize(&$master)
    {
        $this->_master =& $master;
        $this->_i18n =& $_MIDCOM->i18n;
        $this->_l10n_midcom =& $master->_l10n_midcom;

        $this->_request_data =& $master->_request_data;
        $this->_topic =& $master->_topic;

        // Load component specific stuff, special treatment if the handler has
        // a component different then the master handler set.
        if (   $this->_component
            && $this->_component != $master->_component)
        {
            $this->_l10n =& $this->_i18n->get_l10n($this->_component);
            $this->_component_data =& $GLOBALS['midcom_component_data'][$this->_component];
            $this->_config = $this->_component_data['config'];
            $this->_config->store_from_object($this->_topic, $this->_component);
        }
        else
        {
            $this->_component = $master->_component;
            $this->_l10n =& $master->_l10n;
            $this->_component_data =& $master->_component_data;
            $this->_config =& $master->_config;
        }

        $this->_on_initialize();
    }


    /**#@+
     * Event Handler callback.
     */

    /**
     * Initialization event handler, called at the end of the initialization process.
     * Use this for all initialization work you need, as the component state is already
     * populated when this event handler is called.
     */
    function _on_initialize()
    {
        return;
    }

    /**#@-*/
}

?>