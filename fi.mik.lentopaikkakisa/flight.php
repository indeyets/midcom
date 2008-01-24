<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package fi.mik.lentopaikkakisa
 */
class fi_mik_flight_dba extends __fi_mik_flight_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->pilot)
        {
            $pilot = new midcom_db_person($this->pilot);
            return $pilot->guid;
        }
        return null;
    }

    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        if ($this->origin)
        {
            $date = $this->start;
            if ($date == 0)
            {
                $date = $this->end;
            }
            return sprintf($_MIDCOM->i18n->get_string('%s %s to %s', 'midcom'), strftime('%x', $date), $this->origin, $this->destination);
        }
        return "flight #{$this->id}";
    }
}
?>