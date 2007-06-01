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
class net_nemein_internalorders_handler_edit extends midcom_baseclasses_components_handler
{

	/**
	 * The root event to use with this topic.
	 * 
	 * @var midcom_baseclasses_database_event
	 * @access private
	 */
	var $_root_event = null;

	function net_nemein_internalorders_handler_edit()
	{
		parent::midcom_baseclasses_components_handler();
	}

	function _on_initialize()
	{
		if (is_null($this->_config->get('root_event')))
		{
			$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Component is not properly initialized, root event missing");
		}
	
		$this->_root_event = mgd_get_object_by_guid($this->_config->get('root_event'));
		if (!$this->_root_event)
		{
			$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Root event not found: ".mgd_errstr());
		}
	}
	
	function _send_mail_about_new_order($guid_for_order, $to_id, $from_id)
	{
		$_MIDCOM->componentloader->load_graceful('org.openpsa.mail');
//		ini_set('error_reporting', E_ALL & ~E_NOTICE);
		$email_to_person = new midcom_db_person($to_id);
		$email_from_person = new midcom_db_person($from_id);
		
		$email_to = $email_to_person->email;
		
		$mail_to = $email_to;
		$mail_from = 'sisainen.siirto@anttila.fi';
		$subject = "Teille on uusi sisäisen siirron lomake";


		$body = "Teille on tullut käsiteltäväksi uusi sisäisen siirron lomake..\n\n";
		$body .= "Lähettäjänä: ".$email_from_person->firstname." ".$email_from_person->lastname."\n\n";
		$body .= $GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX).'receive/'.$guid_for_order;

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
	
