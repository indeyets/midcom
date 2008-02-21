<?php

/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration system.
 *
 * <b>Required privileges:</b>
 *
 * Each user which is allowed to do registrations must have midgard:create privileges on the root
 * event. This implies create privileges for event members, as these are childs of the actual
 * events.
 *
 * In addition, registered users should have update privileges to their own person record. If these
 * are not granted, the personal details part of the registration form will be frozen.
 *
 * To operate in anonymous access mode, no further privileges need to be set, the system will
 * operate in sudo mode while creating the person/membership records required.
 *
 * <b>Important Request data keys:</b>
 *
 * During each request, these keys will always be available:
 *
 * - midcom_db_topic 'content_topic': The content topic to use.
 * - array 'schemadb': The schemadb specified in the configuration.
 *
 * Be aware that this data isloaded during the _on_handle callback, which means that you can't make
 * use of it in the can_handle callbacks.
 *
 * <b>Privilege notes</b>
 *
 * All users that should be able to register need the midgard:create privilege on the event
 * in question. This <em>includes</em> anonymous users if the component is cleared for anonymous
 * registration. Permissions are checked on a per-event level granularity.
 *
 * All registrations will have their owner privilege pointing to the associated person record.
 * In addition, the system will revoke the midgard:read privilege on each created registration.
 *
 * The net.nemein.registrations:manage privilege controls access to registration management
 * operations. It has to be granted (in addition to update/delete access) to all users which should
 * approve/reject registrations.
 *
 * I recommend assigning the management group ownership privileges to the root event or at least
 * the event they should manage. From there the required privileges will then inherit down to the
 * event members. Owners will <em>not</em> receive the manage privilege automatically, it has to
 * be granted manually.
 *
 * Upon approval of an registration, the system will revoke the ownership privilege of the
 * registrar, replacing it by a simple read privilege. (Registrations, which are approved, should
 * no longer be changeable by the registrar). Thus, managers also need privilege management permissions
 * on the registration (they are part of midgard:owner).
 *
 *
 * @package net.nemein_registrations
 */
class net_nemein_registrations_interface extends midcom_baseclasses_components_interface
{
    /**
     * Standard-Constructor.
     */
    function net_nemein_registrations_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.registrations';
        $this->_autoload_files = Array
        (
            'viewer.php',
            'navigation.php',
            'event.php',
            'registrar.php',
            'registration.php',
        );
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.mail',
        );
    }

    function _on_initialize()
    {
        return $_MIDCOM->componentloader->load_graceful('net.nemein.calendar');
    }

    function _on_reindex($topic, $config, &$indexer)
    {
        $content_topic_guid = $config->get('content_topic');
        if (!$content_topic_guid)
        {
            $content_topic_guid = $topic->guid;
        }
        $content_topic = new midcom_db_topic($content_topic_guid);
        if (   !$content_topic
            || !$content_topic->guid)
        {
            debug_add("Failed to load root event, aborting.");
            return;
        }
        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        if (! $schemadb)
        {
            debug_add("Failed to load schemadb, aborting.");
            return;
        }

        $qb = net_nemein_calendar_event_dba::new_query_builder();
        $qb->add_constraint('node', '=', $content_topic->id);
        $result = $qb->execute();

        if ($result)
        {
            $dm = new midcom_helper_datamanager2_datamanager($schemadb);
            if (! $dm->set_schema($config->get('event_schema')))
            {
                debug_add("Failed to set schema, aborting.");
                return;
            }
            foreach ($result as $event)
            {
                if (! $dm->set_storage($event))
                {
                    debug_add("Failed to set storage, skipping event {$event->id}.");
                    continue;
                }
                net_nemein_registrations_event::index($dm, $indexer, $topic);
            }
        }
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $content_topic_guid = $config->get('content_topic');
        if (!$content_topic_guid)
        {
            $content_topic_guid = $topic->guid;
        }
        $content_topic = new midcom_db_topic($content_topic_guid);
        if (   !$content_topic
            || !isset($content_topic->guid)
            || empty($content_topic->guid))
        {
            return null;
        }

        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);

        if (   is_a($object, 'net_nemein_calendar_event_dba')
            && $object->node == $content_topic->id)
        {
            return "event/view/{$guid}/";
        }

        if (is_a($object, 'net_nemein_registrations_registration_dba'))
        {
            $event = new net_nemein_calendar_event_dba($object->eid);
            if (   $event->guid
                && $event->node == $content_topic->id)
            {
                return "registration/view/{$guid}/";
            }
        }

        return null;
    }

}

?>