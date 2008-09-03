<?php
/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: index.php 3495 2006-05-26 17:11:20Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum create post handler
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_handler_index extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function fi_mik_lentopaikkakisa_handler_index()
    {
        parent::__construct();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_index($handler_id, $args, &$data)
    {
        $this->_request_data['node'] =& $this->_topic;

        $qb = fi_mik_flight_dba::new_query_builder();
        $qb->add_order('created', 'DESC');
        $qb->set_limit($this->_config->get('show_latest'));
        $this->_request_data['latest'] = $qb->execute();

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('view-index');
    }
}
?>