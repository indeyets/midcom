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
class net_nemein_internalorders_viewer extends midcom_baseclasses_components_request
{

	/**
	 * The root event to use with this topic.
	 * 
	 * @var midcom_baseclasses_database_event
	 * @access private
	 */
	var $_root_event = null;

	function net_nemein_internalorders_viewer($topic, $config)
	{
		parent::midcom_baseclasses_components_request($topic, $config);
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
			$_MIDCOM->componentloader->load_graceful('org.openpsa.products');
		
//				$nap2 =& new midcom_helper_nav();
//				$node = $nap2->get_node($nap2->get_current_node());
//				$this->_request_data['products_topic'] = $node[MIDCOM_NAV_OBJECT];

//		$this->_request_data['products_topic'] = mgd_get_object_by_guid($this->_config->get('products_topic'));


		// Define the URL space
		
		// / shows user's sent and incoming orders
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_own', 'own'),
		);			

		//search products
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_search', 'search_products'),
			'fixed_args' => array('edit', 'search'),
		);

		// /view/<event GUID>.html shows individual event
		$this->_request_switch[] = Array
		(
			'handler' => 'view',
			'fixed_args' => array('view'),
			'variable_args' => 1,
		);
		// /print/<event GUID>.html shows individual event for print
		$this->_request_switch[] = Array
		(
			'handler' => 'print',
			'fixed_args' => array('print'),
			'variable_args' => 1,
		);
		
		// /report shows reports
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report'),
			'fixed_args' => array('report'),
		);
		
		// /report/by_places shows report by places
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places'),
			'fixed_args' => array('report', 'by_places'),
		);
		// /report/by_places/sent/<person ID>.html shows report by sent location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_sent'),
			'fixed_args' => array('report', 'by_places', 'sent'),
			'variable_args' => 1,
		);
		// /report/by_places/sent_export/<person ID>.html shows report by sent location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_sent_export'),
			'fixed_args' => array('report', 'by_places', 'sent_export'),
			'variable_args' => 1,
		);
		// /report/by_places/receive/<person ID>.html shows report by received location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_receive'),
			'fixed_args' => array('report', 'by_places', 'receive'),
			'variable_args' => 1,
		);
		// /report/by_places/receive_export/<person ID>.html shows report by received location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_receive_export'),
			'fixed_args' => array('report', 'by_places', 'receive_export'),
			'variable_args' => 1,
		);
		// /report/by_places/sent_2/<person ID>.html shows report by sent location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_sent_2'),
			'fixed_args' => array('report', 'by_places', 'sent_2'),
			'variable_args' => 1,
		);
		// /report/by_places/sent_2_export/<person ID>.html shows report by sent location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_sent_2_export'),
			'fixed_args' => array('report', 'by_places', 'sent_2_export'),
			'variable_args' => 1,
		);
		// /report/by_places/receive_2/<person ID>.html shows report by received location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_receive_2'),
			'fixed_args' => array('report', 'by_places', 'receive_2'),
			'variable_args' => 1,
		);
		// /report/by_places/receive_2_export/<person ID>.html shows report by received location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_receive_2_export'),
			'fixed_args' => array('report', 'by_places', 'receive_2_export'),
			'variable_args' => 1,
		);
		
		
		
		// /report/by_places/sent_3/<person ID>.html shows report by sent location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_sent_3'),
			'fixed_args' => array('report', 'by_places', 'sent_3'),
			'variable_args' => 1,
		);
		// /report/by_places/sent_3_export/<person ID>.html shows report by sent location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_sent_3_export'),
			'fixed_args' => array('report', 'by_places', 'sent_3_export'),
			'variable_args' => 1,
		);
		// /report/by_places/receive_3/<person ID>.html shows report by received location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_receive_3'),
			'fixed_args' => array('report', 'by_places', 'receive_3'),
			'variable_args' => 1,
		);
		// /report/by_places/receive_3_export/<person ID>.html shows report by received location (user)
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_places_receive_3_export'),
			'fixed_args' => array('report', 'by_places', 'receive_3_export'),
			'variable_args' => 1,
		);
		
		
		// /report/by_products shows report by products. Shows the product groups
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_products'),
			'fixed_args' => array('report', 'by_products'),
		);
		
		// /report/by_products/<product group> shows report by products. Shows the products under the product group
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_products_group'),
			'fixed_args' => array('report', 'by_products_group'),
			'variable_args' => 1,
		);
		
		// /report/by_products shows report by products
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_products_detail'),
			'fixed_args' => array('report', 'by_products', 'detail'),
			'variable_args' => 1,
		);
		
		// /report/by_products_export shows report by products
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_by_products_detail_export'),
			'fixed_args' => array('report', 'by_products', 'detail_export'),
			'variable_args' => 1,
		);
		
		// /report/unclear shows report by orders that are unclear (means that which doesn't have the same amount
		// sending and receiving
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_unclear'),
			'fixed_args' => array('report', 'unclear'),
		);
		
		// /report/unclear/detail shows report by orders that are unclear (means that which doesn't have the same amount
		// sending and receiving. Shows by the sender id
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_unclear_detail'),
			'fixed_args' => array('report', 'unclear', 'detail'),
			'variable_args' => 1,
		);
		
		// /report/unclear/detail_export shows report by orders that are unclear (means that which doesn't have the same amount
		// sending and receiving. Shows by the sender id
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_unclear_detail_export'),
			'fixed_args' => array('report', 'unclear', 'detail_export'),
			'variable_args' => 1,
		);
		
		// /report/unclear/detail_2 shows report by orders that are unclear (means that which doesn't have the same amount
		// sending and receiving. Shows by the sender id
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_unclear_detail_2'),
			'fixed_args' => array('report', 'unclear', 'detail_2'),
			'variable_args' => 1,
		);
		
		// /report/unclear/detail_2_export shows report by orders that are unclear (means that which doesn't have the same amount
		// sending and receiving. Shows by the sender id
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_reports', 'report_unclear_detail_2_export'),
			'fixed_args' => array('report', 'unclear', 'detail_2_export'),
			'variable_args' => 1,
		);
		
		// /receive/<event GUID>.html marks order as received
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_receive', 'receive'),
			'fixed_args' => array('receive'),
			'variable_args' => 1,
		);
		
		// /create/ creates new order
		$this->_request_switch[] = Array
		(
			'handler' => 'create',
			'fixed_args' => array('create'),
		);
		
		// /edit/<event GUID>.html edits order before lockdown
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_edit', 'edit'),
			'fixed_args' => array('edit'),
			'variable_args' => 1,
		);
		
		// /delete/<event GUID>.html deletes order before lockdown
		$this->_request_switch[] = Array
		(
			'handler' => 'delete',
			'fixed_args' => array('delete'),
			'variable_args' => 1,
		);
