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
 */
function net_nemein_calendar_functions_daylabel($label='start', $start, $end) 
{
    $i18n = & $GLOBALS['midcom']->get_service('i18n');
    $language = $i18n->get_current_language();
    $language_db = $i18n->get_language_db();
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
        
        $daylabel .= date('H:i', $start);
    }
    else
    {
        if (date('Y', $start) != date('Y', $end))
        {
            $daylabel .= strftime('%A %d. %B %Y ', $end);
        }
        elseif (date('m', $start) != date('m', $end))
        {
            $daylabel .= strftime('%A %d. %B ', $end);
        }
        elseif (date('d', $start) != date('d', $end))
        {
            $daylabel .= strftime('%A %d. ', $end);        
        }
        
        $daylabel .= date('H:i', $end);        
    }
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
?>
