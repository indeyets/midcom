<?php
/**
 * @package midgard.admin.sitewizard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Site Wizard
 *
 * @package midgard.admin.sitewizard
 */

class midgard_admin_sitewizard_viewer extends midcom_baseclasses_components_request
{

    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
        
        
        // Match /
        $this->_request_switch['sitegroup'] = array(
            'handler' => Array('midgard_admin_sitewizard_handler_sitegroup', 'select')
        );
        
        // Match /host/<SG ID>
        $this->_request_switch['host'] = array(
            'handler' => Array('midgard_admin_sitewizard_handler_host', 'settings'),
            'fixed_args' => Array('host'),            
            'variable_args' => 1,            
        );
        
        // Match /template/<SG ID>
        $this->_request_switch['template'] = array(
            'handler' => Array('midgard_admin_sitewizard_handler_host', 'template'),
            'fixed_args' => Array('template'),            
            'variable_args' => 1,
        );
        
        // Match /create/<SG ID>
        $this->_request_switch['create'] = array(
            'handler' => Array('midgard_admin_sitewizard_handler_host', 'create'),
            'fixed_args' => Array('create'),            
            'variable_args' => 1,
        );
        
        // Match /finish/<host ID>
        $this->_request_switch['finish'] = array(
            'handler' => Array('midgard_admin_sitewizard_handler_host', 'finish'),
            'fixed_args' => Array('finish'),            
            'variable_args' => 1,
        );
    }
    
    function _on_handle($handler_id, $args)
    {
        $_MIDCOM->auth->require_admin_user();
            
        $_MIDCOM->add_link_head(
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/midgard.admin.sitewizard/sitewizard.css',
            )
        );
        
        // FIXME: Midgard 1.7 compatibility hack
        if (version_compare(mgd_version(), '1.8alpha1', '>='))
        {
            $this->_request_data['17_compatibility'] = false;
        }
        else
        {
            $this->_request_data['17_compatibility'] = true;
        }
        
        return true;
    }
}
?>