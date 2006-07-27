<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * registrations AIS interface class
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_admin extends midcom_baseclasses_components_request_admin
{
    function net_nemein_registrations_admin($topic, $config)
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    /**
     * @access private
     */
    function _on_initialize()
    {
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            // 'fixed_args' => Array('config'),
            'schemadb' => 'file:/net/nemein/registrations/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
    }

    /*
     * Legacy Code

    function _create_root_event () {
        debug_push($this->_debug_prefix . "_create_root_event" );
        $event = mgd_get_event();
        $event->owner = $this->_topic->owner;
        $event->title = "__CAMPAIGN";
        $event->description = "Autocreated by net.nemein.registrations.";
        $event->up = 0;
        $event->type = 0;
        $id = $event->create();
        if ($id === false) {
            $msg = sprintf($this->_l10n->get("failed to create root event: %s"),
                           mgd_errstr());
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
            debug_add("Could not create root event: " . mgd_errstr());
            return false;
        }
        $event = mgd_get_event($id);
        $this->_topic->parameter("net.nemein.registrations","root_event_guid",$event->guid());
        $msg = sprintf($this->_l10n->get("created root event <em>%s</em>"),
                       $event->title);
        $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
        $this->_root_event = $event;
        debug_pop();
        return true;
    }

    function _detect_nn_campaignroot () {
        debug_push($this->_debug_prefix . "_detect_nn_campaignroot");
        debug_add("Scanning for an __CAMPAIGN root event.");
        $events = mgd_list_events(0);
        if ($events)
            while($events->fetch())
                if ($events->title == "__CAMPAIGN") {
                    $this->_event = mgd_get_event($events->id);
                    debug_add("Found a __CAMPAIGN event.");
                    debug_pop();
                    return true;
                }
        debug_pop();
        return false;
    }

     */
}

?>
