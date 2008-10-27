<?php
/**
 * @package org.routamc.statusmessage
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: importer.php 4795 2006-12-19 13:42:39Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Position importing factory class. All importers inherit from this.
 *
 * @package org.routamc.statusmessage
 */
class org_routamc_statusmessage_importer extends midcom_baseclasses_components_purecode
{
    /**
     * The imported message entries
     *
     * @var org_routamc_statusmessage_message
     */
    var $message = null;

    /**
     * Error code from trying to import. Either a mgd_errstr() or an additional error code from component
     *
     * @var string
     */
    var $error = 'MGD_ERR_OK';

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function __construct()
    {
         $this->_component = 'org.routamc.statusmessage';
         parent::__construct();
    }

    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param Array $messages message entries in Array format specific to importer
     * @return boolean Indicating success.
     */
    function import($messages)
    {
        return true;
    }

    /**
     * This is a static factory method which lets you dynamically create importer instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * @param string $type The type of the importer (the file name from the importer directory).
     * @return org_routamc_statusmessage_importer A reference to the newly created importer instance.
     * @static
     */
    static function & create($type)
    {
        $filename = MIDCOM_ROOT . "/org/routamc/statusmessage/importer/{$type}.php";
        $classname = "org_routamc_statusmessage_importer_{$type}";
        require_once($filename);
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }
}