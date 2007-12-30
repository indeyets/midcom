<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum create post handler
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_handler_report extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function fi_mik_lentopaikkakisa_handler_report()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _seek_aerodrome($icao)
    {
        // Look for the airports in database
        $icao = strtoupper(substr($icao, 0, 4));
        $airport_qb = org_routamc_positioning_aerodrome_dba::new_query_builder();
        $airport_qb->add_constraint('icao', '=', $icao);
        $results = $airport_qb->execute();
        if (empty($results))
        {
            if ($this->_config->get('create_missing_aerodromes'))
            {
                // Create new aerodrome
                $_MIDCOM->auth->request_sudo('fi.mik.lentopaikkakisa');
                $airport = new org_routamc_positioning_aerodrome_dba();
                $airport->icao = $icao;
                $stat = $airport->create();
                $_MIDCOM->auth->drop_sudo();
                if (!$stat)
                {
                    // TODO: Report error
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to create missing aerodrome {$airport->icao}: " . mgd_errstr(), MIDCOM_LOG_WARN);
                    debug_pop();
                    return false;
                }
                $this->_request_data['new_aerodromes'][] = $airport;
                return $airport->icao;
            }

            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Airport with code {$icao} not found, skipping", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        return $results[0]->icao;
    }

    function _create_flight($origin, $destination, $score_origin, $score_destination)
    {
        $flight = new fi_mik_flight_dba();

        // Common properties
        $flight->pilot = $_MIDGARD['user'];
        $flight->operator = $_POST['operator'];
        $flight->aircraft = $_POST['aircraft'];
        $flight->scoreorigin = $score_origin;
        $flight->scoredestination = $score_destination;

        // Given end date
        $flight->end = @strtotime($_POST['date']);
        if ($flight->end == -1)
        {
            $flight->end = time();
        }

        $flight->origin = $this->_seek_aerodrome($origin);
        $flight->destination = $this->_seek_aerodrome($destination);

        if (!$flight->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to create flight report to {$destination}, " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        $this->_request_data['new_flights'][] = $flight;

        return $flight;
    }

    /**
     * Displays a report edit view.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
     */
    function _handler_new($handler_id, $args, &$data)
    {
        // FIXME: This doesn't work for some reason
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'fi_mik_flight_dba');
        $_MIDCOM->auth->require_valid_user();

        if (isset($_POST['save']))
        {
            $data['new_flights'] = array();
            $data['new_aerodromes'] = array();
            foreach ($_POST['destination'] as $identifier => $destination)
            {
                if (strlen($destination) != 4)
                {
                    continue;
                }

                if (!isset($_POST['origin'][$identifier]))
                {
                    $origin = 'EFHF';
                }
                else
                {
                    $origin = $_POST['origin'][$identifier];
                }

                if (!isset($_POST['score_origin'][$identifier]))
                {
                    $score_origin = 0;
                }
                else
                {
                    $score_origin = $_POST['score_origin'][$identifier];
                }

                if (!isset($_POST['score_destination'][$identifier]))
                {
                    $score_destination = 0;
                }
                else
                {
                    $score_destination = $_POST['score_destination'][$identifier];
                }

                $stat = $this->_create_flight($origin, $destination, $score_origin, $score_destination);
            }

            // Cache scores
            $_MIDCOM->auth->request_sudo('fi.mik.lentopaikkakisa');
            $person_scores = 0;
            $person_aerodromes = array();
            $person_flight_qb = fi_mik_flight_dba::new_query_builder();
            $person_flight_qb->add_constraint('pilot', '=', $_MIDGARD['user']);
            $flights = $person_flight_qb->execute();
            foreach ($flights as $flight)
            {
                if (!array_key_exists($flight->origin, $person_aerodromes))
                {
                    // Only one score per aerodrome for the person
                    $person_scores += $flight->scoreorigin;
                    $person_aerodromes[$flight->origin] = $flight->scoreorigin;
                }

                if (!array_key_exists($flight->destination, $person_aerodromes))
                {
                    // Only one score per aerodrome for the person
                    $person_scores += $flight->scoredestination;
                    $person_aerodromes[$flight->destination] = $flight->scoredestination;
                }
            }
            $person = $_MIDCOM->auth->user->get_storage();
            $person->parameter('fi.mik.lentopaikkakisa', 'person_scores', $person_scores);

            $organization_scores = 0;
            $organization_aerodromes = array();
            $organization_flight_qb = fi_mik_flight_dba::new_query_builder();
            $organization_flight_qb->add_constraint('operator', '=', (int) $_POST['operator']);
            $flights = $organization_flight_qb->execute();
            foreach ($flights as $flight)
            {
                if (!array_key_exists($flight->pilot, $organization_aerodromes))
                {
                    $organization_aerodromes[$flight->pilot] = array();
                }

                if (!array_key_exists($flight->origin, $organization_aerodromes[$flight->pilot]))
                {
                    // Only one score per aerodrome per person
                    $organization_scores += $flight->scoreorigin;
                    $organization_aerodromes[$flight->pilot][$flight->origin] = $flight->scoreorigin;
                }

                if (!array_key_exists($flight->destination, $organization_aerodromes[$flight->pilot]))
                {
                    // Only one score per aerodrome per person
                    $organization_scores += $flight->scoredestination;
                    $organization_aerodromes[$flight->pilot][$flight->destination] = $flight->scoredestination;
                }
            }
            $organization = new org_openpsa_contacts_group($_POST['operator']);
            $organization->parameter('fi.mik.lentopaikkakisa', 'organization_scores', $organization_scores);
            $_MIDCOM->auth->drop_sudo();
            // Redirect to front page
            $_MIDCOM->relocate('');

        }

        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "report.html",
            MIDCOM_NAV_NAME => $this->_l10n->get('report flight'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $_MIDCOM->set_pagetitle($this->_l10n->get('report flight'));

        return true;
    }

    /**
     * Shows the loaded report editor
     */
    function _show_new($handler_id, &$data)
    {
        midcom_show_style('report-widget');
    }
}
?>