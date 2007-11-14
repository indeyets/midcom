<?php
/**
 * @package midgard.admin.sitewizard
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Site Wizard sitegroup creation and selection
 *
 * @package midgard.admin.sitewizard
 */
class midgard_admin_sitewizard_handler_sitegroup extends midcom_baseclasses_components_handler
{


    /**
     * Simple default constructor.
     */
    function midgard_admin_sitewizard_handler_sitegroup()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Displays a comment edit view.
     */
    function _handler_select($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);    
        
        if ($_MIDGARD['sitegroup'] != 0)
        {
            // We're not in SG0, redirect to correct SG
            $_MIDCOM->relocate("{$prefix}host/{$_MIDGARD['sitegroup']}/");
            // This will exit
        }
        
        // FIXME: For some reason not all 1.7 installs have midgard_quota defined
        if (   $_MIDGARD['config']['quota']
            && class_exists('midgard_quota'))
        {
            $this->_request_data['enable_quota'] = true;
        }
        else
        {
            $this->_request_data['enable_quota'] = false;
        }
        
        if (array_key_exists('midgard_admin_sitewizard_process', $_POST))
        {        
            if (   array_key_exists('midgard_admin_sitewizard_sitegroup_id', $_POST)
                && $_POST['midgard_admin_sitewizard_sitegroup_id'])
            {
                if ($this->_request_data['17_compatibility'])
                {
                    // FIXME: Midgard 1.7 compatibility hack
                    $session = new midcom_service_session();
                    $session->set("midgard_admin_sitewizard_17compat_{$_POST['midgard_admin_sitewizard_sitegroup_id']}", Array($_POST['midgard_admin_sitewizard_sg0_admin'], $_POST['midgard_admin_sitewizard_sg0_password']));
                }
            
                // Existing sitegroup selected, relocate
                $_MIDCOM->relocate("{$prefix}host/{$_POST['midgard_admin_sitewizard_sitegroup_id']}/");
                // This will exit
            }
            
            // Set up the config
            $config = new midgard_admin_sitegroup_creation_config_sitegroup();
            $config->_username = '';
            $config->_password = '';
            $config->sitegroup_name = $_POST['midgard_admin_sitewizard_sitegroup_name'];
            $config->admin_name = $_POST['midgard_admin_sitewizard_sitegroup_admin'];
            $config->admin_password = $_POST['midgard_admin_sitewizard_sitegroup_password'];
            $config->verbose = true;
            
            // Create the sitegroup    
            ob_start();
            $runner = new midgard_admin_sitegroup_creation_sitegroup($config);
            $runner->validate();
            $stat = $runner->run();
            $sg_errors = ob_get_contents();
            ob_end_clean();
            
            if ($stat)
            {
                if ($this->_request_data['17_compatibility'])
                {
                    // FIXME: Midgard 1.7 compatibility hack
                    $session = new midcom_service_session();
                    $session->set("midgard_admin_sitewizard_17compat_" . $runner->get_id(), Array($_POST['midgard_admin_sitewizard_sg0_admin'], $_POST['midgard_admin_sitewizard_sg0_password']));
                }
                
                if (   $this->_request_data['enable_quota']
                    && array_key_exists('midgard_admin_sitewizard_sitegroup_quota', $_POST))
                {
                    $q = new midgard_quota();
                    
                    // Quota settings for MgdSchema
                    $q->typename = '';
                    $q->sgsizelimit = $_POST['midgard_admin_sitewizard_sitegroup_quota'] * 1048576;
                    $q->sitegroup = $runner->get_id();
                    
                    // Quota settings for Classic Midgard API
                    $q->sg = $runner->get_id();
                    $q->tablename = 'wholesg';
                    $q->space = $_POST['midgard_admin_sitewizard_sitegroup_quota'] * 1048576;
                    $q->spacefields = '';
                    $q->number = 0;

                    $q->create();
                }
                            
                $_MIDCOM->relocate("{$prefix}host/" . $runner->get_id() . "/");
                // This will exit
            }
            else
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, nl2br("<strong>Failed to create sitegroup</strong>:\n" . $sg_errors));
                // This will exit
            }

        }
        
        $_MIDCOM->set_pagetitle($this->_l10n->get('select organization'));
        
        /* 
        TODO: Move to new SG API when one exists       
        $qb = new MidgardQueryBuilder('midgard_sitegroup');
        $this->_request_data['sitegroups'] = $qb->execute();
        */        
        $this->_request_data['sitegroups'] = Array();
        $sitegroups = mgd_list_sitegroups();
        if ($sitegroups)
        {
            while ($sitegroups->fetch())
            {
                $this->_request_data['sitegroups'][] = mgd_get_sitegroup($sitegroups->id);
            }
        }
        
        return true;
    }

    /**
     * Shows the loaded article.
     */
    function _show_select($handler_id, &$data)
    {

        midcom_show_style('show-wizard-header');
        midcom_show_style('wizard-select-organization');
        midcom_show_style('show-wizard-footer');
    }



}

?>