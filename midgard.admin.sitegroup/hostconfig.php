<?php
/**
 * Created on Mar 6, 2006
 * @author tarjei huse
 * @package midgard.admin.sitegroup
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
/**
 * This class encapsulates the operations needed to configure a 
 * MidCom host as per mRFC 0025
 * http://www.midgard-project.org/development/mrfc/0025.html
 */
class midgard_admin_sitegroup_hostconfig 
{

    /**
     * The array containing various configuration details
     * @access private
     */
    var $config = array();
    /**
     * the path to the midcom lib directory.
     * Defaults to a PEAR installation.
     * Appends midcom.php itself.
     * @access public
     */
    var $midcom_path = 'midcom';
    
    /**
     * Version of the hostsetup.
     * @access public
     */
    var $version = 1;
    
    /**
     * The page to save the configuration on.
     */
    var $page = null;
    
    /**
     * Update the configuration to the Page.
     */
    
    var $values_to_skip = array (
            'midcom_path' => 1,
            'enable_cache' => 1,
                );
              
    function midgard_admin_sitegroup_hostconfig( $page) 
    {
        $this->page = $page;
    }
    function update_configuration()  
    {
        if ($this->page === null) 
        {
            return false; // I WANT EXCEPTIONS!!!    
        }
        foreach ($this->config as $key => $value)
        {
            $this->page->parameter('midgard', $key, $value);
        }
        return true;
    }
    
    /**
     * Generate the content of code init. 
     */
    function get_code_init() 
    {
        if (!$this->update_configuration())
        {
            return false;
        }    
        $codeinit = "<?php \n/* this is a generated snippet. Do not edit!  */\n";
        $template = '$GLOBALS[\'midcom_config_local\'][\'{__NAME__}\'] = \'{__VALUE__}\';';
        $configuration = array();
        
        $params = $this->page->listparameters('midgard');
        if ($params) while ($params->fetch()) 
        {
            if (!array_key_exists($params->name, $this->values_to_skip)) 
            {
                $txt = $template;
                $txt = str_replace('{__NAME__}', $params->name, $txt);
                $txt = str_replace('{__VALUE__}', $params->value, $txt);
                $configuration[] = $txt;
            }
            $this->config[$params->name] = $params->value;
        }
        
        $codeinit .= implode ("\n", $configuration);
        
        $codeinit .= "\n\nrequire '{$this->midcom_path}/midcom.php';\n";
        
        $codeinit .= '$_MIDCOM->codeinit();' . "\n?>";
        
        return $codeinit;
        
    }
    /**
     * Set a configuration value
     * @param string name name of the cofniguration value
     * @param string value the value
     * @access public
     */
    function set ($name, $value) 
    {
        if ($name == 'midcom_path') 
        {
            $this->midcom_path = $value;
            return;
        }
        $this->config[$name] = $value;
        return;
    }    
    /**
     * Get a config value
     * @return string the value
     * @param string name of the config value.
     * @access public
     */
    function get($name) 
    {
        return $this->config[$name];
    }
}
?>