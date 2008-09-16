<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager 2 Form Manager data filter base class.
 *
 * This class allows you to create form data filter subclasses, see the schema documentation
 * in the Midgard Wiki for details.
 *
 * @package midcom.helper.datamanager2
 */

class midcom_helper_datamanager2_baseclasses_filter extends midcom_baseclasses_core_object
{
    /**
     * A reference to the Formmanager instance.
     *
     * @var midcom_helper_datamanager2_formmanager
     * @access protected
     */
    var $_formmanager = null;

    /**
     * Configuration as passed from the callback.
     *
     * @var mixed
     * @access protected
     */
    var $_config = null;

    /**
     * The name of the schema field we are currently processing.
     *
     * @var string
     * @access protected
     */
    var $_fieldname = null;

    var $_lastfield = null;

    /**
     * Standard constructor, as defined by the schema specification. Pre initializes all
     * members.
     *
     * @param midcom_helper_datamanager2_formmanager &$formmanager The formmanager we are bound to.
     * @param mixed $config The configuration we are operating on.
     */
    function __construct(&$formmanager, $config)
    {
        $this->_formmanager =& $formmanager;
        $this->_config =& $config;
    }

    /**
     * Simple setter, populates the $_fieldname member.
     *
     * @param string $name The new field name
     */
    function set_fieldname ($name)
    {
        $this->_fieldname = $name;
    }

    /**
     * Actual callback. Be aware, that QF might call this more then once for a single schema
     * field, since it calls it recursively and form-element-wise.
     *
     * @param mixed $input The form values which should be filtered.
     * @return mixed The filtered values.
     */
    function execute ($input)
    {
        die ('The function ' . __CLASS__ . '::' . __FUNCTION__ . ' must be overridden.');
    }
}
?>