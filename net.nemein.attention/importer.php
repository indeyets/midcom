<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Attention profile importing factory class. All importers inherit from this.
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_importer extends midcom_baseclasses_components_purecode
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function net_nemein_attention_importer()
    {
         $this->_component = 'net.nemein.attention';
         parent::__construct();
    }

    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param Array $logs Log entries in Array format specific to importer
     * @return boolean Indicating success.
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
     * @return net_nemein_attention_importer A reference to the newly created importer instance.
     * @static
     */
    function & create($type)
    {
        $filename = MIDCOM_ROOT . "/net/nemein/attention/importer/{$type}.php";
        $classname = "net_nemein_attention_importer_{$type}";
        require_once($filename);
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }
}