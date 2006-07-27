<?php
/**
 * Created on Jan 28, 2006
 * @author tarjei huse
 * @package midcom.admin.aegir
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
 
class midcom_helper_imagepopup_attachmentlist_dm2 {

    /**
     * List of attachments
     * @access public
     * @var array
     */
    var $attachments= array();
    
    /**
     * The object parent object
     */
    var $_object = null;
    
    /**
     * The fieldnames for the attachments
     */
    var $_fieldnames = array();
    
    var $schema = array(
        'description' => 'Imagelist',
        'fields' => Array()
    );
    
    
    function midcom_helper_imagepopup_attachmentlist_dm2 (&$object) 
    {
        $this->_object = $object;
        $this->_generate_listing();        
    }
    
    function _generate_listing() 
    {
        //$qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_topic');
//        $atts = new MidgardQueryBuilder('midgard_attachment');
        
          
        $images = $this->_object->list_parameters();
        
        $files = $this->_object->listattachments();
        
        if ( !$files) 
        {
            return;
        }
        
        while ($files->fetch()) 
        {
            $this->_add_attachment($files);
        }
        
        foreach ($this->_fieldnames as $fieldname => $noval)
        {
            $this->schema['fields'][$fieldname] = Array
            (
                'title' => 'Image',
                'storage' => null,
                'type' => 'image',
                'type_config' => Array
                (
                    'filter_chain' => 'resize(800,600)',
                    'keep_original' => true,
                    'auto_thumbnail' => Array(200,200),
                ),
                'widget' => 'image',
            );
        }
        
    }
    
    function _add_attachment ($file) 
    {
        $query = new MidgardQueryBuilder('midgard_parameter');
        $query->add_constraint('tablename', '=', 'blobs');
        $query->add_constraint('oid', '=', $file->id);
        // Temporary workaround for missing delete support
        $query->add_constraint('value', '<>', '');
        $query->add_constraint('domain', '=','midcom.helper.datamanager2.type.blob' );
        //$query->add_constraint('name', '=','fieldname' );
        $res = $query->execute();
        
        foreach ($res as $p) 
        {
            $this->_fieldnames[$p->value]  = 1;
           // $this->schema[$file->id][$p->domain][$p->name] = $p->value;    
        }
        
        
    
    }
     
    function get_schema() 
    {
        return $this->schema;
    }
}
 
?>