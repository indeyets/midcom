<?php
/**
 * @package org.openpsa.core
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: acl_synchronizer.php,v 1.13 2006/05/03 14:09:44 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA core stuff
 *
 * @package org.openpsa.core
 */
class org_openpsa_core_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::__construct();

        $this->_component = 'org.openpsa.core';
        $this->_autoload_files = array
        (
        );
    }

    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->define_constants();
        $this->set_acl_options();
        $this->set_workgroup_filter();
        $return = $this->load_my_company();

        debug_pop();
        return $return;
    }

    /**
     * Load "my company" or "owner company", the group that is the main user of this instance
     *
     * @return boolean Indicating success
     */
    private function load_my_company()
    {
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
        return true;
    }

    /**
     * Make the selected workgroup filter available to all components
     */
    private function set_workgroup_filter()
    {
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
    }

    private function define_constants()
    {
        //Constant versions of wgtype bitmasks
        define('ORG_OPENPSA_WGTYPE_NONE', 0);
        define('ORG_OPENPSA_WGTYPE_INACTIVE', 1);
        define('ORG_OPENPSA_WGTYPE_ACTIVE', 3);
        
        //Constants for ACL shortcuts
        define('ORG_OPENPSA_ACCESSTYPE_PRIVATE', 100);
        define('ORG_OPENPSA_ACCESSTYPE_WGPRIVATE', 101);
        define('ORG_OPENPSA_ACCESSTYPE_PUBLIC', 102);
        define('ORG_OPENPSA_ACCESSTYPE_AGGREGATED', 103);
        define('ORG_OPENPSA_ACCESSTYPE_WGRESTRICTED', 104);
        define('ORG_OPENPSA_ACCESSTYPE_ADVANCED', 105);
        
        //org.openpsa.contacts object types
        define('ORG_OPENPSA_OBTYPE_OTHERGROUP', 0);
        define('ORG_OPENPSA_OBTYPE_ORGANIZATION', 1000);
        define('ORG_OPENPSA_OBTYPE_DAUGHTER', 1001);
        define('ORG_OPENPSA_OBTYPE_DEPARTMENT', 1002);
        define('ORG_OPENPSA_OBTYPE_PERSON', 2000);
        define('ORG_OPENPSA_OBTYPE_RESOURCE', 2001);
        
        //org.openpsa.documents object types
        define('ORG_OPENPSA_OBTYPE_DOCUMENT', 3000);
        //org.openpsa.documents document status
        define('ORG_OPENPSA_DOCUMENT_STATUS_DRAFT', 4000);
        define('ORG_OPENPSA_DOCUMENT_STATUS_FINAL', 4001);
        define('ORG_OPENPSA_DOCUMENT_STATUS_REVIEW', 4002);
        
        //org.openpsa.calendar object types
        define('ORG_OPENPSA_OBTYPE_EVENT', 5000);
        define('ORG_OPENPSA_OBTYPE_EVENTPARTICIPANT', 5001);
        define('ORG_OPENPSA_OBTYPE_EVENTRESOURCE', 5002);
        
        
        //org.openpsa.reports object types
        define('ORG_OPENPSA_OBTYPE_REPORT', 7000);
        define('ORG_OPENPSA_OBTYPE_REPORT_TEMPORARY', 7001);
        
        //org.openpsa.directmarketing message types
        define('ORG_OPENPSA_MESSAGETYPE_EMAIL_TEXT', 8000);
        define('ORG_OPENPSA_MESSAGETYPE_SMS', 8001);
        define('ORG_OPENPSA_MESSAGETYPE_MMS', 8002);
        define('ORG_OPENPSA_MESSAGETYPE_CALL', 8003);
        define('ORG_OPENPSA_MESSAGETYPE_SNAILMAIL', 8004);
        define('ORG_OPENPSA_MESSAGETYPE_FAX', 8005);
        define('ORG_OPENPSA_MESSAGETYPE_EMAIL_HTML', 8006);
        //org.openpsa.directmarketing message receipt types
        define('ORG_OPENPSA_MESSAGERECEIPT_SENT', 8500); //Created when message has been sent successfully
        define('ORG_OPENPSA_MESSAGERECEIPT_DELIVERED', 8501); //Created if we get a delivery receipt
        define('ORG_OPENPSA_MESSAGERECEIPT_RECEIVED', 8502); //Created if we get some confirmation from the recipient
        define('ORG_OPENPSA_MESSAGERECEIPT_FAILURE', 8503); //Created when message has been send has failed
        //org.openpsa.directmarketing campaign member types
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER', 9000);
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER', 9001);
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED', 9002);
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_BOUNCED', 9003);
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_INTERVIEWED', 9004);
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_LOCKED', 9005);
        //org.openpsa.directmarketing campaign types
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN', 9500);
        define('ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART', 9501);
    }

    /**
     * Make the ACL selection array available to all components
     */
    private function set_acl_options()
    {
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
    }
}
?>