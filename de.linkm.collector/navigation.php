<?php

class de_linkm_collector_navigation {

    var $_topic;
    var $_config;
    var $_l10n_midcom;

    function de_linkm_collector_navigation () {
        $this->_topic = null;
        $this->_config = $GLOBALS['de_linkm_collector__default_config'];
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
    }

    function is_internal() {
        return false;
    }

    function set_object($topic) {
        $this->_topic = $topic;
        $this->_config->store_from_object ($topic, "de.linkm.collector");
        return true;
    }

    function get_node() {
        $toolbar[100] = Array
        (
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        );

        return Array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $this->_topic->extra,
            MIDCOM_NAV_TOOLBAR => $toolbar,
            MIDCOM_NAV_CONFIGURATION => $this->_config,
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITOR => $this->_topic->revisor,
            MIDCOM_META_EDITED => $this->_topic->revised
        );
    }

    function get_leaves() {
        return Array();
    }

    function get_current_leaf () {
        return null;
    }


}

?>