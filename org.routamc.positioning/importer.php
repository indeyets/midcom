<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Position importing factory class. All importers inherit from this.
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_importer extends midcom_baseclasses_components_purecode
{
    /**
     * The imported log entries
     *
     * @var org_routamc_positioning_log
     */
    var $log = null;

    /**
     * Error code from trying to import. Either an mgd_errstr() or an additional error code from component
     *
     * @var string
     */
    var $error = 'MGD_ERR_OK';

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_positioning_importer()
    {
         $this->_component = 'org.routamc.positioning';
         parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Normalize coordinates into decimal values
     *
     * @return Array
     */
    function normalize_coordinates($latitude, $longitude)
    {
        $normalized_coordinates = Array
        (
            'latitude' => null,
            'longitude' => null,
        );

        if (!is_float($latitude))
        {
            // TODO: Convert to decimal
        }
        $normalized_coordinates['latitude'] = $latitude;

        if (!is_float($longitude))
        {
            // TODO: Convert to decimal
        }
        $normalized_coordinates['longitude'] = $longitude;

        return $normalized_coordinates;
    }

    /**
     * Map locations that are not yet mapped to their nearest city
     */
    function map_to_city($log)
    {
        // TODO: Find latest city
        return null;
    }

    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param Array $logs Log entries in Array format specific to importer
     * @return bool Indicating success.
     */
    function import($logs)
    {
        return true;
    }

    /**
     * This is a static factory method which lets you dynamically create importer instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * On any error (class not found etc.) the factory method will call generate_error.
     *
     * <b>This function must be called statically.</b>
     *
     * @param string $type The type of the importer (the file name from the importer directory).
     * @return org_routamc_positioning_importer A reference to the newly created importer instance.
     */
    function & create($type)
    {
        $filename = MIDCOM_ROOT . "/org/routamc/positioning/importer/{$type}.php";
        $classname = "org_routamc_positioning_importer_{$type}";
        require_once($filename);
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }
}