<?php
/**
 * OpenPSA core stuff
 *
 * @package org.openpsa.core
 */
class org_openpsa_core_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_core_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.core';
        $this->_purecode = true;
        $this->_autoload_files = Array(
            'constants.php',
            'version.php',
            'acl_synchronizer.php',
        );
        $this->_autoload_libraries = Array(
            'org.openpsa.helpers',
        );
    }

    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Make sure UI message stack is present and initialized
        org_openpsa_helpers_uimessages::initialize_stack();

        // Make the ACL selection array available to all components
        if (!array_key_exists('org_openpsa_core_acl_options', $GLOBALS))
        {
            $GLOBALS['org_openpsa_core_acl_options'] = array(
                ORG_OPENPSA_ACCESSTYPE_WGRESTRICTED => 'workgroup restricted',
                ORG_OPENPSA_ACCESSTYPE_WGPRIVATE => 'workgroup private',
                ORG_OPENPSA_ACCESSTYPE_PRIVATE => 'private',
                ORG_OPENPSA_ACCESSTYPE_PUBLIC => 'public',
                ORG_OPENPSA_ACCESSTYPE_AGGREGATED => 'aggregated',
            );
        }

        // Make the selected workgroup filter available to all components
        if (   !array_key_exists('org_openpsa_core_workgroup_filter', $GLOBALS)
            // Sessioning kills caching and I doubt we really need this info when we don't have a user
            && $_MIDGARD['user'])
        {

            if ($this->_data['config']->get('default_workgroup_filter') == 'me')
            {
                if ($_MIDCOM->auth->user)
                {

                    $default_filter = $_MIDCOM->auth->user->id;
                }
                else
                {
                    $default_filter = 'all';
                }
            }
            else
            {
                $default_filter = $this->_data['config']->get('default_workgroup_filter');
            }

            $GLOBALS['org_openpsa_core_workgroup_filter'] = $default_filter;

            /* the workgroup filter is deprecated, let's not screw caching over with it
            $session = new midcom_service_session('org.openpsa.core');
            if (!$session->exists('org_openpsa_core_workgroup_filter'))
            {
                $session->set('org_openpsa_core_workgroup_filter', $default_filter);
            }
            $GLOBALS['org_openpsa_core_workgroup_filter'] = $session->get('org_openpsa_core_workgroup_filter');
            */
            $GLOBALS['org_openpsa_core_workgroup_filter'] = $default_filter;
        }

        // Load "my company" or "owner company", the group that is the main user of this instance
        $my_company_guid = $this->_data['config']->get('owner_organization');
        $GLOBALS['org.openpsa.core:owner_organization_obj'] = false;
        if (   !empty($my_company_guid)
            && mgd_is_guid($my_company_guid))
        {
            // For some reason this trigger error 500
            //$_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');
            if (!class_exists('org_openpsa_contacts_group'))
            {
                // Fallback to standard group object
                $class = 'midcom_db_group';
            }
            else
            {
                $class = 'org_openpsa_contacts_group';
            }
            $_MIDCOM->auth->request_sudo();
            $my_company_object = new $class($my_company_guid);
            $_MIDCOM->auth->drop_sudo();
            if (!$my_company_object->guid)
            {
                // TODO: Generate proper error
                debug_pop();
                return false;
            }
            $GLOBALS['org.openpsa.core:owner_organization_obj'] = $my_company_object;
        }

        debug_pop();
        return true;
    }
}
?>