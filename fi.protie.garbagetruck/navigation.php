<?php
class fi_protie_garbagetruck_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, which calls for the baseclass
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }
    
    /**
     * Return the leaves
     * 
     * @access protected
     */
    function get_leaves()
    {
        $leaves = array ();
        
        $leaves[FI_PROTIE_GARBAGETRUCK_LEAFID_AREA] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => '',
                MIDCOM_NAV_NAME => $this->_l10n->get('areas'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_NAV_GUID => null,
            MIDCOM_NAV_OBJECT => null,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITED  => 0,
            MIDCOM_META_EDITOR  => 0,
        );
        
        $leaves[FI_PROTIE_GARBAGETRUCK_LEAFID_ROUTE] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => 'route/',
                MIDCOM_NAV_NAME => $this->_l10n->get('routes'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_NAV_GUID => null,
            MIDCOM_NAV_OBJECT => null,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITED  => 0,
            MIDCOM_META_EDITOR  => 0,
        );
        
        $leaves[FI_PROTIE_GARBAGETRUCK_LEAFID_VEHICLE] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => 'vehicle/',
                MIDCOM_NAV_NAME => $this->_l10n->get('vehicles'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_NAV_GUID => null,
            MIDCOM_NAV_OBJECT => null,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITED  => 0,
            MIDCOM_META_EDITOR  => 0,
        );
        
        $leaves[FI_PROTIE_GARBAGETRUCK_LEAFID_LOG] = array
        (
            MIDCOM_NAV_SITE => array
            (
                MIDCOM_NAV_URL => 'log/',
                MIDCOM_NAV_NAME => $this->_l10n->get('logs'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_NAV_GUID => null,
            MIDCOM_NAV_OBJECT => null,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITED  => 0,
            MIDCOM_META_EDITOR  => 0,
        );
        
        return $leaves;
    }
}
?>