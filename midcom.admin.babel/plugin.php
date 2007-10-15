<?php
class midcom_admin_babel_plugin extends midcom_baseclasses_components_request
{

    function midcom_admin_babel_plugin() 
    {
        parent::midcom_baseclasses_components_request();
    }

    function get_plugin_handlers()
    {

        $_MIDCOM->load_library('midgard.admin.asgard');
        $_MIDCOM->load_library('midcom.admin.babel');


        return array
        (
	       'select' => array
            (
                'handler' => Array('midcom_admin_babel_handler_process', 'select'),
            ),
            'status' => array
            (
                'handler' => Array('midcom_admin_babel_handler_process', 'status'),
                'fixed_args' => 'status',
                'variable_args' => 1,
            ),
            'edit' => array
            (
                'handler' => Array('midcom_admin_babel_handler_process', 'edit'),
                'fixed_args' => 'edit',
                'variable_args' => 2,
            ),
            'save' => array
            (
            	'handler' => Array('midcom_admin_babel_handler_process', 'save'),
            	'fixed_args' => 'save',
                'variable_args' => 2,
    	    ),
        );
    }


    function calculate_language_status($lang)
    {
        $status = array
        (
            'components_core' => array(),
            'components_other' => array(),
            'strings_all' => array
            (
                'total'      => 0,
                'translated' => 0,
            ),
            'strings_core' => array
            (
                'total'      => 0,
                'translated' => 0,
            ),
            'strings_other' => array
            (
                'total'      => 0,
                'translated' => 0,
            )
        );
        
        $components = array('midcom');
        
        // Load translation status of each component
        foreach ($_MIDCOM->componentloader->manifests as $manifest)
        {
            $components[] = $manifest->name;
        }
        
        foreach ($components as $component)
        {
            $component_l10n = $_MIDCOM->i18n->get_l10n($component);
            
            if (midcom_admin_babel_plugin::is_core_component($component))
            {
                $string_array = 'components_core';
            }
            else
            {
                $string_array = 'components_other';
            }       
            
            $status[$string_array][$component] = array();

            $string_ids = array_unique($component_l10n->get_all_string_ids());
            
            $status[$string_array][$component]['total'] = count($string_ids);
            $status['strings_all']['total'] += $status[$string_array][$component]['total'];
            
            if ($string_array == 'components_core')
            {
                $status['strings_core']['total'] += $status[$string_array][$component]['total'];
            }
            else
            {
                $status['strings_other']['total'] += $status[$string_array][$component]['total'];
            }
            
            $status[$string_array][$component]['translated'] = 0;
            
            foreach ($string_ids as $id) 
            {
                if ($component_l10n->string_exists($id, $lang))
                {
                    $status[$string_array][$component]['translated']++;
                    $status['strings_all']['translated']++;
                    
                    if (midcom_admin_babel_plugin::is_core_component($component))
                    {
                        $status['strings_core']['translated']++;
                    }
                    else
                    {
                        $status['strings_other']['translated']++;
                    }
                }
            }         
        }
        
        return $status;
    }
    
    /**
     * Checks if component is a part of the default MidCOM distribution
     * or an external component
     *
     * @param string $component Component to check
     */
    function is_core_component($component)
    {
        // TODO: Put this into a centralized location
        $core_components = array
        (
            'midcom',
            // From midcom dependencies
            'midcom.admin.babel',            
            'midcom.admin.folder',
            'midcom.admin.help',
            'midcom.admin.settings',
            'midcom.admin.styleeditor',
            'midcom.admin.user',
            'midgard.admin.acl',
            'midgard.admin.asgard',
            'midgard.admin.sitegroup',
            'no.bergfald.rcs',
            // From task_midgardcms dependencies
            'net.nehmer.blog',
            'net.nemein.calendar',
            'midcom.helper.imagepopup',
            'midcom.helper.search',
            'de.linkm.sitemap',
            'midgard.admin.sitewizard',
            'net.nehmer.static',
            // Other dependencies
            'midcom.helper.datamanager',
            'midcom.helper.datamanager2',
            'midcom.helper.xml',
            'net.nehmer.markdown',
            'net.nemein.tag',
            'net.nemein.rss',
            'org.openpsa.calendarwidget',
        );
        
        if (in_array($component, $core_components))
        {
            return true;
        }
        return false;
    }

	function navigation()
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $languages = $this->_l10n->_language_db;
        $curlang = $_MIDCOM->i18n->get_current_language();

        echo '<ul class="midgard_admin_asgard_navigation">';

        foreach ($languages as $language => $language_info) 
        {
            $language_name = $language_info['enname'];

            // Calculate status
            $state = midcom_admin_babel_plugin::calculate_language_status($language);  
            $percentage = round(100 / $state['strings_core']['total'] * $state['strings_core']['translated']);
            $percentage_other = round(100 / $state['strings_other']['total'] * $state['strings_other']['translated']);

            if ($percentage >= 96)
            {
                $status = 'ok';
            }
            elseif ($percentage >= 75)
            {
                $status = 'acceptable';
            }
            else
            {
                $status = 'bad';
            }        
            
            echo "            <li class=\"status\"><a href=\"{$prefix}__mfa/asgard_midcom.admin.babel/status/{$language}/\">{$language_name}</a></li>\n";
        }

        echo "</ul>";

    }

}

?>