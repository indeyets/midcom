<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_importer extends midcom_baseclasses_components_purecode
{
    /**
     * Possible processing error.
     *
     * @var string
     * @access protected
     */
    var $error = '';

    /**
     * Lists imported object counts indexed by mgdschema class name
     */
    var $counter = array();

    /**
     * Initializes the class.
     */
    function __construct()
    {
         $this->_component = 'midcom.helper.replicator';
         
         parent::__construct();
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
     * @param string $type Type of the importer
     * @return midcom_helper_replicator_importer A reference to the newly created importer instance.
     * @static
     */
    function & create($type)
    {
        $filename = MIDCOM_ROOT . "/midcom/helper/replicator/importer/{$type}.php";
        
        if (!file_exists($filename))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested importer file {$type} is not installed.");
            // This will exit.
        }
        require_once($filename);

        $classname = "midcom_helper_replicator_importer_{$type}";        
        if (!class_exists($classname))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested importer class {$type} is not installed.");
            // This will exit.
        }
        
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname();
        return $class;
    }
    
    /**
     * This is the checkpoint of the importer. 
     * 
     * This should be overridden in subclasses for more contextual handling of dependencies.
     *
     * @param midgard_object &$object The Object to import
     * @return boolean Whether the object may be imported with this importer
     */
    function is_importable(&$object)
    {
        return true;
    }
    
    /**
     * This is the main entry point of the importer. 
     * 
     * This should be overridden in subclasses for more contextual handling of dependencies.
     *
     * @param string &$xml XML replication content
     * @param boolean $use_force Whether to force importing
     * @return boolean Whether importing was successful
     */
    function import_xml(&$xml, $use_force = false)
    {
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix(__CLASS__ . '::' . __FUNCTION__);
        // TODO: Ensure validity of XML
    
        // Call silenced to avoid warnings generated by missed dependencies
        $objects = @midcom_helper_replicator_unserialize($xml, $use_force);
        //$objects = midcom_helper_replicator_unserialize($xml, $use_force);
        if (empty($objects))
        {
            $this->error = mgd_errstr();
            $GLOBALS['midcom_helper_replicator_logger']->log('midcom_helper_replicator_unserialize() failed, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            return false;
        }
        foreach ($objects as $object)
        {
            // Handle special case of midgard_blob
            if (is_a($object, 'midgard_blob'))
            {
                if (!$this->is_importable($object))
                {
                    $GLOBALS['midcom_helper_replicator_logger']->log('is_importable() returned false', MIDCOM_LOG_ERROR);
                    $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                    return false;
                }
                if (!$_MIDCOM->dbfactory->import_blob($object, $xml, $use_force))
                {
                    $this->error = mgd_errstr();
                    $GLOBALS['midcom_helper_replicator_logger']->log("Failed to import midgard_blob for object {$object->parentguid}, errstr: {$this->error}", MIDCOM_LOG_ERROR);
                    $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                    return false;
                }
                $GLOBALS['midcom_helper_replicator_logger']->log("Imported midgard_blob for object {$object->parentguid}");
                continue;
            }
            $object_class = get_class($object);
            if (!$this->import_object($object, $use_force))
            {
                // Error importing object
                $this->error = mgd_errstr();
                $GLOBALS['midcom_helper_replicator_logger']->log("Failed to import {$object_class} {$object->guid} (action: {$object->action}), errstr: {$this->error}", MIDCOM_LOG_ERROR);
                $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                return false;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log("Imported {$object_class} {$object->guid} (action: {$object->action})");
            
        }
        
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return true;
    }

    /**
     * Imports given object received from midgard_replicator::unserialize
     *
     * Does some basic sanity checks before calling $_MIDCOM->dbfactory->import()
     * @param string $object Importable Midgard object
     * @param boolean $use_force Whether to force importing
     * @return boolean Whether importing was successful
     */
    function import_object(&$object, $use_force)
    {
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix(__CLASS__ . '::' . __FUNCTION__);
        if (is_a($object, 'midgard_blob'))
        {
            $GLOBALS['midcom_helper_replicator_logger']->log("BLOBS can only be imported from XML, the unserialized object does not contain the binary data", MIDCOM_LOG_ERROR);
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            return false;
        }
        if (   !is_object($object)
            || !isset($object->action))
        {
            $GLOBALS['midcom_helper_replicator_logger']->log('given argument is not valid object from midgard_replicator::unserialize', MIDCOM_LOG_ERROR);
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            return false;
        }
        
        if (!$this->is_importable($object))
        {
            $GLOBALS['midcom_helper_replicator_logger']->log('is_importable() returned false', MIDCOM_LOG_ERROR);
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            return false;
        }
        
        $stat = $_MIDCOM->dbfactory->import($object, $use_force);
        if (!$stat)
        {
            $this->error = mgd_errstr();
        }
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        return $stat;
    }

    /**
     * Main entry point for importer, must be overridden by subclass
     *
     * @return boolean always false (subclasses must override this)
     */
    function import()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Subclasses MUST override this method', MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }
}
?>