<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanager Form Manager core class.
 *
 * This class controls all form rendering and basic form data i/o. It works independent
 * of any data storage, getting its defaults from some external controlling instance in
 * the form of a type array (f.x. a datamanager class can provide this). The list of types
 * is taken by-reference.
 *
 * @package midcom_helper_datamanager
 */
class midcom_helper_datamanager_formmanager
{
    /**
     * The schema (not the schema <i>database!</i>) to use for operation. This variable will always contain a parsed
     * representation of the schema, so that one can swiftly switch between individual schemas
     * of the Database.
     *
     * This member is initialized by-reference.
     *
     * @var Array
     */
    protected var $schema = null;

    /**
     * The list of types which should be used for rendering. They must match the schemadb passed
     * to the class.
     *
     * The member is initialized by-reference.
     *
     * @var Array
     */
    protected var $types = null;

    /**
     * A list of widgets, indexed by the field names from the schema, thus matching the type
     * listing.
     *
     * @var Array
     */
    public var $widgets = array();
    
    /**
     * The namespace of the form. This value is to be considered read only.
     *
     * This is the Namespace to use for all HTML/CSS/JS elements. It is deduced by the formmanager
     * and tries to be as smart as possible to work safely with more then one form on a page.
     *
     * You have to prefix all elements which must be unique using this string (it includes a trailing
     * underscore).
     *
     * @var const string
     */
    public var $namespace = '';
    
    /**
     * Initializes the Form manager with a list of types for a given schema.
     *
     * @param midcom_helper_datamanager_schema &$schema The schema to use for processing. This
     *     variable is taken by reference.
     * @param Array &$types A list of types matching the passed schema, used as a basis for the
     *     form types. This variable is taken by reference.
     */
    public function __construct(&$schema, &$types, &$widgets)
    {
        if (! is_a($schema, 'midcom_helper_datamanager_schema'))
        {
            throw new Exception('Invalid schema instance passed, cannot startup formmanager');
            // This will exit.
        }

        $this->schema =& $schema;
        $this->types =& $types;
        $this->widgets =& $widgets;
    }
    
    
}

?>