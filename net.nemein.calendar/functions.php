<?php
/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar DayLabel function
 * @param string $label 'start' if it's the startdate or 'end' if it's the end date.
 * @param timestamp $start unixtimestamp
 * @param timestamp $end unix timestamp
 * @param boolean $add_time true if you want to add hour:minute to the date
 */
function net_nemein_calendar_functions_daylabel($label='start', $start, $end , $add_time = true, $add_year = false)
{
    /**
     * If mucking about with locales at least have the courtesy to return
     * the value to what it used to be, here we read the current value,
     * see the end of function for restoring it
     */
    $current_time_locale = setlocale(LC_TIME, '0');

    /**
     * Make double sure LC_TIME is set according to current language
     *
     * This should not be necessary, also there might be a case where someone
     * wants to set LC_TIME but not set actual language, this will negate that attempt
     */
    $language = $_MIDCOM->i18n->get_current_language();
    $language_db = $_MIDCOM->i18n->get_language_db();
    /**
     * NOTE: setlocale can take an array of locales as value, it will use
     * the first name valid for the system, thus if some charset or such
     * variant is not present, add it to the language db, do not make up weird
     * variable rewriting rules here
     */
    setlocale(LC_TIME, $language_db[$language]['locale']);

    $daylabel = '';

    if ($label == 'start')
    {
        // We want to output the label for start time
        $daylabel .= strftime('%A %d. %B ', $start);

        if (date('Y', $start) != date('Y', $end))
        {
            $daylabel .= date('Y ', $start);
        }

        if ($add_time)
        {
            $daylabel .= date('H:i', $start);
        }
    }
    else
    {
        if (   $add_year
            || date('Y', $start) != date('Y', $end))
        {
            $daylabel .= strftime('%A %d. %B %Y ', $end);
        }
        elseif (date('m', $start) != date('m', $end))
        {
            $daylabel .= strftime('%A %d. %B ', $end);
        }
        elseif (date('d', $start) != date('d', $end))
        {
            $daylabel .= strftime('%A %d. %B ', $end);
        }

        if ($add_time)
        {
            $daylabel .= date('H:i', $end);
        }
    }
    /**
     * Restore original LC_TIME value, see beginning of function
     */
    setlocale(LC_TIME, $current_time_locale);
    return $daylabel;
}

/**
 * Transforms an event object into a valid MidCOM Indexer
 * Document, using the midcom document base class.
 *
 * @param NemeinCalendar_event $event The event to transform.
 * @return midcom_services_indexer_document_midcom Transformed Event or false on failure.
 */
function net_nemein_calendar_event2document($event)
{
    // Add missing fields to the object:
    $event->__table__ = 'event';
    $event->revised = 0;
    $event->revisor = 1;
    $event->created = 0;

    $document = new midcom_services_indexer_document_midcom($event);
    $document->content = "{$event->description} {$event->title}";
    $document->title = $event->title;
    if (strlen($event->description) > 200)
    {
        $document->abstract = substr($event->description, 0, 200) . '...';
    }
    else
    {
        $document->abstract = $event->description;
    }
    return $document;
}

function net_nemein_calendar_compute_first_event(&$parent)
{
    $qb = net_nemein_calendar_event_dba::new_query_builder();
    if (is_a($parent, 'net_nemein_calendar_event'))
    {
        $qb->add_constraint('up', 'INTREE', $parent->id);
    }
    elseif (is_a($parent, 'midgard_topic'))
    {
        $qb->add_constraint('node', '=', $parent->id);
    }
    else
    {
        return false;
    }
    // Avoid problems with events too close to the epoch (highly unlikely usage scenario in any case)
    $qb->add_constraint('start', '>', '1972-01-02 00:00:00');
    $qb->add_order('start');
    $qb->set_limit(1);

    if ($_MIDCOM->auth->request_sudo())
    {
        $result = $qb->execute();
        $_MIDCOM->auth->drop_sudo();
    }
    else
    {
        $result = $qb->execute();
    }
    unset($qb);
    if (empty($result))
    {
        return false;
    }
    return $result[0];
}

function net_nemein_calendar_compute_last_event(&$parent)
{
    $qb = net_nemein_calendar_event_dba::new_query_builder();
    if (is_a($parent, 'net_nemein_calendar_event'))
    {
        $qb->add_constraint('up', 'INTREE', $parent->id);
    }
    elseif (is_a($parent, 'midgard_topic'))
    {
        $qb->add_constraint('node', '=', $parent->id);
    }
    else
    {
        return false;
    }
    // Avoid problems with events too close to the epoch (highly unlikely usage scenario in any case)
    $qb->add_constraint('start', '>', '1972-01-02 00:00:00');
    $qb->add_order('end', 'DESC');
    $qb->set_limit(1);

    if ($_MIDCOM->auth->request_sudo())
    {
        $result = $qb->execute();
        $_MIDCOM->auth->drop_sudo();
    }
    else
    {
        $result = $qb->execute();
    }
    unset($qb);
    if (empty($result))
    {
        return false;
    }
    return $result[0];
}


?>