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
class net_nemein_internalorders_handler_products extends midcom_baseclasses_components_handler
{

	/**
	 * The root event to use with this topic.
	 * 
	 * @var midcom_baseclasses_database_event
	 * @access private
	 */
	var $_root_event = null;

	function net_nemein_internalorders_handler_products()
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
	
	

	function _handler_products_get_from_csv($handler_id, $args, &$data)
	{

		$fp = fopen('/tmp/Tuotteet_1000.csv', 'r');
		$i=0;
		while ($csvline = fgetcsv($fp, 1024, ';', '"'))
		{
			$i++;
			if ($i<3)
			{
				continue;
			}


			$QB_product = org_openpsa_products_product_dba::new_query_builder();
			$QB_product->add_constraint('code', '=', $csvline[6]);
			$QB_product->add_order('code', 'ASC');
			$tmp_product = $QB_product->execute();
			$tmp_product_count = $QB_product->count();
			//product exists, updating
			if($tmp_product_count > 0)
			{
				if($csvline[1] == '0')
				{
					$csvline[1] == '00';
				}
				$QB_root = org_openpsa_products_product_group_dba::new_query_builder();
				$QB_root->add_constraint('up', '=', '0');
				$QB_root->add_constraint('code', '=', $csvline[1]);
				$QB_root->add_order('code', 'ASC');
				$root_group = $QB_root->execute();
				$root_group_count = $QB_root->count();
				
				if($root_group_count > 0)
				{
					$QB_child = org_openpsa_products_product_group_dba::new_query_builder();
					$QB_child->add_constraint('up', '=', $root_group[0]->id);
					$QB_child->add_constraint('code', '=', $csvline[2]);
					$QB_child->add_order('code', 'ASC');
					$child_group = $QB_child->execute();
					$child_group_count = $QB_child->count();
				}
				else
				{
					continue;
				}
				if($child_group_count == 0)
				{
					continue;
				}
				$tmp_product = new org_openpsa_products_product_dba($tmp_product[0]->id);
				$tmp_product->code = $csvline[6];
				$tmp_product->title = $csvline[7];
				$tmp_product->price = $csvline[10];
				$tmp_product->productGroup = $child_group[0]->id;
			
				if($csvline[12] == '0')
				{
					$tmp_product->unit = N_N_INTERNALORDERS_PRODUCT_ACTIVE;
				}
				elseif($csvline[12] == '1')
				{
					$tmp_product->unit = N_N_INTERNALORDERS_PRODUCT_TOBEREMOVED;
				}
				else
				{
					$tmp_product->unit = N_N_INTERNALORDERS_PRODUCT_REMOVED;
				}
				echo "UPDATING PRODUCT<br />";
				$stat = $tmp_product->update();
			
			
				if (!$stat)
				{
					$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to update order: ".mgd_errstr());
				}
			}
			//it's a new product
			else
			{
			
				$QB_root = org_openpsa_products_product_group_dba::new_query_builder();
				$QB_root->add_constraint('up', '=', '0');
				$QB_root->add_constraint('code', '=', $csvline[1]);
				$QB_root->add_order('code', 'ASC');
				$root_group = $QB_root->execute();
				$root_group_count = $QB_root->count();
				
				if($root_group_count > 0)
				{
					$QB_child = org_openpsa_products_product_group_dba::new_query_builder();
					$QB_child->add_constraint('up', '=', $root_group[0]->id);
					$QB_child->add_constraint('code', '=', $csvline[2]);
					$QB_child->add_order('code', 'ASC');
					$child_group = $QB_child->execute();
					$child_group_count = $QB_child->count();
				}
				else
				{
					continue;
				}
				
				if($child_group_count == 0)
				{
					continue;
				}
			
				$product = new org_openpsa_products_product_dba();

				$product->title = $csvline[7];
				$product->code = $csvline[6];
				$product->price = $csvline[10];
				$product->productGroup = $child_group[0]->id;
			
				if($csvline[12] == '0')
				{
					$tmp_product->unit = N_N_INTERNALORDERS_PRODUCT_ACTIVE;
				}
				elseif($csvline[12] == '1')
				{
					$tmp_product->unit = N_N_INTERNALORDERS_PRODUCT_TOBEREMOVED;
				}
				else
				{
					$tmp_product->unit = N_N_INTERNALORDERS_PRODUCT_REMOVED;
				}

				echo "CREATING PRODUCT<br />";
				$stat = $product->create();
			
				if (!$stat)
				{
					$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create order: ".mgd_errstr());
				}
			}
			
		}
			
				
		return true;
	}

