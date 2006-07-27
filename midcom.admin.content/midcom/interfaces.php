<?php
/**
 * @package midcom.admin.content
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM AIS interface class.
 * 
 * @package midcom.admin.content
 */
class midcom_admin_content_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function midcom_admin_content_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'midcom.admin.content';
        $this->_site_class_suffix = 'main';
        $this->_autoload_files = Array('main.php', 'toolbar.php', 'navigation.php', 
            '_cmddata.php', '_cmdmeta.php', '_cmdtopic.php', '_cmdattachment.php');
        
        $this->_acl_privileges['topic_management'] = MIDCOM_PRIVILEGE_DENY;
    }
    
    /**
     * Iniitialize the admin class, this will populate some compatibility members and 
     * load the topic admin configuration, which is not yet merged into the main configuration.
     * 
     * Note, that the restrict(delete|create) flags have been deprecated in favor of the
     * ACL system and are no longer read but forced to false value instead.
     */
    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $GLOBALS['midcom_admin_content_ais_config'] =& $this->_data['config'];
        
        if (mgd_snippet_exists("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/midcom.admin.content/topicadmin")) 
        {
            $snippet = mgd_get_snippet_by_path("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/midcom.admin.content/topicadmin");
            eval ("\$config = Array ( {$snippet->code} \n);");
        } 
        else 
        {
            $config = Array();
        }
        $config['restrict_delete'] = false;
        $config['restrict_create'] = false;
        
        $defaults = Array (
            "components" => null,
        );
        
        foreach ($defaults as $key => $value) 
        {
            if (! array_key_exists($key, $config))
            {
                $config[$key] = $value;
            }
        }
        
        $GLOBALS['midcom_admin_content_topicadmin_config'] = $config;

        debug_pop();
        return true;
    }
    
}

?>