/*
		// /products shows products
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_products', 'products'),
			'fixed_args' => array('products'),
		);
		// /products shows products
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_products', 'productsnew'),
			'fixed_args' => array('productsnew'),
		);
		 // /products shows products
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_products', 'productsedit'),
			'fixed_args' => array('productsedit'),
			'variable_args' => 1,
		);
*/
		// /getproducts gets products from CSV
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_products', 'products_get_from_csv'),
			'fixed_args' => array('getproducts'),
		);
		// /getgroups gets groups from CSV
		$this->_request_switch[] = Array
		(
			'handler' => Array('net_nemein_internalorders_handler_products', 'groups_get_from_csv'),
			'fixed_args' => array('getgroups'),
		);

		// Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/internalorders/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

	}

	/**
	 * This view creates a new empty event record and redirects user to the editing
	 * state. This has the unfortunate consequence of possibly littering the system
	 * with empty event records if users don't save data.
	 */
	function _handler_create($handler_id, $args, &$data)
	{
		$nap2 =& new midcom_helper_nav();
		$node = $nap2->get_node($nap2->get_current_node());
		$topic = $node[MIDCOM_NAV_OBJECT];
		if(!$topic->get_parameter('net.nemein.internalorders', 'counter'))
		{
			$topic->set_parameter('net.nemein.internalorders', 'counter', 1);
		}
		else
		{
			$counter = $topic->get_parameter('net.nemein.internalorders', 'counter');
		}
	
		$event = mgd_get_event();
		$event->up = $this->_root_event->id;
		$event->type = NET_NEMEIN_INTERNALORDERS_NEW;
		$event->title = $counter;
		$topic->set_parameter('net.nemein.internalorders', 'counter', $counter+1);
		$event->start = time();
		$event->end = time()+1;
		print_r($event);
		$stat = $event->create();
		
		if (!$stat)
		{
			$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create order: ".mgd_errstr());
		}
		

		
		$event = mgd_get_event($stat);
//			$event->parameter('net.nemein.internalorders', 'date', time());
//			$event->parameter('net.nemein.internalorders', 'handler', $_MIDGARD['user']);
//			$event->update();
		$_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX).'edit/'.$event->guid().'.html');
	}
	
	function _send_mail_about_deleted_order($number_for_order, $to_id, $from_id)
	{
		$_MIDCOM->componentloader->load_graceful('org.openpsa.mail');
//		ini_set('error_reporting', E_ALL & ~E_NOTICE);
		$email_to_person = new midcom_db_person($to_id);
		$email_from_person = new midcom_db_person($from_id);
		
		$email_to = $email_to_person->email;
		
		$mail_to = $email_to;
		$mail_from = 'sisainen.siirto@anttila.fi';
		$subject = "Teille lähetetty sisäisen siirron lomake on poistettu";


		$body = "Teille aikaisemmin lähetetty sisäisen siirron lomake on poistettu..\n\n";
		$body .= "Poistaja: ".$email_from_person->firstname." ".$email_from_person->lastname."\n\n";
		$body .= "Lähetteen numero: ".$number_for_order;
		
		$mail =  new org_openpsa_mail();

		$mail->headers["content-type"] = "text/plain; charset=UTF-8; format=flowed;";
		$mail->to = $mail_to;
		$mail->from = $mail_from;
		$mail->subject = $subject;
		$mail->headers["subject"] = $subject;
		$mail->body = $body;
		
		$mail->send();
//		ini_set('error_reporting', E_ALL);
	}
	
	function _handler_delete($handler_id, $args, &$data)
	{
		$event = mgd_get_object_by_guid($args[0]);
		if(		$event->type == NET_NEMEIN_INTERNALORDERS_NEW
			||	$event->type == NET_NEMEIN_INTERNALORDERS_SENT
		)
		{
			$event->type = NET_NEMEIN_INTERNALORDERS_REMOVED;
			$event->update();

			$stat = $this->_send_mail_about_deleted_order($event->title, $event->extra, $_MIDGARD['user']);

		}
		
		$_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
	}

	


	function _handler_view($handler_id, $args, &$data)
	{
		$event = mgd_get_object_by_guid($args[0]);
		if (	!$event
			|| $event->up != $this->_root_event->id)
		{
			// Wrong kind of event
			return false;
		}
		
		
		$this->_request_data['event'] = $event;
		
		
		$tmp[] = Array
		(
			MIDCOM_NAV_URL => 'view/'.$args[0].'/',
			MIDCOM_NAV_NAME => 'Näytä lähete nro '.$event->title,
		);
	
		$_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', array_reverse($tmp));
		
		
		// Load products under the order
		$this->_request_data['products'] = array();
		$products = mgd_list_events($this->_request_data['event']->id, 'created');
		if ($products)
		{
			while ($products->fetch())
			{
				$this->_request_data['products'][$products->guid()] = array(
					'title' => $products->title,
					'value' => $products->extra,
					'salesprice' => $products->parameter('net.nemein.internalorders', 'salesprice'),
					'quantity' => $products->parameter('net.nemein.internalorders', 'quantity'),
					'sum' => $products->parameter('net.nemein.internalorders', 'sum'),
					'quantity_received' => $products->parameter('net.nemein.internalorders', 'quantity_received'),
					'additional' => $products->parameter('net.nemein.internalorders', 'additional'),
				);
			}
		}
		
		$_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('edit %s'), $this->_request_data['event']->title));

		return true;
	}
	
	function _show_view($handler_id, &$data)
	{
		midcom_show_style('show_order');

	}


	function _handler_print($handler_id, $args, &$data)
	{
		$event = mgd_get_object_by_guid($args[0]);
		if (	!$event
			|| $event->up != $this->_root_event->id)
		{
			// Wrong kind of event
			return false;
		}
		
		
		$this->_request_data['event'] = $event;
		
		
		
		// Load products under the order
		$this->_request_data['products'] = array();
		$products = mgd_list_events($this->_request_data['event']->id, 'created');
		if ($products)
		{
			while ($products->fetch())
			{
				$this->_request_data['products'][$products->guid()] = array(
					'title' => $products->title,
					'value' => $products->extra,
					'salesprice' => $products->parameter('net.nemein.internalorders', 'salesprice'),
					'quantity' => $products->parameter('net.nemein.internalorders', 'quantity'),
					'sum' => $products->parameter('net.nemein.internalorders', 'sum'),
					'quantity_received' => $products->parameter('net.nemein.internalorders', 'quantity_received'),
					'additional' => $products->parameter('net.nemein.internalorders', 'additional'),
				);
			}
		}
		
		$_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('edit %s'), $this->_request_data['event']->title));

		return true;
	}
	
	function _show_print($handler_id, &$data)
	{
		midcom_show_style('show_order_print');

	}
	
	

}
?>
	
