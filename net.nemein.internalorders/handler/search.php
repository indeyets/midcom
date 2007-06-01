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
class net_nemein_internalorders_handler_search extends midcom_baseclasses_components_handler
{

	/**
	 * The root event to use with this topic.
	 * 
	 * @var midcom_baseclasses_database_event
	 * @access private
	 */
	var $_root_event = null;

	function net_nemein_internalorders_handler_search()
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
	
	function _handler_search_products($handler_id, $args, &$data)
	{
		$_MIDCOM->componentloader->load_graceful('org.openpsa.products');
		$_MIDCOM->skip_page_style = true;

		if(isset($_GET['search']))
		{
			$this->_request_data['searchinput'] = $_GET['search'];
		}
		else
		{
			$this->_request_data['searchinput'] = "";
		}

		$this->_request_data['products'] = array();
		$this->_request_data['products2'] = array();
		$this->_request_data['tr'] = array();
		$this->_request_data['ar'] = array();
		
		$QB = org_openpsa_products_product_dba::new_query_builder();
		$QB->begin_group('OR');
		$QB->add_constraint('code', 'LIKE', '%'.trim($this->_request_data['searchinput']).'%');
		$QB->add_constraint('title', 'LIKE', '%'.trim($this->_request_data['searchinput']).'%');
		$QB->end_group();
		$QB->add_order('code', 'ASC');
		$orders = $QB->execute();
		$this->_request_data['products'] = $orders;

		$QB2 = org_openpsa_products_product_group_dba::new_query_builder();
		$QB2->add_constraint('up', '=', '0');
		$QB2->add_order('code', 'ASC');
		$orders = $QB2->execute();
		$this->_request_data['tr'] = $orders;


		if (strlen($this->_request_data['searchinput'])>0)
		{
			$QB2 = org_openpsa_products_product_group_dba::new_query_builder();
			$QB2->add_constraint('code', '=', $this->_request_data['searchinput']);
			$QB2->add_order('code', 'ASC');
			$orders_tmp = $QB2->execute();
			if ($orders_tmp)
			{
				$QB3 = org_openpsa_products_product_group_dba::new_query_builder();
				$QB3->add_constraint('up', '=', $orders_tmp[0]->id);
				$QB3->add_order('code', 'ASC');
				$orders3 = $QB3->execute();
				$this->_request_data['ar'] = $orders3;
			}
			if ((strlen($this->_request_data['searchinput']) > 2 && strlen($this->_request_data['searchinput']) < 6) && is_numeric($this->_request_data['searchinput']))
			{
				$QB_prod_TR = org_openpsa_products_product_group_dba::new_query_builder();
				$QB_prod_TR->add_constraint('code', '=', substr($this->_request_data['searchinput'], 0,3));
				$QB_prod_TR->add_order('code', 'ASC');
				$orders_prod_TR = $QB_prod_TR->execute();
				foreach($orders_prod_TR as $orders_prod_TR_2)
				{
					$QB_prod_AR = org_openpsa_products_product_group_dba::new_query_builder();
					$QB_prod_AR->add_constraint('up', '=', $orders_prod_TR_2->id);
					if(strlen(substr($this->_request_data['searchinput'], 3,2) > 0))
					{
						$QB_prod_AR->add_constraint('code', '=', substr($this->_request_data['searchinput'], 3,2));
					}
					$QB_prod_AR->add_order('code', 'ASC');
					$orders_prod_AR = $QB_prod_AR->execute();
					$orders_prod_AR_count = $QB_prod_AR->count();
					$this->_request_data['ar'] = $orders_prod_AR;
					foreach($orders_prod_AR as $orders_prod_AR_2)
					{
						$QB = org_openpsa_products_product_dba::new_query_builder();
						$QB->add_constraint('productGroup', '=', $orders_prod_AR_2->id);
						$QB->add_order('code', 'ASC');
						$orders = $QB->execute();
						if($orders_prod_AR_count > 0)
						{
							$this->_request_data['products2'][$orders_prod_AR_2->id] = $orders;
						}
					}
				}
			}
		}

		
		return true;
	}

	function _show_search_products($handler_id, &$data)
	{
		midcom_show_style('search_products');
	}
	
}
?>