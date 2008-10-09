<?php
/**
 * @package org.openpsa.core
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * Helper class that locates topics for specific components used in OpenPsa and automatically 
 * generates a cached version of the site structure in the config snippet 
 *
 * @package org.openpsa.core 
 */
class org_openpsa_core_structure extends midcom_baseclasses_components_purecode
{

    /**
     * The components for which we're creating the structure information
     *
     * @var array
     * @access private
     */
    private $components = array
    (
        'org.openpsa.calendar' => 'org.openpsa.calendar',
        'org.openpsa.contacts' => 'org.openpsa.contacts',
        'org.openpsa.expenses' => 'org.openpsa.expenses',
        'org.openpsa.invoices' => 'org.openpsa.invoices',
        'org.openpsa.projects' => 'org.openpsa.projects',
        'org.openpsa.sales' => 'org.openpsa.sales',
        'net.nemein.wiki' => 'net.nemein.wiki',
    );


    /**
     * The snippet we're working with
     *
     * @var midcom_baseclasses_database_snippet
     * @access private
     */
    private $snippet = null;

    function __construct()
    {
        $this->_component = 'org.openpsa.core';
        parent::__construct();
        if ($this->_config->get('auto_init'))
        {
            $this->initialize_site_structure();
        }
    }

    private function initialize_site_structure()
    {

        $nodes = array();
        foreach ($this->components as $component)
        {
            $nodes[$component] = midcom_helper_find_node_by_component($component);
        }

        if (empty($nodes))
        {
            return;
        }

        $this->snippet = $this->get_snippet();
        foreach ($nodes as $component => $node)
        {
            $parts = explode('.', $component);
            $last = array_pop($parts);
            $node_guid = 'false';
            $node_full_url = 'false';
            $node_relative_url = 'false';
            if (is_array($node))
            {
              $node_guid = "'" . $node[MIDCOM_NAV_OBJECT]->guid . "'";
              $node_full_url = "'" . $node[MIDCOM_NAV_FULLURL] . "'";
              $node_relative_url = "'" . $node[MIDCOM_NAV_RELATIVEURL] . "'";
            }
            $this->set_config_value($last . '_guid', $node_guid);
            $this->set_config_value($last . '_full_url', $node_full_url);
            $this->set_config_value($last . '_relative_url', $node_relative_url);
        }
        //set auto_init to true to write only once
        $this->set_config_value('auto_init', 'false');

        $_MIDCOM->auth->request_sudo('org.openpsa.core');
        $this->snippet->update();
        $_MIDCOM->auth->drop_sudo();
        $_MIDCOM->uimessages->add($this->_i18n->get_string('org.openpsa.core'), $this->_i18n->get_string('site structure cache created'), 'info');
    }

    /**
     * Helper function to set the values in the config snippet
     *
     * @param string $key The config key to set
     * @param string $value The config value to set
     */
    private function set_config_value($key, $value)
    {
        if (strpos($this->snippet->code, $key) != false)
        {
          $this->snippet->code = preg_replace("/^.+?" . $key . ".+?$/m", " '" . $key . "' => " . $value . ",", $this->snippet->code);
        }
        else
        {
            $this->snippet->code = $this->snippet->code . " '" . $key . "' => " . $value . ",\n";
        }
    }

    /**
     * Save the configuration to the config snippet
     * (copied from midgard_admin_asgard_handler_component_configuration)
     *
     * @return midcom_baseclasses_database_snippet
     */
    private function get_snippet()
    {
        $_MIDCOM->auth->request_sudo('org.openpsa.core');
        $sg_snippetdir = new midcom_baseclasses_database_snippetdir();
        $sg_snippetdir->get_by_path($GLOBALS['midcom_config']['midcom_sgconfig_basedir']);
        if (!$sg_snippetdir->guid)
        {
            // Create SG config snippetdir
            $sd = new midcom_baseclasses_database_snippetdir();
            $sd->up = 0;
            $sd->name = $GLOBALS['midcom_config']['midcom_sgconfig_basedir'];
            // remove leading slash from name
            $sd->name = preg_replace("/^\//", "", $sd->name);
            if (!$sd->create())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create snippetdir {$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}: " . mgd_errstr());
            }
            $sg_snippetdir = new midcom_baseclasses_database_snippetdir($sd->guid);
        }

        $lib_snippetdir = new midcom_baseclasses_database_snippetdir();
        $lib_snippetdir->get_by_path("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/org.openpsa.core");
        if (!$lib_snippetdir->guid)
        {
            $sd = new midcom_baseclasses_database_snippetdir();
            $sd->up = $sg_snippetdir->id;
            $sd->name = 'org.openpsa.core';
            if (!$sd->create())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,"Failed to create snippetdir {$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/org.openpsa.core: " . mgd_errstr());
            }
            $lib_snippetdir = new midcom_baseclasses_database_snippetdir($sd->guid);
        }

        $snippet = new midcom_baseclasses_database_snippet();
        $snippet->get_by_path("{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}/org.openpsa.core/config");
        if ($snippet->id == false )
        {
            $sn = new midcom_baseclasses_database_snippet();
            $sn->up = $lib_snippetdir->id;
            $sn->name = 'config';
            $sn->code = "//AUTO-GENERATED BY org_openpsa_core_structure\n";
            $sn->create();
            $snippet = new midcom_baseclasses_database_snippet($sn->guid);
        }
        $_MIDCOM->auth->drop_sudo();
        return $snippet;
    }

    /**
     * Helper function to retrieve the full URL for the first topic of a given component
     *
     * @param string $component the component to look for 
     * @return mixed The component URL or false
     */
    function get_node_full_url($component)
    {
        if (!array_key_exists($component, $this->components))
        {
            return false;
        }
        $parts = explode('.', $component);
        $last = array_pop($parts);
        return $this->_config->get($last . '_full_url');
    }

    /**
     * Helper function to retrieve the relative URL for the first topic of a given component
     *
     * @param string $component The component to look for 
     * @return mixed the component URL or false
     */
    function get_node_relative_url($component)
    {
        if (!array_key_exists($component, $this->components))
        {
            return false;
        }
        $parts = explode('.', $component);
        $last = array_pop($parts);
        return $this->_config->get($last . '_relative_url');
    }
    
    /**
     * Helper function to retrieve the GUID for the first topic of a given component
     *
     * @param string $component the component to look for 
     * @return mixed the component URL or false
     */
    function get_node_guid($component)
    {
        if (!array_key_exists($component, $this->components))
        {
            return false;
        }
        $parts = explode('.', $component);
        $last = array_pop($parts);
        return $this->_config->get($last . '_guid');
    }
}
?>