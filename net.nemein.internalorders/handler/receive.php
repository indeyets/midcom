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
class net_nemein_internalorders_handler_receive extends midcom_baseclasses_components_handler
{

	/**
	 * The root event to use with this topic.
	 *
	 * @var midcom_baseclasses_database_event
	 * @access private
	 */
	var $_root_event = null;

	function net_nemein_internalorders_handler_receive()
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


	function _send_mail_about_unclear_order($guid_for_order, $to_id, $from_id, $receivenotes)
	{
		$_MIDCOM->componentloader->load_graceful('org.openpsa.mail');
//		ini_set('error_reporting', E_ALL & ~E_NOTICE);
		$email_to_person = new midcom_db_person($to_id);
		$email_from_person = new midcom_db_person($from_id);

		$email_to = $email_to_person->email;

		$mail_to = $email_to;
		$mail_from = 'sisainen.siirto@anttila.fi';
		$subject = "Lähettämässänne lähetteessä oli epäselvyyksiä";


		$body = $receivenotes."\n\n";
		$body .= "Lähettäjänä: ".$email_from_person->firstname." ".$email_from_person->lastname."\n\n";
		$body .= $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX).'view/'.$guid_for_order;

//		mgd_include_snippet('/NemeinNet_Core/Mail');


		$mail =  new org_openpsa_mail();

		$mail->headers["content-type"] = "text/plain; charset=UTF-8; format=flowed;";
//		$mail->encoding = $this->_i18n->get_current_charset();
		$mail->to = $mail_to;
		$mail->from = $mail_from;
		$mail->subject = $subject;
		$mail->headers["subject"] = $subject;
		$mail->body = $body;

		$mail->send();
//		ini_set('error_reporting', E_ALL);
	}

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
	function _handler_receive($handler_id, $args, &$data)
	{
		$event = mgd_get_object_by_guid($args[0]);
		$is_unclear = false;
		if (	!$event
			|| $event->up != $this->_root_event->id)
		{
			// Wrong kind of event
			return false;
		}


		$this->_request_data['event'] = $event;


		$tmp[] = Array
		(
			MIDCOM_NAV_URL => 'receive/'.$args[0].'/',
			MIDCOM_NAV_NAME => 'Vastaanota lähete nro '.$event->title,
		);

		$_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', array_reverse($tmp));


		if (array_key_exists('net_nemein_internalorders_pricelist_update', $_POST))
		{
			 // TODO: Submit pressed, handle saving of the form and possible subevent/eventmember creation

			// Save fields of the actual order

			if (array_key_exists('net_nemein_internalorders_pricelist_approve', $_POST) && $_POST['net_nemein_internalorders_pricelist_approve'] == 1)
			{
			 	$this->_request_data['event']->type = NET_NEMEIN_INTERNALORDERS_RECEIVED;
			 	$this->_request_data['event']->end = time();
			}
			else
			{
			 	$this->_request_data['event']->type = NET_NEMEIN_INTERNALORDERS_SENT_LOCKED;
			}

			$this->_request_data['event']->parameter('net.nemein.internalorders', 'receivenotes', $_POST['net_nemein_internalorders_receivenotes']);

			$stat = $this->_request_data['event']->update();
			if (!$stat)
			{
				$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to update order: ".mgd_errstr());
			}

			 // Handle the multiple products inside the order
			foreach ($_POST['net_nemein_internalorders_product'] as $guid => $product)
			{
				// Existing product, update
				$event2 = mgd_get_object_by_guid($guid);
				if (   !$event2
					|| $event2->up != $this->_request_data['event']->id)
				{
					$_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Tried to update wrong object!');
				}

				$stat = $event2->update();
				if (!$stat)
				{
					$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to update product {$event2->title}: ".mgd_errstr());
				}
				$event2->parameter('net.nemein.internalorders', 'quantity_received', $product['quantity_received']);
				$event2->parameter('net.nemein.internalorders', 'additional', $product['additional']);

				if ($event2->parameter('net.nemein.internalorders', 'quantity_received') != $event2->parameter('net.nemein.internalorders', 'quantity'))
				{
					$is_unclear = true;
				}
			}

			if ($is_unclear && $this->_request_data['event']->type == NET_NEMEIN_INTERNALORDERS_RECEIVED)
			{
				$to_user = $this->_request_data['event']->creator;
				$from_user = $this->_request_data['event']->extra;
				$receivenotes = $this->_request_data['event']->parameter('net.nemein.internalorders', 'receivenotes');
				$this->_send_mail_about_unclear_order($this->_request_data['event']->guid, $to_user, $from_user, $receivenotes);
			}

		 	$_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
		}


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

	function _show_receive($handler_id, &$data)
	{
		midcom_show_style('receive_order');
	}

}
?>