	function _show_products_get_from_csv($handler_id, &$data)
	{
			midcom_show_style('show_products');
	}


	function _handler_groups_get_from_csv($handler_id, $args, &$data)
	{

		$nap2 =& new midcom_helper_nav();
		$node = $nap2->get_node($nap2->get_current_node());


		if (isset($_FILES['net_nemein_internalorders_groups_upload']))
		{
			if(is_uploaded_file($_FILES['net_nemein_internalorders_groups_upload']['tmp_name']))
			{
				$groups_raw = file_get_contents($_FILES['net_nemein_internalorders_groups_upload']['tmp_name']);
				$groups = explode("\n", $groups_raw);
				if (count($groups) > 0)
				{
					$i=0;
					foreach ($groups as $group)
					{
						$i++;
						if ($i<2)
						{
							continue;
						}
						$group_tmp = explode(";", $group);
/*						echo "<pre>";
						print_r($group_tmp);
						echo "</pre>";*/
						
						if ($group_tmp[0] == '0')
						{
							$group_tmp[0] = '00';
						}
						
						$QB_root = org_openpsa_products_product_group_dba::new_query_builder();
						$QB_root->add_constraint('up', '=', '0');
						$QB_root->add_constraint('code', '=', $group_tmp[0]);
						$QB_root->add_order('code', 'ASC');
						$root_group = $QB_root->execute();
						$root_group_count = $QB_root->count();
/*						echo "<pre>";
						print_r($root_group);
						echo "\n\n";
						print_r($root_group_count);
						echo "</pre>";
*/
						
						if($root_group_count == 0)
						{
							echo "CREATING ROOTGROUP<br />";
							//We create a new top-level product-group							
							$group_create = new org_openpsa_products_product_group_dba();
							$group_create->up = 0;
							$group_create->code = $group_tmp[0];
							$group_create->title = $group_tmp[1];
							$group_create->create();
						}
						else
						{
							echo "UPDATING ROOTGROUP<br />";
							$group_update = new org_openpsa_products_product_group_dba($root_group[0]->id);
							$group_update->up = 0;
							$group_update->code = $group_tmp[0];
							$group_update->title = $group_tmp[1];
							$group_update->update();
						}
						$QB_root = org_openpsa_products_product_group_dba::new_query_builder();
						$QB_root->add_constraint('up', '=', '0');
						$QB_root->add_constraint('code', '=', $group_tmp[0]);
						$QB_root->add_order('code', 'ASC');
						$root_group = $QB_root->execute();
						$root_group_count = $QB_root->count();
						if ($root_group_count > 0)
						{
							//We have top-level group, now we insert a subgroup
							$QB_child = org_openpsa_products_product_group_dba::new_query_builder();
							$QB_child->add_constraint('up', '=', $root_group[0]->id);
							$QB_child->add_constraint('code', '=', $group_tmp[2]);
							$QB_child->add_order('code', 'ASC');
							$child_group = $QB_child->execute();
							$child_group_count = $QB_child->count();
/*
							echo "<pre>";
							print_r($child_group);
							echo "\n\n";
							print_r($child_group_count);
							echo "</pre>";
*/

							if($child_group_count == 0)
							{
								echo "CREATING SUBGROUP<br />";
								//We create a new top-level product-group							
								$sub_group_create = new org_openpsa_products_product_group_dba();
								$sub_group_create->up = $root_group[0]->id;
								$sub_group_create->code = $group_tmp[2];
								$sub_group_create->title = $group_tmp[3];
								$sub_group_create->create();
							}
							else
							{
								echo "UPDATING SUBGROUP<br />";
								$sub_group_update = new org_openpsa_products_product_group_dba($child_group[0]->id);
								$sub_group_update->up = $root_group[0]->id;
								$sub_group_update->code = $group_tmp[2];
								$sub_group_update->title = $group_tmp[3];
								$sub_group_update->update();
							}
						}
						else
						{
							echo "<pre>";
							print_r($root_group);
							echo "</pre>";
						}
					}
				}
			}
		}

		return true;
	}




	function _show_groups_get_from_csv($handler_id, &$data)
	{
		midcom_show_style('show_groups_tmp');
	}

	
}
?>