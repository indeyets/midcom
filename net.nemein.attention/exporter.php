<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Attention profile exporting factory class. All exporters inherit from this.
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_exporter extends midcom_baseclasses_components_purecode
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function net_nemein_attention_exporter()
    {
         $this->_component = 'net.nemein.attention';
         parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param Array $logs Log entries in Array format specific to exporter
     * @return bool Indicating success.
     */
    function export($logs)
    {
        return true;
    }

    /**
     * This is a static factory method which lets you dynamically create exporter instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * On any error (class not found etc.) the factory method will call generate_error.
     *
     * <b>This function must be called statically.</b>
     *
     * @param string $type The type of the exporter (the file name from the exporter directory).
     * @return net_nemein_attention_exporter A reference to the newly created exporter instance.
     */
    function & create($type)
    {
        $filename = MIDCOM_ROOT . "/net/nemein/attention/exporter/{$type}.php";
        $classname = "net_nemein_attention_exporter_{$type}";
        require_once($filename);
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }
}