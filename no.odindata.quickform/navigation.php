<?php
/**
 * Quickform navigation class
 * Does very little as quickform doesn't have leaves
 * @package no.odindata.quickform
 */

class no_odindata_quickform_navigation extends midcom_baseclasses_components_navigation {
    
    
    function no_odindata_quickform_navigation() {
        parent::midcom_baseclasses_components_navigation();
    } 

    /* get_leaves used to display the leaves of the topic. 
     * I.e. the submitted forms. 
     * we do not show allready submitted articles!
     * So we return 
     * */
    function get_leaves() {
        
        // for now, this is only an emailform.
        return array();       
        
    } 
    
    function get_node() {
        
        $toolbar[100] = Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => 
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
            )
        );
        
        return array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => ((count ($toolbar) > 0) ? $toolbar : null),
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
    } 
    
} // navigation

?>
