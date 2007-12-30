<?php

/**
 * @package net.nemein.internalorders
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: viewer.php,v 1.3.2.7 2005/11/07 18:57:45 bergius Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar Viewer interface class.
 *
 * @package net.nemein.internalorders
 */
class net_nemein_internalorders_handler_own extends midcom_baseclasses_components_handler
{

	/**
	 * The root event to use with this topic.
	 *
	 * @var midcom_baseclasses_database_event
	 * @access private
	 */
	var $_root_event = null;

	function net_nemein_internalorders_handler_own()
	{
		parent::midcom_baseclasses_components_handler();
	}

	function _on_initialize()
	{
		if (is_null($this->_config->get('root_event')))
		{
			$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Component is not properly initialized, root event missing");
		}

		$this->_root_event = mgd_get_object_by_guid($this->_config->get('root_event'));
		if (!$this->_root_event)
		{
			$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Root event not found: ".mgd_errstr());
		}
	}

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
	function _handler_own($handler_id, $args, &$data)
	{

		if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
		// TODO: List user's own orders and incoming orders
		$this->_request_data['created'] = array();
		$this->_request_data['sent'] = array();
		$this->_request_data['incoming'] = array();


//		whit this we make all the events as new internalorder
//		Do not remove comments if you don't know exactly what you are doing
/*		 	$all_orders_tmp = mgd_list_events($this->_root_event->id);
		while ($all_orders_tmp->fetch())
		{
			$all_orders_tmp->type = NET_NEMEIN_INTERNALORDERS_RECEIVED;
			$all_orders_tmp->update();
		}*/


//		$all_orders = mgd_list_events($this->_root_event->id, 'created', NET_NEMEIN_INTERNALORDERS_NEW);
//		print_r($all_orders);

		$QB = midcom_db_event::new_query_builder();
		$QB->add_constraint('up', '=', $this->_root_event->id);
		$QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_NEW);
		$QB->add_constraint('creator', '=', $_MIDGARD['user']);
		$QB->add_order('created', 'ASC');
		$orders = $QB->execute();
		$this->_request_data['created'] = $orders;


		$QB = midcom_db_event::new_query_builder();
		$QB->add_constraint('up', '=', $this->_root_event->id);
		$QB->begin_group('OR');
		$QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
		$QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
		$QB->end_group();
		$QB->add_constraint('creator', '=', $_MIDGARD['user']);
		$QB->add_order('created', 'ASC');
		$orders = $QB->execute();
		$this->_request_data['sent'] = $orders;


		$QB = midcom_db_event::new_query_builder();
		$QB->add_constraint('up', '=', $this->_root_event->id);
		$QB->begin_group('OR');
		$QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT);
		$QB->add_constraint('type', '=', NET_NEMEIN_INTERNALORDERS_SENT_LOCKED);
		$QB->end_group();
		$QB->add_constraint('extra', '=', $_MIDGARD['user']);
		$QB->add_order('created', 'ASC');
		$orders = $QB->execute();
		$this->_request_data['incoming'] = $orders;


		return true;
	}

	function _show_own($handler_id, &$data)
	{
		midcom_show_style('show_own');
	}

}
?>
