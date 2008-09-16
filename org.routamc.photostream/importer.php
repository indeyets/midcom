<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: importer.php 4795 2006-12-19 13:42:39Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Photo importing factory class. All importers inherit from this.
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_importer extends midcom_baseclasses_components_purecode
{
    /**
     * The imported message entries
     *
     * @var org_routamc_photostream_message
     */
    var $message = null;

    /**
     * Error code from trying to import. Either a mgd_errstr() or an additional error code from component
     *
     * @var string
     */
    var $error = 'MGD_ERR_OK';
    
    /**
     * Database ID of a photostream node
     *
     * @var int
     */
    var $photostream = null;
    
    /**
     * Array of midcom_helper_datamanager2_schemas for handling photo objects
     */
    var $schemadb = array();
    
    /**
     * midcom_helper_datamanager2
     */
    var $datamanager = null;
    
    /**
     * @var string
     */
    var $photo_field = '';

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function __construct($photostream)
    {
         $this->photostream = $photostream;
             
         $this->_component = 'org.routamc.photostream';
         parent::__construct();
         
         $this->schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
         $this->datamanager = new midcom_helper_datamanager2_datamanager($this->schemadb);
         
        foreach ($this->schemadb['photo']->fields as $name => $field)
        {
            if ($field['type'] == 'photo')
            {
                $this->photo_field = $name;
            }
        }
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
     * On any error (class not found etc.) the factory method will call generate_error.
     *
     * <b>This function must be called statically.</b>
     *
     * @param string $type The type of the importer (the file name from the importer directory).
     * @param int $photostream ID of a photostream node
     * @return org_routamc_photostream_importer A reference to the newly created importer instance.
     * @static
     */
    function & create($type, $photostream)
    {
        $filename = MIDCOM_ROOT . "/org/routamc/photostream/importer/{$type}.php";
        $classname = "org_routamc_photostream_importer_{$type}";
        require_once($filename);
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname($photostream);
        return $class;
    }
}