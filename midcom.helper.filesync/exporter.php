<?php
/**
* @package midcom.helper.filesync
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.filesync
 */
class midcom_helper_filesync_exporter extends midcom_baseclasses_components_purecode
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     *
     * @param midcom_helper_replication_type_dba $type type
     */
    function __construct()
    {
         $this->_component = 'midcom.helper.filesync';
         parent::__construct();
    }
    
    /**
     * This is a static factory method which lets you dynamically create exporter instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * @param string $type type
     * @return midcom_helper_filesync_exporter A reference to the newly created exporter instance.
     * @static
     */
    static function & create($type)
    {
        $filename = MIDCOM_ROOT . "/midcom/helper/filesync/exporter/{$type}.php";
        
        if (!file_exists($filename))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested exporter file {$type} is not installed.");
            // This will exit.
        }
        require_once($filename);

        $classname = "midcom_helper_filesync_exporter_{$type}";        
        if (!class_exists($classname))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested exporter class {$type} is not installed.");
            // This will exit.
        }
        
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }
}
?>