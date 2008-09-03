<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: importer.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * ActiveCalendar-like month display widget
 *
 * @package net.nemein.reservations
 */
class net_nemein_reservations_calendar extends org_openpsa_calendarwidget_month
{
    /**
     * URL prefix for links
     */
    var $day_prefix = null;

    /**
     * Simple constructor method. Initializes
     */
    function net_nemein_reservations_calendar($day_prefix = null)
    {
        $this->day_prefix = $day_prefix;
        parent::__construct();
    }

    /**
     * Draws one day
     *
     * @access private
     */
    function _draw_day($timestamp)
    {
        // Set the table cell classes
        echo "            <td".$this->_day_class($timestamp) . $this->_javascript_functions($timestamp).">\n";

        if (is_null($this->day_prefix))
        {
            $day_label = strftime('%d', $timestamp);
        }
        else
        {
            $day_label = "<a href=\"{$this->day_prefix}" . date('Y-m-d', $timestamp) . "/\">" . strftime('%d', $timestamp) . "</a>\n";
        }

        if (   $this->days_outside_month
            || (   $this->_parser >= $this->_month_start
                && $this->_parser <= $this->_month_end))
        {
            if (!array_key_exists(date('Y-m-d', $timestamp), $this->_events))
            {
                echo "                {$day_label}\n";
                echo "            </td>\n";
                return;
            }

            echo "                {$day_label}\n";


            if ($this->details_box)
            {
                $this->_draw_details_box($this->_events[date('Y-m-d', $timestamp)], $timestamp);
            }

        }
        echo "            </td>\n";
    }
}
?>