	function _handler_edit($handler_id, $args, &$data)
	{

		$_MIDCOM->componentloader->load_graceful('org.openpsa.products');
		$nap2 =& new midcom_helper_nav();
		$node = $nap2->get_node($nap2->get_current_node());
//		$this->_request_data['products_topic'] = $node[MIDCOM_NAV_OBJECT];
		
		$known_products = true;
		$hasSamplesInText = false;

		$event = mgd_get_object_by_guid($args[0]);
		

		global $statuserrors;
		$statuserrors = "";
		global $statuserrors2;
		$statuserrors = "";
		global $statuserrors_focus;
		$statuserrors_focus = "";
		if (	!$event
			|| $event->up != $this->_root_event->id)
		{
			// Wrong kind of event
			return false;
		}
		
		if($event->type == NET_NEMEIN_INTERNALORDERS_SENT)
		{
			if ($event->extra == $_MIDGARD['user'])
			{
		 		$GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX).'receive/'.$event->guid().'.html');
		 	}
		 	else
		 	{
		 		$GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX).'view/'.$event->guid().'.html');
		 	}
		}
		

		$tmp[] = Array
		(
			MIDCOM_NAV_URL => 'edit/'.$args[0].'/',
			MIDCOM_NAV_NAME => 'Muokkaa lähetettä nro '.$event->title,
		);
	
		$_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', array_reverse($tmp));

		
		$this->_request_data['event'] = $event;
		
		
		
		if (array_key_exists('net_nemein_internalorders_pricelist_update', $_POST) || (array_key_exists('net_nemein_internalorders_pricelist_refresh', $_POST) && $_POST['net_nemein_internalorders_pricelist_refresh'] == '1'))
		{
			 // TODO: Submit pressed, handle saving of the form and possible subevent/eventmember creation

			// Save fields of the actual order
			$this->_request_data['event']->extra = $_POST['net_nemein_internalorders_receiver'];
			if (array_key_exists('net_nemein_internalorders_reason_1', $_POST) && array_key_exists('net_nemein_internalorders_reason_2', $_POST))
			{
				$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_2', $_POST['net_nemein_internalorders_reason_2']);
				$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_1', $_POST['net_nemein_internalorders_reason_1']);
				if(array_key_exists('net_nemein_internalorders_reason_3', $_POST) && ($_POST['net_nemein_internalorders_reason_2'] == 1 || $_POST['net_nemein_internalorders_reason_2'] == 4))
				{
					$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_3', $_POST['net_nemein_internalorders_reason_3']);
				}
				else
				{
					$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_3', '');
				}
			}
			else
			{
				if(array_key_exists('net_nemein_internalorders_reason_2', $_POST))
				{
					$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_2', $_POST['net_nemein_internalorders_reason_2']);
					if(array_key_exists('net_nemein_internalorders_reason_3', $_POST) && ($_POST['net_nemein_internalorders_reason_2'] == 1 || $_POST['net_nemein_internalorders_reason_2'] == 4))
					{
						$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_3', $_POST['net_nemein_internalorders_reason_3']);
					}
					else
					{
						$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_3', '');
					}
				}
				if(array_key_exists('net_nemein_internalorders_reason_1', $_POST))
				{
					$this->_request_data['event']->parameter('net.nemein.internalorders', 'reason_1', $_POST['net_nemein_internalorders_reason_1']);
				}
			}
			
			$this->_request_data['event']->parameter('net.nemein.internalorders', 'packing', $_POST['net_nemein_internalorders_packing']);
			 
			$this->_request_data['event']->parameter('net.nemein.internalorders', 'packer', $_POST['net_nemein_internalorders_packer']);
			if($_POST['net_nemein_internalorders_colls'])
			{
				$this->_request_data['event']->parameter('net.nemein.internalorders', 'colls', $_POST['net_nemein_internalorders_colls']);
			}
			else
			{
				$this->_request_data['event']->parameter('net.nemein.internalorders', 'colls', '');
			}
			$this->_request_data['event']->parameter('net.nemein.internalorders', 'm3', $_POST['net_nemein_internalorders_m3']);
			$this->_request_data['event']->parameter('net.nemein.internalorders', 'sendentry', $_POST['net_nemein_internalorders_sendentry']);
			 
			
			 
			$stat = $this->_request_data['event']->update();
			if (!$stat)
			{
				$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to update order: ".mgd_errstr());
			}
			 
			 // Handle the multiple products inside the order
			foreach ($_POST['net_nemein_internalorders_product'] as $guid => $product)
			{
				if (mgd_is_guid($guid))
				{
					// Existing product, update
					$event = mgd_get_object_by_guid($guid);
					if (	!$event
						|| $event->up != $this->_request_data['event']->id)
					{
						$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 'Tried to update wrong object!');
					}
					if (strlen($product['value']) == 7)
					{
						$QB = org_openpsa_products_product_dba::new_query_builder();
						$QB->add_constraint('code', '=', $product['value']);
						$products = $QB->execute();

//						$article = mgd_get_article_by_name($this->_request_data['products_topic']->id, $product['value']);
						if ($products)
						{
							if (array_key_exists('remove', $product))
							{
								$parameters = $event->listparameters();
								if ($parameters)
								{
									while ($parameters->fetch())
									{
										$parameter = $event->listparameters($parameters->domain);
										while ($parameter->fetch())
										{
											$event->parameter($parameter->domain, $parameter->name, '');
										}
									}
								}
								$stat = $event->delete();
//								echo $stat."::".mgd_errstr()."\n\n";
								if (!$stat)
								{
									$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to delete product {$event->title}: ".mgd_errstr());
								}
								else
								{
									continue;
								}
							}
							$event->title = $products[0]->title;
							$event->extra = $products[0]->code;
							$stat = $event->update();
							if (!$stat)
							{
								$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to update product {$event->title}: ".mgd_errstr());
							}
							$event->parameter('net.nemein.internalorders', 'salesprice', $products[0]->price);
							$event->parameter('net.nemein.internalorders', 'quantity', $product['quantity']);
							if ($product['quantity'] == "")
							{
								$statuserrors .= 'M&auml;&auml;rit&auml; tuotteen <strong>'.$products[0]->title.'</strong> siirrett&auml;v&auml; m&auml;&auml;r&auml;<br />'."\n";
								$statuserrors2 .= 'Määritä tuotteen '.$products[0]->title.' siirrettävä määrä\n';
								if($statuserrors_focus == "" )
								{
									$statuserrors_focus = "prods_quant_".$guid;
								}
							}
							$tmpSum = 0;
							$tmpSum = floatval($products[0]->price) * floatval($product['quantity']);
							$event->parameter('net.nemein.internalorders', 'sum', $tmpSum);
						}
						else
						{
							$statuserrors .= "Virheellinen tuotekoodi --> ".$product['value']."<br />\n";
							$statuserrors2 .= 'Virheellinen tuotekoodi --> '.$product['value'].'\n';
//							$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to update product ".$product['value'].": ".mgd_errstr());
						}
					}
					elseif (strlen($product['value']) == 5 || strlen($product['value']) == 4 || strlen($product['value']) == 6 || $product['value'] =='näyte' )
					{
						if (array_key_exists('remove', $product))
						{
							$parameters = $event->listparameters();
							if ($parameters)
							{
								while ($parameters->fetch())
								{
									$parameter = $event->listparameters($parameters->domain);
									while ($parameter->fetch())
									{
										$event->parameter($parameter->domain, $parameter->name, '');
									}
								}
							}
							$stat = $event->delete();
//							echo $stat."::".mgd_errstr()."\n\n";
							if (!$stat)
							{
								$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to delete product {$event->title}: ".mgd_errstr());
							}
							else
							{
								continue;
							}
						}
					
						if(!is_numeric($product['value']))
						{ $hasSamplesInText = true; }
						
						$event->title = $product['title'];
						$event->extra = $product['value'];
						$stat = $event->update();
						if (!$stat)
						{
							$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to update product {$event->title}: ".mgd_errstr());
						}
						$event->parameter('net.nemein.internalorders', 'salesprice', $product['salesprice']);
						$event->parameter('net.nemein.internalorders', 'quantity', $product['quantity']);
						if ($product['quantity'] == "")
						{
							$statuserrors .= 'M&auml;&auml;rit&auml; tuotteen <strong>'.$product['title'].'</strong> siirrett&auml;v&auml; m&auml;&auml;r&auml;<br />'."\n";
							$statuserrors2 .= 'Määritä tuotteen '.$product['title'].' siirrettävä määrä\n';
							if($statuserrors_focus == "" )
							{
								$statuserrors_focus = "prods_quant_".$guid;
							}
						}
						if ($product['salesprice'] == "")
						{
							$statuserrors .= 'M&auml;&auml;rit&auml; tuotteen <strong>'.$product['title'].'</strong> myyntihinta<br />'."\n";
							$statuserrors2 .= 'Määrritä tuotteen '.$product['title'].' myyntihinta\n';
							if($statuserrors_focus == "" )
							{
								$statuserrors_focus = "prods_price_".$guid;
							}
						}
						$tmpSum = 0;
						$tmpSum = floatval($product['salesprice']) * floatval($product['quantity']);
						$event->parameter('net.nemein.internalorders', 'sum', $tmpSum);
						

					}
				}
				elseif (!array_key_exists('remove', $product))
				{
					// New product, create
					if (strlen($product['value']) == 7)
					{
						$QB = org_openpsa_products_product_dba::new_query_builder();
						$QB->add_constraint('code', '=', $product['value']);
						$products = $QB->execute();

						if ($products)
						{
							$event = mgd_get_event();
							$event->up = $this->_request_data['event']->id;
							$event->title = $products[0]->title;
							$event->extra = $products[0]->code;
							$stat = $event->create();
							if (!$stat)
							{
								$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to create product ".$product['value'].": ".mgd_errstr());
							}
							$event = mgd_get_event($event->id);
							$event->parameter('net.nemein.internalorders', 'salesprice', $products[0]->price);
							if(isset($product['quantity']))
							{
								$event->parameter('net.nemein.internalorders', 'quantity', $product['quantity']);
							}
							if (!isset($product['quantity']) || $product['quantity'] == "")
							{
								$statuserrors .= 'M&auml;&auml;rit&auml; tuotteen <strong>'.$products[0]->title.'</strong> siirrett&auml;v&auml; m&auml;&auml;r&auml;<br />'."\n";
								$statuserrors2 .= 'Määritä tuotteen '.$products[0]->title.' siirrettävä määrä\n';
								if($statuserrors_focus == "" )
								{
									$statuserrors_focus = "prods_quant_".$event->guid();
								}
							}
							if(isset($product['quantity']))
                                                        {
								$tmpSum = 0;
								$tmpSum = floatval($products[0]->price) * floatval($product['quantity']);
								$event->parameter('net.nemein.internalorders', 'sum', $tmpSum);
								$event->parameter('net.nemein.internalorders', 'quantity_received', 0);
							}
						}
						else
						{
							$event = mgd_get_event();
							$event->up = $this->_request_data['event']->id;
							$event->title = "Näyte";
							$event->extra = "Näyte";
							$stat = $event->create();
							$event->parameter('net.nemein.internalorders', 'quantity', $product['quantity']);
							$statuserrors .= "Virheellinen tuotekoodi --> ".$product['value']."<br />\n";
							$statuserrors2 .= 'Virheellinen tuotekoodi --> '.$product['value'].'\n';
						}
					}
					elseif (strlen($product['value']) == 5 || strlen($product['value']) == 4)
					{
					    if(!is_numeric($product['value']))
						{ $hasSamplesInText = true; }
						
						
						$known_products = false;
						$event = mgd_get_event();
						$event->up = $this->_request_data['event']->id;
						$event->title = "Uusi näyte";
						$event->extra = $product['value'];
						$stat = $event->create();
						if (!$stat)
						{
							$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to create product ".$product['value'].": ".mgd_errstr());
						}
							$event = mgd_get_event($event->id);
						$event->parameter('net.nemein.internalorders', 'quantity', $product['quantity']);
						if ($product['quantity'] == "")
						{
							$statuserrors .= 'M&auml;&auml;rit&auml; tuotteen <strong>'.$event->title.'</strong> siirrett&auml;v&auml; m&auml;&auml;r&auml;<br />'."\n";
							$statuserrors2 .= 'Määritä tuotteen '.$event->title.' siirrettävä määrä\n';
							if($statuserrors_focus == "" )
							{
								$statuserrors_focus = "prods_quant_".$event->guid();
							}
						}

						$event->parameter('net.nemein.internalorders', 'quantity_received', 0);
					}
					elseif((strstr($product['value'], 'yte') && strlen($product['value']) == 6))
					{
					    if(strstr($product['value'], 'yte'))
						{ $hasSamplesInText = true; }
						$known_products = false;
						$event = mgd_get_event();
						$event->up = $this->_request_data['event']->id;
						$event->title = "Uusi näyte";
						$event->extra = $product['value'];
						$stat = $event->create();
						if (!$stat)
						{
							$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to create product ".$product['value'].": ".mgd_errstr());
						}
							$event = mgd_get_event($event->id);
						$event->parameter('net.nemein.internalorders', 'quantity', $product['quantity']);
						if ($product['quantity'] == "")
						{
							$statuserrors .= 'M&auml;&auml;rit&auml; tuotteen <strong>'.$event->title.'</strong> siirrett&auml;v&auml; m&auml;&auml;r&auml;<br />'."\n";
							$statuserrors2 .= 'Määritä tuotteen '.$event->title.' siirrettävä määrä\n';
							if($statuserrors_focus == "" )
							{
								$statuserrors_focus = "prods_quant_".$event->guid();
							}
						}

						$event->parameter('net.nemein.internalorders', 'quantity_received', 0);
					}
				}
			}
			
			if (array_key_exists('net_nemein_internalorders_pricelist_approve', $_POST) && $_POST['net_nemein_internalorders_pricelist_approve'] == 1 && $known_products)
			{
				if(!$_POST['net_nemein_internalorders_colls'])
				{
					$statuserrors .= "T&auml;yt&auml; kolliluku<br />\n";
					$statuserrors2 .= 'Täytä kolliluku\n';
					if($statuserrors_focus == "" )
					{
						$statuserrors_focus = "cols";
					}
				}


				if(!array_key_exists('net_nemein_internalorders_reason_2', $_POST))
				{
					$statuserrors .= "M&auml;&auml;rit&auml; siirron syy.<br />\n";
					$statuserrors2 .= 'Määritä siirron syy\n';
					if($statuserrors_focus == "" )
					{
						$statuserrors_focus = "reason_2";
					}
				}
				if(!array_key_exists('net_nemein_internalorders_reason_1', $_POST))
				{
					$statuserrors .= "M&auml;&auml;rit&auml; mist&auml; mihin siirto kohdentuu.<br />\n";
					$statuserrors2 .= 'Määritä mistä mihin siirto kohdentuu\n';
					if($statuserrors_focus == "" )
					{
						$statuserrors_focus = "reason_1";
					}
				}
				
				if ($_POST['net_nemein_internalorders_receiver'] == "XX")
				{
					$statuserrors .= "Virheellinen vastaanottaja<br />\n";
					$statuserrors2 .= 'Virheellinen vastaanottaja\n';
					if($statuserrors_focus == "" )
					{
						$statuserrors_focus = "receiver";
					}
				}
				elseif ($hasSamplesInText)
				{
					$statuserrors .= "M&auml;&auml;rit&auml; n&auml;yte-tuotteille tuoteryhm&auml; ja alaryhm&auml; tunnukseksi<br />\n";
					$statuserrors2 .= 'Määritä näyte-tuotteille tuoteryhmä ja alaryhmä tunnukseksi\n';
				}
				elseif ($statuserrors == "")
				{
					$this->_request_data['event']->type = NET_NEMEIN_INTERNALORDERS_SENT;
					$this->_request_data['event']->parameter('net.nemein.internalorders', 'senddate', time());
					$stat = $this->_send_mail_about_new_order($this->_request_data['event']->guid, $this->_request_data['event']->extra, $_MIDGARD['user']);
				}
			}
			$stat = $this->_request_data['event']->update();
			if (!$stat)
			{
				$GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Failed to update order: ".mgd_errstr());
			}
			
			if ($statuserrors == "" && $known_products && (array_key_exists('net_nemein_internalorders_pricelist_refresh', $_POST) && $_POST['net_nemein_internalorders_pricelist_refresh'] != '1'))
			{
				if (array_key_exists('net_nemein_internalorders_pricelist_approve', $_POST) && $_POST['net_nemein_internalorders_pricelist_approve'] == 1 && $known_products)
				{
				 	$GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."print/".$args[0].".html");
				}
				else
				{
				 	$GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
				}
		 	}
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
					'remove' => "",
				);
			}
		}
		
		$GLOBALS['midcom']->set_pagetitle(sprintf($this->_request_data['l10n']->get('edit %s'), $this->_request_data['event']->title));
		return true;
	}

	function _show_edit($handler_id, &$data)
	{
		$this->_request_data['config'] =& $this->_config;
		midcom_show_style('edit_order');
	}
}
?>
	
