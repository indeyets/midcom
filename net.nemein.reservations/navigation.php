<?php

class net_nemein_reservations_navigation {
    var $_object;
    var $_config;
    var $_root_group;
    var $_root_event;
    var $_l10n;
    var $_l10n_midcom;
    var $_auth;
    
    function net_nemein_reservations_navigation() {
        $this->_object = null;
        $this->_config = $GLOBALS["net_nemein_reservations__default_config"];
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.reservations");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $this->_auth = new net_nemein_reservations_auth();
    } 
    
    function is_internal() {
        return false;
    } 
    
    function get_leaves() {
        $ret = Array();
        $memberships = mgd_list_members($this->_root_group->id);
        if ($memberships)
        {
            while ($memberships->fetch()) 
            {
                $person = mgd_get_person($memberships->uid);
                if (! $person) 
                {
                    continue;
                }
                $toolbar = Array();
                if ($this->_auth->is_admin()) {
                    $toolbar[50] = Array(
                        MIDCOM_TOOLBAR_URL => "resource/edit/{$person->id}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit resource'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                        MIDCOM_TOOLBAR_ENABLED => true
                    );
                    $toolbar[51] = Array(
                        MIDCOM_TOOLBAR_URL => "resource/delete/{$person->id}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete resource'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                        MIDCOM_TOOLBAR_ENABLED => true
                    );
                }
                $person_guid = $person->guid();
                $ret[$person->id] = array (
                    MIDCOM_NAV_SITE => Array (
                        MIDCOM_NAV_URL =>  "{$person_guid}.html",
                        MIDCOM_NAV_NAME => $person->name),
                    MIDCOM_NAV_ADMIN => Array (
                        MIDCOM_NAV_URL => "resource/view/{$person->id}.html",
                        MIDCOM_NAV_NAME => $person->name),
                    MIDCOM_NAV_GUID => $person_guid,
                    MIDCOM_NAV_TOOLBAR => ((count($toolbar) > 0) ? $toolbar : null),
                    MIDCOM_META_CREATOR => $person->creator,
                    MIDCOM_META_EDITOR => $person->creator,
                    MIDCOM_META_CREATED => $person->created,
                    MIDCOM_META_EDITED => $person->created
                );
                
                $events = mgd_list_events_between_by_member($person->id, time()-7200, 2000000000, 'start');
                if ($events)
                {
                    while ($events->fetch())
                    {
                        $event = mgd_get_event($events->id);
                        $event_guid = $event->guid();
                        $toolbar = array();
                        /*
                        if (   is_object($this->_root_event)
                            && mgd_is_event_owner($this->_root_event->id))
                        */
                        if (mgd_is_event_owner($event->id))
                        {
                            $toolbar[50] = Array(
                                MIDCOM_TOOLBAR_URL => "reservation/edit/{$event->id}.html",
                                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit reservation'),
                                MIDCOM_TOOLBAR_HELPTEXT => null,
                                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                                MIDCOM_TOOLBAR_ENABLED => true
                            );
                            $toolbar[51] = Array(
                                MIDCOM_TOOLBAR_URL => "reservation/delete/{$event->id}.html",
                                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('delete reservation'),
                                MIDCOM_TOOLBAR_HELPTEXT => null,
                                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                                MIDCOM_TOOLBAR_ENABLED => true
                            );
                        }
                        //$ret["event-{$event->id}"] = array (
                        $ret[$event->id] = array (
                            MIDCOM_NAV_SITE => Array (
                                MIDCOM_NAV_URL =>  "{$person_guid}/reservation/{$event_guid}.html",
                                MIDCOM_NAV_NAME => $event->title,
                            ),
                            MIDCOM_NAV_ADMIN => Array (
                                MIDCOM_NAV_URL => "reservation/view/{$event->id}.html",
                                MIDCOM_NAV_NAME => $event->title,
                            ),
                            MIDCOM_NAV_GUID => $event_guid,
                            MIDCOM_NAV_TOOLBAR => ((count($toolbar) > 0) ? $toolbar : null),
                            MIDCOM_META_CREATOR => $event->creator,
                            MIDCOM_META_EDITOR => $event->creator,
                            MIDCOM_META_CREATED => $event->created,
                            MIDCOM_META_EDITED => $event->created,
                            MIDCOM_NAV_VISIBLE => false,
                        );
                    }
                }
            }
        }
        return $ret;
    } 
    
    function get_node() {
        $topic = &$this->_object;
        
        $toolbar = Array();
        if ($this->_auth->is_poweruser()) 
        {
            $toolbar[100] = Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
        
            /* Add the new article link at the beginning*/
            $toolbar[0] = Array(
                MIDCOM_TOOLBAR_URL => 'resource/create.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create resource'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
        }
        if ($this->_auth->is_admin()) 
        {
            $toolbar[101] = Array(
                MIDCOM_TOOLBAR_URL => 'reservation/maintain.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('incomplete/corrupt reservations'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                MIDCOM_TOOLBAR_ENABLED => true
            );
        }
        
        return array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $topic->extra,
            MIDCOM_NAV_TOOLBAR => ((count($toolbar) > 0) ? $toolbar : null),
            MIDCOM_META_CREATOR => $topic->creator,
            MIDCOM_META_EDITOR => $topic->revisor,
            MIDCOM_META_CREATED => $topic->created,
            MIDCOM_META_EDITED => $topic->revised
        );
    } 
    
    function set_object($object) {
        $this->_object = $object;
        $this->_config->store_from_object($object, "net.nemein.reservations");
        $guid = $this->_config->get("root_group_guid");
        if (! is_null ($guid)) 
        {
            $object = mgd_get_object_by_guid($guid);
            if ($object == false || $object->__table__ != "grp") 
            {
                debug_add("We could not successfully load the root group, the guid was [$guid], "
                          . "last Midgard Error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r("Resulting object dump: ", $object);

                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                    "Invalid configuration, root_group_guid [$guid] is invalid.");
            }
            $this->_root_group = $object;
        }
        /*
        $guid = $this->_config->get("root_event_guid");
        if (! is_null ($guid)) 
        {
            $object = mgd_get_object_by_guid($guid);
            if ($object == false || $object->__table__ != "event") 
            {
                debug_add("We could not successfully load the root event, the guid was [$guid], "
                          . "last Midgard Error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r("Resulting object dump: ", $object);

                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                    "Invalid configuration, root_group_guid [$guid] is invalid.");
            }
            $this->_root_event = $object;
        }
        */
        return true;
    } 
    
    function get_current_leaf() {
        if (array_key_exists("net_nemein_reservations_current_leaf", $GLOBALS)) 
        {
            return $GLOBALS["net_nemein_reservations_current_leaf"];
        }
        return false;
    } 
} // navigation

?>
