<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: geocoder.php 11571 2007-08-13 11:07:02Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Position geocoding factory class. All geocoders inherit from this.
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_geocoder extends midcom_baseclasses_components_purecode
{
    /**
     * Error code from trying to geocode. Either a mgd_errstr() or an additional error code from component
     *
     * @var string
     */
    var $error = 'MGD_ERR_OK';

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function __construct()
    {
         $this->_component = 'org.routamc.positioning';
         parent::__construct();
    }

    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param Array $location Parameters to geocode with, conforms to XEP-0080
     * @return Array containing geocoded information
     */
    function geocode($location)
    {
        return null;
    }

    /**
     * This is a static factory method which lets you dynamically create geocoder instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * On any error (class not found etc.) the factory method will call generate_error.
     *
     * <b>This function must be called statically.</b>
     *
     * @param string $type The type of the geocoder (the file name from the geocoder directory).
     * @return org_routamc_positioning_geocoder A reference to the newly created geocoder instance.
     * @static
     */
    function & create($type)
    {
        $filename = MIDCOM_ROOT . "/org/routamc/positioning/geocoder/{$type}.php";
        if (!file_exists($filename))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Geocoder {$type} not available.");
            // This will exit.
        }

        $classname = "org_routamc_positioning_geocoder_{$type}";
        require_once($filename);
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }
}