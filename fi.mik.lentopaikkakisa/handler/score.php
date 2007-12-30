<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Flight reports in downloadable format
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_handler_score extends midcom_baseclasses_components_handler
{
    function fi_mik_lentopaikkakisa_handler_score()
    {
        parent::midcom_baseclasses_components_handler();
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_score($handler_id, $args, &$data)
    {
        $data['scores'] = Array();
        $data['total'] = 0;

        switch ($handler_id)
        {
            case 'score_pilot':
                $data['view_title'] = $this->_l10n->get('scores by pilot');
                $this->_component_data['active_leaf'] = "{$this->_topic->id}:scores_pilot";
                $report_type = 'person';
                $report_class = 'org_openpsa_contacts_person';
                $report_label = 'name';
                break;
            default:
                $data['view_title'] = $this->_l10n->get('scores by organization');
                $this->_component_data['active_leaf'] = "{$this->_topic->id}:scores_organization";
                $report_type = 'organization';
                $report_class = 'org_openpsa_contacts_group';
                $report_label = 'official';
                break;
        }

        $_MIDCOM->set_pagetitle($data['view_title']);

        $qb = new MidgardQueryBuilder('midgard_parameter');
        $qb->add_constraint('domain', '=', 'fi.mik.lentopaikkakisa');
        $qb->add_constraint('name', '=', "{$report_type}_scores");
        $qb->add_order('value', 'DESC');
        $scores = $qb->execute();

        foreach ($scores as $score)
        {
            $owner = new $report_class($score->parentguid);
            $data['scores']["{$owner->$report_label} ({$score->parentguid}"] = (int) $score->value;
            $data['total'] += (int) $score->value;
        }

        arsort($data['scores']);
        return true;
    }

    function _show_score($handler_id, &$data)
    {
        midcom_show_style('view-scores');
    }
}
?>